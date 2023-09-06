<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components\Plugin\XmlReader;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Shopware\Components\Plugin\XmlReader\StoreValueParser\StoreValueParserFactory;
use Symfony\Component\Config\Util\XmlUtils;

abstract class XmlReaderBase implements XmlReaderInterface
{
    public const SCOPE_LOCALE = 0;
    public const SCOPE_SHOP = 1;

    public const DEFAULT_LANG = 'en';

    /**
     * @var string should be set in instance that extends this class
     */
    protected $xsdFile;

    public function read(string $xmlFile): array
    {
        try {
            $dom = XmlUtils::loadFile($xmlFile, $this->xsdFile);
        } catch (Exception $e) {
            throw new InvalidArgumentException(sprintf('Unable to parse file "%s". Message: %s', $xmlFile, $e->getMessage()), $e->getCode(), $e);
        }

        return $this->parseFile($dom);
    }

    /**
     * @return array<string, string>|null
     */
    public static function parseTranslatableNodeList(DOMNodeList $list): ?array
    {
        if ($list->length === 0) {
            return null;
        }

        $translations = [];

        foreach ($list as $item) {
            $language = $item->getAttribute('lang') ?: self::DEFAULT_LANG;
            if (!\is_string($language)) {
                throw new RuntimeException('"lang" attribute needs to be a string');
            }

            // XSD Requires en-GB, Zend uses en_GB
            $language = str_replace('-', '_', $language);

            $translations[$language] = trim((string) $item->nodeValue);
        }

        return $translations;
    }

    /**
     * @return array<string, string>|null
     */
    public static function parseTranslatableElement(DOMNode $element, string $name): ?array
    {
        $list = self::getChildren($element, $name);

        if (\count($list) === 0) {
            return null;
        }

        $translations = [];

        foreach ($list as $item) {
            $language = $item->getAttribute('lang') ?: self::DEFAULT_LANG;
            if (!\is_string($language)) {
                throw new RuntimeException('"lang" attribute needs to be a string');
            }

            // XSD Requires en-GB, Zend uses en_GB
            $language = str_replace('-', '_', $language);

            $translations[$language] = trim((string) $item->nodeValue);
        }

        return $translations;
    }

    /**
     * @return array<DOMElement>
     */
    public static function getChildren(DOMNode $node, string $name): array
    {
        $children = [];
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === $name) {
                $children[] = $child;
            }
        }

        return $children;
    }

    public static function getFirstChildren(DOMNode $list, string $name): ?DOMElement
    {
        $children = self::getChildren($list, $name);

        if (\count($children) === 0) {
            return null;
        }

        return $children[0];
    }

    public static function validateBooleanAttribute(string $value, bool $defaultValue = false): bool
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
     * @return array|string|null
     */
    public static function parseStoreNodeList(DOMNodeList $list)
    {
        if ($list->length === 0) {
            return null;
        }

        $storeItem = $list->item(0);
        if (!$storeItem instanceof DOMElement) {
            return null;
        }

        $type = $storeItem->getAttribute('type') ?: 'xml';

        return StoreValueParserFactory::create($type)->parse($storeItem);
    }

    public static function parseOptionsNodeList(DOMNodeList $optionsList): ?array
    {
        if ($optionsList->length === 0) {
            return null;
        }

        $optionList = $optionsList->item(0)->childNodes;

        if ($optionList->length === 0) {
            return null;
        }

        $options = [];

        foreach ($optionList as $option) {
            if ($option instanceof DOMElement) {
                $options[$option->nodeName] = XmlUtils::phpize($option->nodeValue);
            }
        }

        return $options;
    }

    public static function getElementChildValueByName(DOMElement $element, string $name, bool $throwException = false): ?string
    {
        $children = self::getChildren($element, $name);

        if (\count($children) === 0) {
            if ($throwException) {
                throw new InvalidArgumentException(sprintf('Element with %s not found', $name));
            }

            return null;
        }

        return $children[0]->nodeValue;
    }

    public static function validateTextAttribute(string $type, string $defaultValue = ''): string
    {
        if ($type === '') {
            return $defaultValue;
        }

        return $type;
    }

    abstract protected function parseFile(DOMDocument $xml): array;
}
