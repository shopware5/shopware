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

use PHPUnit\Framework\TestCase;
use Shopware\Components\Plugin\XmlReader\XmlConfigReader;

/**
 * Class XmlConfigReaderTest
 */
class XmlConfigReaderTest extends TestCase
{
    /**
     * @var XmlConfigReader
     */
    private $configReader;

    protected function setUp()
    {
        $this->configReader = new XmlConfigReader();
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlConfigReader::read()
     * @covers \Shopware\Components\Plugin\XmlReader\XmlConfigReader::parseFile()
     * @covers \Shopware\Components\Plugin\XmlReader\XmlConfigReader::parseElementNodeList()
     */
    public function testReadFile()
    {
        $result = $this->readFile('config.xml');

        //form label
        self::assertArrayHasKey('label', $result);
        self::assertCount(2, $result['label']);
        self::assertArrayHasKey('en', $result['label']);
        self::assertArrayHasKey('de', $result['label']);
        self::assertEquals('My Form Label', $result['label']['en']);
        self::assertEquals('Mein Form', $result['label']['de']);

        //form description
        self::assertArrayHasKey('description', $result);
        self::assertCount(2, $result['description']);
        self::assertArrayHasKey('en', $result['description']);
        self::assertArrayHasKey('de', $result['description']);
        self::assertEquals('My Form description', $result['description']['en']);
        self::assertEquals('Meine Form Beschreibung', $result['description']['de']);

        //elements
        self::assertArrayHasKey('elements', $result);

        //first element
        $element1 = $result['elements'][0];

        self::assertArrayHasKey('options', $element1);
        self::assertCount(2, $element1['options']);
        self::assertArrayHasKey('minValue', $element1['options']);
        self::assertArrayHasKey('maxValue', $element1['options']);
        self::assertEquals('1', $element1['options']['minValue']);
        self::assertEquals('2', $element1['options']['maxValue']);

        //second element store
        $element2 = $result['elements'][1];

        //element label
        self::assertArrayHasKey('label', $element2);
        self::assertCount(2, $element2['label']);
        self::assertArrayHasKey('en', $element2['label']);
        self::assertArrayHasKey('de', $element2['label']);
        self::assertEquals('label', $element2['label']['en']);
        self::assertEquals('Mein textfeld', $element2['label']['de']);

        //element description
        self::assertArrayHasKey('description', $element2);
        self::assertCount(2, $element2['description']);
        self::assertArrayHasKey('en', $element2['description']);
        self::assertArrayHasKey('de', $element2['description']);
        self::assertEquals('My Field description', $element2['description']['en']);
        self::assertEquals('Meine Feld Beschreibung', $element2['description']['de']);

        self::assertArrayHasKey('store', $element2);
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlConfigReader::parseElementNodeList()
     */
    public function testParseElementNodeListEmpty()
    {
        $reflection = new \ReflectionClass(get_class($this->configReader));
        $method = $reflection->getMethod('parseElementNodeList');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->configReader, [new \DOMNodeList()]);

        self::assertInternalType('array', $result);
        self::assertCount(0, $result);
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlConfigReader::validateAttributeScope()
     */
    public function testValidateAttributeScope()
    {
        //default value SCOPE_LOCALE
        self::assertEquals(
            XmlConfigReader::SCOPE_LOCALE,
            XmlConfigReader::validateAttributeScope('')
        );

        //SCOPE_LOCALE
        self::assertEquals(
            XmlConfigReader::SCOPE_LOCALE,
            XmlConfigReader::validateAttributeScope('locale')
        );

        //SCOPE_SHOP
        self::assertEquals(
            XmlConfigReader::SCOPE_SHOP,
            XmlConfigReader::validateAttributeScope('shop')
        );
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlConfigReader::validateAttributeScope()
     * @expectedException \InvalidArgumentException
     */
    public function testValidateAttributeScopeThrowsException()
    {
        XmlConfigReader::validateAttributeScope('invalid value');
    }

    /**
     * Helper function to read a plugin xml file.
     *
     * @param string $file
     *
     * @return array
     */
    private function readFile($file)
    {
        return $this->configReader->read(
            sprintf('%s/examples/config/%s', __DIR__, $file)
        );
    }
}
