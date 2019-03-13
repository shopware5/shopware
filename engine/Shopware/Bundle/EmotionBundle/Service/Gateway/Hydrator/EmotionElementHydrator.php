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

namespace Shopware\Bundle\EmotionBundle\Service\Gateway\Hydrator;

use Shopware\Bundle\EmotionBundle\Struct\Element;
use Shopware\Bundle\EmotionBundle\Struct\ElementConfig;
use Shopware\Bundle\EmotionBundle\Struct\ElementViewport;
use Shopware\Bundle\EmotionBundle\Struct\Library\Component;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\Hydrator;

class EmotionElementHydrator extends Hydrator
{
    /**
     * @return Element
     */
    public function hydrate(array $data, array $config = [], array $viewports = [])
    {
        $element = $this->assignData($data);

        $this->assignComponent($element, $data);
        $this->assignConfig($element, $config);
        $this->assignViewports($element, $viewports);

        return $element;
    }

    /**
     * @return Element
     */
    private function assignData(array $data)
    {
        $element = new Element();

        $element->setId((int) $data['__emotionElement_id']);
        $element->setEmotionId((int) $data['__emotionElement_emotion_id']);
        $element->setComponentId((int) $data['__emotionElement_component_id']);
        $element->setStartRow((int) $data['__emotionElement_start_row']);
        $element->setEndRow((int) $data['__emotionElement_end_row']);
        $element->setStartCol((int) $data['__emotionElement_start_col']);
        $element->setEndCol((int) $data['__emotionElement_end_col']);
        $element->setCssClass($data['__emotionElement_css_class']);

        return $element;
    }

    private function assignComponent(Element $element, array $data)
    {
        $component = new Component();

        $component->setId((int) $data['__emotionLibraryComponent_id']);
        $component->setName($data['__emotionLibraryComponent_name']);
        $component->setType($data['__emotionLibraryComponent_x_type']);
        $component->setConvertFunction($data['__emotionLibraryComponent_convert_function']);
        $component->setDescription($data['__emotionLibraryComponent_description']);
        $component->setTemplate($data['__emotionLibraryComponent_template']);
        $component->setCssClass($data['__emotionLibraryComponent_cls']);
        $component->setPluginId($data['__emotionLibraryComponent_plugin_id'] !== null ? (int) $data['__emotionLibraryComponent_plugin_id'] : null);

        $element->setComponent($component);
    }

    private function assignConfig(Element $element, array $config = [])
    {
        $config = $this->assignConfigTranslation($config);

        $elementConfig = new ElementConfig($config);

        $element->setConfig($elementConfig);
    }

    private function assignViewports(Element $element, array $viewports = [])
    {
        $elementViewports = [];

        foreach ($viewports as $rawViewportData) {
            $viewport = new ElementViewport();
            $viewport->setId((int) $rawViewportData['__emotionElementViewport_id']);
            $viewport->setEmotionId((int) $rawViewportData['__emotionElementViewport_emotion_id']);
            $viewport->setElementId((int) $rawViewportData['__emotionElementViewport_element_id']);
            $viewport->setAlias($rawViewportData['__emotionElementViewport_alias']);
            $viewport->setStartRow((int) $rawViewportData['__emotionElementViewport_start_row']);
            $viewport->setStartCol((int) $rawViewportData['__emotionElementViewport_start_col']);
            $viewport->setEndCol((int) $rawViewportData['__emotionElementViewport_end_col']);
            $viewport->setEndRow((int) $rawViewportData['__emotionElementViewport_end_row']);
            $viewport->setVisible((bool) $rawViewportData['__emotionElementViewport_visible']);

            $elementViewports[] = $viewport;
        }

        $element->setViewports($elementViewports);
    }

    /**
     * @return array
     */
    private function assignConfigTranslation(array $config)
    {
        if (count($config)) {
            $translation = $this->getTranslation(reset($config), '__emotionElementValue', [], null, false);

            foreach ($config as &$configItem) {
                $key = $configItem['__emotionLibraryComponentField_name'];
                if (array_key_exists($key, $translation)) {
                    $configItem['__emotionElementValue_value'] = $translation[$key];
                }
            }
        }

        return $config;
    }
}
