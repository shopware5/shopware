<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Unit\Bundle\ContentTypeBundle\Field\Shopware;

use Generator;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\ContentTypeBundle\Field\MediaGrid;
use Shopware\Bundle\ContentTypeBundle\Field\Shopware\AbstractGridField;
use Shopware\Bundle\ContentTypeBundle\Field\Shopware\ProductGrid;
use Shopware\Bundle\ContentTypeBundle\Field\Shopware\ShopGrid;
use Shopware\Bundle\ContentTypeBundle\Structs\Field;
use Shopware\Models\Article\Article;
use Shopware\Models\Media\Media;
use Shopware\Models\Shop\Shop;

class AbstractGridFieldTest extends TestCase
{
    /**
     * @dataProvider fieldProvider
     */
    public function testItEscapesExtJsOptions(AbstractGridField $field, string $expected): void
    {
        static::assertEquals(
            $expected,
            $field::getExtjsOptions(static::createStub(Field::class))['model']
        );
    }

    /**
     * @return Generator<string, array>
     */
    public function fieldProvider(): Generator
    {
        yield 'MediaGrid' => [
            new MediaGrid(),
            addslashes(Media::class),
        ];

        yield 'ProductGrid' => [
            new ProductGrid(),
            addslashes(Article::class),
        ];

        yield 'ShopGrid' => [
            new ShopGrid(),
            addslashes(Shop::class),
        ];
    }
}
