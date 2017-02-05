<?php

namespace Shopware\Components\Plugin\XmlReader\StoreValueParser;

/**
 * Interface StoreValueParserInterface
 */
interface StoreValueParserInterface
{
    /**
     * parses store options
     *
     * @param \DOMElement $element
     *
     * @return string|array
     */
    public function parse(\DOMElement $element);
}
