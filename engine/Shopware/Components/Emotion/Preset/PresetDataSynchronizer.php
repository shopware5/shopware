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

namespace Shopware\Components\Emotion\Preset;

use Doctrine\Common\Collections\ArrayCollection;
use IteratorAggregate;
use Shopware\Components\Emotion\Preset\ComponentHandler\ComponentHandlerInterface;
use Shopware\Components\Emotion\Preset\Exception\PresetAssetImportException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Emotion\Preset;
use Symfony\Component\HttpFoundation\ParameterBag;

class PresetDataSynchronizer implements PresetDataSynchronizerInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var ComponentHandlerInterface[]
     */
    private $componentHandlers;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @param string $rootDir
     */
    public function __construct(ModelManager $modelManager, \Enlight_Event_EventManager $eventManager, IteratorAggregate $componentHandlers, $rootDir)
    {
        $this->modelManager = $modelManager;
        $this->eventManager = $eventManager;

        $this->componentHandlers = $this->registerComponentHandlers(
            iterator_to_array($componentHandlers, false)
        );
        $this->rootDir = $rootDir;
    }

    /**
     * {@inheritdoc}
     */
    public function importElementAssets(Preset $preset, $elementSyncKey)
    {
        if ($preset->getAssetsImported()) {
            throw new PresetAssetImportException('The assets for this preset are already imported.');
        }

        $presetData = json_decode($preset->getPresetData(), true);

        if (!$presetData || !is_array($presetData) || !array_key_exists('elements', $presetData)) {
            throw new PresetAssetImportException(sprintf('The preset data of the %s preset seems to be invalid.', $preset->getName()));
        }

        // continue if no sync data present or we just have an assets key which is empty
        if (empty($presetData['syncData'])
            || (count($presetData['syncData']) === 1 && empty($presetData['syncData']['assets']))
        ) {
            return;
        }

        $element = $this->findElementBySyncKey($presetData, $elementSyncKey);

        if (!$element) {
            throw new PresetAssetImportException('The processed element could not be found in preset data.');
        }

        $handler = $this->findComponentHandler($element);

        if (!$handler) {
            return;
        }

        if (!isset($presetData['syncData']['importedAssets'])) {
            $presetData['syncData']['importedAssets'] = [];
        }

        $syncData = new ParameterBag($presetData['syncData']);

        $this->setAssetPaths($syncData);

        try {
            $element = $handler->import($element, $syncData);
        } catch (\Exception $e) {
            throw new PresetAssetImportException($e->getMessage());
        }

        $presetData['syncData'] = $syncData->all();
        $preset->setPresetData(json_encode($presetData));

        $this->synchronizeData($preset, $element);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareAssetExport(Preset $preset)
    {
        $presetData = json_decode($preset->getPresetData(), true);

        $presetData['syncData']['assets'] = [];
        $syncData = new ParameterBag($presetData['syncData']);

        foreach ($presetData['elements'] as &$element) {
            $handler = $this->findComponentHandler($element);

            if (!$handler) {
                continue;
            }

            $element = $handler->export($element, $syncData);
        }
        unset($element);

        $presetData['syncData'] = $syncData->all();

        $preset->setPresetData(json_encode($presetData));
        $this->modelManager->flush($preset);

        return $preset;
    }

    /**
     * @throws PresetAssetImportException
     */
    private function synchronizeData(Preset $preset, array $element)
    {
        $presetData = json_decode($preset->getPresetData(), true);

        foreach ($presetData['elements'] as &$presetElement) {
            if ($presetElement['syncKey'] === $element['syncKey']) {
                $presetElement = $element;

                break;
            }
        }
        unset($presetElement);

        $preset->setPresetData(json_encode($presetData));

        $this->modelManager->flush($preset);
    }

    /**
     * @param string $elementSyncKey
     *
     * @return array
     */
    private function findElementBySyncKey(array $presetData, $elementSyncKey)
    {
        foreach ($presetData['elements'] as $element) {
            if ($element['syncKey'] === $elementSyncKey) {
                return $element;
            }
        }

        return [];
    }

    /**
     * @return array
     */
    private function registerComponentHandlers(array $defaultComponentHandlers)
    {
        $componentHandlers = new ArrayCollection();
        $componentHandlers = $this->eventManager->collect(
            'Shopware_Emotion_Collect_Preset_Component_Handlers',
            $componentHandlers
        );

        return array_merge($defaultComponentHandlers, $componentHandlers->toArray());
    }

    /**
     * @return ComponentHandlerInterface|bool
     */
    private function findComponentHandler(array $element)
    {
        if (!isset($element['componentId'])) {
            return false;
        }

        $componentType = $element['componentId'];

        foreach ($this->componentHandlers as $handler) {
            if ($handler->supports($componentType)) {
                return $handler;
            }
        }

        return false;
    }

    /**
     * Sets paths for assets coming from plugins which use relative paths.
     */
    private function setAssetPaths(ParameterBag $syncData)
    {
        $assets = $syncData->get('assets');

        foreach ($assets as $key => &$path) {
            if (strpos($path, '/custom/') === 0) {
                $path = 'file://' . $this->rootDir . $path;
            }
        }
        unset($path);

        $syncData->set('assets', $assets);
    }
}
