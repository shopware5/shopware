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

use Shopware\Components\OrderNumberValidator\Exception\InvalidOrderNumberException;
use Shopware\Components\Random;
use Shopware\Models\Form\Field;
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
     * @throws \Exception
     * @throws \DomainException
     * @throws \Enlight_Exception
     * @throws \Zend_Mail_Exception
     * @throws \Enlight_Event_Exception
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function indexAction()
    {
        $id = $this->Request()->getParam('sFid');
        $id = $id ?: $this->Request()->getParam('id');

        $this->View()->assign('forceMail', (int) $this->Request()->getParam('forceMail'));
        $this->View()->assign('id', $id);
        $this->View()->assign('sSupport', $this->getContent($id));
        $this->View()->assign('rand', Random::getAlphanumericString(32));

        $success = $this->Request()->getParam('success');
        if ($success) {
            $this->View()->assign('sSupport', array_merge($this->View()->sSupport, ['sElements' => []]));
        }

        $this->renderElementNote($this->View());

        $this->View()->assign('success', $success);

        if ($this->Request()->isPost()) {
            $this->handleFormPost($id);
        }
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Commit form via email (default) or database (ticket system)
     *
     * @throws \Enlight_Exception
     * @throws \Zend_Mail_Exception
     * @throws \Enlight_Event_Exception
     */
    public function commitForm()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        /** @var Enlight_Components_Mail $mail */
        $mail = $this->get('mail');

        // Email field available check
        foreach ($this->_elements as $element) {
            if ($element['typ'] === 'email') {
                $postEmail = $this->_postData[$element['id']];
                $postEmail = trim($postEmail);
            }
        }

        if (!empty($postEmail)) {
            $mail->setReplyTo($postEmail);
        }

        $content = $this->View()->sSupport;

        $mailBody = $this->replaceVariables($content['email_template']);
        $mailSubject = $this->replaceVariables($content['email_subject']);

        $receivers = explode(',', $content['email']);
        $receivers = array_map('trim', $receivers);

        $mail->setFrom(Shopware()->Config()->Mail);
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
     * @throws \Exception
     * @throws \Enlight_Exception
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return array
     */
    protected function getContent($formId)
    {
        $fields = [];
        $labels = [];

        $shopId = $this->container->get('shopware_storefront.context_service')->getShopContext()->getShop()->getId();

        /* @var \Doctrine\ORM\Query $query */
        $query = Shopware()->Models()->getRepository(\Shopware\Models\Form\Form::class)
            ->getActiveFormQuery($formId, $shopId);

        /* @var Form $form */
        $form = $query->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
        $this->getModelManager()->detach($form);

        if (!$form) {
            throw new \Enlight_Controller_Exception(
                'Form not found',
                Enlight_Controller_Exception::Controller_Dispatcher_Controller_Not_Found
            );
        }

        /* @var Field $field */
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

        if (empty($this->Request()->Submit) || count($this->_errors)) {
            foreach ($this->_elements as $id => $element) {
                if ($element['name'] === 'sordernumber') {
                    $orderNumber = $this->Request()->getParam('sOrdernumber');

                    try {
                        $this->get(\Shopware\Components\OrderNumberValidator\OrderNumberValidatorInterface::class)
                            ->validate($orderNumber);

                        $product = Shopware()->Modules()
                            ->Articles()
                            ->sGetArticleNameByOrderNumber($orderNumber, false, true);

                        $element['value'] = sprintf('%s (%s)', $product, $this->get('shopware.escaper')
                            ->escapeHtml($orderNumber));
                        $this->_elements[$id]['value'] = $element['value'];
                    } catch (InvalidOrderNumberException $exception) {
                        // Explicit empty catch
                    } catch (\TypeError $exception) {
                        // Explicit empty catch
                    }
                }

                if ($element['name'] === 'inquiry' && !empty($this->Request()->sInquiry)) {
                    switch ($this->Request()->sInquiry) {
                        case 'basket':
                            $text = Shopware()->Snippets()->getNamespace('frontend/detail/comment')->get('InquiryTextBasket');
                            $getBasket = Shopware()->Modules()->Basket()->sGetBasket();
                            //$text = ''; Fix 100363 / 5416 Thanks to H. Ronecker
                            foreach ($getBasket['content'] as $basketRow) {
                                if (empty($basketRow['modus'])) {
                                    $text .= "\n{$basketRow['quantity']} x {$basketRow['articlename']} ({$basketRow['ordernumber']}) - {$basketRow['price']} " . Shopware()->System()->sCurrency['currency'];
                                }
                            }
                            if (!empty($text)) {
                                $this->_elements[$id]['value'] = $text;
                                $element['value'] = $text;
                            }
                            break;

                        case 'detail':
                            if ($this->Request()->getParam('sOrdernumber') !== null) {
                                $getName = Shopware()->Modules()->Articles()->sGetArticleNameByOrderNumber($this->Request()->getParam('sOrdernumber'));
                                $text = Shopware()->Snippets()->getNamespace('frontend/detail/comment')->get('InquiryTextArticle');
                                $text .= ' ' . $getName;
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
            'attribute' => $this->get('models')->toArray($form->getAttribute()),
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
            $output .= str_replace(';', '/', "{$element['label']}");
        } else {
            $output .= "{$element['label']}";
        }
        if ($element['required'] == 1) {
            $output .= '*';
        }
        $output .= ":</label>\r\n";

        return $output;
    }

    /**
     * Create input element method
     *
     * @param string $post
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
                $output .= "<input type=\"{$element['typ']}\" class=\"{$element['class']} $requiredField\" $requiredFieldAria value=\"{$post}\" id=\"{$element['name']}\" $placeholder name=\"{$element['name']}\"/>\r\n";
                break;

            case 'checkbox':
                if ($post == $element['value']) {
                    $checked = ' checked';
                } else {
                    $checked = '';
                }
                $output .= "<input type=\"{$element['typ']}\" class=\"{$element['class']} $requiredField\" $requiredFieldAria value=\"{$element['value']}\" id=\"{$element['name']}\" name=\"{$element['name']}\"$checked/>\r\n";
                break;

            case 'file':
                $output .= "<input type=\"{$element['typ']}\" class=\"{$element['class']} $requiredField file\" $requiredFieldAria id=\"{$element['name']}\" $placeholder name=\"{$element['name']}\" maxlength=\"100000\" accept=\"{$element['value']}\"/>\r\n";
                break;

            case 'text2':
                $element['class'] = explode(';', $element['class']);
                $element['name'] = explode(';', $element['name']);

                if (strpos($element['label'], ';') !== false) {
                    $placeholders = explode(';', $element['label']);
                    $placeholder0 = "placeholder=\"{$placeholders[0]}$requiredFieldSnippet\"";
                    $placeholder1 = "placeholder=\"{$placeholders[1]}$requiredFieldSnippet\"";
                } else {
                    $placeholder0 = $placeholder;
                    $placeholder1 = $placeholder;
                }

                $output .= "<input type=\"text\" class=\"{$element['class'][0]} $requiredField\" $requiredFieldAria value=\"{$post[0]}\" $placeholder0 id=\"{$element['name'][0]};{$element['name'][1]}\" name=\"{$element['name'][0]}\"/>\r\n";
                $output .= "<input type=\"text\" class=\"{$element['class'][1]} $requiredField\" $requiredFieldAria value=\"{$post[1]}\" $placeholder1 id=\"{$element['name'][0]};{$element['name'][1]}\" name=\"{$element['name'][1]}\"/>\r\n";
                break;

            case 'textarea':
                if (empty($post) && $element['value']) {
                    $post = $element['value'];
                }
                $output .= "<textarea class=\"{$element['class']} $requiredField\" $requiredFieldAria id=\"{$element['name']}\" $placeholder name=\"{$element['name']}\">{$post}</textarea>\r\n";
                break;

            case 'select':
                $values = explode(';', $element['value']);
                $output .= "<select class=\"{$element['class']} $requiredField\" $requiredFieldAria id=\"{$element['name']}\" name=\"{$element['name']}\">\r\n\t";

                if (!empty($requiredField)) {
                    $requiredField = 'disabled="disabled"';
                }

                $label = $element['label'] . $requiredFieldSnippet;

                if (empty($post)) {
                    $output .= "<option selected=\"selected\" $requiredField value=\"\">$label</option>";
                } else {
                    $output .= "<option $requiredField value=\"\">$label</option>";
                }
                foreach ($values as $value) {
                    if ($value == $post) {
                        $output .= "<option selected>$value</option>";
                    } else {
                        $output .= "<option>$value</option>";
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

                    $output .= "<input type=\"radio\" class=\"{$element['class']} $requiredField\" value=\"$value\" id=\"{$element['name']}\" name=\"{$element['name']}\"$checked> $value ";
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
     * @throws \Exception
     *
     * @return array
     */
    protected function _validateInput(array $inputs, array $elements)
    {
        $errors = [];

        /** @var \Shopware\Components\Validator\EmailValidatorInterface $emailValidator */
        $emailValidator = $this->container->get('validator.email');

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
                        $values = preg_split('#[^0-9]#', $inputs[$element['id']], -1, PREG_SPLIT_NO_EMPTY);
                        if (count($values) !== 3) {
                            unset($value);
                            $valid = false;
                            break;
                        }
                        if (strlen($values[0]) === 4) {
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
            if ($valid === false && $element['required'] == 1) {
                $errors['v'][] = $element['id'];
                $errors['e'][$element['id']] = true;
            } elseif (empty($value) && $element['required'] == 1) {
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
        $context = $this->get('shopware_storefront.context_service')->getContext();

        $translation = $this->get('translation')->readWithFallback(
            $context->getShop()->getId(),
            $context->getShop()->getFallbackId(),
            'forms',
            $this->View()->id
        );

        if (!empty($translation)) {
            $form->fromArray($translation);
        }

        if (!empty($form->getAttribute())) {
            $translation = $this->get('translation')->readWithFallback(
                $context->getShop()->getId(),
                $context->getShop()->getFallbackId(),
                's_cms_support_attributes',
                $this->View()->id
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

        $fieldTranslations = $this->get('translation')->readBatchWithFallback(
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
     * @throws \Exception
     */
    private function renderElementNote(Enlight_View_Default $view)
    {
        $rendererService = $this->container->get('shopware.form.string_renderer_service');

        $elements = [];
        foreach ($view->sSupport['sElements'] as $key => &$element) {
            if (empty($element['note'])) {
                $elements[$key] = $element;
                continue;
            }

            $element['note'] = $rendererService->render($element['note'], $view->getAssign(), $element);
            $elements[$key] = $element;
        }

        $view->assign('sSupport', array_merge($view->sSupport, ['sElements' => $elements]));
    }

    /**
     * @param int $formId
     *
     * @throws \Enlight_Exception
     * @throws \Zend_Mail_Exception
     * @throws \Enlight_Event_Exception
     */
    private function handleFormPost($formId)
    {
        if (count($this->_errors) || empty($this->Request()->Submit)) {
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
     * @throws \Exception
     */
    private function checkFields()
    {
        $this->_errors = $this->_validateInput($this->Request()->getPost(), $this->_elements);

        if (!empty(Shopware()->Config()->CaptchaColor)) {
            /** @var \Shopware\Components\Captcha\CaptchaValidator $captchaValidator */
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
                        $this->_elements[$key]['class'] = implode(
                                ' instyle_error has--error;',
                                $class
                            ) . ' instyle_error has--error';
                    } else {
                        $this->_elements[$key]['class'] .= ' instyle_error has--error';
                    }
                }
            }
        }
    }

    /**
     * Replaces placeholder variables
     *
     * @param string $content
     *
     * @return string
     */
    private function replaceVariables($content)
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

        $ip = $this->get('shopware.components.privacy.ip_anonymizer')->anonymize($this->Request()->getClientIp());

        $content = str_replace(
            ['{sIP}', '{sDateTime}', '{sShopname}'],
            [$ip, date('d.m.Y h:i:s'), Shopware()->Config()->shopName],
            $content
        );

        return strip_tags($content);
    }
}
