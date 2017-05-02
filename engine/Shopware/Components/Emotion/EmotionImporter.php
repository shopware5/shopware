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
use Shopware\Components\Api\Resource\EmotionPreset;
use Shopware\Components\Emotion\Exception\EmotionImportException;
use Symfony\Component\Filesystem\Filesystem;

class EmotionImporter implements EmotionImporterInterface
{
    /**
     * @var EmotionPreset
     */
    private $presetResource;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(EmotionPreset $presetResource, Filesystem $filesystem, Connection $connection)
    {
        $this->presetResource = $presetResource;
        $this->filesystem = $filesystem;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function import($filePath)
    {
        $extractPath = dirname($filePath) . '/' . pathinfo($filePath, PATHINFO_FILENAME);
        $emotionData = $this->extractEmotionArchive($filePath, $extractPath);

        if (!$emotionData['presetData']) {
            throw new EmotionImportException('No emotion data available');
        }

        if ($emotionData['requiredPlugins']) {
            $this->checkRequiredPlugins($emotionData['requiredPlugins']);
        }

        if ($emotionData['assets']) {
            $emotionData = $this->spreadElementAssets($emotionData, $extractPath);
        }

        $presetData = $emotionData['presetData'];

        $preset = $this->presetResource->create([
            'name' => pathinfo($filePath, PATHINFO_FILENAME),
            'presetData' => $presetData,
            'hidden' => true,
            'assetsImported' => false,
        ]);

        return $preset;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanupImport($filePath, $presetId = null)
    {
        $extractPath = dirname($filePath) . '/' . pathinfo($filePath, PATHINFO_FILENAME);

        $this->filesystem->remove($extractPath);

        if ($presetId) {
            $this->presetResource->delete($presetId);
        }
    }

    /**
     * @param string $filePath
     * @param string $extractPath
     *
     * @throws \Exception
     *
     * @return array
     */
    private function extractEmotionArchive($filePath, $extractPath)
    {
        $zip = new \ZipArchive();

        if ($zip->open($filePath) !== true) {
            throw new EmotionImportException('Could not open zip file!');
        }

        if ($zip->locateName('emotion.json') === false) {
            throw new EmotionImportException('Missing emotion data file!');
        }

        if ($zip->extractTo($extractPath) !== true) {
            throw new EmotionImportException('Could not extract zip file!');
        }

        $zip->close();
        $this->filesystem->remove($filePath);

        return json_decode(file_get_contents($extractPath . '/emotion.json'), true);
    }

    /**
     * @param array $requiredPlugins
     *
     * @throws EmotionImportException
     */
    private function checkRequiredPlugins(array $requiredPlugins)
    {
        $technicalNames = array_column($requiredPlugins, 'name');
        $missingPlugins = [];

        $plugins = $this->connection->createQueryBuilder()
            ->select([
                'plugin.name as array_key',
                '(plugin.id > 0) as plugin_exists',
                'plugin.name as plugin_name',
                'plugin.label as plugin_label',
                'plugin.active',
                'plugin.installation_date IS NOT NULL as installed',
                'plugin.version as currentVersion',
            ])
            ->from('s_core_plugins', 'plugin')
            ->where('plugin.name IN (:names)')
            ->setParameter(':names', $technicalNames, Connection::PARAM_STR_ARRAY)
            ->execute()->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);

        foreach ($requiredPlugins as $requiredPlugin) {
            $plugin = $plugins[$requiredPlugin['name']];

            if (!$plugin
                || !$plugin['plugin_exists']
                || !$plugin['active']
                || !$plugin['installed']
                || version_compare($plugin['currentVersion'], $requiredPlugin['version'], '<')
            ) {
                $missingPlugins[] = sprintf('%s (%s)', $requiredPlugin['name'], $requiredPlugin['version']);
            }
        }

        if ($missingPlugins) {
            throw new EmotionImportException('The following plugins are required to use this shopping world: <br>' . implode('<br>', $missingPlugins));
        }
    }

    /**
     * @param $emotionData
     * @param $extractPath
     *
     * @return array
     */
    private function spreadElementAssets($emotionData, $extractPath)
    {
        $assets = $emotionData['assets'];
        $presetData = json_decode($emotionData['presetData'], true);

        foreach ($presetData['elements'] as &$element) {
            if (isset($element['assets'])) {
                foreach ($element['assets'] as $key => $path) {
                    $element['assets'][$key] = 'file://' . $extractPath . '/' . $assets[$key];
                }
            }
        }
        unset($element);

        $emotionData['presetData'] = json_encode($presetData);

        return $emotionData;
    }
}
