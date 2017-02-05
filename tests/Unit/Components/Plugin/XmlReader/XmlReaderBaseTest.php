<?php

namespace Shopware\Tests\Unit\Components\Plugin\XmlReader;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Plugin\XmlReader\XmlConfigReader;
use Shopware\Components\Plugin\XmlReader\XmlReaderBase;
use Symfony\Component\Config\Util\XmlUtils;

/**
 * Class XmlReaderBaseTest
 *
 * @package Shopware\Tests\Unit\Components\Plugin\XmlReader
 */
class XmlReaderBaseTest extends TestCase
{
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
        $this->xmlFile = XmlUtils::loadFile(
            __DIR__ . '/examples/base/config.xml',
            __DIR__ . '/../../../../../engine/Shopware/Components/Plugin/schema/config.xsd'
        );

        $this->xpath = new \DOMXPath($this->xmlFile);
    }

    /**
     * testing constants
     */
    public function testConstantsHaveCorrectValue()
    {
        self::assertEquals(0, XmlReaderBase::SCOPE_LOCALE);
        self::assertEquals(1, XmlReaderBase::SCOPE_SHOP);
        self::assertEquals('en', XmlReaderBase::DEFAULT_LANG);
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlReaderBase::read()
     */
    public function testReadValidFile()
    {
        $xmlReader = new XmlConfigReader();
        $data = $xmlReader->read(__DIR__ . '/examples/base/config.xml');

        self::assertInternalType('array', $data);
        self::assertCount(3, $data);
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlReaderBase::read()
     * @expectedException \InvalidArgumentException
     */
    public function testReadInvalidFile()
    {
        $xmlReader = new XmlConfigReader();
        $xmlReader->read(__DIR__ . '/examples/base/config_invalid.xml');
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlReaderBase::parseTranslatableNodeList()
     */
    public function testParseTranslatableNodeList()
    {
        $formDescriptions = $this->xpath->query('//config/description');

        $formDescriptionResult = XmlReaderBase::parseTranslatableNodeList($formDescriptions);
        self::assertCount(3, $formDescriptionResult);
        self::assertArrayHasKey('de', $formDescriptionResult);
        self::assertArrayHasKey('en', $formDescriptionResult);
        self::assertArrayHasKey('fr', $formDescriptionResult);

        self::assertEquals('My description', $formDescriptionResult['en']);
        self::assertEquals('Meine Beschreibung', $formDescriptionResult['de']);
        self::assertEquals('Ma description', $formDescriptionResult['fr']);

        $formLabels = $this->xpath->query('//config/label');
        $formLabelResult = XmlReaderBase::parseTranslatableNodeList($formLabels);

        self::assertCount(2, $formLabelResult);
        self::assertArrayHasKey('de', $formLabelResult);
        self::assertArrayHasKey('en', $formLabelResult);

        self::assertEquals('My Form Label', $formLabelResult['en']);
        self::assertEquals('Mein Form', $formLabelResult['de']);

        $firstElementDescriptions = $this->xpath->query('//config/elements/element[0]/description');
        $firstElementDescriptionResult = XmlReaderBase::parseTranslatableNodeList($firstElementDescriptions);
        self::assertNull($firstElementDescriptionResult);
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlReaderBase::getFirstChildren()
     */
    public function testGetFirstItemOfNodeList()
    {
        $config = $this->xpath->query('//config')->item(0);
        $element = XmlReaderBase::getFirstChildren($config, 'label');

        self::assertEquals($config->getElementsByTagName('label')->item(0), $element);

        $emptyNodeList = $this->xpath->query('//config/elements/element')->item(0);

        self::assertNull(XmlReaderBase::getFirstChildren($emptyNodeList, 'description'));
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlReaderBase::validateBooleanAttribute()
     */
    public function testValidateBooleanAttribute()
    {
        //required="true"
        $element1 = $this->xpath->query('//config/elements/element')->item(0);
        $element1Result = XmlReaderBase::validateBooleanAttribute(
            $element1->getAttribute('required')
        );

        self::assertInternalType('bool', $element1Result);
        self::assertEquals(true, $element1Result);

        //required not given - passed default value
        $element2 = $this->xpath->query('//config/elements/element')->item(1);
        $element2Result = XmlReaderBase::validateBooleanAttribute(
            $element2->getAttribute('required'),
            false
        );

        self::assertInternalType('bool', $element2Result);
        self::assertEquals(false, $element2Result);
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlReaderBase::parseStoreNodeList()
     */
    public function testParseStoreNodeList()
    {
        //ExtJs Store
        $store1 = $this->xpath->query('//config/elements/element[3]/store');
        $store1Result = XmlReaderBase::parseStoreNodeList($store1);

        self::assertInternalType('string', $store1Result);
        self::assertEquals('EXTJS-STORE', $store1Result);

        //Xml Store
        $store2 = $this->xpath->query('//config/elements/element[4]/store');
        $store2Result = XmlReaderBase::parseStoreNodeList($store2);

        self::assertInternalType('array', $store2Result);
        self::assertCount(3, $store2Result);
        self::assertEquals('value2', $store2Result[1][0]);
        self::assertEquals('label2', $store2Result[1][1]['en']);

        //No store found
        $store3 = $this->xpath->query('//config/elements/element[5]/store');
        $store3Result = XmlReaderBase::parseStoreNodeList($store3);

        self::assertNull(null, $store3Result);
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlReaderBase::parseOptionsNodeList()
     */
    public function testParseOptionsNodeList()
    {
        $options = $this->xpath->query('//config/elements/element[6]/options');
        $optionsResult = XmlReaderBase::parseOptionsNodeList($options);

        self::assertInternalType('array', $optionsResult);
        self::assertCount(2, $optionsResult);
        self::assertArrayHasKey('minValue', $optionsResult);
        self::assertArrayHasKey('maxValue', $optionsResult);
        self::assertEquals('1', $optionsResult['minValue']);
        self::assertEquals('2', $optionsResult['maxValue']);
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlReaderBase::parseOptionsNodeList()
     */
    public function testParseOptionsNodeListNoOptions()
    {
        //cannot test with file because of the xsd validation
        $dom = new DOMDocument();
        $dom->loadXML('<element></element>');

        $xpath = new \DOMXPath($dom);
        $options = $xpath->query('//element/options');

        self::assertNull(XmlConfigReader::parseOptionsNodeList(
            $options
        ));
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlReaderBase::parseOptionsNodeList()
     */
    public function testParseOptionsNodeListEmptyOptions()
    {
        //cannot test with file because of the xsd validation
        $dom = new DOMDocument();
        $dom->loadXML('<element><options></options></element>');

        $xpath = new \DOMXPath($dom);
        $options = $xpath->query('//element/options');

        self::assertNull(XmlConfigReader::parseOptionsNodeList(
            $options
        ));
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlReaderBase::getElementChildValueByName()
     */
    public function testGetElementChildValueByName()
    {
        $options = $this->xpath->query('//config/elements/element[6]/options');
        $value = XmlReaderBase::getElementChildValueByName($options->item(0), 'minValue', false);

        self::assertEquals('1', $value);
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlReaderBase::getElementChildValueByName()
     */
    public function testGetElementChildValueByNameReturnsNull()
    {
        $options = $this->xpath->query('//config/elements/element[6]/options');
        $value = XmlReaderBase::getElementChildValueByName($options->item(0), 'invalid', false);

        self::assertNull($value);
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlReaderBase::getElementChildValueByName()
     * @expectedException \InvalidArgumentException
     */
    public function testGetElementChildValueByNameThrowsException()
    {
        $options = $this->xpath->query('//config/elements/element[6]/options');
        XmlReaderBase::getElementChildValueByName($options->item(0), 'invalid', true);
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlReaderBase::validateTextAttribute()
     */
    public function testValidateTextAttribute()
    {
        self::assertEquals('combo', XmlConfigReader::validateTextAttribute('', 'combo'));
        self::assertEquals('select', XmlConfigReader::validateTextAttribute('select'));
    }
}
