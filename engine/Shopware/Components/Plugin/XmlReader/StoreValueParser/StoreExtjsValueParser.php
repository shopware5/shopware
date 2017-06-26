<?php

namespace Shopware\Components\Plugin\XmlReader\StoreValueParser;

/**
 * Class StoreExtjsValueParser
 *
 * @package Shopware\Components\Plugin\XmlReader\StoreValueParser
 */
class StoreExtjsValueParser implements StoreValueParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse(\DOMElement $element)
    {
        return $element->nodeValue ?: '';
    }
}
