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
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Emotion\Library\Component;
use Shopware\Models\Emotion\Preset;

class PresetLoader implements PresetLoaderInterface
{
    /** @var ModelManager $modelManager */
    private $modelManager;

    /** @var MediaServiceInterface $mediaService */
    private $mediaService;

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
     * @return array
     */
    private function refreshElementData(array $elements)
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
                    $data['fieldId'] = $fieldMapping[$data['fieldId']]['id'];
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
     * @return array
     */
    private function getComponentData(array $collectedComponents)
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
