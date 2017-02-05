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
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shopware\Components\Plugin\XmlReader\XmlMenuReader;

class XmlMenuReaderTest extends TestCase
{
    /**
     * @var XmlMenuReader
     */
    private $menuReader;

    protected function setUp()
    {
        $this->menuReader = new XmlMenuReader();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testThatEmptyEntriesThrowException()
    {
        $dom = new DOMDocument();
        $dom->loadXML('<entries></entries>');

        $reflection = new ReflectionClass(get_class($this->menuReader));
        $method = $reflection->getMethod('parseFile');
        $method->setAccessible(true);

        $method->invokeArgs($this->menuReader, [$dom]);
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlMenuReader::parseFile()
     * @covers \Shopware\Components\Plugin\XmlReader\XmlMenuReader::parseEntry()
     */
    public function testReadFile()
    {
        $result = $this->readFile('menu.xml');

        self::assertInternalType('array', $result);
        self::assertCount(2, $result);

        $firstMenu = $result[0];

        self::assertArrayHasKey('isRootMenu', $firstMenu);
        self::assertArrayHasKey('label', $firstMenu);

        self::assertArrayHasKey('name', $firstMenu);
        self::assertArrayHasKey('controller', $firstMenu);
        self::assertArrayHasKey('action', $firstMenu);
        self::assertArrayHasKey('class', $firstMenu);
        self::assertArrayHasKey('parent', $firstMenu);
        self::assertArrayHasKey('active', $firstMenu);
        self::assertArrayHasKey('position', $firstMenu);

        self::assertEquals(false, $firstMenu['isRootMenu']);
        self::assertEquals('SwagDefaultSort', $firstMenu['name']);
        self::assertEquals('SwagDefaultSort', $firstMenu['controller']);
        self::assertEquals('index', $firstMenu['action']);
        self::assertEquals('sprite-sort', $firstMenu['class']);
        self::assertEquals(false, $firstMenu['active']);
        self::assertEquals(-1, $firstMenu['position']);

        self::assertArrayHasKey('label', $firstMenu['parent']);
        self::assertEquals('Einstellungen', $firstMenu['parent']['label']);

        self::assertArrayHasKey('children', $firstMenu);
        self::assertCount(1, $firstMenu['children']);
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
        return $this->menuReader->read(
            sprintf('%s/examples/menu/%s', __DIR__, $file)
        );
    }
}
