<?php

namespace Shopware\Components\Plugin\XmlReader\StoreValueParser;

/**
 * Class StoreValueParserFactory
 *
 * @package Shopware\Components\Plugin\XmlReader\StoreValueParser
 */
class StoreValueParserFactory
{
    /**
     * creates instance of store value parser by given type
     *
     * @param $type
     *
     * @return StoreValueParserInterface
     */
    public static function create($type)
    {
        switch ($type) {
            case 'extjs':
                return new StoreExtjsValueParser();
            case 'xml':
            default:
                return new StoreXmlValueParser();
        }
    }
}
