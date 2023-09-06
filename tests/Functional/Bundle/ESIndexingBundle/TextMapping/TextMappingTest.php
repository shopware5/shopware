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

namespace Shopware\Tests\Functional\Bundle\ESIndexingBundle\TextMapping;

use Doctrine\DBAL\Connection;
use Elasticsearch\Client;
use Shopware\Bundle\ESIndexingBundle\IndexFactory;
use Shopware\Bundle\ESIndexingBundle\Product\ProductMapping;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class TextMappingTest extends TestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = Shopware()->Container()->get(Connection::class);
        $this->connection->beginTransaction();

        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->connection->rollBack();

        parent::tearDown();
    }

    public function testKeywordsIsQueryAble(): void
    {
        $context = $this->getContext();
        $category = $this->helper->createCategory();

        $products = $this->createProducts(['test9999' => []], $context, $category);
        $productNumber = key($products);
        static::assertIsString($productNumber);

        $this->helper->updateProduct($productNumber, ['mainDetail' => [
            'ean' => 444,
            ],
        ]);

        $client = Shopware()->Container()->get(Client::class);
        $indexFactory = Shopware()->Container()->get(IndexFactory::class);

        $this->helper->refreshSearchIndexes($context->getShop());

        $version = Shopware()->Container()->getParameter('shopware.es.version');

        $arguments = [
            'index' => $indexFactory->createShopIndex($context->getShop(), ProductMapping::TYPE)->getName(),
            'body' => [
                'query' => [
                    'term' => [
                        'ean' => '444',
                    ],
                ],
            ],
        ];

        if (version_compare($version, '7', '>=')) {
            $arguments = array_merge(
                $arguments,
                [
                    'rest_total_hits_as_int' => true,
                    'track_total_hits' => true,
                ]
            );
        }

        $response = $client->search($arguments);

        static::assertEquals('test9999', $response['hits']['hits'][0]['_id']);
    }

    public function createProducts(array $products, ShopContext $context, Category $category): array
    {
        $createdProducts = parent::createProducts($products, $context, $category);

        $this->helper->refreshSearchIndexes($context->getShop());

        return $createdProducts;
    }
}
