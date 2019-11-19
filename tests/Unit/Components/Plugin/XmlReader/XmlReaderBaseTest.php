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

namespace Shopware\Tests\Unit\Components\Plugin\XmlReader;

use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Plugin\XmlReader\XmlConfigReader;
use Shopware\Components\Plugin\XmlReader\XmlReaderBase;
use Symfony\Component\Config\Util\XmlUtils;

class XmlReaderBaseTest extends TestCase
{
    /**
     * @var \DOMDocument
     */
    private $xmlFile;

    /**
     * @var DOMXPath
     */
    private $xpath;

    protected function setUp(): void
    {
        $this->xmlFile = XmlUtils::loadFile(
            __DIR__ . '/examples/base/config.xml',
            __DIR__ . '/../../../../../engine/Shopware/Components/Plugin/schema/config.xsd'
        );

        $this->xpath = new DOMXPath($this->xmlFile);
    }

    public function testConstantsHaveCorrectValue(): void
    {
        static::assertEquals(0, XmlReaderBase::SCOPE_LOCALE);
        static::assertEquals(1, XmlReaderBase::SCOPE_SHOP);
        static::assertEquals('en', XmlReaderBase::DEFAULT_LANG);
    }

    public function testReadValidFile(): void
    {
        $xmlReader = new XmlConfigReader();
        $data = $xmlReader->read(__DIR__ . '/examples/base/config.xml');

        static::assertIsArray($data);
        static::assertCount(3, $data);
    }

    public function testReadInvalidFile(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('#Unable to parse file#');

        $xmlReader = new XmlConfigReader();
        $xmlReader->read(__DIR__ . '/examples/base/config_invalid.xml');
    }

    public function testParseTranslatableNodeList(): void
    {
        $formDescriptions = $this->xpath->query('//config/description');

        $formDescriptionResult = XmlReaderBase::parseTranslatableNodeList($formDescriptions);
        static::assertCount(3, $formDescriptionResult);
        static::assertArrayHasKey('de', $formDescriptionResult);
        static::assertArrayHasKey('en', $formDescriptionResult);
        static::assertArrayHasKey('fr', $formDescriptionResult);

        static::assertEquals('My description', $formDescriptionResult['en']);
        static::assertEquals('Meine Beschreibung', $formDescriptionResult['de']);
        static::assertEquals('Ma description', $formDescriptionResult['fr']);

        $formLabels = $this->xpath->query('//config/label');
        $formLabelResult = XmlReaderBase::parseTranslatableNodeList($formLabels);

        static::assertCount(2, $formLabelResult);
        static::assertArrayHasKey('de', $formLabelResult);
        static::assertArrayHasKey('en', $formLabelResult);

        static::assertEquals('My Form Label', $formLabelResult['en']);
        static::assertEquals('Mein Form', $formLabelResult['de']);

        $firstElementDescriptions = $this->xpath->query('//config/elements/element[0]/description');
        $firstElementDescriptionResult = XmlReaderBase::parseTranslatableNodeList($firstElementDescriptions);
        static::assertNull($firstElementDescriptionResult);
    }

    public function testGetFirstItemOfNodeList(): void
    {
        $config = $this->xpath->query('//config')->item(0);
        $element = XmlReaderBase::getFirstChildren($config, 'label');

        static::assertEquals($config->getElementsByTagName('label')->item(0), $element);

        $emptyNodeList = $this->xpath->query('//config/elements/element')->item(0);

        static::assertNull(XmlReaderBase::getFirstChildren($emptyNodeList, 'description'));
    }

    public function testValidateBooleanAttribute(): void
    {
        //required="true"
        $element1 = $this->xpath->query('//config/elements/element')->item(0);
        $element1Result = XmlReaderBase::validateBooleanAttribute(
            $element1->getAttribute('required')
        );

        static::assertIsBool($element1Result);
        static::assertEquals(true, $element1Result);

        //required not given - passed default value
        $element2 = $this->xpath->query('//config/elements/element')->item(1);
        $element2Result = XmlReaderBase::validateBooleanAttribute(
            $element2->getAttribute('required'),
            false
        );

        static::assertIsBool($element2Result);
        static::assertEquals(false, $element2Result);
    }

    public function testParseStoreNodeList(): void
    {
        //ExtJs Store
        $store1 = $this->xpath->query('//config/elements/element[3]/store');
        $store1Result = XmlReaderBase::parseStoreNodeList($store1);

        static::assertIsString($store1Result);
        static::assertEquals('EXTJS-STORE', $store1Result);

        //Xml Store
        $store2 = $this->xpath->query('//config/elements/element[4]/store');
        $store2Result = XmlReaderBase::parseStoreNodeList($store2);

        static::assertIsArray($store2Result);
        static::assertCount(3, $store2Result);
        static::assertEquals('value2', $store2Result[1][0]);
        static::assertEquals('label2', $store2Result[1][1]['en']);

        //No store found
        $store3 = $this->xpath->query('//config/elements/element[5]/store');
        $store3Result = XmlReaderBase::parseStoreNodeList($store3);

        static::assertNull($store3Result);
    }

    public function testParseOptionsNodeList(): void
    {
        $options = $this->xpath->query('//config/elements/element[6]/options');
        $optionsResult = XmlReaderBase::parseOptionsNodeList($options);

        static::assertIsArray($optionsResult);
        static::assertCount(2, $optionsResult);
        static::assertArrayHasKey('minValue', $optionsResult);
        static::assertArrayHasKey('maxValue', $optionsResult);
        static::assertEquals('1', $optionsResult['minValue']);
        static::assertEquals('2', $optionsResult['maxValue']);
    }

    public function testParseOptionsNodeListNoOptions(): void
    {
        //cannot test with file because of the xsd validation
        $dom = new DOMDocument();
        $dom->loadXML('<element></element>');

        $xpath = new DOMXPath($dom);
        $options = $xpath->query('//element/options');

        static::assertNull(XmlConfigReader::parseOptionsNodeList(
            $options
        ));
    }

    public function testParseOptionsNodeListEmptyOptions(): void
    {
        //cannot test with file because of the xsd validation
        $dom = new DOMDocument();
        $dom->loadXML('<element><options></options></element>');

        $xpath = new DOMXPath($dom);
        $options = $xpath->query('//element/options');

        static::assertNull(XmlConfigReader::parseOptionsNodeList(
            $options
        ));
    }

    public function testGetElementChildValueByName(): void
    {
        $options = $this->xpath->query('//config/elements/element[6]/options');
        $value = XmlReaderBase::getElementChildValueByName($options->item(0), 'minValue', false);

        static::assertEquals('1', $value);
    }

    public function testGetElementChildValueByNameReturnsNull(): void
    {
        $options = $this->xpath->query('//config/elements/element[6]/options');
        $value = XmlReaderBase::getElementChildValueByName($options->item(0), 'invalid', false);

        static::assertNull($value);
    }

    public function testGetElementChildValueByNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Element with invalid not found');

        $options = $this->xpath->query('//config/elements/element[6]/options');
        XmlReaderBase::getElementChildValueByName($options->item(0), 'invalid', true);
    }

    public function testValidateTextAttribute(): void
    {
        static::assertEquals('combo', XmlConfigReader::validateTextAttribute('', 'combo'));
        static::assertEquals('select', XmlConfigReader::validateTextAttribute('select'));
    }
}
