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

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Config\Util\XmlUtils;

/**
 * Class XmlPermissionsReader
 */
class XmlPermissionsReader
{
    /**
     * @param $file
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    public function read($file)
    {
        try {
            $dom = XmlUtils::loadFile($file, __DIR__ . '/schema/permission.xsd');
        } catch (Exception $e) {
            throw new InvalidArgumentException(sprintf('Unable to parse file "%s".', $file), $e->getCode(), $e);
        }

        $permissions = $this->parsePermissions($dom);

        return array_column($permissions, 'name');
    }

    /**
     * @param DOMDocument $xml
     *
     * @return array
     */
    private function parsePermissions(DOMDocument $xml)
    {
        $xpath = new DOMXPath($xml);

        $entries = $xpath->query('//permissions/permission');

        if (false === $entries) {
            return [];
        }

        $permissions = [];
        foreach ($entries as $entry) {
            $permissions[] = $this->parseEntry($entry);
        }

        return $permissions;
    }

    /**
     * @param DOMElement $entry
     *
     * @return array
     */
    private function parseEntry(DOMElement $entry)
    {
        $cronjobEntry = [];

        $cronjobEntry['name'] = $this->getFirstChild($entry, 'name');

        return $cronjobEntry;
    }

    /**
     * @param DOMNode $node
     * @param $name
     *
     * @return null|string
     */
    private function getFirstChild(DOMNode $node, $name)
    {
        if ($children = $this->getChildren($node, $name)) {
            return $children[0]->nodeValue;
        }

        return null;
    }

    /**
     * Get child elements by name.
     *
     * @param DOMNode $node
     * @param mixed $name
     *
     * @return DOMElement[]
     */
    private function getChildren(DOMNode $node, $name)
    {
        $children = array();

        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === $name) {
                $children[] = $child;
            }
        }

        return $children;
    }
}
