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
use Shopware\Components\Api\Resource\Media;
use Shopware\Components\DependencyInjection\Container;

class Html5VideoComponentHandler implements ComponentHandlerInterface
{
    const TYPE = 'emotion-components-html-video';

    /**
     * @var MediaService
     */
    private $mediaService;

    /**
     * @var Media
     */
    private $mediaResource;

    public function __construct(MediaService $mediaService, Media $mediaResource, Container $container)
    {
        $this->mediaService = $mediaService;
        $this->mediaResource = $mediaResource;
        $this->mediaResource->setContainer($container);
        $this->mediaResource->setManager($container->get('models'));
    }

    public function supports($componentType)
    {
        return $componentType === self::TYPE;
    }

    public function import(array $element)
    {
        if (!isset($element['data'], $element['assets'])) {
            return $element;
        }

        return $this->processElementData($element);
    }

    public function export(array $element)
    {
        if (!array_key_exists('data', $element)) {
            return $element;
        }

        return $this->prepareElementExport($element);
    }

    /**
     * @param array $element
     *
     * @return array
     */
    private function prepareElementExport(array $element)
    {
        $element['assets'] = [];

        /** @var array $elementData */
        foreach ($element['data'] as &$field) {
            $key = $field['key'];

            $asset = null;
            switch ($key) {
                case 'webm_video':
                case 'ogg_video':
                case 'h264_video':
                case 'fallback_picture':
                    $asset = $this->mediaService->getUrl($field['value']);
            }

            if ($asset === null) {
                continue;
            }

            $element['assets'][$key] = $asset;
        }

        return $element;
    }

    private function processElementData($element)
    {
        $assets = $element['assets'];

        foreach ($element['data'] as &$field) {
            if (!array_key_exists($field['key'], $assets)) {
                continue;
            }

            $asset = $assets[$field['key']];

            $media = null;

            switch ($field['key']) {
                case 'webm_video':
                case 'ogg_video':
                case 'h264_video':
                    $media = $this->mediaResource->internalCreateMediaByFileLink($asset, -7);
                    break;

                case 'fallback_picture':
                    $media = $this->mediaResource->internalCreateMediaByFileLink($asset, -3);
                    break;
            }

            if ($media === null) {
                continue;
            }

            $this->mediaResource->getManager()->flush($media);

            $field['value'] = $media->getPath();
        }

        unset($element['assets']);

        return $element;
    }
}
