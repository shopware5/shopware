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
        $presetData['elements'] = $this->refreshElementData($presetData['elements']);

        $preset->setPresetData(json_encode($presetData));
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

                if (!empty($data['value'] && strtolower($field['valueType']) === 'json')) {
                    $data['value'] = json_decode($data['value'], true);
                }

                if (in_array($field['name'], ['file', 'image', 'fallback_picture'], true)) {
                    $data['value'] = $this->mediaService->getUrl($data['value']);
                }

                if (is_array($data['value']) && in_array($field['name'], ['selected_manufacturers', 'banner_slider'], true)) {
                    foreach ($data['value'] as $key => &$value) {
                        if (isset($value['path'])) {
                            $value['path'] = $this->mediaService->getUrl($value['path']);
                        }
                    }
                    unset($value);
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
        $collectedFields = [];

        foreach ($elements as $element) {
            $component = $element['component'];
            $collectedComponents[$component['name']] = $component['id'];

            foreach ($component['fields'] as $field) {
                $collectedFields[$field['name']] = $field['id'];
            }
        }

        $componentNames = array_keys($collectedComponents);
        $components = $this->getComponentData($componentNames);
        $componentMapping = array_combine($collectedComponents, array_merge($collectedComponents, $components));

        $fieldNames = array_keys($collectedFields);
        $fields = $this->getFieldData($fieldNames);
        $fieldMapping = array_combine($collectedFields, array_merge($collectedFields, $fields));

        return $this->processRefresh($elements, $componentMapping, $fieldMapping);
    }

    /**
     * @param array $elements
     * @param array $componentMapping
     * @param array $fieldMapping
     *
     * @return array
     */
    private function processRefresh(array $elements, array $componentMapping, array $fieldMapping)
    {
        foreach ($elements as &$element) {
            $component = $element['component'];
            $element['componentId'] = $componentMapping[$element['componentId']];

            foreach ($component['fields'] as &$field) {
                $field['id'] = $fieldMapping[$field['id']];
                $field['componentId'] = $componentMapping[$field['componentId']];
            }
            unset($field);

            foreach ($element['data'] as &$data) {
                $data['componentId'] = $componentMapping[$data['componentId']];
                $data['fieldId'] = $fieldMapping[$data['fieldId']];
            }
        }

        return $elements;
    }

    /**
     * @param array $componentNames
     *
     * @return array
     */
    private function getComponentData(array $componentNames)
    {
        $queryResult = $this->modelManager->getConnection()->createQueryBuilder()
            ->select('id, name')
            ->from('s_library_component', 'component')
            ->where('component.name IN (:names)')
            ->setParameter('names', $componentNames, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        return array_flip($queryResult);
    }

    /**
     * @param array $fieldNames
     *
     * @return array
     */
    private function getFieldData(array $fieldNames)
    {
        $queryResult = $this->modelManager->getConnection()->createQueryBuilder()
            ->select('id, name')
            ->from('s_library_component_field', 'field')
            ->where('name IN (:names)')
            ->setParameter('names', $fieldNames, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        return array_flip($queryResult);
    }
}
