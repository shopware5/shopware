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

namespace Shopware\Tests\Functional\Bundle\ESIndexingBundle\TextMapping;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\ESIndexingBundle\Product\ProductMapping;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class TextMappingTest extends TestCase
{
    /**
     * @var Connection
     */
    private $connection;

    protected function setUp(): void
    {
        $this->connection = Shopware()->Container()->get('dbal_connection');
        $this->connection->beginTransaction();

        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->connection->rollBack();

        parent::tearDown();
    }

    public function testKeywordsIsQueryAble()
    {
        $context = $this->getContext();
        $category = $this->helper->createCategory();

        $products = $this->createProducts(['test9999' => []], $context, $category);

        $this->helper->updateArticle(key($products), ['mainDetail' => [
            'ean' => 444,
            ],
        ]);

        $client = Shopware()->Container()->get('shopware_elastic_search.client');
        $indexFactory = Shopware()->Container()->get('shopware_elastic_search.index_factory');

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

    /**
     * {@inheritdoc}
     */
    public function createProducts($products, ShopContext $context, Category $category)
    {
        $articles = parent::createProducts($products, $context, $category);

        $this->helper->refreshSearchIndexes($context->getShop());

        return $articles;
    }
}
