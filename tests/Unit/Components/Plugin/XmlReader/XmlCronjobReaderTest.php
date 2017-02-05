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
use Shopware\Components\Plugin\XmlReader\XmlCronjobReader;

class XmlCronjobReaderTest extends TestCase
{
    /**
     * @var XmlCronjobReader
     */
    private $cronjobReader;

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
     * Helper function to read a plugin xml file.
     *
     * @param string $file
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
