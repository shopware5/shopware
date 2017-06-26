<?php

namespace Shopware\Tests\Unit\Components\Plugin\XmlReader\StoreValueParser;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Plugin\XmlReader\StoreValueParser\StoreExtjsValueParser;
use Shopware\Components\Plugin\XmlReader\StoreValueParser\StoreValueParserFactory;
use Shopware\Components\Plugin\XmlReader\StoreValueParser\StoreXmlValueParser;

/**
 * Class StoreValueParserFactoryTest
 *
 * @package Shopware\Tests\Unit\Components\Plugin\XmlReader\StoreValueParser
 */
class StoreValueParserFactoryTest extends TestCase
{
    /**
     * @covers \Shopware\Components\Plugin\XmlReader\StoreValueParser\StoreValueParserFactory::create()
     */
    public function testThatFactoryReturnsCorrectInstance()
    {
        self::assertInstanceOf(StoreXmlValueParser::class, StoreValueParserFactory::create(''));
        self::assertInstanceOf(StoreXmlValueParser::class, StoreValueParserFactory::create('xml'));
        self::assertInstanceOf(StoreExtjsValueParser::class, StoreValueParserFactory::create('extjs'));
    }
}
