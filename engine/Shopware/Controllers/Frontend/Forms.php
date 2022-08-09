<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

use Doctrine\ORM\AbstractQuery;
use Shopware\Bundle\CartBundle\CartKey;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\OrderNumberValidator\Exception\InvalidOrderNumberException;
use Shopware\Components\OrderNumberValidator\OrderNumberValidatorInterface;
use Shopware\Components\Privacy\IpAnonymizerInterface;
use Shopware\Components\Random;
use Shopware\Components\Validator\EmailValidator;
use Shopware\Models\Form\Form;

/**
 * Shopware Frontend Controller for the form module
 */
class Shopware_Controllers_Frontend_Forms extends Enlight_Controller_Action
{
    /**
     * Contains the validated post data
     *
     * @var array
     */
    public $_postData;

    /**
     * Contains the errors
     *
     * @var array
     */
    public $_errors;

    /**
     * Contains the form elements
     *
     * @var array
     */
    protected $_elements;

    /**
     * Render form - onSubmit checkFields -
     *
     * @throws Exception
     * @throws DomainException
     * @throws Enlight_Exception
     * @throws Zend_Mail_Exception
     * @throws Enlight_Event_Exception
     *
     * @return void
     */
    public function indexAction()
    {
        $id = $this->Request()->getParam('sFid') ?: $this->Request()->getParam('id');
        $id = (int) $id;

        $view = $this->View();
        $view->assign('forceMail', (int) $this->Request()->getParam('forceMail'));
        $view->assign('id', $id);
        $view->assign('sSupport', $this->getContent($id));
        $view->assign('rand', Random::getAlphanumericString(32));

        $success = (bool) $this->Request()->getParam('success');
        if ($success) {
            $view->assign('sSupport', array_merge($view->getAssign('sSupport'), ['sElements' => []]));
        }

        $this->renderElementNote($view);

        $view->assign('success', $success);

        if ($this->Request()->isPost()) {
            $this->handleFormPost($id);
        }
    }

    /**
     * @throws Enlight_Exception
     * @throws Zend_Mail_Exception
     * @throws Enlight_Event_Exception
     *
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Commit form via email (default) or database (ticket system)
     * Method is extended in SwagTicketSystem
     *
     * @return void
     */
    public function commitForm()
    {
        $mail = $this->get('mail');

        // Email field available check
        foreach ($this->_elements as $element) {
            if ($element['typ'] === 'email') {
                $postEmail = trim($this->_postData[$element['id']]);
            }
        }

        if (!empty($postEmail)) {
            $mail->setReplyTo($postEmail);
        }

        $content = $this->View()->getAssign('sSupport');

        $mailBody = $this->replaceVariables($content['email_template']);
        $mailSubject = $this->replaceVariables($content['email_subject']);

        $receivers = explode(',', $content['email']);
        $receivers = array_map('trim', $receivers);

        $mail->setFrom(Shopware()->Config()->get('Mail'));
        $mail->clearRecipients();
        $mail->addTo($receivers);
        $mail->setBodyText($mailBody);
        $mail->setSubject($mailSubject);

        $mail = Shopware()->Events()->filter('Shopware_Controllers_Frontend_Forms_commitForm_Mail', $mail, ['subject' => $this]);

        if (!$mail->send()) {
            throw new Enlight_Exception('Could not send mail');
        }
    }

    /**
     * @param int $formId
     *
     * @throws Enlight_Exception
     * @throws Exception
     *
     * @return array
     */
    protected function getContent($formId)
    {
        $fields = [];
        $labels = [];

        $shopId = $this->container->get(ContextServiceInterface::class)->getShopContext()->getShop()->getId();

        $modelManager = $this->getModelManager();
        $query = $modelManager->getRepository(Form::class)->getActiveFormQuery($formId, $shopId);

        $form = $query->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);

        if (!$form instanceof Form) {
            throw new Enlight_Controller_Exception(
                'Form not found',
                Enlight_Controller_Exception::Controller_Dispatcher_Controller_Not_Found
            );
        }

        $attributeArray = [];
        if ($form->getAttribute() !== null) {
            $attributeArray = $modelManager->toArray($form->getAttribute());
            $modelManager->detach($form->getAttribute());
        }

        $modelManager->detach($form);
        foreach ($form->getFields() as $field) {
            $modelManager->detach($field);
        }

        foreach ($form->getFields() as $field) {
            $fieldId = $field->getId();
            $this->_elements[$fieldId] = [
                'id' => (string) $fieldId, // intended string cast to keep compatibility
                'name' => $field->getName(),
                'note' => $field->getNote(),
                'typ' => $field->getTyp(),
                'required' => (string) $field->getRequired(),  // intended string cast to keep compatibility
                'label' => $field->getLabel(),
                'class' => $field->getClass(),
                'value' => $field->getValue(),
                'error_msg' => $field->getErrorMsg(),
            ];
        }

        $this->translateForm($form, $this->_elements);

        if ($this->Request()->isPost()) {
            $this->checkFields();
        }

        $orderNumber = $this->Request()->getParam('sOrdernumber');

        if (empty($this->Request()->Submit) || \count($this->_errors)) {
            foreach ($this->_elements as $id => $element) {
                if ($element['name'] === 'sordernumber') {
                    try {
                        $this->get(OrderNumberValidatorInterface::class)->validate($orderNumber);

                        $product = Shopware()->Modules()->Articles()->sGetArticleNameByOrderNumber($orderNumber);

                        $element['value'] = sprintf('%s (%s)', $product, $this->get('shopware.escaper')->escapeHtml($orderNumber));
                        $this->_elements[$id]['value'] = $element['value'];
                    } catch (InvalidOrderNumberException $exception) {
                        // Explicit empty catch
                    } catch (TypeError $exception) {
                        // Explicit empty catch
                    }
                }

                if ($element['name'] === 'inquiry' && !empty($this->Request()->sInquiry)) {
                    switch ($this->Request()->sInquiry) {
                        case 'basket':
                            $text = Shopware()->Snippets()->getNamespace('frontend/detail/comment')->get('InquiryTextBasket');
                            $getBasket = Shopware()->Modules()->Basket()->sGetBasket();
                            foreach ($getBasket[CartKey::POSITIONS] ?? [] as $basketRow) {
                                if (empty($basketRow['modus'])) {
                                    $text .= sprintf(
                                        "\n%s x %s (%s) - %s %s",
                                        $basketRow['quantity'],
                                        $basketRow['articlename'],
                                        $basketRow['ordernumber'],
                                        $basketRow['price'],
                                        Shopware()->System()->sCurrency['currency']
                                    );
                                }
                            }
                            if (!empty($text)) {
                                $this->_elements[$id]['value'] = $text;
                                $element['value'] = $text;
                            }
                            break;

                        case 'detail':
                            if ($orderNumber !== null) {
                                $getName = Shopware()->Modules()->Articles()->sGetArticleNameByOrderNumber($orderNumber);
                                $text = Shopware()->Snippets()->getNamespace('frontend/detail/comment')->get('InquiryTextArticle');
                                $text .= ' ' . $getName . ' (' . htmlentities($orderNumber, ENT_QUOTES | ENT_HTML5) . ')';
                                $this->_elements[$id]['value'] = $text;
                                $element['value'] = $text;
                            }
                            break;
                    }
                }

                $fields[$id] = $this->_createInputElement($element, $this->_postData[$id]);
                $labels[$id] = $this->_createLabelElement($element);
            }
        }

        // prepare form data for view
        $formData = [
            'id' => (string) $form->getId(),  // intended string cast to keep compatibility
            'active' => $form->getActive(),
            'name' => $form->getName(),
            'text' => $form->getText(),
            'text2' => $form->getText2(),
            'email' => $form->getEmail(),
            'email_template' => $form->getEmailTemplate(),
            'email_subject' => $form->getEmailSubject(),
            'metaTitle' => $form->getMetaTitle(),
            'metaDescription' => $form->getMetaDescription(),
            'metaKeywords' => $form->getMetaKeywords(),
            'attribute' => $attributeArray,
        ];

        return array_merge($formData, [
            'sErrors' => $this->_errors,
            'sElements' => $this->_elements,
            'sFields' => $fields,
            'sLabels' => $labels,
        ]);
    }

    /**
     * Create label element
     *
     * @param array $element
     *
     * @return string
     */
    protected function _createLabelElement($element)
    {
        $output = "<label for=\"{$element['name']}\">";
        if ($element['typ'] === 'text2') {
            $output .= str_replace(';', '/', (string) $element['label']);
        } else {
            $output .= (string) $element['label'];
        }
        if ((bool) $element['required'] === true) {
            $output .= '*';
        }
        $output .= ":</label>\r\n";

        return $output;
    }

    /**
     * Create input element method
     *
     * @param string|null $post
     *
     * @return string
     */
    protected function _createInputElement(array $element, $post = null)
    {
        if ((int) $element['required'] === 1) {
            $requiredField = 'is--required required';
            $requiredFieldSnippet = '%*%';
            $requiredFieldAria = 'required="required" aria-required="true"';
        } else {
            $requiredField = '';
            $requiredFieldSnippet = '';
            $requiredFieldAria = '';
        }

        $placeholder = "placeholder=\"{$element['label']}$requiredFieldSnippet\"";

        switch ($element['typ']) {
            case 'password':
            case 'hidden':
            case 'email':
            case 'text':
            case 'textarea':
            case 'file':
                $post = $this->_filterInput($post);
                if (empty($post) && !empty($element['value'])) {
                    $post = $element['value'];
                }

                if ($element['typ'] !== 'textarea') {
                    $post = str_replace('"', '', $post);
                }
                break;

            case 'text2':
                $post[0] = $this->_filterInput($post[0]);
                if (empty($post[0]) && !empty($element['value'][0])) {
                    $post[0] = $element['value'][0];
                }
                $post[0] = str_replace('"', '', $post[0]);

                $post[1] = $this->_filterInput($post[1]);
                if (empty($post[0]) && !empty($element['value'][1])) {
                    $post[1] = $element['value'][1];
                }
                $post[1] = str_replace('"', '', $post[1]);
                break;

            default:
                break;
        }

        $output = '';
        switch ($element['typ']) {
            case 'password':
            case 'hidden':
            case 'email':
            case 'text':
                $output .= sprintf(
                    "<input type=\"%s\" class=\"%s %s\" %s value=\"%s\" id=\"%s\" %s name=\"%s\"/>\r\n",
                    $element['typ'],
                    $element['class'],
                    $requiredField,
                    $requiredFieldAria,
                    $post,
                    $element['name'],
                    $placeholder,
                    $element['name']
                );
                break;

            case 'checkbox':
                if ($post == $element['value']) {
                    $checked = ' checked';
                } else {
                    $checked = '';
                }
                $output .= sprintf(
                    "<input type=\"%s\" class=\"%s %s\" %s value=\"%s\" id=\"%s\" name=\"%s\"%s/>\r\n",
                    $element['typ'],
                    $element['class'],
                    $requiredField,
                    $requiredFieldAria,
                    $element['value'],
                    $element['name'],
                    $element['name'],
                    $checked
                );
                break;

            case 'file':
                $output .= sprintf(
                    "<input type=\"%s\" class=\"%s %s file\" %s id=\"%s\" %s name=\"%s\" maxlength=\"100000\" accept=\"%s\"/>\r\n",
                    $element['typ'],
                    $element['class'],
                    $requiredField,
                    $requiredFieldAria,
                    $element['name'],
                    $placeholder,
                    $element['name'],
                    $element['value']
                );
                break;

            case 'text2':
                $element['class'] = explode(';', $element['class']);
                $element['name'] = explode(';', $element['name']);

                if (strpos($element['label'], ';') !== false) {
                    $placeholders = explode(';', $element['label']);
                    $placeholder0 = sprintf('placeholder="%s%s"', $placeholders[0], $requiredFieldSnippet);
                    $placeholder1 = sprintf('placeholder="%s%s"', $placeholders[1], $requiredFieldSnippet);
                } else {
                    $placeholder0 = $placeholder;
                    $placeholder1 = $placeholder;
                }

                $output .= sprintf(
                    "<input type=\"text\" class=\"%s %s\" %s value=\"%s\" %s id=\"%s\" name=\"%s\"/>\r\n",
                    $element['class'][0],
                    $requiredField,
                    $requiredFieldAria,
                    $post[0],
                    $placeholder0,
                    $element['name'][0],
                    $element['name'][0]
                );
                $output .= sprintf(
                    "<input type=\"text\" class=\"%s %s\" %s value=\"%s\" %s id=\"%s\" name=\"%s\"/>\r\n",
                    $element['class'][1],
                    $requiredField,
                    $requiredFieldAria,
                    $post[1],
                    $placeholder1,
                    $element['name'][1],
                    $element['name'][1]
                );
                break;

            case 'textarea':
                if (empty($post) && $element['value']) {
                    $post = $element['value'];
                }
                $output .= sprintf(
                    "<textarea class=\"%s %s\" %s id=\"%s\" %s name=\"%s\">%s</textarea>\r\n",
                    $element['class'],
                    $requiredField,
                    $requiredFieldAria,
                    $element['name'],
                    $placeholder,
                    $element['name'],
                    $post
                );
                break;

            case 'select':
                $values = explode(';', $element['value']);
                $output .= sprintf(
                    "<select class=\"%s %s\" %s id=\"%s\" name=\"%s\">\r\n\t", $element['class'],
                    $requiredField,
                    $requiredFieldAria,
                    $element['name'],
                    $element['name']
                );

                if (!empty($requiredField)) {
                    $requiredField = 'disabled="disabled"';
                }

                $label = $element['label'] . $requiredFieldSnippet;

                if (empty($post)) {
                    $output .= sprintf('<option selected="selected" %s value="">%s</option>', $requiredField, $label);
                } else {
                    $output .= sprintf('<option %s value="">%s</option>', $requiredField, $label);
                }
                foreach ($values as $value) {
                    if ($value == $post) {
                        $output .= sprintf('<option selected>%s</option>', $value);
                    } else {
                        $output .= sprintf('<option>%s</option>', $value);
                    }
                }
                $output .= "</select>\r\n";
                break;

            case 'radio':
                $values = explode(';', $element['value']);
                foreach ($values as $value) {
                    $checked = '';

                    if ($value == $post) {
                        $checked = ' checked';
                    }

                    $output .= sprintf(
                        '<input type="radio" class="%s %s" value="%s" id="%s" name="%s"%s> %s ',
                        $element['class'],
                        $requiredField,
                        $value,
                        $element['name'],
                        $element['name'],
                        $checked,
                        $value
                    );
                }
                $output .= "\r\n";
                break;
        }

        return $output;
    }

    /**
     * @param string $input
     *
     * @return string
     */
    protected function _filterInput($input)
    {
        // Remove all control characters, unassigned, private use, formatting and surrogate code points
        $input = preg_replace('#[^\PC\s]#u', '', $input);

        $temp = str_replace('"', '', $input);
        if (preg_match('#{\s*/*literal\s*}#i', $temp) > 0) {
            return '';
        }

        return $this->get('shopware.escaper')->escapeHtml($input);
    }

    /**
     * Validate input method
     *
     * Populates $this->_postData
     *
     * @throws Exception
     *
     * @return array
     */
    protected function _validateInput(array $inputs, array $elements)
    {
        $errors = [];

        $emailValidator = $this->container->get(EmailValidator::class);

        foreach ($elements as $element) {
            $valid = true;
            $value = '';
            if ($element['typ'] === 'text2') {
                $value = [];
                $element['name'] = explode(';', $element['name']);
                if (!empty($inputs[$element['name'][0]])) {
                    $value[0] = $inputs[$element['name'][0]];
                }
                if (!empty($inputs[$element['name'][1]])) {
                    $value[1] = $inputs[$element['name'][1]];
                }
            } elseif (!empty($inputs[$element['name']])) {
                $value = $inputs[$element['name']];
            }

            if (!empty($value)) {
                switch ($element['typ']) {
                    case 'date':
                        $values = preg_split('#\D#', $inputs[$element['id']], -1, PREG_SPLIT_NO_EMPTY);
                        if (!\is_array($values)) {
                            unset($value);
                            $valid = false;
                            break;
                        }
                        if (\count($values) !== 3) {
                            unset($value);
                            $valid = false;
                            break;
                        }
                        if (\strlen($values[0]) === 4) {
                            $value = mktime(0, 0, 0, (int) $values[1], (int) $values[2], (int) $values[0]);
                        } else {
                            $value = mktime(0, 0, 0, (int) $values[0], (int) $values[2], (int) $values[1]);
                        }
                        if (empty($value) || ((int) $value === -1)) {
                            unset($value);
                            $valid = false;
                            break;
                        }
                        $value = date('Y-m-d', $value);
                        break;

                    case 'email':
                        $value = strtolower($value);
                        if (!$emailValidator->isValid($value)) {
                            unset($value);
                            $valid = false;
                            break;
                        }
                        $host = trim(substr($value, strpos($value, '@') + 1));
                        if (empty($host) || !gethostbyname($host)) {
                            unset($value);
                            $valid = false;
                        }
                        break;

                    case 'text2':
                        foreach (array_keys($value) as $key) {
                            $value[$key] = trim(strip_tags($value[$key]));
                            if (empty($value[$key])) {
                                unset($value[$key]);
                                $valid = false;
                            }
                            $value = array_values($value);
                        }
                        break;

                    default:
                        $value = trim(strip_tags($value));
                        if (empty($value)) {
                            unset($value);
                            $valid = true;
                            break;
                        }
                        break;
                }
            }
            if ($valid === false && (bool) $element['required'] === true) {
                $errors['v'][] = $element['id'];
                $errors['e'][$element['id']] = true;
            } elseif (empty($value) && (bool) $element['required'] === true) {
                $errors['e'][$element['id']] = true;
            }
            if (isset($value)) {
                $this->_postData[$element['id']] = $value;
            }
        }

        return $errors;
    }

    /**
     * @return Form
     */
    protected function translateForm(Form $form, array &$fields)
    {
        $context = $this->get(ContextServiceInterface::class)->getContext();

        $translation = $this->get(Shopware_Components_Translation::class)->readWithFallback(
            $context->getShop()->getId(),
            $context->getShop()->getFallbackId(),
            'forms',
            $this->View()->getAssign('id')
        );

        if (!empty($translation)) {
            $form->fromArray($translation);
        }

        if (!empty($form->getAttribute())) {
            $translation = $this->get(Shopware_Components_Translation::class)->readWithFallback(
                $context->getShop()->getId(),
                $context->getShop()->getFallbackId(),
                's_cms_support_attributes',
                $this->View()->getAssign('id')
            );

            if (!empty($translation)) {
                $data = [];

                foreach ($translation as $key => $value) {
                    $data[str_replace('__attribute_', '', $key)] = $value;
                }

                $form->getAttribute()->fromArray($data);
            }
        }

        $elementIds = array_keys($fields);

        $fieldTranslations = $this->get(Shopware_Components_Translation::class)->readBatchWithFallback(
            $context->getShop()->getId(),
            $context->getShop()->getFallbackId(),
            'forms_elements',
            $elementIds,
            false
        );

        foreach ($fieldTranslations as $fieldTranslation) {
            $key = $fieldTranslation['objectkey'];
            $translation = $fieldTranslation['objectdata'];

            // If we have another field type selected in the translation and no value is translated, don't use the translation
            if (isset($translation['typ']) && !isset($translation['value']) && $translation['typ'] !== $fields[$key]['typ']) {
                $translation['value'] = '';
            }

            $fields[$key] = $translation + $fields[$key];
        }

        return $form;
    }

    /**
     * @throws Exception
     */
    private function renderElementNote(Enlight_View_Default $view): void
    {
        $rendererService = $this->container->get('shopware.form.string_renderer_service');

        $elements = [];
        foreach ($view->getAssign('sSupport')['sElements'] as $key => &$element) {
            if (empty($element['note'])) {
                $elements[$key] = $element;
                continue;
            }

            $element['note'] = $rendererService->render($element['note'], $view->getAssign(), $element);
            $elements[$key] = $element;
        }
        unset($element);

        $view->assign('sSupport', array_merge($view->getAssign('sSupport'), ['sElements' => $elements]));
    }

    /**
     * @throws Enlight_Exception
     * @throws Zend_Mail_Exception
     * @throws Enlight_Event_Exception
     */
    private function handleFormPost(int $formId): void
    {
        if (\count($this->_errors) || empty($this->Request()->Submit)) {
            return;
        }

        $this->commitForm();

        $this->redirect(
            [
                'controller' => 'forms',
                'action' => 'index',
                'sFid' => $formId,
                'success' => 1,
            ]
        );
    }

    /**
     * Validate input - check form field rules defined by merchant
     * Checks captcha and doing simple blacklist-/spam-check
     *
     * Populates $this->_errors
     * Modifies  $this->_elements
     *
     * @throws Exception
     */
    private function checkFields(): void
    {
        $this->_errors = $this->_validateInput($this->Request()->getPost(), $this->_elements);

        if (!empty(Shopware()->Config()->get('CaptchaColor'))) {
            $captchaValidator = $this->container->get('shopware.captcha.validator');

            if (!$captchaValidator->validate($this->Request())) {
                $this->_elements['sCaptcha']['class'] = ' instyle_error has--error';
                $this->_errors['e']['sCaptcha'] = true;
            }
        }

        if (!empty($this->_errors)) {
            foreach ($this->_errors['e'] as $key => $value) {
                if (isset($this->_errors['e'][$key])) {
                    if ($this->_elements[$key]['typ'] === 'text2') {
                        $class = explode(';', $this->_elements[$key]['class']);
                        $this->_elements[$key]['class'] = implode(' instyle_error has--error;', $class) . ' instyle_error has--error';
                    } else {
                        $this->_elements[$key]['class'] .= ' instyle_error has--error';
                    }
                }
            }
        }
    }

    /**
     * Replaces placeholder variables
     */
    private function replaceVariables(string $content): string
    {
        foreach ($this->_postData as $key => $value) {
            if ($this->_elements[$key]['typ'] === 'text2') {
                $names = explode(';', $this->_elements[$key]['name']);
                $content = str_replace(
                    ['{sVars.' . $names[0] . '}', '{sVars.' . $names[1] . '}'],
                    [$value[0], $value[1]],
                    $content
                );
            } else {
                $content = str_replace('{sVars.' . $this->_elements[$key]['name'] . '}', $value, $content);
            }
        }

        $ip = $this->get(IpAnonymizerInterface::class)->anonymize($this->Request()->getClientIp());

        $content = str_replace(
            ['{sIP}', '{sDateTime}', '{sShopname}'],
            [$ip, date('d.m.Y h:i:s'), Shopware()->Config()->get('shopName')],
            $content
        );

        return strip_tags($content);
    }
}
