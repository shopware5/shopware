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
use Doctrine\ORM\Query;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Emotion\Emotion;

class EmotionToPresetDataTransformer implements EmotionToPresetDataTransformerInterface
{
    /** @var ModelManager $modelManager */
    private $modelManager;

    /**
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($emotionId)
    {
        $emotionData = $this->getEmotionData($emotionId);

        return $this->cleanupEmotionData($emotionData);
    }

    /**
     * @param $emotionId
     *
     * @throws NoResultException
     *
     * @return array
     */
    private function getEmotionData($emotionId)
    {
        $builder = $this->modelManager->createQueryBuilder();

        return $builder->select([
                'emotion',
                'elements',
                'viewports',
                'component',
                'data',
                'fields',
            ])
            ->from(Emotion::class, 'emotion')
            ->leftJoin('emotion.elements', 'elements')
            ->leftJoin('elements.viewports', 'viewports')
            ->leftJoin('elements.component', 'component')
            ->leftJoin('component.fields', 'fields')
            ->leftJoin('elements.data', 'data')
            ->where('emotion.id = :emotionId')
            ->setParameter('emotionId', $emotionId)
            ->getQuery()
            ->getSingleResult(Query::HYDRATE_ARRAY);
    }

    /**
     * @param array $emotionData
     *
     * @return array
     */
    private function cleanupEmotionData(array $emotionData)
    {
        unset(
            $emotionData['id'],
            $emotionData['parentId'],
            $emotionData['name'],
            $emotionData['userId'],
            $emotionData['validFrom'],
            $emotionData['validTo'],
            $emotionData['createDate'],
            $emotionData['modified'],
            $emotionData['previewId'],
            $emotionData['previewSecret']
        );

        $emotionData['active'] = false;

        $requiredPlugins = $this->getRequiredPlugins($emotionData['elements']);
        $emotionData['elements'] = $this->cleanupElements($emotionData['elements'], $requiredPlugins);
        $data['requiredPlugins'] = $requiredPlugins;
        $data['presetData'] = json_encode($emotionData);

        return $data;
    }

    /**
     * @param array $elements
     * @param array $requiredPlugins
     *
     * @return array
     */
    private function cleanupElements(array $elements, array $requiredPlugins)
    {
        if ($requiredPlugins) {
            $requiredPlugins = $this->getPluginsById($requiredPlugins);
        }

        $componentIdentifiers = [];
        $fieldIdentifiers = [];

        /** @var array $element */
        foreach ($elements as &$element) {
            unset(
                $element['id'],
                $element['emotionId']
            );

            if (isset($componentIdentifiers[$element['componentId']])) {
                $componentId = $componentIdentifiers[$element['componentId']];
            } else {
                $componentId = uniqid('preset-component-', false);
                $componentIdentifiers[$element['componentId']] = $componentId;
            }

            $element['componentId'] = $componentId;
            $element['component']['id'] = $componentId;
            $element['syncKey'] = uniqid('preset-element-', false);

            if ($requiredPlugins && array_key_exists('pluginId', $element['component'])) {
                $element['component']['plugin'] = $requiredPlugins[$element['component']['pluginId']];
                unset($element['component']['pluginId']);
            }

            $fieldMapping = $this->createFieldMapping($element['component']['fields'], $fieldIdentifiers, $componentId);
            $element['component']['fields'] = array_values($fieldMapping);
            $element['data'] = $this->cleanupElementData($element['data'], $fieldMapping);
            $element['viewports'] = $this->cleanupElementViewports($element['viewports']);
        }

        return $elements;
    }

    /**
     * @param array  $fields
     * @param array  $fieldIdentifiers
     * @param string $componentId
     *
     * @return array
     */
    private function createFieldMapping(array $fields, array &$fieldIdentifiers, $componentId)
    {
        $fieldMapping = [];

        foreach ($fields as $field) {
            $id = $field['id'];

            if (isset($fieldIdentifiers[$id])) {
                $generatedId = $fieldIdentifiers[$id];
            } else {
                $generatedId = uniqid('preset-field-', false);
                $fieldIdentifiers[$id] = $generatedId;
            }

            $field['componentId'] = $componentId;
            $field['id'] = $generatedId;
            $fieldMapping[$id] = $field;
        }

        return $fieldMapping;
    }

    /**
     * @param array $elementData
     * @param array $fieldMapping
     *
     * @return array
     */
    private function cleanupElementData(array $elementData, array $fieldMapping)
    {
        foreach ($elementData as &$data) {
            unset(
                $data['id'],
                $data['emotionId'],
                $data['elementId']
            );
            $field = $fieldMapping[$data['fieldId']];

            if ($field) {
                $data['fieldId'] = $field['id'];
                $data['key'] = $field['name'];
                $data['valueType'] = $field['valueType'];
                $data['componentId'] = $field['componentId'];
            }
        }

        return $elementData;
    }

    /**
     * @param array $viewports
     *
     * @return array
     */
    private function cleanupElementViewports(array $viewports)
    {
        foreach ($viewports as &$viewport) {
            unset(
                $viewport['id'],
                $viewport['emotionId'],
                $viewport['elementId']
            );
        }

        return $viewports;
    }

    /**
     * @param array $elements
     *
     * @return array
     */
    private function getRequiredPlugins(array $elements)
    {
        $pluginIds = [];

        /** @var array $element */
        foreach ($elements as $element) {
            $pluginId = $element['component']['pluginId'];
            if ($pluginId && !in_array($pluginId, $pluginIds, false)) {
                $pluginIds[] = $pluginId;
            }
        }

        return $pluginIds;
    }

    /**
     * @param array $pluginIds
     *
     * @return array
     */
    private function getPluginsById(array $pluginIds)
    {
        return $this->modelManager->getConnection()->createQueryBuilder()
            ->select('id, name')
            ->from('s_core_plugins', 'plugin')
            ->where('plugin.id IN (:ids)')
            ->setParameter('ids', $pluginIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
}
