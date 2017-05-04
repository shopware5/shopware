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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Components\Api\Resource\EmotionPreset;
use Shopware\Components\Emotion\Preset\EmotionToPresetDataTransformerInterface;
use Shopware\Components\Emotion\Preset\PresetDataSynchronizerInterface;
use Shopware\Components\Slug\SlugInterface;

class EmotionExporter implements EmotionExporterInterface
{
    /**
     * @var EmotionToPresetDataTransformerInterface
     */
    private $transformer;

    /**
     * @var PresetDataSynchronizerInterface
     */
    private $synchronizer;

    /**
     * @var EmotionPreset
     */
    private $presetResource;

    /**
     * @var MediaService
     */
    private $mediaService;

    /**
     * @var string
     */
    private $rootDirectory;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var SlugInterface
     */
    private $slug;

    /**
     * @param EmotionToPresetDataTransformerInterface $emotionToPresetDataTransformer
     * @param PresetDataSynchronizerInterface         $synchronizer
     * @param EmotionPreset                           $emotionPresetResource
     * @param string                                  $rootDirectory
     */
    public function __construct(
        EmotionToPresetDataTransformerInterface $transformer,
        PresetDataSynchronizerInterface $synchronizer,
        EmotionPreset $emotionPresetResource,
        MediaService $mediaService,
        $rootDirectory,
        Connection $connection,
        SlugInterface $slug
    ) {
        $this->transformer = $transformer;
        $this->synchronizer = $synchronizer;
        $this->presetResource = $emotionPresetResource;
        $this->mediaService = $mediaService;
        $this->rootDirectory = $rootDirectory;
        $this->connection = $connection;
        $this->slug = $slug;
    }

    /**
     * @param int $emotionId
     *
     * @throws \Exception
     *
     * @return string
     */
    public function export($emotionId)
    {
        $zip = new \ZipArchive();

        $name = $this->connection->fetchColumn('SELECT name FROM s_emotion WHERE id = :id', [':id' => $emotionId]);
        $name = strtolower($this->slug->slugify($name));

        $filename = $this->rootDirectory . '/files/downloads/' . $name . time() . '.zip';

        if ($zip->open($filename, \ZipArchive::CREATE) !== true) {
            throw new \Exception('Could not create zip file!');
        }

        $emotionData = $this->transformer->transform($emotionId, true);

        $preset = $this->createHiddenPreset($emotionData);
        $preset = $this->synchronizer->prepareAssetExport($preset);

        $presetData = $preset->getPresetData();
        $collectedAssets = $this->collectElementAssets($presetData);

        $zip->addEmptyDir('images');

        foreach ($collectedAssets as $key => &$path) {
            $fileContent = $this->mediaService->read($path);
            $zipPath = 'images/' . basename($path);

            $zip->addFromString($zipPath, $fileContent);
            $path = $zipPath;
        }
        unset($path);

        $exportData = [
            'requiredPlugins' => json_decode($preset->getRequiredPlugins(), true),
            'presetData' => $presetData,
            'assets' => $collectedAssets,
        ];

        $zip->addFromString('emotion.json', json_encode($exportData));

        if (!$zip->close()) {
            throw new \Exception('Could not close zip file!');
        }

        $this->presetResource->delete($preset->getId());

        return $filename;
    }

    /**
     * @param array $emotionData
     *
     * @return \Shopware\Models\Emotion\Preset
     */
    private function createHiddenPreset(array $emotionData)
    {
        $presetData = json_decode($emotionData['presetData'], true);

        $presetData = [
            'name' => $presetData['name'] . time(),
            'hidden' => true,
            'presetData' => $emotionData['presetData'],
            'requiredPlugins' => $emotionData['requiredPlugins'],
        ];

        return $this->presetResource->create($presetData);
    }

    /**
     * @param string $presetData
     *
     * @return array
     */
    private function collectElementAssets($presetData)
    {
        $assets = [];
        $decodedData = json_decode($presetData, true);

        if (array_key_exists('elements', $decodedData)) {
            foreach ($decodedData['elements'] as $element) {
                if (is_array($element['assets'])) {
                    $assets[] = $element['assets'];
                }
            }
            $assets = array_merge(...$assets);
        }

        return $assets;
    }
}
