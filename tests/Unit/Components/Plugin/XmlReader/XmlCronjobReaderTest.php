<?php

namespace Shopware\Tests\Unit\Components\Plugin\XmlReader;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Plugin\XmlReader\XmlCronjobReader;

/**
 * Class XmlCronjobReaderTest
 *
 * @package Shopware\Tests\Unit\Components\Plugin\XmlReader
 */
class XmlCronjobReaderTest extends TestCase
{
    /**
     * @var XmlCronjobReader
     */
    private $cronjobReader;

    /**
     * set up test
     */
    protected function setUp()
    {
        $this->cronjobReader = new XmlCronjobReader();
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlCronjobReader::read()
     * @covers \Shopware\Components\Plugin\XmlReader\XmlCronjobReader::parseFile()
     * @covers \Shopware\Components\Plugin\XmlReader\XmlCronjobReader::parseList()
     * @covers \Shopware\Components\Plugin\XmlReader\XmlCronjobReader::parseItem()
     */
    public function testReadFile()
    {
        $result = $this->readFile('cronjob.xml');

        self::assertInternalType('array', $result);
        self::assertCount(2, $result);

        $firstCron = $result[0];

        self::assertArrayHasKey('name', $firstCron);
        self::assertArrayHasKey('action', $firstCron);
        self::assertArrayHasKey('active', $firstCron);
        self::assertArrayHasKey('interval', $firstCron);
        self::assertArrayHasKey('disable_on_error', $firstCron);

        self::assertEquals('Article Importer', $firstCron['name']);
        self::assertEquals('ImportArticle', $firstCron['action']);
        self::assertEquals(true, $firstCron['active']);
        self::assertEquals(3600, $firstCron['interval']);
        self::assertEquals(true, $firstCron['disable_on_error']);

        $secondCron = $result[1];

        self::assertArrayHasKey('name', $secondCron);
        self::assertArrayHasKey('action', $secondCron);
        self::assertArrayHasKey('active', $secondCron);
        self::assertArrayHasKey('interval', $secondCron);
        self::assertArrayHasKey('disable_on_error', $secondCron);

        self::assertEquals('Order Export', $secondCron['name']);
        self::assertEquals('ExportOrder', $secondCron['action']);
        self::assertEquals(true, $secondCron['active']);
        self::assertEquals(3600, $secondCron['interval']);
        self::assertEquals(false, $secondCron['disable_on_error']);
    }

    /**
     * @covers \Shopware\Components\Plugin\XmlReader\XmlCronjobReader::parseList()
     */
    public function testReadEmptyFile()
    {
        $reflection = new \ReflectionClass(get_class($this->cronjobReader));
        $method = $reflection->getMethod('parseList');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->cronjobReader, [new \DOMNodeList()]);

        self::assertInternalType('array', $result);
        self::assertCount(0, $result);
    }

    /**
     * helper function to read a cronjob xml file
     *
     * @param $file
     *
     * @return array
     */
    private function readFile($file)
    {
        return $this->cronjobReader->read(
            sprintf('%s/examples/cronjob/%s', __DIR__, $file)
        );
    }
}
