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

namespace Shopware\Components\Emotion;

use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware_Components_Translation;

class LandingPageViewLoader
{
    /**
     * @var DeviceConfiguration
     */
    private $deviceConfiguration;

    /**
     * @var Shopware_Components_Translation
     */
    private $translationComponent;

    public function __construct(
        DeviceConfiguration $deviceConfiguration,
        Shopware_Components_Translation $translationComponent = null
    ) {
        $this->deviceConfiguration = $deviceConfiguration;
        $this->translationComponent = $translationComponent ?: Shopware()->Container()->get('translation');
    }

    /**
     * @param int $emotionId
     *
     * @throws \Exception
     * @throws \Enlight_Controller_Exception
     *
     * @return LandingPageViewStruct
     */
    public function load($emotionId, ShopContextInterface $context)
    {
        $landingPage = $this->deviceConfiguration->getLandingPage($emotionId);
        $landingPageShops = $this->deviceConfiguration->getLandingPageShops($emotionId);

        $shopId = $context->getShop()->getId();
        $fallbackId = $context->getShop()->getFallbackId();

        if (!$landingPage || !in_array($shopId, $landingPageShops)) {
            throw new \Enlight_Controller_Exception(
                'Landing page missing, non-existent or invalid for the current shop',
                404
            );
        }

        $translation = $this->translationComponent->readWithFallback($shopId, $fallbackId, 'emotion', $emotionId);

        if (!empty($translation['name'])) {
            $landingPage['name'] = $translation['name'];
        }

        if (!empty($translation['seoTitle'])) {
            $landingPage['seo_title'] = $translation['seoTitle'];
        }

        if (!empty($translation['seoKeywords'])) {
            $landingPage['seo_keywords'] = $translation['seoKeywords'];
        }

        if (!empty($translation['seoDescription'])) {
            $landingPage['seo_description'] = $translation['seoDescription'];
        }

        $struct = new LandingPageViewStruct();
        $struct->sBreadcrumb = [['name' => $landingPage['name']]];
        $struct->seo_title = $landingPage['seo_title'];
        $struct->seo_keywords = $landingPage['seo_keywords'];
        $struct->seo_description = $landingPage['seo_description'];
        $struct->landingPage = $landingPage;
        $struct->hasEmotion = true;
        $struct->isEmotionLandingPage = true;

        return $struct;
    }
}
