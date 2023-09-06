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

namespace Shopware\Tests\Functional\Bundle\SearchBundle;

use Doctrine\DBAL\Connection;
use OutOfBoundsException;
use Shopware\Bundle\SearchBundle\BatchProductNumberSearch;
use Shopware\Bundle\SearchBundle\BatchProductNumberSearchRequest;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Condition\PriceCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Sorting\ProductNameSorting;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class BatchProductNumberSearchTest extends TestCase
{
    /**
     * @var BatchProductNumberSearch
     */
    private $batchSearch;

    /**
     * @var Connection
     */
    private $connection;

    protected function setUp(): void
    {
        $this->connection = Shopware()->Container()->get(Connection::class);
        $this->connection->beginTransaction();
        $this->batchSearch = Shopware()->Container()->get('shopware_search.batch_product_number_search');

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
    public function createProducts(array $products, ShopContext $context, Category $category): array
    {
        $articles = parent::createProducts($products, $context, $category);

        $this->helper->refreshSearchIndexes($context->getShop());

        return $articles;
    }

    public function testSearchWithMatchingProducts(): void
    {
        $context = $this->getContext();
        $category = $this->helper->createCategory();
        $this->createProducts(['BATCH-1' => [], 'BATCH-2' => []], $context, $category);

        $request = new BatchProductNumberSearchRequest();
        $request->setProductNumbers('test-1', ['BATCH-1', 'BATCH-2']);

        $products = $this->batchSearch->search($request, $context)->get('test-1');

        static::assertCount(2, $products);
        $this->assertProductNumbersExists($products, ['BATCH-1', 'BATCH-2']);
    }

    public function testSearchIncludingMissingProducts(): void
    {
        $context = $this->getContext();
        $category = $this->helper->createCategory();
        $this->createProducts(['BATCH-1' => [], 'BATCH-2' => []], $context, $category);

        $request = new BatchProductNumberSearchRequest();
        $request->setProductNumbers('test-1', ['BATCH-1', 'BATCH-2', 'NOT_EXISTING']);
        $request->setProductNumbers('test-2', ['NOT_EXISTING']);

        $result = $this->batchSearch->search($request, $context);
        $products = $result->get('test-1');
        static::assertCount(2, $products);
        $this->assertProductNumbersExists($products, ['BATCH-1', 'BATCH-2']);

        static::assertSame([], $result->get('test-2'));
    }

    public function testSearchWithCriteria(): void
    {
        $context = $this->getContext();
        $category = $this->helper->createCategory();
        $this->createProducts(['BATCH-A' => [], 'BATCH-B' => [], 'BATCH-C' => []], $context, $category);

        $criteria = new Criteria();
        $criteria->addCondition(new CategoryCondition([$category->getId()]));
        $criteria->limit(3);

        $request = new BatchProductNumberSearchRequest();
        $request->setCriteria('test-criteria-1', $criteria);

        $products = $this->batchSearch->search($request, $context)->get('test-criteria-1');
        static::assertCount(3, $products);
        $this->assertProductNumbersExists($products, ['BATCH-A', 'BATCH-B', 'BATCH-C']);
    }

    public function testSearchWithMultipleCriteria(): void
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
            ],
            $context,
            $category
        );

        $criteria = new Criteria();
        $criteria->addCondition(new CategoryCondition([$category->getId()]));
        $criteria->addSorting(new ProductNameSorting());
        $criteria->limit(3);

        $criteria2 = new Criteria();
        $criteria2->addCondition(new CategoryCondition([$category->getId()]));
        $criteria2->addSorting(new ProductNameSorting());
        $criteria2->limit(4);

        $request = new BatchProductNumberSearchRequest();
        $request->setCriteria('test-criteria-1', $criteria);
        $request->setCriteria('test-criteria-2', $criteria2);

        $result = $this->batchSearch->search($request, $context);

        $products = $result->get('test-criteria-1');
        static::assertCount(3, $products);
        $this->assertProductNumbersExists($products, ['BATCH-A', 'BATCH-B', 'BATCH-C']);

        $products = $result->get('test-criteria-2');
        static::assertCount(4, $products);
        $this->assertProductNumbersExists($products, ['BATCH-D', 'BATCH-E', 'BATCH-F', 'BATCH-G']);
    }

    public function testSearchWithMultipleCriteriaAndProductNumbers(): void
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
        $criteria->limit(3);

        $criteria2 = new Criteria();
        $criteria2->addCondition(new CategoryCondition([$category->getId()]));
        $criteria2->addSorting(new ProductNameSorting());
        $criteria2->limit(4);

        $request = new BatchProductNumberSearchRequest();
        $request->setCriteria('test-criteria-1', $criteria);
        $request->setCriteria('test-criteria-2', $criteria2);
        $request->setProductNumbers('test-1', ['BATCH-A', 'BATCH-H', 'BATCH-J']);

        $result = $this->batchSearch->search($request, $context);

        $products = $result->get('test-criteria-1');
        static::assertCount(3, $products);
        $this->assertProductNumbersExists($products, ['BATCH-A', 'BATCH-B', 'BATCH-C']);

        $products = $result->get('test-criteria-2');
        static::assertCount(4, $products);
        $this->assertProductNumbersExists($products, ['BATCH-D', 'BATCH-E', 'BATCH-F', 'BATCH-G']);

        $products = $result->get('test-1');
        static::assertCount(3, $products);
        $this->assertProductNumbersExists($products, ['BATCH-A', 'BATCH-H', 'BATCH-J']);
    }

    public function testNotExistingKeyShouldThrowException(): void
    {
        $context = $this->getContext();
        $request = new BatchProductNumberSearchRequest();

        $result = $this->batchSearch->search($request, $context);

        $this->expectException(OutOfBoundsException::class);
        $result->get('not_existing');
    }

    public function testNonMatchingProductNumbersShouldReturnEmptyArray(): void
    {
        $context = $this->getContext();
        $request = new BatchProductNumberSearchRequest();
        $request->setProductNumbers('test-1', ['NOT_EXISTING']);

        $result = $this->batchSearch->search($request, $context);

        static::assertSame([], $result->get('test-1'));
    }

    public function testNonMatchingConditionShouldReturnEmptyArray(): void
    {
        $criteria = new Criteria();
        $criteria->addCondition(new PriceCondition(99999999));
        $criteria->limit(1);

        $context = $this->getContext();
        $request = new BatchProductNumberSearchRequest();
        $request->setCriteria('test-1', $criteria);

        $result = $this->batchSearch->search($request, $context);

        static::assertSame([], $result->get('test-1'));
    }

    /**
     * @param BaseProduct[] $result
     * @param string[]      $numbers
     */
    private function assertProductNumbersExists(array $result, array $numbers): void
    {
        array_walk($numbers, function ($number) use ($result) {
            static::assertArrayHasKey($number, $result, sprintf('Expected "%s" to be in [%s]', $number, implode(', ', array_keys($result))));
            static::assertSame($number, $result[$number]->getNumber());
        });
    }
}
