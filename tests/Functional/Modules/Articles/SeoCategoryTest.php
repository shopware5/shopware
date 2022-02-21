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

namespace Shopware\Tests\Functional\Modules\Articles;

use Enlight_Components_Test_Plugin_TestCase;
use Shopware\Components\Api\Resource\Article;
use Shopware\Components\Api\Resource\Resource;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article as Product;
use Shopware\Tests\Functional\Traits\ContainerTrait;

class SeoCategoryTest extends Enlight_Components_Test_Plugin_TestCase
{
    use ContainerTrait;

    private Article $resource;

    public function setUp(): void
    {
        $this->getContainer()->get(ModelManager::class)->clear();
        $this->resource = new Article();
        $this->resource->setManager(Shopware()->Models());
        parent::setUp();
    }

    public function testSeoCategory(): void
    {
        $this->dispatch('/');

        $data = $this->getSimpleTestData();

        $data['categories'] = $this->getContainer()->get('dbal_connection')->fetchAllAssociative('SELECT DISTINCT id FROM s_categories LIMIT 5, 10');

        $first = $data['categories'][3];
        $second = $data['categories'][4];

        $data['seoCategories'] = [
            ['shopId' => 1, 'categoryId' => $first['id']],
            ['shopId' => 2, 'categoryId' => $second['id']],
        ];

        $product = $this->resource->create($data);
        static::assertInstanceOf(Product::class, $product);

        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);

        $product = $this->resource->getOne($product->getId());
        static::assertInstanceOf(Product::class, $product);

        $german = Shopware()->Modules()->Categories()->sGetCategoryIdByArticleId(
            $product->getId(),
            null,
            1
        );

        $english = Shopware()->Modules()->Categories()->sGetCategoryIdByArticleId(
            $product->getId(),
            null,
            2
        );

        static::assertSame((int) $first['id'], $german);
        static::assertSame((int) $second['id'], $english);
    }

    private function getSimpleTestData(): array
    {
        return [
            'name' => 'Testartikel',
            'description' => 'Test description',
            'active' => true,
            'mainDetail' => [
                'number' => 'swTEST' . uniqid((string) rand()),
                'inStock' => 15,
                'unitId' => 1,
                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'from' => 1,
                        'to' => '-',
                        'price' => 400,
                    ],
                ],
            ],
            'taxId' => 1,
            'supplierId' => 2,
        ];
    }
}
