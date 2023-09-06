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

namespace Shopware\Tests\Unit\Bundle\SearchBundle;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shopware\Bundle\SearchBundle\BatchProductNumberSearch;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Condition\CloseoutCondition;
use Shopware\Bundle\SearchBundle\Condition\IsNewCondition;
use Shopware\Bundle\SearchBundle\Condition\PriceCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Sorting\PriceSorting;
use Shopware\Bundle\SearchBundle\Sorting\ReleaseDateSorting;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;

class BatchProductNumberSearchTest extends TestCase
{
    /**
     * @var BatchProductNumberSearch
     */
    private $batchSearch;

    protected function setUp(): void
    {
        $this->batchSearch = $this->createPartialMock(BatchProductNumberSearch::class, []);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object $object     instantiated object that we will run method on
     * @param string $methodName Method name to call
     * @param array  $parameters array of parameters to pass into method
     *
     * @return mixed method return
     */
    public function invokeMethod($object, $methodName, array $parameters = [])
    {
        $method = (new ReflectionClass(\get_class($object)))->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function testOptimizeCriteriaListWithEmptyCriteria(): void
    {
        $criteriaList = $this->invokeMethod($this->batchSearch, 'getOptimizedCriteriaList', [[]]);

        static::assertSame([], $criteriaList);
    }

    public function testOptimizeCriteriaListWithSingleCriteria(): void
    {
        $criteria = new Criteria();
        $criteria->addBaseCondition(new CategoryCondition([3]));
        $criteria->limit(5);

        $testCriterias = [
            'unit-test-1' => $criteria,
        ];

        $criteriaList = $this->invokeMethod($this->batchSearch, 'getOptimizedCriteriaList', [$testCriterias]);

        $expectedCriteria1 = new Criteria();
        $expectedCriteria1->addCondition(new CategoryCondition([3]));
        $expectedCriteria1->limit(5);

        $expectedOptimizedCriteriaList = [
            [
                'criteria' => $expectedCriteria1,
                'requests' => [
                    ['criteria' => $criteria, 'key' => 'unit-test-1'],
                ],
            ],
        ];

        static::assertEquals($expectedOptimizedCriteriaList, $criteriaList);
    }

    public function testOptimizeCriteriaListWithMultipleEqualCriteriaButMixedBaseConditions(): void
    {
        $criteria = new Criteria();
        $criteria->addBaseCondition(new CategoryCondition([3]));
        $criteria->limit(5);

        $criteria2 = new Criteria();
        $criteria2->addCondition(new CategoryCondition([3]));
        $criteria2->limit(5);

        $testCriterias = [
            'unit-test-1' => $criteria,
            'unit-test-2' => $criteria2,
        ];

        $criteriaList = $this->invokeMethod($this->batchSearch, 'getOptimizedCriteriaList', [$testCriterias]);

        $expectedCriteria1 = new Criteria();
        $expectedCriteria1->addCondition(new CategoryCondition([3]));
        $expectedCriteria1->limit(10);

        $expectedOptimizedCriteriaList = [
            [
                'criteria' => $expectedCriteria1,
                'requests' => [
                    ['criteria' => $criteria, 'key' => 'unit-test-1'],
                    ['criteria' => $criteria2, 'key' => 'unit-test-2'],
                ],
            ],
        ];

        static::assertEquals($expectedOptimizedCriteriaList, $criteriaList);
    }

    public function testOptimizeCriteriaListWithMultipleDifferentCriteria(): void
    {
        $criteria = new Criteria();
        $criteria->addBaseCondition(new CategoryCondition([3]));
        $criteria->limit(5);

        $criteria2 = clone $criteria;
        $criteria2->limit(12);

        $criteria3 = new Criteria();
        $criteria3->addBaseCondition(new CategoryCondition([7]));
        $criteria3->limit(9);

        $testCriterias = [
            'unit-test-1' => $criteria,
            'unit-test-2' => $criteria2,
            'unit-test-3-different' => $criteria3,
        ];

        $criteriaList = $this->invokeMethod($this->batchSearch, 'getOptimizedCriteriaList', [$testCriterias]);

        $expectedCriteria1 = new Criteria();
        $expectedCriteria1->addCondition(new CategoryCondition([3]));
        $expectedCriteria1->limit(17);

        $expectedCriteria2 = new Criteria();
        $expectedCriteria2->addCondition(new CategoryCondition([7]));
        $expectedCriteria2->limit(9);

        $expectedOptimizedCriteriaList = [
            [
                'criteria' => $expectedCriteria1,
                'requests' => [
                    ['criteria' => $criteria, 'key' => 'unit-test-1'],
                    ['criteria' => $criteria2, 'key' => 'unit-test-2'],
                ],
            ],
            [
                'criteria' => $expectedCriteria2,
                'requests' => [
                    ['criteria' => $criteria3, 'key' => 'unit-test-3-different'],
                ],
            ],
        ];

        static::assertEquals($expectedOptimizedCriteriaList, $criteriaList);
    }

    public function testOptimizeCriteriaListWithDifferentCriteriaSorting(): void
    {
        $criteria = new Criteria();
        $criteria->addCondition(new CategoryCondition([3]));
        $criteria->addCondition(new PriceCondition(5, 10));
        $criteria->addBaseCondition(new CloseoutCondition());
        $criteria->addBaseCondition(new IsNewCondition());
        $criteria->addSorting(new ReleaseDateSorting());
        $criteria->addSorting(new PriceSorting());
        $criteria->limit(5);

        $criteria2 = new Criteria();
        $criteria2->addCondition(new PriceCondition(5, 10));
        $criteria2->addCondition(new CategoryCondition([3]));
        $criteria2->addBaseCondition(new IsNewCondition());
        $criteria2->addBaseCondition(new CloseoutCondition());
        $criteria2->addSorting(new PriceSorting());
        $criteria2->addSorting(new ReleaseDateSorting());
        $criteria2->limit(9);

        $testCriterias = [
            'unit-test-1' => $criteria,
            'unit-test-2' => $criteria2,
        ];

        $criteriaList = $this->invokeMethod($this->batchSearch, 'getOptimizedCriteriaList', [$testCriterias]);

        $expectedCriteria1 = new Criteria();
        $expectedCriteria1->addCondition(new CategoryCondition([3]));
        $expectedCriteria1->addCondition(new PriceCondition(5, 10));
        $expectedCriteria1->addCondition(new CloseoutCondition());
        $expectedCriteria1->addCondition(new IsNewCondition());
        $expectedCriteria1->addSorting(new PriceSorting());
        $expectedCriteria1->addSorting(new ReleaseDateSorting());
        $expectedCriteria1->limit(14);

        $expectedOptimizedCriteriaList = [
            [
                'criteria' => $expectedCriteria1,
                'requests' => [
                    ['criteria' => $criteria, 'key' => 'unit-test-1'],
                    ['criteria' => $criteria2, 'key' => 'unit-test-2'],
                ],
            ],
        ];

        static::assertEquals($expectedOptimizedCriteriaList, $criteriaList);
    }

    public function testOptimizeCriteriaListWithMultipleEqualCriteria(): void
    {
        $criteria = new Criteria();
        $criteria->addBaseCondition(new CategoryCondition([3]));
        $criteria->limit(5);

        $criteria2 = clone $criteria;
        $criteria2->limit(12);

        $criteria3 = clone $criteria;
        $criteria3->limit(7);

        $testCriterias = [
            'unit-test-1' => $criteria,
            'unit-test-2' => $criteria2,
            'unit-test-3' => $criteria3,
        ];

        $criteriaList = $this->invokeMethod($this->batchSearch, 'getOptimizedCriteriaList', [$testCriterias]);

        $expectedCriteria1 = new Criteria();
        $expectedCriteria1->addCondition(new CategoryCondition([3]));
        $expectedCriteria1->limit(24);

        $expectedOptimizedCriteriaList = [
            [
                'criteria' => $expectedCriteria1,
                'requests' => [
                    ['criteria' => $criteria, 'key' => 'unit-test-1'],
                    ['criteria' => $criteria2, 'key' => 'unit-test-2'],
                    ['criteria' => $criteria3, 'key' => 'unit-test-3'],
                ],
            ],
        ];

        static::assertEquals($expectedOptimizedCriteriaList, $criteriaList);
    }

    public function testOptimizeCriteriaListWithMultipleEqualAndDifferentCriteria(): void
    {
        $criteria = new Criteria();
        $criteria->addBaseCondition(new CategoryCondition([3]));
        $criteria->limit(5);

        $criteria2 = clone $criteria;
        $criteria2->limit(12);

        $criteria3 = new Criteria();
        $criteria3->addBaseCondition(new CategoryCondition([7]));
        $criteria3->limit(9);

        $criteria4 = clone $criteria3;

        $criteria5 = new Criteria();
        $criteria5->addBaseCondition(new CategoryCondition([8]));
        $criteria5->offset(1);
        $criteria5->limit(1);

        $testCriterias = [
            'unit-test-1' => $criteria,
            'unit-test-2' => $criteria2,
            'unit-test-3-different' => $criteria3,
            'unit-test-4-different' => $criteria4,
            'unit-test-5-different-different' => $criteria5,
        ];

        $criteriaList = $this->invokeMethod($this->batchSearch, 'getOptimizedCriteriaList', [$testCriterias]);

        $expectedCriteria1 = new Criteria();
        $expectedCriteria1->addCondition(new CategoryCondition([3]));
        $expectedCriteria1->limit(17);

        $expectedCriteria2 = new Criteria();
        $expectedCriteria2->addCondition(new CategoryCondition([7]));
        $expectedCriteria2->limit(18);

        $expectedCriteria3 = new Criteria();
        $expectedCriteria3->addCondition(new CategoryCondition([8]));
        $expectedCriteria3->limit(1);

        $expectedOptimizedCriteriaList = [
            [
                'criteria' => $expectedCriteria1,
                'requests' => [
                    ['criteria' => $criteria, 'key' => 'unit-test-1'],
                    ['criteria' => $criteria2, 'key' => 'unit-test-2'],
                ],
            ],
            [
                'criteria' => $expectedCriteria2,
                'requests' => [
                    ['criteria' => $criteria3, 'key' => 'unit-test-3-different'],
                    ['criteria' => $criteria4, 'key' => 'unit-test-4-different'],
                ],
            ],
            [
                'criteria' => $expectedCriteria3,
                'requests' => [
                    ['criteria' => $criteria5, 'key' => 'unit-test-5-different-different'],
                ],
            ],
        ];

        static::assertEquals($expectedOptimizedCriteriaList, $criteriaList);
    }

    public function testOptimizeCriteriaListWithEqualCriteriaButDifferentSorting(): void
    {
        $criteria = new Criteria();
        $criteria->addBaseCondition(new CategoryCondition([3]));
        $criteria->limit(5);

        $criteria2 = clone $criteria;
        $criteria2->limit(12);

        $criteria3 = clone $criteria;
        $criteria3->addSorting(new ReleaseDateSorting());
        $criteria3->limit(2);

        $testCriterias = [
            'unit-test-1' => $criteria,
            'unit-test-2' => $criteria2,
            'unit-test-3-different' => $criteria3,
        ];

        $criteriaList = $this->invokeMethod($this->batchSearch, 'getOptimizedCriteriaList', [$testCriterias]);

        $expectedCriteria1 = new Criteria();
        $expectedCriteria1->addCondition(new CategoryCondition([3]));
        $expectedCriteria1->limit(17);

        $expectedCriteria2 = new Criteria();
        $expectedCriteria2->addCondition(new CategoryCondition([3]));
        $expectedCriteria2->addSorting(new ReleaseDateSorting());
        $expectedCriteria2->limit(2);

        $expectedOptimizedCriteriaList = [
            [
                'criteria' => $expectedCriteria1,
                'requests' => [
                    ['criteria' => $criteria, 'key' => 'unit-test-1'],
                    ['criteria' => $criteria2, 'key' => 'unit-test-2'],
                ],
            ],
            [
                'criteria' => $expectedCriteria2,
                'requests' => [
                    ['criteria' => $criteria3, 'key' => 'unit-test-3-different'],
                ],
            ],
        ];

        static::assertEquals($expectedOptimizedCriteriaList, $criteriaList);
    }

    /**
     * @covers \Shopware\Bundle\SearchBundle\BatchProductNumberSearch::getBaseProductsRange
     */
    public function testGetBaseProductsRangeWithEmptyProducts(): void
    {
        $products = $this->invokeMethod($this->batchSearch, 'getBaseProductsRange', [0, [], 1]);
        static::assertSame([], $products);
    }

    /**
     * @covers \Shopware\Bundle\SearchBundle\BatchProductNumberSearch::getBaseProductsRange
     */
    public function testGetBaseProductsRangeWithMoreProductsThanRequested(): void
    {
        $products = [
            new BaseProduct(1, 1, '1'),
            new BaseProduct(2, 2, '2'),
            new BaseProduct(3, 3, '3'),
            new BaseProduct(4, 4, '4'),
        ];

        $result = $this->invokeMethod($this->batchSearch, 'getBaseProductsRange', [0, $products, 1]);
        $expectedProducts = [
            new BaseProduct(1, 1, '1'),
        ];

        static::assertEquals($expectedProducts, $result);
    }

    /**
     * @covers \Shopware\Bundle\SearchBundle\BatchProductNumberSearch::getBaseProductsRange
     */
    public function testMultipleCallGetBaseProductsRangeWithMoreProductsThanRequested(): void
    {
        $products = [
            new BaseProduct(1, 1, '1'),
            new BaseProduct(2, 2, '2'),
            new BaseProduct(3, 3, '3'),
            new BaseProduct(4, 4, '4'),
        ];

        // first call
        $result = $this->invokeMethod($this->batchSearch, 'getBaseProductsRange', [0, $products, 1]);
        $expectedProducts = [
            new BaseProduct(1, 1, '1'),
        ];

        static::assertEquals($expectedProducts, $result);

        // second call should not return the same product
        $result = $this->invokeMethod($this->batchSearch, 'getBaseProductsRange', [0, $products, 2]);
        $expectedProducts = [
            new BaseProduct(2, 2, '2'),
            new BaseProduct(3, 3, '3'),
        ];

        static::assertEquals($expectedProducts, $result);
    }

    /**
     * @covers \Shopware\Bundle\SearchBundle\BatchProductNumberSearch::getBaseProductsRange
     */
    public function testGetBaseProductsRangeWithLessProductsThanRequested(): void
    {
        $products = [
            new BaseProduct(1, 1, '1'),
            new BaseProduct(2, 2, '2'),
            new BaseProduct(3, 3, '3'),
            new BaseProduct(4, 4, '4'),
        ];

        $result = $this->invokeMethod($this->batchSearch, 'getBaseProductsRange', [0, $products, 6]);
        $expectedProducts = [
            new BaseProduct(1, 1, '1'),
            new BaseProduct(2, 2, '2'),
            new BaseProduct(3, 3, '3'),
            new BaseProduct(4, 4, '4'),
            new BaseProduct(1, 1, '1'),
            new BaseProduct(2, 2, '2'),
        ];

        static::assertEquals($expectedProducts, $result);
    }

    /**
     * @covers \Shopware\Bundle\SearchBundle\BatchProductNumberSearch::getBaseProductsRange
     */
    public function testGetBaseProductsRangeWithLessProductsThanRequestedAndDifferentHashes(): void
    {
        $products = [
            new BaseProduct(1, 1, '1'),
            new BaseProduct(2, 2, '2'),
            new BaseProduct(3, 3, '3'),
            new BaseProduct(4, 4, '4'),
        ];

        $result = $this->invokeMethod($this->batchSearch, 'getBaseProductsRange', [0, $products, 6]);
        $expectedProducts = [
            new BaseProduct(1, 1, '1'),
            new BaseProduct(2, 2, '2'),
            new BaseProduct(3, 3, '3'),
            new BaseProduct(4, 4, '4'),
            new BaseProduct(1, 1, '1'),
            new BaseProduct(2, 2, '2'),
        ];

        static::assertEquals($expectedProducts, $result);

        $result = $this->invokeMethod($this->batchSearch, 'getBaseProductsRange', [1, $products, 6]);

        static::assertEquals($expectedProducts, $result);
    }
}
