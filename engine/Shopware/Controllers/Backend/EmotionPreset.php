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

use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Bundle\PluginInstallerBundle\Context\PluginsByTechnicalNameRequest;
use Shopware\Models\Emotion\Preset;

class Shopware_Controllers_Backend_EmotionPreset extends Shopware_Controllers_Backend_ExtJs
{
    public function listAction()
    {
        $resource = $this->container->get('shopware.api.emotionpreset');

        $presets = $resource->getList($this->getLocale());

        $presets = $this->enrichImagePaths($presets);
        $presets = $this->enrichPlugins($presets);

        $this->View()->assign([
            'success' => true,
            'data' => $presets,
        ]);
    }

    /**
     * Model event listener function which fired when the user configure an emotion preset over the backend
     * module and clicks the save button.
     */
    public function saveAction()
    {
        $resource = $this->container->get('shopware.api.emotionpreset');
        $transformer = $this->container->get('shopware.emotion.emotion_presetdata_transformer');
        $data = $this->Request()->getParams();

        if (!$data['emotionId']) {
            $this->View()->assign(['success' => false]);

            return;
        }

        $data = array_merge($data, $transformer->transform($data['emotionId']));

        if ($data['id']) {
            $resource->update($data['id'], $data, $this->getLocale());
        } else {
            $resource->create($data, $this->getLocale());
        }

        $this->View()->assign(['success' => true]);
    }

    public function deleteAction()
    {
        $id = $this->Request()->getParam('id');

        $resource = $this->container->get('shopware.api.emotionpreset');

        $resource->delete($id);

        $this->View()->assign(['success' => true]);
    }

    /**
     * Imports preset assets and synchronizes media path inside the
     * preset data based on unique asset key.
     */
    public function importAssetAction()
    {
        $id = $this->Request()->getParam('id');
        $syncKey = $this->Request()->getParam('syncKey');

        if (!$id || !$syncKey) {
            $this->View()->assign(['success' => false]);

            return;
        }

        /** @var Preset $preset */
        $preset = $this->container->get('models')->getRepository(Preset::class)->find($id);

        if (!$preset || !$syncKey || $preset->getAssetsImported()) {
            $this->View()->assign(['success' => false]);

            return;
        }

        $synchronizerService = $this->container->get('shopware.emotion.preset_data_synchronizer');
        $synchronizerService->importElementAssets($preset, $syncKey);

        $this->View()->assign(['success' => true]);
    }

    /**
     * @return string
     */
    private function getLocale()
    {
        /** @var Shopware_Components_Auth $auth */
        if (!$auth = $this->container->get('Auth')) {
            return 'de_DE';
        }
        if (!$identity = $auth->getIdentity()) {
            return 'de_DE';
        }
        /** @var \Shopware\Models\Shop\Locale $locale */
        if (!$locale = $identity->locale) {
            return 'de_DE';
        }

        return $locale->getLocale();
    }

    /**
     * @param array $presets
     *
     * @return array
     */
    private function enrichImagePaths(array $presets)
    {
        foreach ($presets as &$preset) {
            $preset['thumbnailUrl'] = $this->getImagePath($preset['thumbnail']);
            $preset['previewUrl'] = $this->getImagePath($preset['preview']);
        }

        return $presets;
    }

    /**
     * @param $path
     *
     * @return null|string
     */
    private function getImagePath($path)
    {
        if (empty($path)) {
            return $path;
        }
        /** @var MediaService $mediaService */
        $mediaService = $this->container->get('shopware_media.media_service');

        if (strpos($path, 'media') === 0) {
            return $mediaService->getUrl($path);
        }

        return $this->View()->fetch(sprintf('string:{url file="%s"}', $path));
    }

    /**
     * @param array[] $presets
     *
     * @return array[]
     */
    private function enrichPlugins(array $presets)
    {
        $pluginManager = $this->container->get('shopware_plugininstaller.plugin_service_view');

        $names = [];
        foreach ($presets as $preset) {
            $names = array_merge($names, array_column($preset['requiredPlugins'], 'name'));
        }
        if (empty($names)) {
            return $presets;
        }

        $plugins = $pluginManager->getPlugins(
            new PluginsByTechnicalNameRequest($this->getLocale(), Shopware::VERSION, $names)
        );

        foreach ($presets as &$preset) {
            foreach ($preset['requiredPlugins'] as &$plugin) {
                $plugin['in_store'] = array_key_exists(strtolower($plugin['name']), $plugins);
            }
        }

        return $presets;
    }
}
