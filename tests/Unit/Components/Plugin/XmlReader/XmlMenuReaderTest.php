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

use DOMDocument;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopware\Components\Plugin\XmlReader\XmlMenuReader;
use Shopware\Tests\TestReflectionHelper;

class XmlMenuReaderTest extends TestCase
{
    private XmlMenuReader $menuReader;

    protected function setUp(): void
    {
        $this->menuReader = new XmlMenuReader();
    }

    public function testThatEmptyEntriesThrowException(): void
    {
        $dom = new DOMDocument();
        $dom->loadXML('<entries></entries>');
        $method = TestReflectionHelper::getMethod(\get_class($this->menuReader), 'parseFile');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Required element "entry" is missing in file ".*\/tests\/Unit\/Components\/Plugin\/XmlReader\/examples\/cronjob\/cronjob\.xml"\./');
        $method->invokeArgs($this->menuReader, [$dom]);
    }

    public function testReadFile(): void
    {
        $result = $this->readFile('menu.xml');

        static::assertCount(2, $result);

        $firstMenu = $result[0];

        static::assertArrayHasKey('isRootMenu', $firstMenu);
        static::assertArrayHasKey('label', $firstMenu);

        static::assertArrayHasKey('name', $firstMenu);
        static::assertArrayHasKey('controller', $firstMenu);
        static::assertArrayHasKey('action', $firstMenu);
        static::assertArrayHasKey('class', $firstMenu);
        static::assertArrayHasKey('parent', $firstMenu);
        static::assertArrayHasKey('active', $firstMenu);
        static::assertArrayHasKey('position', $firstMenu);

        static::assertFalse($firstMenu['isRootMenu']);
        static::assertEquals('SwagDefaultSort', $firstMenu['name']);
        static::assertEquals('SwagDefaultSort', $firstMenu['controller']);
        static::assertEquals('index', $firstMenu['action']);
        static::assertEquals('sprite-sort', $firstMenu['class']);
        static::assertFalse($firstMenu['active']);
        static::assertEquals(-1, $firstMenu['position']);

        static::assertArrayHasKey('label', $firstMenu['parent']);
        static::assertEquals('Einstellungen', $firstMenu['parent']['label']);

        static::assertArrayHasKey('children', $firstMenu);
        static::assertCount(1, $firstMenu['children']);
    }

    public function testMultipleChildren(): void
    {
        $result = $this->readFile('paypal.xml');

        static::assertEquals('PayPal', $result[0]['label']['de']);
        static::assertNotNull($result[0]);
        static::assertArrayHasKey('children', $result[0]);
        static::assertCount(2, $result[0]['children']);
        static::assertEquals('PaypalUnified', $result[0]['children'][0]['controller']);
        static::assertEquals('PaypalUnifiedSettings', $result[0]['children'][1]['controller']);
    }

    private function readFile(string $file): array
    {
        return $this->menuReader->read(
            sprintf('%s/examples/menu/%s', __DIR__, $file)
        );
    }
}
