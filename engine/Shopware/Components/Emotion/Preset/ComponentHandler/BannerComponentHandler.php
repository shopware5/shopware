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
use Shopware\Models\Media\Media;

class BannerComponentHandler implements ComponentHandlerInterface
{
    const COMPONENT_TYPE = 'emotion-components-banner';

    const ELEMENT_DATA_KEY = 'file';

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
     */
    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;

        $mediaResource = new MediaResource();
        $mediaResource->setContainer(Shopware()->Container());
        $mediaResource->setManager(Shopware()->Container()->get('models'));

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
        if (!array_key_exists('data', $element)) {
            return $element;
        }

        $element['data'] = $this->processElementData($element['data']);

        return $element;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function processElementData(array $data)
    {
        foreach ($data as &$elementData) {
            if ($elementData['key'] === self::ELEMENT_DATA_KEY) {
                $assetPath = $elementData['value'];

                $media = $this->doAssetImport($assetPath);

                $elementData['value'] = $media->getPath();

                break;
            }
        }

        return $data;
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
}
