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
    public function transform($emotionId, $keepName = false)
    {
        $emotionData = $this->getEmotionData($emotionId);

        return $this->cleanupEmotionData($emotionData, $keepName);
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
    private function cleanupEmotionData(array $emotionData, $keepName = false)
    {
        unset(
            $emotionData['id'],
            $emotionData['parentId'],
            $emotionData['userId'],
            $emotionData['validFrom'],
            $emotionData['validTo'],
            $emotionData['createDate'],
            $emotionData['modified'],
            $emotionData['previewId'],
            $emotionData['previewSecret']
        );

        if (!$keepName) {
            unset($emotionData['name']);
        }

        $emotionData['active'] = false;

        $requiredPlugins = $this->getRequiredPlugins($emotionData['elements']);
        $emotionData['elements'] = $this->cleanupElements($emotionData['elements']);
        $data['requiredPlugins'] = $requiredPlugins;
        $data['presetData'] = json_encode($emotionData);

        return $data;
    }

    /**
     * @param array $elements
     *
     * @return array
     */
    private function cleanupElements(array $elements)
    {
        /** @var array $element */
        foreach ($elements as &$element) {
            unset(
                $element['id'],
                $element['emotionId']
            );

            $element['componentId'] = $element['component']['xType'];
            $fieldMapping = [];

            foreach ($element['component']['fields'] as $field) {
                $fieldMapping[$field['id']] = $field;
            }

            $element = $this->cleanupElementData($element, $fieldMapping);
            $element['viewports'] = $this->cleanupElementViewports($element['viewports']);
            $element['syncKey'] = uniqid('preset-element-', true);
            unset($element['component']);
        }

        return $elements;
    }

    /**
     * @param array $element
     * @param array $fieldMapping
     *
     * @return array
     */
    private function cleanupElementData(array $element, array $fieldMapping)
    {
        $elementData = $element['data'];

        foreach ($elementData as &$data) {
            unset(
                $data['id'],
                $data['emotionId'],
                $data['elementId']
            );
            $field = $fieldMapping[$data['fieldId']];

            if ($field) {
                $data['fieldId'] = $field['name'];
                $data['key'] = $field['name'];
                $data['valueType'] = $field['valueType'];
                $data['componentId'] = $element['componentId'];
            }
        }
        unset($data);

        $element['data'] = $elementData;

        return $element;
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
}
