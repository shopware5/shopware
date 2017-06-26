<?php

namespace Shopware\Tests\Unit\Components\Plugin\XmlReader;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Plugin\XmlReader\XmlMenuReader;

/**
 * Class XmlMenuReaderTest
 *
 * @package Shopware\Tests\Unit\Components\Plugin\XmlReader
 */
class XmlMenuReaderTest extends TestCase
{
    /**
     * @var XmlMenuReader
     */
    private $menuReader;

    /**
     * set up test
     */
    protected function setUp()
    {
        $this->menuReader = new XmlMenuReader();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testThatEmptyEntriesThrowException()
    {
        $dom = new \DOMDocument();
        $dom->loadXML('<entries></entries>');

        $reflection = new \ReflectionClass(get_class($this->menuReader));
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
     * helper function to read a menu xml file
     *
     * @param $file
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
