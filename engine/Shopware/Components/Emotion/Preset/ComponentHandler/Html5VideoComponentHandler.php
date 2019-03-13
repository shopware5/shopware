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

use Symfony\Component\HttpFoundation\ParameterBag;

class Html5VideoComponentHandler extends AbstractComponentHandler
{
    const COMPONENT_TYPE = 'emotion-components-html-video';

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
    public function import(array $element, ParameterBag $syncData)
    {
        if (!isset($element['data'])) {
            return $element;
        }

        return $this->processElementData($element, $syncData);
    }

    /**
     * {@inheritdoc}
     */
    public function export(array $element, ParameterBag $syncData)
    {
        if (!isset($element['data'])) {
            return $element;
        }

        return $this->prepareElementExport($element, $syncData);
    }

    /**
     * @return array
     */
    private function processElementData(array $element, ParameterBag $syncData)
    {
        $data = $element['data'];
        $assets = $syncData->get('assets', []);
        $importedAssets = $syncData->get('importedAssets', []);

        foreach ($data as &$elementData) {
            if (!array_key_exists($elementData['value'], $assets)) {
                continue;
            }

            if (!array_key_exists($elementData['value'], $importedAssets)) {
                $assetPath = $assets[$elementData['value']];

                $media = null;

                switch ($elementData['key']) {
                    case 'webm_video':
                    case 'ogg_video':
                    case 'h264_video':
                        $media = $this->doAssetImport($assetPath, -7);
                        break;

                    case 'fallback_picture':
                        $media = $this->doAssetImport($assetPath);
                        break;
                }
                $importedAssets[$elementData['value']] = $media->getId();
            } else {
                $media = $this->getMediaById($importedAssets[$elementData['value']]);
            }

            if ($media === null) {
                continue;
            }

            $elementData['value'] = $media->getPath();
        }
        unset($elementData);

        $syncData->set('importedAssets', $importedAssets);
        $element['data'] = $data;

        return $element;
    }

    /**
     * @return array
     */
    private function prepareElementExport(array $element, ParameterBag $syncData)
    {
        $assets = $syncData->get('assets', []);
        $data = $element['data'];

        /** @var array $elementData */
        foreach ($data as &$elementData) {
            if (empty(trim($elementData['value']))) {
                continue;
            }

            $key = $elementData['key'];

            $assetPath = null;
            switch ($key) {
                case 'webm_video':
                case 'ogg_video':
                case 'h264_video':
                case 'fallback_picture':
                    $assetPath = $this->mediaService->getUrl($elementData['value']);
            }

            if ($assetPath === null) {
                continue;
            }

            $media = $this->getMediaByPath($elementData['value']);

            if ($media) {
                $assetHash = md5($media->getId());
                $assets[$assetHash] = $assetPath;
                $elementData['value'] = $assetHash;
            }
        }
        unset($elementData);

        $syncData->set('assets', $assets);
        $element['data'] = $data;

        return $element;
    }
}
