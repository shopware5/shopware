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

use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;
use Symfony\Component\Config\Util\XmlUtils;

class XmlMenuReader extends XmlReaderBase
{
    /**
     * @var string
     */
    protected $xsdFile = __DIR__ . '/../schema/menu.xsd';

    protected function parseFile(DOMDocument $xml): array
    {
        $xpath = new DOMXPath($xml);

        $entries = $xpath->query('//entries/entry');

        if ($entries->length === 0) {
            throw new RuntimeException('Required element "entry" is missing.');
        }

        $menu = [];
        foreach ($entries as $entry) {
            $menu[] = $this->parseEntry($entry);
        }

        return $menu;
    }

    private function parseEntry(DOMElement $entry): array
    {
        $menuEntry = [];

        $menuEntry['isRootMenu'] = self::validateBooleanAttribute(
            $entry->getAttribute('isRootMenu'),
            false
        );

        $label = $label = self::parseTranslatableElement(
            $entry,
            'label'
        );

        if ($label !== null) {
            $menuEntry['label'] = $label;
        }

        $simpleFields = ['name', 'controller', 'action', 'class', 'onclick'];
        foreach ($simpleFields as $simpleField) {
            if (($fieldValue = self::getElementChildValueByName($entry, $simpleField)) !== null) {
                $menuEntry[$simpleField] = $fieldValue;
            }
        }

        if (($parent = $entry->getElementsByTagName('parent')->item(0)) !== null) {
            $identifiedBy = self::validateTextAttribute(
                $parent->getAttribute('identifiedBy'),
                'controller'
            );

            $menuEntry['parent'] = [
                $identifiedBy => $parent->nodeValue,
            ];
        }

        if ($active = self::getElementChildValueByName($entry, 'active')) {
            $menuEntry['active'] = (bool) XmlUtils::phpize($active);
        }

        if ($position = self::getElementChildValueByName($entry, 'position')) {
            $menuEntry['position'] = (int) $position;
        }

        if (($children = $entry->getElementsByTagName('children')) !== null && $children->length) {
            $children = $children->item(0);
            foreach (XmlReaderBase::getChildren($children, 'entry') as $child) {
                $menuEntry['children'][] = $this->parseEntry($child);
            }
        }

        return $menuEntry;
    }
}
