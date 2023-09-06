<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Unit\Components\Plugin\XmlReader;

use DOMNodeList;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shopware\Components\Plugin\XmlReader\XmlCronjobReader;

class XmlCronjobReaderTest extends TestCase
{
    private XmlCronjobReader $cronjobReader;

    protected function setUp(): void
    {
        $this->cronjobReader = new XmlCronjobReader();
    }

    public function testReadFile(): void
    {
        $result = $this->readFile();

        static::assertCount(2, $result);

        $firstCron = $result[0];

        static::assertArrayHasKey('name', $firstCron);
        static::assertArrayHasKey('action', $firstCron);
        static::assertArrayHasKey('active', $firstCron);
        static::assertArrayHasKey('interval', $firstCron);
        static::assertArrayHasKey('disable_on_error', $firstCron);

        static::assertEquals('Article Importer', $firstCron['name']);
        static::assertEquals('ImportArticle', $firstCron['action']);
        static::assertTrue($firstCron['active']);
        static::assertEquals(3600, $firstCron['interval']);
        static::assertTrue($firstCron['disable_on_error']);

        $secondCron = $result[1];

        static::assertArrayHasKey('name', $secondCron);
        static::assertArrayHasKey('action', $secondCron);
        static::assertArrayHasKey('active', $secondCron);
        static::assertArrayHasKey('interval', $secondCron);
        static::assertArrayHasKey('disable_on_error', $secondCron);

        static::assertEquals('Order Export', $secondCron['name']);
        static::assertEquals('ExportOrder', $secondCron['action']);
        static::assertTrue($secondCron['active']);
        static::assertEquals(3600, $secondCron['interval']);
        static::assertFalse($secondCron['disable_on_error']);
    }

    public function testReadEmptyFile(): void
    {
        $reflection = new ReflectionClass(\get_class($this->cronjobReader));
        $method = $reflection->getMethod('parseList');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->cronjobReader, [new DOMNodeList()]);

        static::assertIsArray($result);
        static::assertCount(0, $result);
    }

    private function readFile(): array
    {
        return $this->cronjobReader->read(
            sprintf('%s/examples/cronjob/%s', __DIR__, 'cronjob.xml')
        );
    }
}
