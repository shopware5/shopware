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

namespace Shopware\Components\Plugin;

use Symfony\Component\Config\Util\XmlUtils;

/**
 * Class XmlAttributeReader
 * @package Shopware\Components\Plugin
 */
class XmlAttributeReader
{
    private $boolFields = ['translatable', 'displayInBackend', 'custom', 'updateDependingTables'];

    /**
     * @param string $file
     * @return array
     */
    public function read($file)
    {
        try {
            $dom = XmlUtils::loadFile($file, __DIR__.'/schema/attributes.xsd');
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf('Unable to parse file "%s".', $file), $e->getCode(), $e);
        }

        return $this->parseInfo($dom);
    }

    /**
     * @param \DOMDocument $xml
     * @return array
     */
    private function parseInfo(\DOMDocument $xml)
    {
        $xpath = new \DOMXPath($xml);

        if (false === $entries = $xpath->query('//tables/table')) {
            return;
        }

        $attributes = [];
        /** @var \DOMElement $entry */
        foreach ($entries as $entry) {
            $attributes[$entry->getAttribute('name')] = $this->parseTable($entry);
        }

        return $attributes;
    }

    /**
     * @param \DOMElement $entry
     * @return array
     */
    private function parseTable(\DOMElement $entry)
    {
        $entries = [];
        foreach ($this->getChildren($entry, 'attribute') as $child) {
            $entryArray = [];

            $entryArray['name'] = $child->getAttribute('name');
            $entryArray['type'] = $child->getAttribute('type');
            $entryArray['label'] = $this->getFirstChild($child, 'label');
            $entryArray['supportText'] = $this->getFirstChild($child, 'supportText');
            $entryArray['helpText'] = $this->getFirstChild($child, 'helpText');
            $entryArray['translatable'] = $this->getFirstChild($child, 'translatable');
            $entryArray['displayInBackend'] = $this->getFirstChild($child, 'displayInBackend');
            $entryArray['custom'] = $this->getFirstChild($child, 'custom');
            $entryArray['updateDependingTables'] = $this->getFirstChild($child, 'updateDependingTables');
            $entryArray['defaultValue'] = $this->getFirstChild($child, 'defaultValue');
            $entryArray['entity'] = $this->getFirstChild($child, 'entity');
            $entryArray['position'] = $this->getFirstChild($child, 'position');

            $arrayStore = $this->getChildren($child, 'arrayStore');
            if ($arrayStore[0]) {
                $entryArray['arrayStore'] = $this->parseArrayStore($this->getChildren($arrayStore[0], 'option'));
            }

            // Remove empty fields
            foreach ($entryArray as $key => $item) {
                if ($item === null) {
                    unset($entryArray[$key]);
                    continue;
                }

                if (in_array($key, $this->boolFields)) {
                    $entryArray[$key] = XmlUtils::phpize($entryArray[$key]);
                }
            }

            // Default fields

            if (!isset($entryArray['updateDependingTables'])) {
                $entryArray['updateDependingTables'] = false;
            }

            if (!isset($entryArray['defaultValue'])) {
                $entryArray['defaultValue'] = null;
            }

            $entries[] = $entryArray;
        }

        return $entries;
    }

    /**
     * @param array $entries
     * @return array
     */
    private function parseArrayStore(array $entries)
    {
        $arrayStore = [];

        /** @var \DOMElement $entry */
        foreach ($entries as $entry) {
            $arrayStore[] = ['key' => $entry->getAttribute('key'), 'value' => $entry->nodeValue];
        }

        return $arrayStore;
    }

    /**
     * @param \DOMNode $node
     * @param $name
     * @return null|string
     */
    private function getFirstChild(\DOMNode $node, $name)
    {
        if ($children = $this->getChildren($node, $name)) {
            return $children[0]->nodeValue;
        }

        return null;
    }

    /**
     * Get child elements by name.
     *
     * @param \DOMNode $node
     * @param mixed    $name
     *
     * @return \DOMElement[]
     */
    private function getChildren(\DOMNode $node, $name)
    {
        $children = array();
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMElement && $child->localName === $name) {
                $children[] = $child;
            }
        }

        return $children;
    }
}
