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

use Symfony\Component\Config\Util\XmlUtils;

/**
 * Class XmlCronjobReader
 *
 * @package Shopware\Components\Plugin
 */
class XmlCronjobReader extends XmlReaderBase
{
    protected $xsdFile = __DIR__ . '/../schema/cronjob.xsd';

    /**
     * @inheritdoc
     */
    protected function parseFile(\DOMDocument $xml)
    {
        $xpath = new \DOMXPath($xml);

        $nodeList = $xpath->query('//cronjobs/cronjob');

        return $this->parseList($nodeList);
    }

    /**
     * parses cronjob list
     *
     * @param \DOMNodeList $list
     *
     * @return array
     */
    private function parseList(\DOMNodeList $list)
    {
        if ($list->length === 0) {
            return [];
        }

        $items = [];

        /** @var \DOMElement $item */
        foreach ($list as $item) {
            $items[] = $this->parseItem($item);
        }

        return $items;
    }

    /**
     * parses cronjob item
     *
     * @param \DOMElement $element
     *
     * @return array
     */
    private function parseItem(\DOMElement $element)
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
            $item['interval'] = (integer) XmlUtils::phpize($interval);
        }

        if ($disableOnError = self::getElementChildValueByName($element, 'disableOnError', true)) {
            $item['disable_on_error'] = (bool) XmlUtils::phpize($disableOnError);
        }

        return $item;
    }
}
