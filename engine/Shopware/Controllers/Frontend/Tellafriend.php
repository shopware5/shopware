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

use Shopware\Components\Random;
use Shopware\Components\Validator\EmailValidator;

class Shopware_Controllers_Frontend_Tellafriend extends Enlight_Controller_Action
{
    /**
     * @deprecated Will be removed in Shopware 5.8 without replacement
     *
     * @var \sSystem
     */
    public $sSYSTEM;

    /**
     * @return void
     */
    public function init()
    {
        $this->sSYSTEM = Shopware()->System();
    }

    public function preDispatch()
    {
        $config = $this->container->get(\Shopware_Components_Config::class);

        if (!$config->get('showTellAFriend')) {
            throw new Enlight_Controller_Exception('Tell a friend is not activated for the current shop', 404);
        }
    }

    /**
     * @return void
     */
    public function successAction()
    {
        $this->View()->loadTemplate('frontend/tellafriend/index.tpl');
        $this->View()->assign('sSuccess', true);
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $productId = (int) ($this->Request()->get('sDetails') ?? $this->Request()->get('sArticle'));
        if ($productId === 0) {
            $this->forward('index', 'index');

            return;
        }

        $product = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, $productId);
        if (!isset($product['articleName']) || !isset($product['linkDetails'])
            || !\is_string($product['articleName']) || !\is_string($product['linkDetails'])
            || $product['articleName'] === '' || $product['linkDetails'] === '') {
            $this->forward('index', 'index');

            return;
        }

        if ($this->Request()->getPost('sMailTo')) {
            $fromName = $this->Request()->getPost('sName');
            $fromMail = $this->Request()->getPost('sMail');
            $recipient = $this->Request()->getPost('sRecipient');
            $comment = $this->Request()->getPost('sComment', '');

            $emailValidator = $this->container->get(EmailValidator::class);

            $validInputParameters = \is_string($fromName) && \is_string($fromMail)
                && \is_string($recipient) && \is_string($comment)
                && !preg_match('/;/', $recipient) && \strlen($recipient) < 50
                && $emailValidator->isValid($fromMail) && $emailValidator->isValid($recipient)
            ;

            if ($validInputParameters && !empty(Shopware()->Config()->get('CaptchaColor'))) {
                /** @var \Shopware\Components\Captcha\CaptchaValidator $captchaValidator */
                $captchaValidator = $this->container->get('shopware.captcha.validator');

                $validInputParameters = $captchaValidator->validate($this->Request());
            }

            if ($validInputParameters) {
                $mail = Shopware()->TemplateMail()->createMail('sTELLAFRIEND', [
                    'sName' => strip_tags($fromName),
                    'sArticle' => strip_tags($product['articleName']),
                    'sLink' => $product['linkDetails'],
                    'sComment' => strip_tags($comment),
                ]);

                $mail->setFrom($fromMail, $fromName);
                $mail->addTo($recipient);

                $mail->send();

                $this->View()->assign('sSuccess', true);
                $url = $this->Front()->ensureRouter()->assemble(['controller' => 'tellafriend', 'action' => 'success']);
                $this->redirect($url);
            } else {
                $this->View()->assign('sError', true);
                $this->View()->assign('sName', (string) $fromName);
                $this->View()->assign('sMail', (string) $fromMail);
                $this->View()->assign('sRecipient', (string) $recipient);
                $this->View()->assign('sComment', (string) $comment);
            }
        }

        $this->View()->assign('rand', Random::getAlphanumericString(32));
        $this->View()->assign('sArticle', $product);
    }
}
