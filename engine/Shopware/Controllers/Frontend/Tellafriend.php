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

/**
 * @category  Shopware
 * @package   Shopware\Controllers\Frontend
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_Tellafriend extends Enlight_Controller_Action
{
    public $sSYSTEM;

    public function init()
    {
        $this->sSYSTEM = Shopware()->System();
    }

    public function successAction()
    {
        $this->View()->loadTemplate("frontend/tellafriend/index.tpl");
        $this->View()->sSuccess = true;
    }

    public function indexAction()
    {
        if (empty($this->Request()->sDetails)) {
            $id = $this->Request()->sArticle;
        } else {
            $id = $this->Request()->sDetails;
        }

        if (empty($id)) {
            return $this->forward("index", "index");
        }

        // Get Article-Information
        $sArticle = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, intval($id));
        if (empty($sArticle["articleName"])) {
            return $this->forward("index", "index");
        }

        if ($this->Request()->getPost("sMailTo")) {
            $variables["sError"] = false;
            if (!$this->Request()->getPost("sName")) {
                $variables["sError"] = true;
            }
            if (!$this->Request()->getPost("sMail")) {
                $variables["sError"] = true;
            }
            if (!$this->Request()->getPost("sRecipient")) {
                $variables["sError"] = true;
            }

            if (preg_match("/;/", $this->Request()->getPost("sRecipient")) || strlen($this->Request()->getPost("sRecipient") >= 50)) {
                $variables["sError"] = true;
            }

            $validator = $this->container->get('validator.email');
            if (!$validator->isValid($this->Request()->getPost("sRecipient"))) {
                $variables["sError"] = true;
            }

            if (!empty(Shopware()->Config()->CaptchaColor)) {
                /** @var \Shopware\Components\Captcha\CaptchaValidator $captchaValidator */
                $captchaValidator = $this->container->get('shopware.captcha.validator');

                if (!$captchaValidator->validate($this->Request())) {
                    $sErrorFlag['sCaptcha'] = true;
                }
            }

            if ($variables["sError"] == false) {
                // Prepare eMail
                $sArticle["linkDetails"] = $this->Front()->Router()->assemble(array('sViewport' => 'detail', 'sArticle' => $sArticle["articleID"]));

                $context = array(
                    'sName'    => $this->sSYSTEM->_POST["sName"],
                    'sArticle' => html_entity_decode($sArticle["articleName"]),
                    'sLink'    => $sArticle["linkDetails"],
                );

                if ($this->sSYSTEM->_POST["sComment"]) {
                    $context['sComment'] = strip_tags(html_entity_decode($this->sSYSTEM->_POST["sComment"]));
                } else {
                    $context['sComment'] = '';
                }

                $mail = Shopware()->TemplateMail()->createMail('sTELLAFRIEND', $context, null, array(
                    "fromMail" => $this->sSYSTEM->_POST["sMail"],
                    "fromName" => $this->sSYSTEM->_POST["sName"]
                ));

                $mail->addTo($this->sSYSTEM->_POST["sRecipient"]);
                $mail->send();

                $this->View()->sSuccess = true;
                $url = $this->Front()->Router()->assemble(array('controller' => 'tellafriend', 'action' => 'success'));
                $this->redirect($url);
            } else {
                $this->View()->sError = true;
                $this->View()->sName = $this->Request()->getPost("sName");
                $this->View()->sMail = $this->Request()->getPost("sMail");
                $this->View()->sRecipient = $this->Request()->getPost("sRecipient");
                $this->View()->sComment = $this->Request()->getPost("sComment");
            }
        }
        $this->View()->rand = md5(uniqid(rand()));
        $this->View()->sArticle = $sArticle;
    }
}
