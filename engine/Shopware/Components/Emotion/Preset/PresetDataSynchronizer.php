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
use Shopware\Components\Emotion\Preset\ComponentHandler\ComponentHandlerInterface;
use Shopware\Components\Emotion\Preset\Exception\PresetAssetImportException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Emotion\Preset;

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
     * @var array
     */
    private $componentHandlers;

    /**
     * @param ModelManager                $modelManager
     * @param \Enlight_Event_EventManager $eventManager
     * @param array                       $componentHandlers
     */
    public function __construct(ModelManager $modelManager, \Enlight_Event_EventManager $eventManager, array $componentHandlers)
    {
        $this->modelManager = $modelManager;
        $this->eventManager = $eventManager;

        $this->componentHandlers = $componentHandlers;
        $this->componentHandlers = $this->registerComponentHandlers();
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
            throw new PresetAssetImportException('The preset data of the ' . $preset->getName() . ' preset seems to be invalid.');
        }

        $element = $this->findElementBySyncKey($presetData, $elementSyncKey);

        if (!$element) {
            throw new PresetAssetImportException('The processed element could not be found in preset data.');
        }

        $handler = $this->findComponentHandler($element);

        if (!$handler) {
            throw new PresetAssetImportException('Element handler not found. Import not possible.');
        }

        $element = $handler->import($element);
        $this->synchronizeData($preset, $element);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareAssetExport(Preset $preset)
    {
        $presetData = json_decode($preset->getPresetData(), true);

        foreach ($presetData['elements'] as &$element) {
            $handler = $this->findComponentHandler($element);

            if (!$handler) {
                continue;
            }

            $element = $handler->export($element);
        }

        $preset->setPresetData(json_encode($presetData));
        $this->modelManager->flush($preset);

        return $preset;
    }

    /**
     * @param Preset $preset
     * @param array  $element
     *
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
     * @param array  $presetData
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
    private function registerComponentHandlers()
    {
        $componentHandlers = new ArrayCollection();
        $componentHandlers = $this->eventManager->collect(
            'Shopware_Emotion_Collect_Preset_Component_Handlers',
            $componentHandlers
        );

        return array_merge($this->componentHandlers, $componentHandlers->toArray());
    }

    /**
     * @param array $element
     *
     * @return ComponentHandlerInterface|bool
     */
    private function findComponentHandler(array $element)
    {
        if (!isset($element['component']['xType'])) {
            return false;
        }

        $componentType = $element['component']['xType'];

        foreach ($this->componentHandlers as $handler) {
            if ($handler->supports($componentType)) {
                return $handler;
            }
        }

        return false;
    }
}
