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

namespace Shopware\Bundle\ContentTypeBundle\Services\XmlReader;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Shopware\Components\Plugin\XmlReader\XmlReaderBase;
use Symfony\Component\Config\Util\XmlUtils;

class ContentTypesReader extends XmlReaderBase
{
    /**
     * @var string
     */
    protected $xsdFile = __DIR__ . '/../../Resources/contenttypes.xsd';

    public function readType(array $xmlFile): array
    {
        $data = $this->read($xmlFile['file']);

        foreach ($data as &$item) {
            $item['source'] = $xmlFile['type'];
        }
        unset($item);

        return $data;
    }

    protected function parseFile(DOMDocument $xml): array
    {
        $nodeList = (new DOMXPath($xml))->query('//types/type');

        return self::parseList($nodeList);
    }

    private static function parseList(DOMNodeList $list): array
    {
        if ($list->length === 0) {
            return [];
        }
        $items = [];

        /** @var DOMElement $item */
        foreach ($list as $item) {
            $item = self::parseItem($item);
            $items[$item['typeName']] = $item;
            unset($items[$item['typeName']]['typeName']);
        }

        return $items;
    }

    private static function parseItem(DOMElement $element): array
    {
        $item = [];
        if ($typeName = self::getElementChildValueByName($element, 'typeName', true)) {
            $item['typeName'] = $typeName;
        }

        if ($name = self::getElementChildValueByName($element, 'name', true)) {
            $item['name'] = $name;
        }

        if ($showInFrontend = self::getElementChildValueByName($element, 'showInFrontend')) {
            $item['showInFrontend'] = (bool) XmlUtils::phpize($showInFrontend);
        }

        if ($menuIcon = self::getElementChildValueByName($element, 'menuIcon')) {
            $item['menuIcon'] = $menuIcon;
        }

        if ($menuPosition = self::getElementChildValueByName($element, 'menuPosition')) {
            $item['menuPosition'] = (int) $menuPosition;
        }

        if ($viewTitleFieldName = self::getElementChildValueByName($element, 'viewTitleFieldName')) {
            $item['viewTitleFieldName'] = $viewTitleFieldName;
        }

        if ($viewDescriptionFieldName = self::getElementChildValueByName($element, 'viewDescriptionFieldName')) {
            $item['viewDescriptionFieldName'] = $viewDescriptionFieldName;
        }

        if ($viewImageFieldName = self::getElementChildValueByName($element, 'viewImageFieldName')) {
            $item['viewImageFieldName'] = $viewImageFieldName;
        }

        if ($viewMetaTitleFieldName = self::getElementChildValueByName($element, 'viewMetaTitleFieldName')) {
            $item['viewMetaTitleFieldName'] = $viewMetaTitleFieldName;
        }

        if ($viewMetaDescriptionFieldName = self::getElementChildValueByName($element, 'viewMetaDescriptionFieldName')) {
            $item['viewMetaDescriptionFieldName'] = $viewMetaDescriptionFieldName;
        }

        if ($seoUrlTemplate = self::getElementChildValueByName($element, 'seoUrlTemplate')) {
            $item['seoUrlTemplate'] = $seoUrlTemplate;
        }

        if ($seoRobots = self::getElementChildValueByName($element, 'seoRobots')) {
            $item['seoRobots'] = $seoRobots;
        }

        if ($showInFrontend && (empty($viewDescriptionFieldName) || empty($viewImageFieldName) || empty($viewTitleFieldName) || empty($viewMetaTitleFieldName) || empty($viewMetaDescriptionFieldName))) {
            throw new \InvalidArgumentException('Content-Type with enabled showInFrontend requires a viewTitleFieldName, viewDescriptionFieldName, viewImageFieldName, viewMetaTitleFieldName, viewMetaDescriptionFieldName');
        }

        $item['menuParent'] = 'Content';

        if ($menuParent = self::getElementChildValueByName($element, 'menuParent')) {
            $item['menuParent'] = $menuParent;
        }

        if (($fieldSets = $element->getElementsByTagName('fieldSet')) !== null) {
            foreach ($fieldSets as $fieldSet) {
                $item['fieldSets'][] = self::parseFieldset($fieldSet);
            }
        }

        return $item;
    }

    private static function parseField(DOMElement $element): array
    {
        $item = [];

        $fields = ['label', 'showListing', 'searchAble', 'helpText', 'description'];
        $boolFields = ['showListing', 'searchAble'];

        foreach ($fields as $field) {
            if ($value = self::getElementChildValueByName($element, $field)) {
                if (in_array($field, $boolFields, true)) {
                    $value = (bool) XmlUtils::phpize($value);
                }

                $item[$field] = $value;
            }
        }

        $item['name'] = $element->getAttribute('name');
        $item['type'] = $element->getAttribute('type');
        $item['translatable'] = XmlUtils::phpize($element->getAttribute('translatable') ?: false);
        $item['required'] = XmlUtils::phpize($element->getAttribute('required') ?: false);
        $item['custom'] = self::parseCustom($element);
        $item['options'] = self::parseCustom($element, 'options');

        $store = $element->getElementsByTagName('store');

        if ($store->length) {
            /** @var DOMElement $storeElement */
            $storeElement = $store->item(0);
            $item['store'] = self::parseComboboStoreList($storeElement);
        }

        return $item;
    }

    private static function parseFieldset(DOMElement $element): array
    {
        $fieldSet = [];

        if ($label = $element->getAttribute('label')) {
            $fieldSet['label'] = $label;
        }

        $fieldSet['options'] = self::parseCustom($element, 'options');

        if (($fields = $element->getElementsByTagName('field')) !== null) {
            foreach ($fields as $field) {
                $fieldSet['fields'][] = self::parseField($field);
            }
        }

        return $fieldSet;
    }

    private static function parseCustom(DOMElement $element, string $fieldName = 'custom'): array
    {
        $elements = $element->getElementsByTagName($fieldName);
        if (!$elements->length) {
            return [];
        }

        return self::cleanArray(self::xmlToArray($elements->item(0)));
    }

    /**
     * @see https://stackoverflow.com/questions/14553547/what-is-the-best-php-dom-2-array-function
     */
    private static function xmlToArray(DOMNode $root)
    {
        $result = [];

        if ($root->hasAttributes()) {
            foreach ($root->attributes as $attr) {
                $result['@attributes'][$attr->name] = $attr->value;
            }
        }

        if ($root->hasChildNodes()) {
            $children = $root->childNodes;
            if ($children->length == 1) {
                $child = $children->item(0);
                if ($child->nodeType == XML_TEXT_NODE) {
                    $result['_value'] = $child->nodeValue;

                    return count($result) == 1
                        ? $result['_value']
                        : $result;
                }
            }
            $groups = [];
            foreach ($children as $child) {
                if (!isset($result[$child->nodeName])) {
                    $result[$child->nodeName] = self::xmlToArray($child);
                } else {
                    if (!isset($groups[$child->nodeName])) {
                        $result[$child->nodeName] = [$result[$child->nodeName]];
                        $groups[$child->nodeName] = 1;
                    }
                    $result[$child->nodeName][] = self::xmlToArray($child);
                }
            }
        }

        return $result;
    }

    private static function cleanArray(array $haystack): array
    {
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                $haystack[$key] = self::cleanArray($haystack[$key]);
            }

            if (empty($haystack[$key])) {
                unset($haystack[$key]);
            }
        }

        return $haystack;
    }

    private static function parseComboboStoreList(DOMElement $element): array
    {
        $storeOptions = $element->getElementsByTagName('option');
        if ($storeOptions->length === 0) {
            return [];
        }
        $options = [];
        /** @var DOMElement $storeOption */
        foreach ($storeOptions as $storeOption) {
            $value = '';
            $label = '';
            if ($optionValue = $storeOption->getElementsByTagName('value')->item(0)) {
                $value = $optionValue->nodeValue;
            }
            if ($labelNode = $storeOption->getElementsByTagName('label')->item(0)) {
                $label = $labelNode->nodeValue;
            }
            $options[] = [
                'id' => $value,
                'name' => $label,
            ];
        }

        return $options;
    }
}
