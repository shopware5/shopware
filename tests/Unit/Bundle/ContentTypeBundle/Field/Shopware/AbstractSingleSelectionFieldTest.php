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

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\ContentTypeBundle\Field\Shopware\AbstractSingleSelectionField;
use Shopware\Bundle\ContentTypeBundle\Field\Shopware\ProductField;
use Shopware\Bundle\ContentTypeBundle\Field\Shopware\ShopField;
use Shopware\Bundle\ContentTypeBundle\Structs\Field;
use Shopware\Models\Article\Article;
use Shopware\Models\Shop\Shop;

class AbstractSingleSelectionFieldTest extends TestCase
{
    /**
     * @dataProvider fieldProvider
     */
    public function testItEscapesExtJsOptions(AbstractSingleSelectionField $field, string $expected): void
    {
        static::assertEquals(
            $expected,
            $field::getExtjsOptions(static::createStub(Field::class))['model']
        );
    }

    /**
     * @return \Generator<string, array>
     */
    public function fieldProvider(): \Generator
    {
        yield 'ProductField' => [
            new ProductField(),
            addslashes(Article::class),
        ];

        yield 'ShopField' => [
            new ShopField(),
            addslashes(Shop::class),
        ];
    }
}
