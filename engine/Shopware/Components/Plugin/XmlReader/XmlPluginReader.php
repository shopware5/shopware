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
use DOMNode;
use DOMNodeList;
use DOMXPath;

class XmlPluginReader extends XmlReaderBase
{
    /**
     * @var string
     */
    protected $xsdFile = __DIR__ . '/../schema/plugin.xsd';

    public static function parseBlacklist(DOMNodeList $items): ?array
    {
        if ($items->length === 0) {
            return null;
        }

        $blacklist = [];

        foreach ($items as $item) {
            $blacklist[] = $item->nodeValue;
        }

        return $blacklist;
    }

    protected function parseFile(DOMDocument $xml): array
    {
        $xpath = new DOMXPath($xml);

        $plugin = $xpath->query('//plugin');
        if (!$plugin instanceof DOMNodeList) {
            return [];
        }

        $pluginData = $plugin->item(0);
        if (!$pluginData instanceof DOMElement) {
            return [];
        }

        $info = [];

        $label = $xpath->query('//plugin/label');
        if ($label instanceof DOMNodeList) {
            $label = self::parseTranslatableNodeList($label);
            if ($label) {
                $info['label'] = $label;
            }
        }

        $description = $xpath->query('//plugin/description');
        if ($description instanceof DOMNodeList) {
            $description = self::parseTranslatableNodeList($description);
            if ($description) {
                $info['description'] = $description;
            }
        }

        $simpleFields = ['version', 'license', 'author', 'copyright', 'link'];
        foreach ($simpleFields as $simpleField) {
            if (($fieldValue = self::getElementChildValueByName($pluginData, $simpleField)) !== null) {
                $info[$simpleField] = $fieldValue;
            }
        }

        foreach ($pluginData->getElementsByTagName('changelog') as $changelog) {
            $version = $changelog->getAttribute('version');

            foreach ($changelog->getElementsByTagName('changes') as $changes) {
                $lang = $changes->getAttribute('lang') ?: 'en';
                $info['changelog'][$version][$lang][] = $changes->nodeValue;
            }
        }

        $compatibility = $xpath->query('//plugin/compatibility');
        if ($compatibility instanceof DOMNodeList) {
            $compatibility = $compatibility->item(0);
            if ($compatibility instanceof DOMNode) {
                $info['compatibility'] = [
                    'minVersion' => $compatibility->getAttribute('minVersion'),
                    'maxVersion' => $compatibility->getAttribute('maxVersion'),
                    'blacklist' => self::parseBlacklist(
                        $compatibility->getElementsByTagName('blacklist')
                    ),
                ];
            }
        }

        $requiredPlugins = self::getFirstChildren(
            $pluginData,
            'requiredPlugins'
        );

        if ($requiredPlugins !== null) {
            $info['requiredPlugins'] = $this->parseRequiredPlugins($requiredPlugins);
        }

        return $info;
    }

    private function parseRequiredPlugins(DOMElement $requiredPluginNode): array
    {
        $plugins = [];

        $requiredPlugins = $requiredPluginNode->getElementsByTagName('requiredPlugin');

        foreach ($requiredPlugins as $requiredPlugin) {
            $plugin = [];

            $plugin['pluginName'] = $requiredPlugin->getAttribute('pluginName');

            if ($minVersion = $requiredPlugin->getAttribute('minVersion')) {
                $plugin['minVersion'] = $minVersion;
            }

            if ($maxVersion = $requiredPlugin->getAttribute('maxVersion')) {
                $plugin['maxVersion'] = $maxVersion;
            }

            $blacklist = self::parseBlacklist(
                $requiredPlugin->getElementsByTagName('blacklist')
            );

            if ($blacklist !== null) {
                $plugin['blacklist'] = $blacklist;
            }

            $plugins[] = $plugin;
        }

        return $plugins;
    }
}
