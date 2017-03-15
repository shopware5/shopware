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

namespace Shopware\Bundle\EmotionBundle\ComponentHandler;

use Shopware\Bundle\EmotionBundle\Struct\Collection\PrepareDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Collection\ResolvedDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Element;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class BannerSliderComponentHandler implements ComponentHandlerInterface
{
    const LEGACY_CONVERT_FUNCTION = 'getBannerSlider';
    const COMPONENT_NAME = 'emotion-components-banner-slider';

    /**
     * @param Element $element
     *
     * @return bool
     */
    public function supports(Element $element)
    {
        return $element->getComponent()->getType() === self::COMPONENT_NAME
            || $element->getComponent()->getConvertFunction() === self::LEGACY_CONVERT_FUNCTION;
    }

    /**
     * @param PrepareDataCollection $collection
     * @param Element               $element
     * @param ShopContextInterface  $context
     */
    public function prepare(PrepareDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        /** @var array $sliderList */
        $sliderList = $element->getConfig()->get('banner_slider', []);

        $collection->addMediaIds(array_column($sliderList, 'mediaId'));

        $url = sprintf('http%s://', $context->getShop()->getSecure() ? 's' : '') .
            $context->getShop()->getHost() .
            $context->getShop()->getUrl();

        foreach ($sliderList as &$slider) {
            if (!empty($slider['link']) && !preg_match('/^(http|https):\/\//', $slider['link'])) {
                $slider['link'] = $url . $slider['link'];
            }
        }

        $element->getConfig()->set('banner_slider', $sliderList);
    }

    /**
     * @param ResolvedDataCollection $collection
     * @param Element                $element
     * @param ShopContextInterface   $context
     */
    public function handle(ResolvedDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        /** @var array $sliderList */
        $sliderList = $element->getConfig()->get('banner_slider', []);

        $mediaList = [];
        foreach ($sliderList as $slider) {
            $mediaList[] = $collection->getMedia($slider['mediaId']);
        }

        $element->getData()->set('mediaList', $mediaList);
    }
}
