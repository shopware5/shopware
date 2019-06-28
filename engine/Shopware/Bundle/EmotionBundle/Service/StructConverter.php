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

namespace Shopware\Bundle\EmotionBundle\Service;

use Shopware\Bundle\EmotionBundle\ComponentHandler\ArticleComponentHandler;
use Shopware\Bundle\EmotionBundle\ComponentHandler\ArticleSliderComponentHandler;
use Shopware\Bundle\EmotionBundle\ComponentHandler\BannerComponentHandler;
use Shopware\Bundle\EmotionBundle\ComponentHandler\BannerSliderComponentHandler;
use Shopware\Bundle\EmotionBundle\ComponentHandler\BlogComponentHandler;
use Shopware\Bundle\EmotionBundle\ComponentHandler\CategoryTeaserComponentHandler;
use Shopware\Bundle\EmotionBundle\ComponentHandler\ManufacturerSliderComponentHandler;
use Shopware\Bundle\EmotionBundle\Struct\Element;
use Shopware\Bundle\EmotionBundle\Struct\Emotion;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer;
use Shopware\Components\Compatibility\LegacyStructConverter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StructConverter
{
    /**
     * @var LegacyStructConverter
     */
    private $converter;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(LegacyStructConverter $converter, MediaServiceInterface $mediaService, \Enlight_Event_EventManager $eventManager, ContainerInterface $container)
    {
        $this->converter = $converter;
        $this->mediaService = $mediaService;
        $this->eventManager = $eventManager;
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function convertEmotion(Emotion $emotion)
    {
        $data = json_decode(json_encode($emotion), true);

        // legacy keys
        $data['modified'] = $data['modifiedDate'];
        $data['device'] = $data['devices'];
        $data['attribute'] = $data['attributes']['core'];
        $data['categories'] = array_values($data['categories']);

        foreach ($data['categories'] as &$category) {
            $category['streamId'] = $category['productStream'];
            $category['hideTop'] = !$category['displayInNavigation'];
            $category['hideFilter'] = !$category['displayFacets'];
            $category['external'] = $category['externalLink'];
            $category['mediaId'] = $category['media'] ? $category['media']['id'] : null;
            $category['path'] = count($category['path']) ? '|' . implode('|', $category['path']) . '|' : null;
            $category['active'] = true;
        }

        // Mapping for the grid settings
        $data['grid'] = [
            'cols' => $data['cols'],
            'rows' => $data['rows'],
            'gutter' => $data['cellSpacing'],
            'cellHeight' => $data['cellHeight'],
            'articleHeight' => $data['articleHeight'],
        ];

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Emotion', $data, ['emotion' => $emotion]);
    }

    /**
     * @return array
     */
    public function convertEmotionElement(Element $element)
    {
        $elementArray = json_decode(json_encode($element), true);
        $elementArray['component']['xType'] = $element->getComponent()->getType();
        $elementArray['component']['cls'] = $element->getComponent()->getCssClass();

        $elementArray['data'] = array_merge($element->getConfig()->getAll(), $element->getData()->getAll());
        $elementArray['data']['objectId'] = md5((string) $element->getId());

        switch ($element->getComponent()->getType()) {
            case BannerSliderComponentHandler::COMPONENT_NAME:
                $bannerConfig = $element->getConfig()->get('banner_slider');

                $config = [];
                foreach ($bannerConfig as $bannerConfigData) {
                    $config[$bannerConfigData['mediaId']] = $bannerConfigData;
                }

                foreach ($element->getData()->get('mediaList') as $index => $mediaObject) {
                    $media = $this->converter->convertMediaStruct($mediaObject);

                    $media['title'] = $config[$media['id']]['title'];
                    $media['altText'] = $config[$media['id']]['altText'];
                    $media['link'] = $config[$media['id']]['link'];

                    $media['mediaId'] = $media['id'];
                    $media['path'] = $media['source'];
                    $media['fileInfo'] = [
                        'width' => $media['width'],
                        'height' => $media['height'],
                    ];

                    $elementArray['data']['values'][] = $media;
                }
                break;

            case BannerComponentHandler::COMPONENT_NAME:
                if (!$element->getData()->get('media')) {
                    break;
                }

                $media = $this->converter->convertMediaStruct($element->getData()->get('media'));
                $elementArray['data'] = array_merge($elementArray['data'], $media);
                break;

            case ArticleComponentHandler::COMPONENT_NAME:
                if (!$element->getData()->get('product')) {
                    break;
                }

                $product = $this->converter->convertListProductStruct($element->getData()->get('product'));

                if ($element->getConfig()->get('article_type') === ArticleComponentHandler::TYPE_STATIC_VARIANT) {
                    $product['linkDetails'] = $product['linkVariant'];
                }

                $elementArray['data'] = array_merge($elementArray['data'], $product);
                break;

            case ArticleSliderComponentHandler::COMPONENT_NAME:
                if (
                    !$element->getData()->get('products')
                    || empty(array_filter($element->getData()->get('products')))) {
                    break;
                }

                $elementArray['data']['categoryId'] = (int) $elementArray['article_slider_category'];

                $products = $this->converter->convertListProductStructList($element->getData()->get('products'));

                $type = $element->getConfig()->get('article_slider_type', ArticleSliderComponentHandler::TYPE_STATIC_PRODUCT);
                if ($type === ArticleSliderComponentHandler::TYPE_STATIC_VARIANT) {
                    $products = array_map(function ($product) {
                        $product['linkDetails'] = $product['linkVariant'];

                        return $product;
                    }, $products);
                }

                $elementArray['data']['values'] = array_values($products);
                break;

            case CategoryTeaserComponentHandler::COMPONENT_NAME:
                if ($element->getData()->get('category')) {
                    $elementArray['data']['categoryName'] = $element->getData()->get('category')->getName();
                }

                if ($element->getData()->get('image')) {
                    $elementArray['data']['image'] = $this->converter->convertMediaStruct($element->getData()->get('image'));
                    $elementArray['data']['images'] = $elementArray['data']['image']['thumbnails'];
                } elseif ($element->getData()->get('media')) {
                    $elementArray['data']['media'] = $this->converter->convertMediaStruct($element->getData()->get('media'));
                }
                break;

            case ManufacturerSliderComponentHandler::COMPONENT_NAME:
                /** @var Manufacturer $manufacturer */
                foreach ($element->getData()->get('manufacturers') as $manufacturer) {
                    $manufacturerArray = $this->converter->convertManufacturerStruct($manufacturer);

                    $manufacturerArray['link'] = $this->container->get('config')->get('baseFile') . '?controller=listing&action=manufacturer&sSupplier=' . $manufacturer->getId();
                    $manufacturerArray['website'] = $manufacturer->getLink();

                    $elementArray['data']['values'][$manufacturer->getId()] = $manufacturerArray;
                }
                break;

            case BlogComponentHandler::COMPONENT_NAME:
                $entries = [];
                foreach ($element->getData()->get('entries', []) as $blog) {
                    $entries[] = $this->converter->convertBlogStruct($blog);
                }

                $elementArray['data']['totalCount'] = count($entries);
                $elementArray['data']['entries'] = $entries;
                break;
        }

        return $this->eventManager->filter('Legacy_Struct_Converter_Convert_Emotion_Element', $elementArray, ['element' => $element]);
    }
}
