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

class BannerComponentHandler implements ComponentHandlerInterface
{
    const LEGACY_CONVERT_FUNCTION = 'getBannerMappingLinks';
    const COMPONENT_NAME = 'emotion-components-banner';

    /**
     * {@inheritdoc}
     */
    public function supports(Element $element)
    {
        return $element->getComponent()->getType() === self::COMPONENT_NAME
            || $element->getComponent()->getConvertFunction() === self::LEGACY_CONVERT_FUNCTION;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(PrepareDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $collection->addMediaPaths([$element->getConfig()->get('file')]);

        $this->generateLink($element, $context);
        $this->addMappings($collection, $element, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ResolvedDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $bannerPath = $element->getConfig()->get('file');
        $media = $collection->getMediaByPath($bannerPath);
        $element->getData()->set('media', $media);

        if ($media) {
            $fileInfo = ['width' => $media->getWidth(), 'height' => $media->getHeight()];
            $element->getConfig()->set('fileInfo', $fileInfo);
        }

        // hydrate mappings with products
        $this->resolveMappings($collection, $element);
    }

    private function generateLink(Element $element, ShopContextInterface $context)
    {
        $link = $element->getConfig()->get('link');
        if (empty($link)) {
            return;
        }

        preg_match('/^([a-z]*:\/\/|shopware\.php|mailto:)/i', $link, $matches);

        if (empty($matches) && strpos($link, '/') === 0) {
            $link = $context->getShop()->getUrl() . $link;
        }

        $element->getConfig()->set('link', $link);
    }

    private function addMappings(PrepareDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        /** @var array $mappings */
        $mappings = $element->getConfig()->get('bannerMapping', []);
        if (empty($mappings)) {
            return;
        }

        foreach ($mappings as $key => $mapping) {
            preg_match('/^([a-z]*:\/\/|shopware\.php|mailto:)/i', $mapping['link'], $matches);

            if (!empty($matches)) {
                continue;
            }

            $mappingKey = $this->getMappingKey($mapping);
            $mappingKey = 'emotion-banner--' . $element->getId() . '-' . $mappingKey;

            $isLink = strpos($mapping['link'], '/') === 0;

            if ($isLink) {
                $mapping['link'] = $context->getShop()->getUrl() . $mapping['link'];
            } else {
                // link is actually a product number
                $mapping['ordernumber'] = $mapping['link'];
                $collection->getBatchRequest()->setProductNumbers($mappingKey, [$mapping['ordernumber']]);
            }

            $mappings[$key] = $mapping;
        }

        $element->getConfig()->set('bannerMapping', $mappings);
    }

    private function resolveMappings(ResolvedDataCollection $collection, Element $element)
    {
        /** @var array $mappings */
        $mappings = $element->getConfig()->get('bannerMapping', []);

        if (empty($mappings)) {
            return;
        }

        foreach ($mappings as $key => $mapping) {
            preg_match('/^([a-z]*:\/\/|shopware\.php|mailto:)/i', $mapping['link'], $matches);

            if (!empty($matches)) {
                continue;
            }

            $isLink = strpos($mapping['link'], '/') === 0;

            if (!$isLink) {
                $mappingKey = $this->getMappingKey($mapping);
                $mappingKey = 'emotion-banner--' . $element->getId() . '-' . $mappingKey;

                $products = $collection->getBatchResult()->get($mappingKey);
                $product = reset($products);
                if (!$product) {
                    continue;
                }

                $mapping['link'] = Shopware()->Container()->get('config')->get('baseFile') . '?sViewport=detail&sArticle=' . $product->getId() . '&number=' . $product->getNumber();
            }

            $mappings[$key] = $mapping;
        }

        $element->getConfig()->set('bannerMapping', $mappings);
    }

    /**
     * @return string
     */
    private function getMappingKey(array $mapping)
    {
        return md5($mapping['x'] . $mapping['y'] . $mapping['width'] . $mapping['height'] . $mapping['link']);
    }
}
