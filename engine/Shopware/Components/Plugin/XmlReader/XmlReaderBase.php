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
use InvalidArgumentException;
use Shopware\Components\Plugin\XmlReader\StoreValueParser\StoreValueParserFactory;
use Shopware\Components\Plugin\XmlReader\StoreValueParser\StoreValueParserInterface;
use Symfony\Component\Config\Util\XmlUtils;

/**
 * Class XmlReaderBase
 */
abstract class XmlReaderBase implements XmlReaderInterface
{
    const SCOPE_LOCALE = 0;
    const SCOPE_SHOP = 1;

    const DEFAULT_LANG = 'en';

    /**
     * Map of language shortcuts to locale.
     *
     * @var array
     */
    protected static $languageMapping = [
        'de' => 'de_DE',
        'en' => 'en_GB',
    ];

    /**
     * @var string should be set in instance that extends this class
     */
    protected $xsdFile;

    /**
     * load and validate xml file - parse to array
     *
     * @param string $xmlFile
     *
     * @return array
     */
    public function read($xmlFile)
    {
        try {
            $dom = XmlUtils::loadFile($xmlFile, $this->xsdFile);
        } catch (\Exception $e) {
            throw new InvalidArgumentException(sprintf('Unable to parse file "%s".', $xmlFile), $e->getCode(), $e);
        }

        return $this->parseFile($dom);
    }

    /**
     * Parses translatable node list.
     *
     * @param DOMNodeList $list
     *
     * @return array|null
     */
    public static function parseTranslatableNodeList(DOMNodeList $list)
    {
        if ($list->length === 0) {
            return null;
        }

        $translations = [];

        /** @var DOMElement $item */
        foreach ($list as $item) {
            $language = $item->getAttribute('lang') ?: self::DEFAULT_LANG;

            // XSD Requires en-GB, Zend uses en_GB
            $language = str_replace('-', '_', $language);

            $translations[$language] = trim($item->nodeValue);
        }

        return $translations;
    }

    /**
     * Get child elements by name.
     *
     * @param DOMNode $node
     * @param mixed   $name
     *
     * @return array
     */
    public static function getChildren(DOMNode $node, $name)
    {
        $children = [];
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === $name) {
                $children[] = $child;
            }
        }

        return $children;
    }

    /**
     * Returns first item of DOMNodeList or null.
     *
     * @param DOMNode $list
     * @param string  $name
     *
     * @return DOMElement|null
     */
    public static function getFirstChildren(DOMNode $list, $name)
    {
        $children = self::getChildren($list, $name);

        if (count($children) === 0) {
            return null;
        }

        return $children[0];
    }

    /**
     * Validates boolean attribute.
     *
     * @param string $value
     * @param bool   $defaultValue
     *
     * @return bool
     */
    public static function validateBooleanAttribute($value, $defaultValue = false)
    {
        if ($value === '') {
            return $defaultValue;
        }

        return (bool) XmlUtils::phpize($value);
    }

    /**
     * Returns parsed store.
     * - null if no store found.
     * - string if it is an extjs store
     * - array if it is a xml store
     *
     * @param DOMNodeList $list
     *
     * @return array|string|null
     */
    public static function parseStoreNodeList(DOMNodeList $list)
    {
        if ($list->length === 0) {
            return null;
        }

        $storeItem = $list->item(0);

        $type = $storeItem->getAttribute('type') ?: 'xml';

        /** @var StoreValueParserInterface $storeValueParser */
        $parser = StoreValueParserFactory::create($type);

        return $parser->parse($storeItem);
    }

    /**
     * @param DOMNodeList $optionsList
     *
     * @return array|null
     */
    public static function parseOptionsNodeList(DOMNodeList $optionsList)
    {
        if ($optionsList->length === 0) {
            return null;
        }

        $optionsItem = $optionsList->item(0);

        $optionList = $optionsItem->childNodes;

        if ($optionList->length === 0) {
            return null;
        }

        $options = [];

        /** @var DOMElement $option */
        foreach ($optionList as $option) {
            if ($option instanceof DOMElement) {
                $options[$option->nodeName] = XmlUtils::phpize($option->nodeValue);
            }
        }

        return $options;
    }

    /**
     * Returns all element child values by nodeName.
     *
     * @param DOMElement $element
     * @param string     $name
     * @param bool       $throwException
     *
     * @throws InvalidArgumentException
     *
     * @return string|null
     */
    public static function getElementChildValueByName(DOMElement $element, $name, $throwException = false)
    {
        $children = $element->getElementsByTagName($name);

        if ($children->length === 0) {
            if ($throwException) {
                throw new InvalidArgumentException(sprintf(
                    'Element with %s not found',
                    $name
                ));
            }

            return null;
        }

        return $children->item(0)->nodeValue;
    }

    /**
     * Validates attribute type.
     *
     * @param string $type
     * @param string $defaultValue
     *
     * @return string
     */
    public static function validateTextAttribute($type, $defaultValue = '')
    {
        if ($type === '') {
            return $defaultValue;
        }

        return $type;
    }

    /**
     * This method should be overridden as main entry point to parse a xml file.
     *
     * @param DOMDocument $xml
     *
     * @return array
     */
    abstract protected function parseFile(DOMDocument $xml);
}
