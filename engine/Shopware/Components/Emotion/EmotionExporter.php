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
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Api\Resource\EmotionPreset;
use Shopware\Components\Emotion\Preset\EmotionToPresetDataTransformerInterface;
use Shopware\Components\Emotion\Preset\PresetDataSynchronizerInterface;
use Shopware\Components\Slug\SlugInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

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
     * @var MediaServiceInterface
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
     * @param string $rootDirectory
     */
    public function __construct(
        EmotionToPresetDataTransformerInterface $transformer,
        PresetDataSynchronizerInterface $synchronizer,
        EmotionPreset $emotionPresetResource,
        MediaServiceInterface $mediaService,
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
            throw new \Exception(sprintf('Could not create zip file "%s"!', $filename));
        }

        $emotionData = $this->transformer->transform($emotionId, true);

        $preset = $this->createHiddenPreset($emotionData);
        $preset = $this->synchronizer->prepareAssetExport($preset);

        $presetData = json_decode($preset->getPresetData(), true);
        $syncData = new ParameterBag($presetData['syncData']);
        $assets = $syncData->get('assets', []);

        $zip->addEmptyDir('images');

        foreach ($assets as $key => &$path) {
            $fileContent = $this->mediaService->read($path);
            $zipPath = 'images/' . basename($path);

            $zip->addFromString($zipPath, $fileContent);
            $path = $zipPath;
        }
        unset($path);

        $syncData->set('assets', $assets);
        $presetData['syncData'] = $syncData->all();

        $exportData = [
            'requiredPlugins' => json_decode($preset->getRequiredPlugins(), true),
            'emotionTranslations' => $preset->getEmotionTranslations(),
            'presetData' => json_encode($presetData),
        ];

        $zip->addFromString('emotion.json', json_encode($exportData));

        if (!$zip->close()) {
            throw new \Exception(sprintf('Could not close zip file "%s"!', $filename));
        }

        $this->presetResource->delete($preset->getId());

        return $filename;
    }

    /**
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
            'emotionTranslations' => $emotionData['emotionTranslations'],
        ];

        return $this->presetResource->create($presetData);
    }
}
