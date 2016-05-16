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

class XmlConfigDefinitionReader
{
    /**
     * @param $file string
     * @return array
     * @throws \Exception
     */
    public function read($file)
    {
        try {
            $dom = XmlUtils::loadFile($file, __DIR__.'/schema/config.xsd');
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf('Unable to parse file "%s".', $file), $e->getCode(), $e);
        }

        return $this->parseForm($dom);
    }

    /**
     * @param \DOMDocument $xml
     * @return array
     */
    private function parseForm(\DOMDocument $xml)
    {
        $xpath = new \DOMXPath($xml);

        $form = [];

        foreach ($xpath->query('//config/label') as $label) {
            $lang = ($label->getAttribute('lang')) ? $label->getAttribute('lang') : 'en';
            $form['label'][$lang] = $label->nodeValue;
        }

        foreach ($xpath->query('//config/description') as $description) {
            $lang = ($description->getAttribute('lang')) ? $description->getAttribute('lang') : 'en';
            $form['description'][$lang] = $description->nodeValue;
        }

        if (false === $elemements = $xpath->query('//elements/element')) {
            return;
        }

        $elements = [];

        /** @var \DOMElement $entry */
        foreach ($elemements as $elemement) {
            $elements[] = $this->parseElement($elemement);
        }

        $form['elements'] = $elements;

        return $form;
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
     * @param \DOMElement $entry
     * @return array
     */
    private function parseElement(\DOMElement $entry)
    {
        $element = [];

        $isRequired = ($entry->getAttribute('required')) ? XmlUtils::phpize($entry->getAttribute('required')) : false;
        $type = ($entry->getAttribute('type')) ? $entry->getAttribute('type') : 'text';

        $scope = ($entry->getAttribute('scope')) ? $entry->getAttribute('scope') : 'locale';
        if ($scope === 'locale') {
            $scope = 0;
        } elseif ($scope === 'shop') {
            $scope = 1;
        } else {
            throw new \InvalidArgumentException(sprintf("Invalid scope '%s", $scope));
        }

        $element['isRequired'] = $isRequired;
        $element['type'] = $type;
        $element['scope'] = $scope;

        if ($position = $this->getChildren($entry, 'name')) {
            $element['name'] = $position[0]->nodeValue;
        }

        if ($position = $this->getChildren($entry, 'store')) {
            $element['store'] = $position[0]->nodeValue;
        }

        if ($position = $this->getChildren($entry, 'value')) {
            $element['value'] = XmlUtils::phpize($position[0]->nodeValue);
        } else {
            $element['value'] = null;
        }

        foreach ($this->getChildren($entry, 'description') as $label) {
            $lang = ($label->getAttribute('lang')) ? $label->getAttribute('lang') : 'en';
            $element['description'][$lang] = $label->nodeValue;
        }

        foreach ($this->getChildren($entry, 'label') as $label) {
            $lang = ($label->getAttribute('lang')) ? $label->getAttribute('lang') : 'en';
            $element['label'][$lang] = $label->nodeValue;
        }

        $element['options'] = [];
        foreach ($this->getChildren($entry, 'options') as $option) {
            foreach ($option->childNodes as $node) {
                if (!$node instanceof \DOMElement) {
                    continue;
                }
                $element['options'][$node->nodeName] = XmlUtils::phpize($node->textContent);
            }
        }

        return $element;
    }
}
