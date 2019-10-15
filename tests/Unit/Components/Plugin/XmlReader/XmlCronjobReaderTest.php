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

    protected function setUp(): void
    {
        $this->cronjobReader = new XmlCronjobReader();
    }

    public function testReadFile(): void
    {
        $result = $this->readFile('cronjob.xml');

        static::assertIsArray($result);
        static::assertCount(2, $result);

        $firstCron = $result[0];

        static::assertArrayHasKey('name', $firstCron);
        static::assertArrayHasKey('action', $firstCron);
        static::assertArrayHasKey('active', $firstCron);
        static::assertArrayHasKey('interval', $firstCron);
        static::assertArrayHasKey('disable_on_error', $firstCron);

        static::assertEquals('Article Importer', $firstCron['name']);
        static::assertEquals('ImportArticle', $firstCron['action']);
        static::assertEquals(true, $firstCron['active']);
        static::assertEquals(3600, $firstCron['interval']);
        static::assertEquals(true, $firstCron['disable_on_error']);

        $secondCron = $result[1];

        static::assertArrayHasKey('name', $secondCron);
        static::assertArrayHasKey('action', $secondCron);
        static::assertArrayHasKey('active', $secondCron);
        static::assertArrayHasKey('interval', $secondCron);
        static::assertArrayHasKey('disable_on_error', $secondCron);

        static::assertEquals('Order Export', $secondCron['name']);
        static::assertEquals('ExportOrder', $secondCron['action']);
        static::assertEquals(true, $secondCron['active']);
        static::assertEquals(3600, $secondCron['interval']);
        static::assertEquals(false, $secondCron['disable_on_error']);
    }

    public function testReadEmptyFile(): void
    {
        $reflection = new \ReflectionClass(get_class($this->cronjobReader));
        $method = $reflection->getMethod('parseList');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->cronjobReader, [new \DOMNodeList()]);

        static::assertIsArray($result);
        static::assertCount(0, $result);
    }

    private function readFile(string $file): array
    {
        return $this->cronjobReader->read(
            sprintf('%s/examples/cronjob/%s', __DIR__, $file)
        );
    }
}
