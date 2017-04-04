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

use Doctrine\DBAL\Connection;
use Doctrine\ORM\NoResultException;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Emotion\Preset;

class PresetLoader implements PresetLoaderInterface
{
    /** @var ModelManager $modelManager */
    private $modelManager;

    /** @var MediaService $mediaService */
    private $mediaService;

    /**
     * @param ModelManager $modelManager
     * @param MediaService $mediaService
     */
    public function __construct(ModelManager $modelManager, MediaService $mediaService)
    {
        $this->modelManager = $modelManager;
        $this->mediaService = $mediaService;
    }

    /**
     * {@inheritdoc}
     */
    public function load($presetId)
    {
        $preset = $this->modelManager->getRepository(Preset::class)->find($presetId);

        if (!$preset) {
            throw new NoResultException('The preset with id ' . $presetId . ' could not be found.');
        }

        $presetData = json_decode($preset->getPresetData(), true);

        if (!$presetData['elements']) {
            return $preset->getPresetData();
        }

        $presetData['elements'] = $this->refreshElementData($presetData['elements']);

        $preset->setPresetData(json_encode($presetData));

        if (!$preset->getAssetsImported()) {
            $preset->setAssetsImported(true);
        }

        $this->modelManager->flush($preset);

        return $this->preparePresetData($presetData);
    }

    /**
     * Prepares preset data to be shown as preview in ui.
     *
     * @param array $presetData
     *
     * @return string $presetData
     */
    private function preparePresetData(array $presetData)
    {
        foreach ($presetData['elements'] as &$element) {
            $fieldMapping = [];
            $fields = $element['component']['fields'];

            foreach ($fields as $field) {
                $fieldMapping[$field['id']] = $field;
            }

            foreach ($element['data'] as &$data) {
                $field = $fieldMapping[$data['fieldId']];

                if (in_array($field['name'], ['file', 'image', 'fallback_picture'], true)) {
                    $data['value'] = $this->mediaService->getUrl($data['value']);
                }

                if (!empty($data['value']) && strtolower($field['valueType']) === 'json') {
                    $data['value'] = json_decode($data['value'], true);
                    if (is_array($data['value'])) {
                        foreach ($data['value'] as $key => &$value) {
                            if (isset($value['path'])) {
                                $value['path'] = $this->mediaService->getUrl($value['path']);
                            }
                        }
                        unset($value);
                    }
                }

                $data['key'] = $field['name'];
                $data['valueType'] = $field['valueType'];
            }
        }

        return json_encode($presetData);
    }

    /**
     * @param array $elements
     *
     * @return array
     */
    private function refreshElementData(array $elements)
    {
        $collectedComponents = [];
        $pluginNames = [];

        foreach ($elements as $element) {
            $component = $element['component'];

            if (array_key_exists('plugin', $component)) {
                $pluginNames[] = $component['plugin'];
            }

            $collectedComponents[$component['name']] = [];
            $collectedComponents[$component['name']]['id'] = $component['id'];
            $collectedComponents[$component['name']]['fields'] = [];

            foreach ($component['fields'] as $field) {
                $collectedComponents[$component['name']]['fields'][$field['name']] = $field['id'];
            }
        }

        $componentMapping = $this->createComponentMapping($collectedComponents);

        $pluginIds = $this->getPluginIds($pluginNames);

        return $this->processRefresh($elements, $componentMapping, $pluginIds);
    }

    /**
     * @param array $elements
     * @param array $componentMapping
     * @param array $pluginIds
     *
     * @return array
     */
    private function processRefresh(array $elements, array $componentMapping, array $pluginIds)
    {
        foreach ($elements as &$element) {
            $componentId = $componentMapping[$element['componentId']]['componentId'];

            if (array_key_exists('plugin', $element['component'])) {
                $element['component']['pluginId'] = $pluginIds[$element['component']['plugin']];
            }

            foreach ($element['component']['fields'] as &$field) {
                $field['id'] = $componentMapping[$element['componentId']][$field['id']];
                $field['componentId'] = $componentId;
            }
            unset($field);

            foreach ($element['data'] as &$data) {
                $data['componentId'] = $componentId;
                $data['fieldId'] = $componentMapping[$element['componentId']][$data['fieldId']];
            }
            unset($data);

            $element['component']['id'] = $componentId;
            $element['componentId'] = $componentId;
        }
        unset($element);

        return $elements;
    }

    /**
     * @param array $componentNames
     *
     * @return array
     */
    private function getComponentData(array $componentNames)
    {
        return $this->modelManager->getConnection()->createQueryBuilder()
            ->select('component.name, component.id as componentId, field.id as fieldId, field.name as fieldName')
            ->from('s_library_component', 'component')
            ->leftJoin('component', 's_library_component_field', 'field', 'field.componentID = component.id')
            ->where('component.name IN (:names)')
            ->setParameter('names', $componentNames, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @param array $technicalNames
     *
     * @return array
     */
    private function getPluginIds(array $technicalNames)
    {
        return $this->modelManager->getConnection()->createQueryBuilder()
            ->select('name, id')
            ->from('s_core_plugins', 'plugin')
            ->where('name IN (:names)')
            ->setParameter('names', $technicalNames, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * @param array $collectedComponents
     *
     * @return array
     */
    private function createComponentMapping(array $collectedComponents)
    {
        $componentNames = array_keys($collectedComponents);
        $componentData = $this->getComponentData($componentNames);

        $componentMapping = [];

        foreach ($collectedComponents as $componentName => $componentDetail) {
            $componentFields = $componentData[$componentName];
            $fields = $componentDetail['fields'];

            $componentMapping[$componentDetail['id']] = [];

            foreach ($componentFields as $field) {
                if (!isset($componentMapping[$componentDetail['id']]['componentId'])) {
                    $componentMapping[$componentDetail['id']]['componentId'] = $field['componentId'];
                }
                $fields[$field['fieldName']] = $field['fieldId'];
            }

            $fieldMapping = array_combine($componentDetail['fields'], $fields);

            $componentMapping[$componentDetail['id']] = array_merge(
                $componentMapping[$componentDetail['id']], $fieldMapping
            );
        }

        return $componentMapping;
    }
}
