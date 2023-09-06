<?php
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

namespace Shopware\Components\Plugin\XmlReader;

use DOMDocument;
use DOMNodeList;
use DOMXPath;
use InvalidArgumentException;
use Symfony\Component\Config\Util\XmlUtils;

class XmlConfigReader extends XmlReaderBase
{
    protected $xsdFile = __DIR__ . '/../schema/config.xsd';

    public static function validateAttributeScope(string $scope): int
    {
        if ($scope === '' || $scope === 'locale') {
            return self::SCOPE_LOCALE;
        }

        if ($scope === 'shop') {
            return self::SCOPE_SHOP;
        }

        throw new InvalidArgumentException(sprintf('Invalid scope "%s"', $scope));
    }

    protected function parseFile(DOMDocument $xml): array
    {
        $xpath = new DOMXPath($xml);

        $form = [
            'label' => [],
            'description' => [],
            'elements' => [],
        ];

        $label = $xpath->query('//config/label');
        if ($label instanceof DOMNodeList) {
            $form['label'] = self::parseTranslatableNodeList($label);
        }

        $description = $xpath->query('//config/description');
        if ($description instanceof DOMNodeList) {
            $form['description'] = self::parseTranslatableNodeList($description);
        }

        $elements = $xpath->query('//config/elements/element');
        if ($elements instanceof DOMNodeList) {
            $form['elements'] = $this->parseElementNodeList($elements);
        }

        return $form;
    }

    private function parseElementNodeList(DOMNodeList $list): array
    {
        if ($list->length === 0) {
            return [];
        }

        $elements = [];

        foreach ($list as $item) {
            $element = [];

            // attributes
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

            // elements
            if ($name = $item->getElementsByTagName('name')->item(0)) {
                $element['name'] = $name->nodeValue;
            }

            if ($item->getElementsByTagName('value')->length) {
                $element['value'] = XmlUtils::phpize($item->getElementsByTagName('value')->item(0)->nodeValue);
            }

            $element['label'] = self::parseTranslatableElement($item, 'label');
            $element['description'] = self::parseTranslatableElement($item, 'description');

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
            } elseif ($item->getElementsByTagName('store')->length) {
                $element['store'] = $item->getElementsByTagName('store')->item(0)->nodeValue;
            }

            $elements[] = $element;
        }

        return $elements;
    }
}
