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

namespace Shopware\Components\Plugin\XmlReader;

use DOMDocument;
use DOMElement;
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

        throw new InvalidArgumentException(sprintf('Invalid config scope "%s" in file "%s"', $scope, static::$xmlFile));
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

        /** @var DOMNodeList<DOMElement>|false $elements */
        $elements = $xpath->query('//config/elements/element');
        if ($elements instanceof DOMNodeList) {
            $form['elements'] = $this->parseElementNodeList($elements);
        }

        return $form;
    }

    /**
     * @param DOMNodeList<DOMElement> $list
     *
     * @return list<array{scope: int, isRequired: bool, type: string, name?: string|null, value?: mixed, label: array<string, string>|null, description: array<string, string>|null, options: array<string, mixed>|null, store?: array<array{0: string, 1: array<string, string>|null}>|string|null}>
     */
    private function parseElementNodeList(DOMNodeList $list): array
    {
        if ($list->length === 0) {
            return [];
        }

        $elements = [];

        foreach ($list as $item) {
            $element = [];

            // attributes
            $element['scope'] = self::validateAttributeScope($item->getAttribute('scope'));

            $element['isRequired'] = self::validateBooleanAttribute($item->getAttribute('required'));

            $element['type'] = self::validateTextAttribute(
                $item->getAttribute('type'),
                'text'
            );

            // elements
            if ($name = $item->getElementsByTagName('name')->item(0)) {
                $element['name'] = $name->nodeValue;
            }

            if ($item->getElementsByTagName('value')->length) {
                $valueItem = $item->getElementsByTagName('value')->item(0);
                if ($valueItem instanceof DOMElement) {
                    $element['value'] = XmlUtils::phpize($valueItem->nodeValue);
                }
            }

            $element['label'] = self::parseTranslatableElement($item, 'label');
            $element['description'] = self::parseTranslatableElement($item, 'description');

            $element['options'] = [];
            if ($options = self::parseOptionsNodeList($item->getElementsByTagName('options'))) {
                $element['options'] = $options;
            }

            if ($store = self::parseStoreNodeList($item->getElementsByTagName('store'))) {
                $element['store'] = $store;
            } elseif ($item->getElementsByTagName('store')->length) {
                $storeItem = $item->getElementsByTagName('store')->item(0);
                if ($storeItem instanceof DOMElement) {
                    $element['store'] = $storeItem->nodeValue;
                }
            }

            $elements[] = $element;
        }

        return $elements;
    }
}
