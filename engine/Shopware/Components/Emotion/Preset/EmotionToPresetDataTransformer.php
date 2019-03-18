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
     * @param int $emotionId
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
            ->orderBy('elements.id')
            ->setParameter('emotionId', $emotionId)
            ->getQuery()
            ->getSingleResult(Query::HYDRATE_ARRAY);
    }

    /**
     * @param bool $keepName
     *
     * @return array
     */
    private function cleanupEmotionData(array $emotionData, $keepName = false)
    {
        unset(
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
        $emotionTranslations = $this->getTranslations($emotionData['elements'], $emotionData['id']);

        unset($emotionData['id']);

        $emotionData['elements'] = $this->cleanupElements($emotionData['elements']);
        $data['emotionTranslations'] = json_encode($emotionTranslations);
        $data['requiredPlugins'] = $requiredPlugins;
        $data['presetData'] = json_encode($emotionData);

        return $data;
    }

    /**
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
     * @return array
     */
    private function getRequiredPlugins(array $elements)
    {
        $pluginIds = [];
        $requiredPlugins = [];

        /** @var array $element */
        foreach ($elements as $element) {
            $pluginId = $element['component']['pluginId'];
            if ($pluginId && !in_array($pluginId, $pluginIds, false)) {
                $pluginIds[] = $pluginId;
            }
        }

        if (!empty($pluginIds)) {
            $pluginData = $this->getRequiredPluginsById($pluginIds);
            $requiredPlugins = array_map(function ($plugin) {
                return ['name' => $plugin['name'], 'version' => $plugin['version'], 'label' => $plugin['label']];
            }, $pluginData);
        }

        return $requiredPlugins;
    }

    /**
     * @return array
     */
    private function getRequiredPluginsById(array $pluginIds)
    {
        return $this->modelManager->getConnection()->createQueryBuilder()
            ->select('plugin.name, plugin.label, plugin.version')
            ->from('s_core_plugins', 'plugin')
            ->where('plugin.id IN (:ids)')
            ->setParameter('ids', $pluginIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param int $emotionId
     *
     * @return array
     */
    private function getTranslations(array $elements, $emotionId)
    {
        $elementIds = array_column($elements, 'id');
        // this is important because id expresses the order elements were created
        // and will be created on import
        sort($elementIds);

        $translations = $this->getEmotionTranslation($emotionId, $elementIds);
        $elementIds = array_flip($elementIds);

        foreach ($translations as $key => &$translation) {
            if (!$translation['locale'] || !$translation['shop']) {
                unset($translations[$key]);
                continue;
            }
            if ($translation['objecttype'] === 'emotionElement' && array_key_exists($translation['objectkey'], $elementIds)) {
                $translation['objectkey'] = 'elementIndex-' . $elementIds[$translation['objectkey']];
                continue;
            }
            unset($translation['objectkey']);
        }
        unset($translation);

        return array_values($translations);
    }

    private function getEmotionTranslation($emotionId, array $elementIds)
    {
        return $this->modelManager->getConnection()->createQueryBuilder()
            ->select('translation.objectkey, translation.objecttype, translation.objectdata, locale.locale, shop.name as shop')
            ->from('s_core_translations', 'translation')
            ->leftJoin('translation', 's_core_shops', 'shop', 'translation.objectlanguage = shop.id')
            ->leftJoin('shop', 's_core_locales', 'locale', 'shop.locale_id = locale.id')
            ->where('translation.objecttype = "emotion" AND translation.objectkey = :emotionId')
            ->orWhere('translation.objectkey IN (:ids)')
            ->setParameter('emotionId', $emotionId)
            ->setParameter('ids', $elementIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll();
    }
}
