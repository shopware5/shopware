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
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Components\Emotion\DeviceConfiguration;

class Shopware_Controllers_Frontend_Campaign extends Enlight_Controller_Action
{
    public function indexAction()
    {
        $emotionId = $this->Request()->getParam('emotionId');

        /**@var $service DeviceConfiguration*/
        $service = $this->get('emotion_device_configuration');
        $landingPage = $service->getLandingPage($emotionId);
        $landingPageShops = $service->getLandingPageShops($emotionId);

        /** @var $context ShopContext */
        $context = $this->get('shopware_storefront.context_service')->getShopContext();
        $shopId = $context->getShop()->getId();
        $fallbackId = $context->getShop()->getFallbackId();

        if (!$landingPage || !in_array($shopId, $landingPageShops)) {
            throw new Enlight_Controller_Exception(
                'Landing page missing, non-existent or invalid for the current shop',
                404
            );
        }

        $translator = new Shopware_Components_Translation();
        $translation = $translator->readWithFallback($shopId, $fallbackId, 'emotion', $emotionId);

        if (!empty($translation['seoTitle'])) {
            $landingPage['seo_title'] = $translation['seoTitle'];
        }

        if (!empty($translation['seoKeywords'])) {
            $landingPage['seo_keywords'] = $translation['seoKeywords'];
        }

        if (!empty($translation['seoDescription'])) {
            $landingPage['seo_description'] = $translation['seoDescription'];
        }

        $this->View()->assign([
            'sBreadcrumb'          => [['name' => $landingPage['name']]],
            'seo_title'            => $landingPage['seo_title'],
            'seo_keywords'         => $landingPage['seo_keywords'],
            'seo_description'      => $landingPage['seo_description'],
            'landingPage'          => $landingPage,
            'hasEmotion'           => true,
            'isEmotionLandingPage' => true,
            'hasEscapedFragment'   => $this->Request()->has('_escaped_fragment_'),
        ]);
    }
}
