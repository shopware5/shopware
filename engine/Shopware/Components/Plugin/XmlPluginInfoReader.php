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
use DOMNodeList;
use DOMXPath;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Config\Util\XmlUtils;

/**
 * @deprecated This class will be removed in 5.6
 *
 * Use new class Shopware\Components\Plugin\XmlReader\XmlPluginInfoReader (see Shopware 5.6)
 *
 * https://github.com/shopware5/shopware/blob/5.6/engine/Shopware/Components/Plugin/XmlReader/XmlPluginInfoReader.php
 */
class XmlPluginInfoReader
{
    public function read($file)
    {
        try {
            $dom = XmlUtils::loadFile($file, __DIR__ . '/schema/plugin.xsd');
        } catch (Exception $e) {
            throw new InvalidArgumentException(sprintf('Unable to parse file "%s". Message: %s', $file, $e->getMessage()), $e->getCode(), $e);
        }

        return $this->parseInfo($dom);
    }

    private function parseInfo(DOMDocument $xml): ?array
    {
        $entries = (new DOMXPath($xml))->query('//plugin');
        if (!$entries instanceof DOMNodeList) {
            return null;
        }

        $entry = $entries[0];
        $info = [];

        foreach ($this->getChildren($entry, 'label') as $label) {
            $lang = ($label->getAttribute('lang')) ?: 'en';
            $info['label'][$lang] = $label->nodeValue;
        }

        foreach ($this->getChildren($entry, 'description') as $description) {
            $lang = ($description->getAttribute('lang')) ?: 'en';
            $info['description'][$lang] = trim((string) $description->nodeValue);
        }

        $simpleKeys = ['version', 'license', 'author', 'copyright', 'link'];
        foreach ($simpleKeys as $simpleKey) {
            if ($names = $this->getChildren($entry, $simpleKey)) {
                $info[$simpleKey] = $names[0]->nodeValue;
            }
        }

        foreach ($this->getChildren($entry, 'changelog') as $changelog) {
            $version = $changelog->getAttribute('version');

            foreach ($this->getChildren($changelog, 'changes') as $changes) {
                $lang = ($changes->getAttribute('lang')) ?: 'en';
                $info['changelog'][$version][$lang][] = $changes->nodeValue;
            }
        }

        $compatibility = $this->getFirstChild($entry, 'compatibility');
        if ($compatibility) {
            $info['compatibility'] = [
                'minVersion' => $compatibility->getAttribute('minVersion'),
                'maxVersion' => $compatibility->getAttribute('maxVersion'),
                'blacklist' => $this->getChildrenValues($compatibility, 'blacklist'),
            ];
        }

        $requiredPlugins = $this->getFirstChild($entry, 'requiredPlugins');
        if ($requiredPlugins) {
            $info['requiredPlugins'] = $this->parseRequiredPlugins($requiredPlugins);
        }

        return $info;
    }

    /**
     * Get child elements by name.
     *
     * @return array<DOMElement>
     */
    private function getChildren(DOMNode $node, string $name): array
    {
        $children = [];
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === $name) {
                $children[] = $child;
            }
        }

        return $children;
    }

    private function getFirstChild(DOMNode $node, string $name): ?DOMElement
    {
        if ($children = $this->getChildren($node, $name)) {
            return $children[0];
        }

        return null;
    }

    /**
     * Get child element values by name.
     *
     * @return array<string>
     */
    private function getChildrenValues(DOMNode $node, string $name): array
    {
        $children = [];
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === $name) {
                $children[] = (string) $child->nodeValue;
            }
        }

        return $children;
    }

    /**
     * @return array<int, array<string, array<string>|string>>
     */
    private function parseRequiredPlugins(DOMNode $requiredPlugins): array
    {
        $requiredPlugins = $this->getChildren($requiredPlugins, 'requiredPlugin');
        $plugins = [];
        foreach ($requiredPlugins as $requiredPlugin) {
            $plugins[] = [
                'pluginName' => $requiredPlugin->getAttribute('pluginName'),
                'minVersion' => $requiredPlugin->getAttribute('minVersion'),
                'maxVersion' => $requiredPlugin->getAttribute('maxVersion'),
                'blacklist' => $this->getChildrenValues($requiredPlugin, 'blacklist'),
            ];
        }

        return $plugins;
    }
}
