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
use DOMElement;
use DOMNodeList;
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
        $entries = (new DOMXPath($xml))->query('//entries/entry');

        if (!$entries instanceof DOMNodeList || $entries->length === 0) {
            throw new RuntimeException(sprintf('Required element "entry" is missing in file "%s".', static::$xmlFile));
        }

        $menu = [];
        foreach ($entries as $entry) {
            if ($entry instanceof DOMElement) {
                $menu[] = $this->parseEntry($entry);
            }
        }

        return $menu;
    }

    private function parseEntry(DOMElement $entry): array
    {
        $menuEntry = [];

        $menuEntry['isRootMenu'] = self::validateBooleanAttribute($entry->getAttribute('isRootMenu'));

        $label = self::parseTranslatableElement($entry, 'label');

        if ($label !== null) {
            $menuEntry['label'] = $label;
        }

        foreach (['name', 'controller', 'action', 'class', 'onclick'] as $simpleField) {
            $fieldValue = self::getElementChildValueByName($entry, $simpleField);
            if ($fieldValue !== null) {
                $menuEntry[$simpleField] = $fieldValue;
            }
        }

        $parent = $entry->getElementsByTagName('parent')->item(0);
        if ($parent !== null) {
            $identifiedBy = self::validateTextAttribute(
                $parent->getAttribute('identifiedBy'),
                'controller'
            );

            $menuEntry['parent'] = [
                $identifiedBy => $parent->nodeValue,
            ];
        }

        $active = self::getElementChildValueByName($entry, 'active');
        if ($active !== null) {
            $menuEntry['active'] = (bool) XmlUtils::phpize($active);
        }

        $position = self::getElementChildValueByName($entry, 'position');
        if ($position !== null) {
            $menuEntry['position'] = (int) $position;
        }

        $children = $entry->getElementsByTagName('children');
        if ($children !== null && $children->length) {
            $children = $children->item(0);
            $menuEntry['children'] = [];
            foreach (self::getChildren($children, 'entry') as $child) {
                $menuEntry['children'][] = $this->parseEntry($child);
            }
        }

        return $menuEntry;
    }
}
