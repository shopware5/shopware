<?php

namespace Shopware\Tests\Unit\Components\Plugin\XmlReader\StoreValueParser;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Plugin\XmlReader\StoreValueParser\StoreValueParserFactory;
use Shopware\Components\Plugin\XmlReader\StoreValueParser\StoreXmlValueParser;
use Symfony\Component\Config\Util\XmlUtils;

/**
 * Class StoreExtjsValueParserTest
 *
 * @package Shopware\Tests\Unit\Components\Plugin\XmlReader\StoreValueParser
 */
class StoreExtjsValueParserTest extends TestCase
{
    /**
     * @var StoreXmlValueParser
     */
    private $parser;

    /**
     * @var \DOMDocument
     */
    private $xmlFile;

    /**
     * @var \DOMXPath
     */
    private $xpath;

    /**
     * set up test
     */
    protected function setUp()
    {
        $this->parser = StoreValueParserFactory::create('extjs');

        $this->xmlFile = XmlUtils::loadFile(
            __DIR__ . '/../examples/config/config_store_extjs.xml',
            __DIR__ . '/../../../../../../engine/Shopware/Components/Plugin/schema/config.xsd'
        );

        $this->xpath = new \DOMXPath($this->xmlFile);
    }

    /**
     * returns store element at index
     *
     * @param integer $elementIndex
     *
     * @return \DOMElement
     */
    private function getStoreElement($elementIndex)
    {
        $stores = $this->xpath->query(
            sprintf(
                '//config/elements/element[%s]/store',
                $elementIndex
            )
        );

        return $stores->item(0);
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\StoreValueParser\StoreExtjsValueParser::parse()
     */
    public function testThatParserReturnsValidData()
    {
        $store = $this->getStoreElement(1);
        $options = $this->parser->parse($store);

        self::assertInternalType('string', $options);
        self::assertEquals('Shopware.apps.Base.store.Category', $options);
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\StoreValueParser\StoreExtjsValueParser::parse()
     */
    public function testThatEmptyOptionsReturnsEmptyArray()
    {
        $store = $this->getStoreElement(2);
        $options = $this->parser->parse($store);

        self::assertInternalType('string', $options);
        self::assertEquals('', $options);
    }
}
