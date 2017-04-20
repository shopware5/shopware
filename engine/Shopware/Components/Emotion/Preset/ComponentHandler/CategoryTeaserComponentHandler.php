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

namespace Shopware\Components\Emotion\Preset\ComponentHandler;

use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Components\Api\Resource\Media as MediaResource;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Models\Media\Media;

class CategoryTeaserComponentHandler implements ComponentHandlerInterface
{
    const COMPONENT_TYPE = 'emotion-components-category-teaser';

    const ELEMENT_DATA_KEY = 'image';

    /**
     * @var MediaResource
     */
    private $mediaResource;

    /**
     * @var MediaService
     */
    private $mediaService;

    /**
     * @param MediaService $mediaService
     * @param Container    $container
     */
    public function __construct(MediaService $mediaService, Container $container)
    {
        $this->mediaService = $mediaService;

        $mediaResource = new MediaResource();
        $mediaResource->setContainer($container);
        $mediaResource->setManager($container->get('models'));

        $this->mediaResource = $mediaResource;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($componentType)
    {
        return $componentType === self::COMPONENT_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function import(array $element)
    {
        if (!isset($element['data'], $element['assets'])) {
            return $element;
        }

        return $this->processElementData($element);
    }

    public function export(array $element)
    {
        if (!isset($element['data'])) {
            return $element;
        }

        return $this->prepareElementExport($element);
    }

    /**
     * @param array $element
     *
     * @return array
     */
    private function processElementData(array $element)
    {
        $data = $element['data'];
        $assets = $element['assets'];

        foreach ($data as &$elementData) {
            if ($elementData['key'] === self::ELEMENT_DATA_KEY && !empty($elementData['value'])) {
                $assetPath = $assets[$elementData['value']];

                $media = $this->doAssetImport($assetPath);

                $elementData['value'] = $media->getPath();

                break;
            }
        }
        unset($elementData);

        $element['data'] = $data;
        unset($element['assets']);

        return $element;
    }

    /**
     * @param string $assetPath
     *
     * @return Media
     */
    private function doAssetImport($assetPath)
    {
        $media = $this->mediaResource->internalCreateMediaByFileLink($assetPath, -3);
        $this->mediaResource->getManager()->flush($media);

        return $media;
    }

    /**
     * @param array $element
     *
     * @return array
     */
    private function prepareElementExport(array $element)
    {
        $element['assets'] = [];
        $data = $element['data'];

        foreach ($data as &$elementData) {
            if ($elementData['key'] === self::ELEMENT_DATA_KEY && !empty($elementData['value'])) {
                $assetPath = $elementData['value'];
                $assetHash = uniqid('asset-', false);

                $element['assets'][$assetHash] = $this->mediaService->getUrl($assetPath);
                $elementData['value'] = $assetHash;

                break;
            }
        }
        unset($elementData);

        $element['data'] = $data;

        return $element;
    }
}
