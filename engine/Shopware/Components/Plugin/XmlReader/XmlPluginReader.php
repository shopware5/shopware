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

        /** @var DOMElement $item */
        foreach ($items as $item) {
            $blacklist[] = $item->nodeValue;
        }

        return $blacklist;
    }

    protected function parseFile(DOMDocument $xml): array
    {
        $xpath = new DOMXPath($xml);

        $plugin = $xpath->query('//plugin');

        /** @var DOMElement $pluginData */
        $pluginData = $plugin->item(0);

        $info = [];

        if ($label = self::parseTranslatableNodeList($xpath->query('//plugin/label'))) {
            $info['label'] = $label;
        }

        if ($description = self::parseTranslatableNodeList($xpath->query('//plugin/description'))) {
            $info['description'] = $description;
        }

        $simpleFields = ['version', 'license', 'author', 'copyright', 'link'];
        foreach ($simpleFields as $simpleField) {
            if (($fieldValue = self::getElementChildValueByName($pluginData, $simpleField)) !== null) {
                $info[$simpleField] = $fieldValue;
            }
        }

        /** @var DOMElement $changelog */
        foreach ($pluginData->getElementsByTagName('changelog') as $changelog) {
            $version = $changelog->getAttribute('version');

            /** @var DOMElement $changes */
            foreach ($changelog->getElementsByTagName('changes') as $changes) {
                $lang = $changes->getAttribute('lang') ?: 'en';
                $info['changelog'][$version][$lang][] = $changes->nodeValue;
            }
        }

        $compatibility = $xpath->query('//plugin/compatibility')->item(0);
        if ($compatibility !== null) {
            $info['compatibility'] = [
                'minVersion' => $compatibility->getAttribute('minVersion'),
                'maxVersion' => $compatibility->getAttribute('maxVersion'),
                'blacklist' => self::parseBlacklist(
                    $compatibility->getElementsByTagName('blacklist')
                ),
            ];
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

        /** @var DOMElement $requiredPlugin */
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
