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
use Symfony\Component\Config\Util\XmlUtils;

class XmlCronjobReader extends XmlReaderBase
{
    protected $xsdFile = __DIR__ . '/../schema/cronjob.xsd';

    protected function parseFile(DOMDocument $xml): array
    {
        $xpath = new DOMXPath($xml);

        $nodeList = $xpath->query('//cronjobs/cronjob');

        return $this->parseList($nodeList);
    }

    private function parseList(DOMNodeList $list): array
    {
        if ($list->length === 0) {
            return [];
        }

        $items = [];

        /** @var DOMElement $item */
        foreach ($list as $item) {
            $items[] = $this->parseItem($item);
        }

        return $items;
    }

    private function parseItem(DOMElement $element): array
    {
        $item = [];

        if ($name = self::getElementChildValueByName($element, 'name', true)) {
            $item['name'] = $name;
        }

        if ($action = self::getElementChildValueByName($element, 'action', true)) {
            $item['action'] = $action;
        }

        if ($active = self::getElementChildValueByName($element, 'active', true)) {
            $item['active'] = (bool) XmlUtils::phpize($active);
        }

        if ($interval = self::getElementChildValueByName($element, 'interval', true)) {
            $item['interval'] = (int) XmlUtils::phpize($interval);
        }

        if ($disableOnError = self::getElementChildValueByName($element, 'disableOnError', true)) {
            $item['disable_on_error'] = (bool) XmlUtils::phpize($disableOnError);
        }

        return $item;
    }
}
