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

use Shopware\Models\Form\Form;
use Shopware\Models\Form\Field;

/**
 * Shopware Frontend Controller for the form module
 *
 * @category  Shopware
 * @package   Shopware\Controllers\Frontend
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
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
     * @throws Enlight_Exception
     */
    public function indexAction()
    {
        $id = $this->Request()->getParam('sFid');
        $id = ($id) ? $id : $this->Request()->getParam('id');

        $this->View()->forceMail = (int)$this->Request()->getParam('forceMail');
        $this->View()->id        = $id;
        $this->View()->sSupport  = $this->getContent($id);
        $this->View()->rand      = md5(uniqid(mt_rand(), true));

        $success = $this->Request()->getParam('success');
        if ($success) {
            $this->View()->sSupport = array_merge($this->View()->sSupport, array('sElements' => ''));
        }

        $this->View()->assign('success', $success);

        if ($this->Request()->isPost()) {
            $this->handleFormPost($id);
        }
    }

    /**
     * @param int $formId
     * @throws Enlight_Exception
     */
    private function handleFormPost($formId)
    {
        if (count($this->_errors) || empty($this->Request()->Submit)) {
            return;
        }

        $this->commitForm();

        $this->redirect(
            array(
                'controller' => 'ticket',
                'action' => 'index',
                'sFid' => $formId,
                'success' => 1
            )
        );
    }

    /**
     * @param integer $formId
     *
     * @return array
     * @throws \Exception
     * @throws Enlight_Exception
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getContent($formId)
    {
        $fields = array();
        $labels = array();

        $shopId = $this->container->get('shopware_storefront.context_service')->getShopContext()->getShop()->getId();

        /* @var $query \Doctrine\ORM\Query */
        $query = Shopware()->Models()->getRepository('Shopware\Models\Form\Form')->getFormQuery($formId, $shopId);

        /* @var $form Form */
        $form = $query->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);

        if (!$form) {
            $this->Response()->setHttpResponseCode(404);
            return $this->forward('index', 'index');
        }

        /* @var $field Field */
        foreach ($form->getFields() as $field) {
            $this->_elements[$field->getId()] = array(
                'id'        => (string) $field->getId(), // intended string cast to keep compatibility
                'name'      => $field->getName(),
                'note'      => $field->getNote(),
                'typ'       => $field->getTyp(),
                'required'  => (string) $field->getRequired(),  // intended string cast to keep compatibility
                'label'     => $field->getLabel(),
                'class'     => $field->getClass(),
                'value'     => $field->getValue(),
                'error_msg' => $field->getErrorMsg(),
            );
        }

        if ($this->Request()->isPost()) {
            $this->checkFields();
        }

        if (empty($this->Request()->Submit) || count($this->_errors)) {
            foreach ($this->_elements as $id => $element) {
                if ($element['name'] == 'inquiry' && !empty($this->Request()->sInquiry)) {
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
                            if ($this->Request()->getParam('sOrdernumber', null) !== null) {
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

        // prepare formadata for view
        $formData = array(
            'id'                => (string) $form->getId(),  // intended string cast to keep compatibility
            'name'              => $form->getName(),
            'text'              => $form->getText(),
            'text2'             => $form->getText2(),
            'email'             => $form->getEmail(),
            'email_template'    => $form->getEmailTemplate(),
            'email_subject'     => $form->getEmailSubject(),
            'metaTitle'         => $form->getMetaTitle(),
            'metaDescription'   => $form->getMetaDescription(),
            'metaKeywords'      => $form->getMetaKeywords(),
        );

        return array_merge($formData, array(
            'sErrors'   => $this->_errors,
            'sElements' => $this->_elements,
            'sFields'   => $fields,
            'sLabels'   => $labels
        ));
    }

    /**
     * Validate input - check form field rules defined by merchant
     * Checks captcha and doing simple blacklist-/spam-check
     *
     * Populates $this->_errors
     * Modifies  $this->_elements
     *
     * @return void
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
                    if ($this->_elements[$key]['typ'] == 'text2') {
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

        $isSpam = false;
        foreach ($this->_postData as $value) {
            if (is_array($value)) {
                continue;
            }

            $badwords = array(' sex ', ' porn ', ' viagra ', 'url=', 'src=', 'link=');
            foreach ($badwords as $badword) {
                if (strpos($value, $badword) !== false) {
                    $isSpam = true;
                }
            }
        }

        if ($isSpam) {
            sleep(3);
            $this->_errors[] = array('1');
        }
    }

    /**
     * Commit form via email (default) or database (ticket  system)
     *
     * @throws Enlight_Exception
     * @return void
     * @throws \Zend_Mail_Exception
     */
    public function commitForm()
    {
        /** @var Enlight_Components_Mail $mail */
        $mail = $this->get('mail');

        //Email field available check
        foreach ($this->_elements as $element) {
            if ($element['typ'] == 'email') {
                $postEmail = $this->_postData[$element['id']];
                $postEmail = trim($postEmail);
            }
        }

        if (!empty($postEmail)) {
            $mail->setReplyTo($postEmail);
        }

        $content = $this->View()->sSupport;

        $mailBody = $this->replaceVariables($content["email_template"]);
        $mailSubject = $this->replaceVariables($content["email_subject"]);

        $mail->setFrom(Shopware()->Config()->Mail);
        $mail->clearRecipients();
        $mail->addTo($content['email']);
        $mail->setBodyText($mailBody);
        $mail->setSubject($mailSubject);

        $mail = Shopware()->Events()->filter('Shopware_Controllers_Frontend_Forms_commitForm_Mail', $mail, array('subject' => $this));

        if (!$mail->send()) {
            throw new Enlight_Exception('Could not send mail');
        }
    }

    /**
     * replaces placeholder variables
     * @param  string $content
     * @return string
     */
    private function replaceVariables($content)
    {
        foreach ($this->_postData as $key => $value) {
            if ($this->_elements[$key]['typ'] == "text2") {
                $names = explode(";", $this->_elements[$key]['name']);
                $content = str_replace("{sVars." . $names[0] . "}", $value[0], $content);
                $content = str_replace("{sVars." . $names[1] . "}", $value[1], $content);
            } else {
                $content = str_replace("{sVars." . $this->_elements[$key]['name'] . "}", $value, $content);
            }
        }

        $content = str_replace("{sIP}", $_SERVER['REMOTE_ADDR'], $content);
        $content = str_replace("{sDateTime}", date("d.m.Y h:i:s"), $content);
        $content = str_replace('{sShopname}', Shopware()->Config()->shopName, $content);

        return strip_tags($content);
    }

    /**
     * Create label element
     *
     * @param array $element
     * @return string
     */
    protected function _createLabelElement($element)
    {
        $output = "<label for=\"{$element['name']}\">";
        if ($element['typ'] == 'text2') {
            $output .= str_replace(';', '/', "{$element['label']}");
        } else {
            $output .= "{$element['label']}";
        }
        if ($element['required'] == 1) {
            $output .= "*";
        }
        $output .= ":</label>\r\n";

        return $output;
    }

    /**
     * Create input element method
     *
     * @param array $element
     * @param array $post
     * @return string
     */
    protected function _createInputElement($element, $post = null)
    {
        if ($element['required'] == 1) {
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
                    if ($value == $post) {
                        $checked = ' checked';
                    } else {
                        $checked = '';
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
     * @return string
     */
    protected function _filterInput($input)
    {
        // remove all control characters, unassigned, private use, formatting and surrogate code points
        $input = preg_replace('#[^\PC\s]#u', '', $input);

        $temp = str_replace('"', '', $input);
        if (preg_match('#{\s*/*literal\s*}#i', $temp) > 0) {
            return '';
        }

        return $input;
    }

    /**
     * Validate input method
     *
     * Populates $this->_postData
     *
     * @param array $inputs
     * @param array $elements
     * @return array
     */
    protected function _validateInput($inputs, $elements)
    {
        $errors = array();

        /** @var \Shopware\Components\Validator\EmailValidatorInterface $emailValidator */
        $emailValidator = $this->container->get('validator.email');

        foreach ($elements as $element) {
            $valide = true;
            $value = '';
            if ($element['typ'] == 'text2') {
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
                        if (count($values) != 3) {
                            unset($value);
                            $valide = false;
                            break;
                        }
                        if (strlen($values[0]) == 4) {
                            $value = mktime(0, 0, 0, $values[1], $values[2], $values[0]);
                        } else {
                            $value = mktime(0, 0, 0, $values[0], $values[2], $values[1]);
                        }
                        if (empty($value) || $value = -1) {
                            unset($value);
                            $valide = false;
                            break;
                        } else {
                            $value = date('Y-m-d', $value);
                        }
                        break;
                    case 'email':
                        $value = strtolower($value);
                        if (!$emailValidator->isValid($value)) {
                            unset($value);
                            $valide = false;
                        }
                        $host = trim(substr($value, strpos($value, '@') + 1));
                        if (empty($host) || !gethostbyname($host)) {
                            unset($value);
                            $valide = false;
                        }
                        break;
                    case 'text2':
                        foreach (array_keys($value) as $key) {
                            $value[$key] = trim(strip_tags($value[$key]));
                            if (empty($value[$key])) {
                                unset($value[$key]);
                                $valide = false;
                            }
                            $value = array_values($value);
                        }
                        break;
                    default:
                        $value = trim(strip_tags($value));
                        if (empty($value)) {
                            unset($value);
                            $valide = true;
                            break;
                        }
                        break;
                }
            }
            if ($valide == false && $element['required'] == 1) {
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
}
