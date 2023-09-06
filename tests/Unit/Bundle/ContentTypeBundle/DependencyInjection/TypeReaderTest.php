<?php
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

namespace Shopware\Tests\Unit\Bundle\ContentTypeBundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopware\Bundle\ContentTypeBundle\DependencyInjection\TypeReader;
use Shopware\Bundle\ContentTypeBundle\Field\TextField;

class TypeReaderTest extends TestCase
{
    private TypeReader $typeReader;

    /**
     * @var array{text: class-string<TextField>}
     */
    private array $mapping = [
        'text' => TextField::class,
    ];

    public function setUp(): void
    {
        $this->typeReader = new TypeReader();
    }

    public function testGetTypesWithoutMapping(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Type with name "text" does not exist');

        $this->typeReader->getTypes(['SwagTest'], [__DIR__ . '/../fixtures/plugins'], []);
    }

    public function testGetTypesWithMapping(): void
    {
        $types = $this->typeReader->getTypes(['SwagTest'], [__DIR__ . '/../fixtures/plugins'], $this->mapping);
        static::assertArrayHasKey('store', $types);
        static::assertEquals('stores', $types['store']['name']);
    }
}
