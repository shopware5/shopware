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

class XmlPluginInfoReader
{
    public function read($file)
    {
        try {
            $dom = XmlUtils::loadFile($file, __DIR__.'/schema/plugin.xsd');
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf('Unable to parse file "%s".', $file), $e->getCode(), $e);
        }

        return $this->parsePlugin($dom);
    }

    /**
     * @param \DOMDocument $xml
     * @return array
     */
    private function parsePlugin(\DOMDocument $xml)
    {
        $xpath = new \DOMXPath($xml);

        if (false === $entries = $xpath->query('//plugin')) {
            return;
        }

        $entry = $entries[0];
        $pluginEntry = [];

        foreach ($this->getChildren($entry, 'label') as $label) {
            $lang = ($label->getAttribute('lang')) ? $label->getAttribute('lang') : 'en';
            $pluginEntry['label'][$lang] = $label->nodeValue;
        }

        foreach ($this->getChildren($entry, 'description') as $description) {
            $lang = ($description->getAttribute('lang')) ? $description->getAttribute('lang') : 'en';
            $pluginEntry['description'][$lang] = trim($description->nodeValue);
        }

        $simpleKeys = ['version', 'license', 'author', 'copyright', 'link'];
        foreach ($simpleKeys as $simpleKey) {
            if ($names = $this->getChildren($entry, $simpleKey)) {
                $pluginEntry[$simpleKey] = $names[0]->nodeValue;
            }
        }

        foreach ($this->getChildren($entry, 'changelog') as $changelog) {
            $version = $changelog->getAttribute('version');

            foreach ($this->getChildren($changelog, 'changes') as $changes) {
                $lang = ($changes->getAttribute('lang')) ? $changes->getAttribute('lang') : 'en';
                $pluginEntry['changelog'][$version][$lang][] = $changes->nodeValue;
            }
        }

        foreach ($this->getChildren($entry, 'cache-invalidation') as $cacheInvalidation) {
            foreach ($this->getChildren($cacheInvalidation, 'invalidate') as $invalidation) {
                $pluginMethod = $invalidation->getAttribute('on');
                foreach ($this->getChildren($invalidation, 'cache') as $cache) {
                    $pluginEntry['cache-invalidation'][$pluginMethod][] = $cache->nodeValue;
                }
            }
        }

        return $pluginEntry;
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
