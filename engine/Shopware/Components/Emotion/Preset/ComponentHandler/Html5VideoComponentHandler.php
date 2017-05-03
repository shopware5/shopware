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
    public function import(array $element)
    {
        if (!isset($element['data'], $element['assets'])) {
            return $element;
        }

        return $this->processElementData($element);
    }

    /**
     * {@inheritdoc}
     */
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
    private function prepareElementExport(array $element)
    {
        $element['assets'] = [];
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

            $assetHash = uniqid('asset-', true);
            $element['assets'][$assetHash] = $assetPath;
            $elementData['value'] = $assetHash;
        }
        unset($elementData);

        $element['data'] = $data;

        return $element;
    }

    /**
     * @param array $element
     *
     * @return array
     */
    private function processElementData(array $element)
    {
        $assets = $element['assets'];
        $data = $element['data'];

        foreach ($data as &$elementData) {
            if (!array_key_exists($elementData['value'], $assets)) {
                continue;
            }

            $asset = $assets[$elementData['value']];
            $media = null;

            switch ($elementData['key']) {
                case 'webm_video':
                case 'ogg_video':
                case 'h264_video':
                    $media = $this->doAssetImport($asset, -7);
                    break;

                case 'fallback_picture':
                    $media = $this->doAssetImport($asset);
                    break;
            }

            if ($media === null) {
                continue;
            }

            $elementData['value'] = $media->getPath();
        }
        unset($elementData);

        $element['data'] = $data;
        unset($element['assets']);

        return $element;
    }
}
