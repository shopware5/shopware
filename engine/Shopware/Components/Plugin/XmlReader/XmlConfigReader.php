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

namespace Shopware\Components\Plugin\XmlReader;

/**
 * Class XmlConfigReader
 *
 * @package Shopware\Components\Plugin\XmlReader
 */
class XmlConfigReader extends XmlReaderBase
{
    protected $xsdFile = __DIR__ . '/../schema/config.xsd';

    /**
     * {@inheritdoc}
     */
    protected function parseFile(\DOMDocument $xml)
    {
        $xpath = new \DOMXPath($xml);

        $form = [];
        $form['label'] = self::parseTranslatableNodeList(
            $xpath->query('//config/label')
        );

        $form['description'] = self::parseTranslatableNodeList(
            $xpath->query('//config/description')
        );

        $form['elements'] = $this->parseElementNodeList(
            $xpath->query('//config/elements/element')
        );

        return $form;
    }

    /**
     * parses DOMNodeList with elements
     *
     * @param \DOMNodeList $list
     *
     * @return array
     */
    private function parseElementNodeList(\DOMNodeList $list)
    {
        if ($list->length === 0) {
            return [];
        }

        $elements = [];

        /** @var \DOMElement $item */
        foreach ($list as $item) {
            $element = [];

            //attributes
            $element['scope'] = self::validateAttributeScope(
                $item->getAttribute('scope')
            );

            $element['isRequired'] = self::validateBooleanAttribute(
                $item->getAttribute('required'),
                false
            );

            $element['type'] = self::validateTextAttribute(
                $item->getAttribute('type'),
                'text'
            );

            //elements
            if ($name = $item->getElementsByTagName('name')->item(0)) {
                $element['name'] = $name->nodeValue;
            }

            if ($value = $item->getElementsByTagName('value')->item(0)) {
                $element['value'] = $value->nodeValue;
            }

            $element['label'] = self::parseTranslatableNodeList(
                $item->getElementsByTagName('label')
            );

            $element['description'] = self::parseTranslatableNodeList(
                $item->getElementsByTagName('description')
            );

            $element['options'] = [];
            if ($options = self::parseOptionsNodeList(
                $item->getElementsByTagName('options')
            )) {
                $element['options'] = $options;
            }

            if ($store = self::parseStoreNodeList(
                $item->getElementsByTagName('store')
            )) {
                $element['store'] = $store;
            }

            $elements[] = $element;
        }

        return $elements;
    }

    /**
     * validates scope attribute
     *
     * @param $scope
     *
     * @return int
     */
    public static function validateAttributeScope($scope)
    {
        if ($scope === '' || $scope === 'locale') {
            return self::SCOPE_LOCALE;
        }

        if ($scope === 'shop') {
            return self::SCOPE_SHOP;
        }

        throw new \InvalidArgumentException(sprintf("Invalid scope '%s", $scope));
    }
}
