<?php

namespace Shopware\Tests\Unit\Components\Plugin\XmlReader\StoreValueParser;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Plugin\XmlReader\StoreValueParser\StoreValueParserFactory;
use Shopware\Components\Plugin\XmlReader\StoreValueParser\StoreXmlValueParser;
use Symfony\Component\Config\Util\XmlUtils;

/**
 * Class StoreXmlValueParserTest
 *
 * @package Shopware\Tests\Unit\Components\Plugin\XmlReader\StoreValueParser
 */
class StoreXmlValueParserTest extends TestCase
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
     * @inheritdoc
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->xmlFile = XmlUtils::loadFile(
            __DIR__ . '/../examples/config/config_store_xml.xml',
            __DIR__ . '/../../../../../../engine/Shopware/Components/Plugin/schema/config.xsd'
        );

        $this->xpath = new \DOMXPath($this->xmlFile);
    }

    /**
     * set up test
     */
    protected function setUp()
    {
        $this->parser = StoreValueParserFactory::create('xml');
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
     * @covers \Shopware\Components\Plugin\XmlReader\StoreValueParser\StoreXmlValueParser::parse()
     */
    public function testThatParserReturnsValidArray()
    {
        $store = $this->getStoreElement(1);
        $options = $this->parser->parse($store);

        self::assertInternalType('array', $options);
        self::assertCount(2, $options);

        $firstOption = $options[0];

        self::assertArrayHasKey(0, $firstOption);
        self::assertEquals('1', $firstOption[0]);
        self::assertArrayHasKey(1, $firstOption);

        $firstOptionLabels = $firstOption[1];

        self::assertCount(2, $firstOptionLabels);
        self::assertArrayHasKey('de', $firstOptionLabels);
        self::assertArrayHasKey('en', $firstOptionLabels);
        self::assertEquals('DE 1', $firstOptionLabels['de']);
        self::assertEquals('EN 1', $firstOptionLabels['en']);

        $secondOption = $options[1];

        self::assertArrayHasKey(0, $secondOption);
        self::assertEquals('TWO', $secondOption[0]);

        self::assertArrayHasKey(1, $secondOption);

        $secondOptionLabels = $secondOption[1];

        self::assertCount(2, $secondOptionLabels);
        self::assertArrayHasKey('de', $secondOptionLabels);
        self::assertArrayHasKey('en', $secondOptionLabels);
        self::assertEquals('DE 2', $secondOptionLabels['de']);
        self::assertEquals('EN 2', $secondOptionLabels['en']);
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\StoreValueParser\StoreXmlValueParser::parse()
     */
    public function testThatEmptyOptionsReturnsEmptyArray()
    {
        $store = $this->getStoreElement(2);
        $options = $this->parser->parse($store);

        self::assertInternalType('array', $options);
        self::assertCount(0, $options);
    }
}
