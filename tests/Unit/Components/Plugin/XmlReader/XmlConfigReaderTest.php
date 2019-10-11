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

class XmlConfigReaderTest extends TestCase
{
    /**
     * @var XmlConfigReader
     */
    private $configReader;

    protected function setUp(): void
    {
        $this->configReader = new XmlConfigReader();
    }

    public function testReadFile(): void
    {
        $result = $this->readFile('config.xml');

        //form label
        static::assertArrayHasKey('label', $result);
        static::assertCount(2, $result['label']);
        static::assertArrayHasKey('en', $result['label']);
        static::assertArrayHasKey('de', $result['label']);
        static::assertEquals('My Form Label', $result['label']['en']);
        static::assertEquals('Mein Form', $result['label']['de']);

        //form description
        static::assertArrayHasKey('description', $result);
        static::assertCount(2, $result['description']);
        static::assertArrayHasKey('en', $result['description']);
        static::assertArrayHasKey('de', $result['description']);
        static::assertEquals('My Form description', $result['description']['en']);
        static::assertEquals('Meine Form Beschreibung', $result['description']['de']);

        //elements
        static::assertArrayHasKey('elements', $result);

        //first element
        $element1 = $result['elements'][0];

        static::assertArrayHasKey('options', $element1);
        static::assertCount(2, $element1['options']);
        static::assertArrayHasKey('minValue', $element1['options']);
        static::assertArrayHasKey('maxValue', $element1['options']);
        static::assertEquals('1', $element1['options']['minValue']);
        static::assertEquals('2', $element1['options']['maxValue']);

        //second element store
        $element2 = $result['elements'][1];

        //element label
        static::assertArrayHasKey('label', $element2);
        static::assertCount(2, $element2['label']);
        static::assertArrayHasKey('en', $element2['label']);
        static::assertArrayHasKey('de', $element2['label']);
        static::assertEquals('My Textfield', $element2['label']['en']);
        static::assertEquals('Mein textfeld', $element2['label']['de']);

        //element description
        static::assertArrayHasKey('description', $element2);
        static::assertCount(2, $element2['description']);
        static::assertArrayHasKey('en', $element2['description']);
        static::assertArrayHasKey('de', $element2['description']);
        static::assertEquals('My Field description', $element2['description']['en']);
        static::assertEquals('Meine Feld Beschreibung', $element2['description']['de']);

        static::assertArrayHasKey('store', $element2);

        // third element
        $element3 = $result['elements'][2];
        static::assertFalse($element3['value']);

        // fourth element
        $element4 = $result['elements'][3];
        static::assertTrue($element4['value']);

        // five element
        $element5 = $result['elements'][4];
        static::assertSame('testText', $element5['value']);
    }

    public function testConfigReadingFromXml(): void
    {
        $result = $this->readFile('config_store_xml.xml');

        $element1 = $result['elements'][0];

        static::assertArrayHasKey('store', $element1);
        static::assertEquals('XML Store', $element1['label']['en']);
        static::assertIsArray($element1['store']);
        static::assertCount(2, $element1['store']);
    }

    public function testConfigReadingWithExtJsStore(): void
    {
        $result = $this->readFile('config_store_extjs.xml');

        $element1 = $result['elements'][0];

        static::assertArrayHasKey('store', $element1);
        static::assertEquals('Shopware.apps.Base.store.Category', $element1['store']);
    }

    public function testParseElementNodeListEmpty(): void
    {
        $reflection = new \ReflectionClass(get_class($this->configReader));
        $method = $reflection->getMethod('parseElementNodeList');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->configReader, [new \DOMNodeList()]);

        static::assertIsArray($result);
        static::assertCount(0, $result);
    }

    public function testValidateAttributeScope(): void
    {
        //default value SCOPE_LOCALE
        static::assertEquals(
            XmlConfigReader::SCOPE_LOCALE,
            XmlConfigReader::validateAttributeScope('')
        );

        //SCOPE_LOCALE
        static::assertEquals(
            XmlConfigReader::SCOPE_LOCALE,
            XmlConfigReader::validateAttributeScope('locale')
        );

        //SCOPE_SHOP
        static::assertEquals(
            XmlConfigReader::SCOPE_SHOP,
            XmlConfigReader::validateAttributeScope('shop')
        );
    }

    public function testValidateAttributeScopeThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid scope "invalid value"');
        XmlConfigReader::validateAttributeScope('invalid value');
    }

    private function readFile(string $file): array
    {
        return $this->configReader->read(
            sprintf('%s/examples/config/%s', __DIR__, $file)
        );
    }
}
