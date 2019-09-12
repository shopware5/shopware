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

use Shopware\Components\Captcha\Exception\CaptchaNotFoundException;

class Shopware_Controllers_Widgets_Captcha extends Enlight_Controller_Action
{
    /**
     * Pre dispatch method
     */
    public function preDispatch()
    {
        $this->Response()->setHeader('x-robots-tag', 'noindex');
    }

    public function refreshCaptchaAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $legacyCaptcha = $this->container->get('shopware.captcha.legacy_captcha');
        $templateData = $legacyCaptcha->getTemplateData();

        $img = $templateData['img'];
        $rand = $templateData['sRand'];

        $body = '<img src="data:image/png;base64,' . $img . '" alt="Captcha" />';
        $body .= '<input type="hidden" name="sRand" value="' . $rand . '" />';

        $this->Response()->setContent($body);
    }

    /**
     * Index action method
     *
     * Creates the captcha images and delivers it as a PNG
     * with the proper HTTP header.
     */
    public function indexAction()
    {
        /** @var \Shopware\Components\Captcha\CaptchaRepository $captchaRepository */
        $captchaRepository = $this->container->get('shopware.captcha.repository');
        /** @var \Shopware\Components\Captcha\CaptchaInterface $captcha */
        $captcha = $captchaRepository->getConfiguredCaptcha();

        $captchaName = $captcha->getName();
        $this->View()->loadTemplate(sprintf('widgets/captcha/%s.tpl', $captchaName));
        $this->View()->assign($captcha->getTemplateData());
    }

    /**
     * Assigns a captcha by the passed name in the request to the view.
     * If no name assign noCaptcha
     */
    public function getCaptchaByNameAction()
    {
        $captchaName = $this->request->getParam('captchaName', 'nocaptcha');

        /** @var \Shopware\Components\Captcha\CaptchaRepository $captchaRepository */
        $captchaRepository = $this->container->get('shopware.captcha.repository');
        try {
            /** @var \Shopware\Components\Captcha\CaptchaInterface $captcha */
            $captcha = $captchaRepository->getCaptchaByName($captchaName);
        } catch (CaptchaNotFoundException $exception) {
            // log captchaNotFound Exception
            $this->container->get('corelogger')->error($exception->getMessage());
            $captcha = $captchaRepository->getCaptchaByName('nocaptcha');
        }

        $this->View()->loadTemplate(sprintf('widgets/captcha/%s.tpl', $captcha->getName()));
        $this->View()->assign($captcha->getTemplateData());
    }
}
