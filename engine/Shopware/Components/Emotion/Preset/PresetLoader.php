<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components\Emotion\Preset;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\NoResultException;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Emotion\Library\Component;
use Shopware\Models\Emotion\Library\Field;
use Shopware\Models\Emotion\Preset;

class PresetLoader implements PresetLoaderInterface
{
    private ModelManager $modelManager;

    private MediaServiceInterface $mediaService;

    public function __construct(ModelManager $modelManager, MediaServiceInterface $mediaService)
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
            throw new NoResultException();
        }

        $presetData = json_decode($preset->getPresetData(), true);

        if (empty($presetData['elements'])) {
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

    private function refreshElementData(array $elements): array
    {
        $collectedComponents = array_column($elements, 'componentId');
        $collectedComponents = array_keys(array_flip($collectedComponents));

        $components = $this->getComponentData($collectedComponents);

        if ($components) {
            foreach ($elements as &$element) {
                $componentIdentifier = $element['componentId'];
                $element['component'] = $components[$componentIdentifier];
                $element['componentId'] = $element['component']['id'];

                $fieldMapping = [];
                foreach ($element['component']['fields'] as $field) {
                    $fieldMapping[$field['name']] = $field;
                }

                foreach ($element['data'] as &$data) {
                    $data['componentId'] = $element['componentId'];
                    $data['fieldId'] = $fieldMapping[$data['fieldId']]['id'] ?? null;
                }
                unset($data);
            }
            unset($element);
        }

        return $elements;
    }

    /**
     * Prepares preset data to be shown as preview in ui.
     *
     * @return string $presetData
     */
    private function preparePresetData(array $presetData): string
    {
        foreach ($presetData['elements'] as &$element) {
            $fieldMapping = [];
            $fields = $element['component']['fields'];

            foreach ($fields as $field) {
                $fieldMapping[$field['id']] = $field;
            }

            foreach ($element['data'] as &$data) {
                $field = $fieldMapping[$data['fieldId']] ?? null;
                if ($field === null) {
                    continue;
                }

                if (\in_array($field['name'], ['file', 'image', 'fallback_picture'], true)) {
                    $data['value'] = $this->mediaService->getUrl($data['value']);
                }

                if (!empty($data['value']) && strtolower($field['valueType']) === Field::VALUE_TYPE_JSON) {
                    $data['value'] = json_decode($data['value'], true);
                    if (\is_array($data['value'])) {
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

    private function getComponentData(array $collectedComponents): array
    {
        return $this->modelManager->createQueryBuilder()
            ->select('component', 'fields')
            ->from(Component::class, 'component', 'component.xType')
            ->leftJoin('component.fields', 'fields')
            ->where('component.xType IN (:components)')
            ->setParameter('components', $collectedComponents, Connection::PARAM_STR_ARRAY)
            ->getQuery()
            ->getArrayResult();
    }
}
