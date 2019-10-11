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

namespace Shopware\Tests\Functional\Bundle\SearchBundle;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\BatchProductNumberSearchRequest;
use Shopware\Bundle\SearchBundle\BatchProductSearch;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Sorting\ProductNameSorting;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class BatchProductSearchTest extends TestCase
{
    /**
     * @var BatchProductSearch
     */
    private $batchProductSearch;

    /**
     * @var Connection
     */
    private $connection;

    protected function setUp(): void
    {
        $this->connection = Shopware()->Container()->get('dbal_connection');
        $this->connection->beginTransaction();
        $this->batchProductSearch = Shopware()->Container()->get('shopware_search.batch_product_search');

        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->connection->rollBack();

        parent::tearDown();
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

    public function testWithNumericArticleNumbers()
    {
        $context = $this->getContext();
        $category = $this->helper->createCategory();
        $this->createProducts([10002 => [], 'SW10001' => []], $context, $category);

        $request = new BatchProductNumberSearchRequest();
        $request->setProductNumbers('test-1', [10002, 'SW10001']);

        $result = $this->batchProductSearch->search($request, $context);

        static::assertArrayHasKey(10002, $result->get('test-1'));
        static::assertArrayHasKey('SW10001', $result->get('test-1'));
    }

    public function testWithLessProductsThanRequested()
    {
        $context = $this->getContext();
        $category = $this->helper->createCategory();
        $this->createProducts(
            [
                'BATCH-A' => ['name' => 'BATCH-A'],
                'BATCH-B' => ['name' => 'BATCH-B'],
                'BATCH-C' => ['name' => 'BATCH-C'],
                'BATCH-D' => ['name' => 'BATCH-D'],
                'BATCH-E' => ['name' => 'BATCH-E'],
                'BATCH-F' => ['name' => 'BATCH-F'],
                'BATCH-G' => ['name' => 'BATCH-G'],
                'BATCH-H' => ['name' => 'BATCH-H'],
                'BATCH-I' => ['name' => 'BATCH-I'],
                'BATCH-J' => ['name' => 'BATCH-J'],
            ],
            $context,
            $category
        );

        $criteria = new Criteria();
        $criteria->addCondition(new CategoryCondition([$category->getId()]));
        $criteria->addSorting(new ProductNameSorting());
        $criteria->limit(11);

        $request = new BatchProductNumberSearchRequest();
        $request->setCriteria('test-criteria-1', $criteria);
        $request->setProductNumbers('test-1', ['BATCH-A', 'BATCH-H', 'BATCH-J']);

        $result = $this->batchProductSearch->search($request, $context);

        $products = $result->get('test-criteria-1');
        static::assertCount(10, $products);
        $this->assertProductNumbersExists(
            $products,
            [
                'BATCH-A',
                'BATCH-B',
                'BATCH-C',
                'BATCH-D',
                'BATCH-E',
                'BATCH-F',
                'BATCH-G',
                'BATCH-H',
                'BATCH-I',
                'BATCH-J',
            ]
        );

        $products = $result->get('test-1');
        static::assertCount(3, $products);
        $this->assertProductNumbersExists($products, ['BATCH-A', 'BATCH-H', 'BATCH-J']);
    }

    /**
     * @param string[] $numbers
     */
    private function assertProductNumbersExists(array $result, array $numbers)
    {
        array_walk($numbers, function ($number) use ($result) {
            static::assertArrayHasKey($number, $result, sprintf('Expected "%s" to be in [%s]', $number, implode(', ', array_keys($result))));
            static::assertSame($number, $result[$number]->getNumber());
        });
    }
}
