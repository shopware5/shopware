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
 * Class XmlCronjobReader
 * @package Shopware\Components\Plugin
 */
class XmlCronjobReader
{
    /**
     * @param string $file
     * @return array
     */
    public function read($file)
    {
        try {
            $dom = XmlUtils::loadFile($file, __DIR__.'/schema/cronjob.xsd');
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

        if (false === $entries = $xpath->query('//cronjobs/cronjob')) {
            return;
        }

        $cronjobs = [];
        foreach ($entries as $entry) {
            $cronjobs[] = $this->parseEntry($entry);
        }

        return $cronjobs;
    }

    /**
     * @param \DOMElement $entry
     * @return array
     */
    private function parseEntry(\DOMElement $entry)
    {
        $cronjobEntry = [];

        $cronjobEntry['name'] = $this->getFirstChild($entry, 'name');
        $cronjobEntry['action'] = $this->getFirstChild($entry, 'action');
        $cronjobEntry['active'] = $this->toBool($this->getFirstChild($entry, 'active'));
        $cronjobEntry['interval'] = $this->getFirstChild($entry, 'interval');
        $cronjobEntry['disable_on_error'] = $this->toBool($this->getFirstChild($entry, 'disableOnError'));

        return $cronjobEntry;
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

    /**
     * @param string $string
     * @return bool
     */
    private function toBool($string)
    {
        return $string == 'true' ? true : false;
    }
}
