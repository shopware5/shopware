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
use Shopware\Bundle\SearchBundle\BatchProductNumberSearchResult;
use Shopware\Bundle\SearchBundle\BatchProductSearch;
use Shopware\Bundle\SearchBundle\BatchProductSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;

class BatchProductSearchTest extends TestCase
{
    private BatchProductSearch $batchSearch;

    protected function setUp(): void
    {
        $this->batchSearch = $this->createPartialMock(BatchProductSearch::class, []);
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

    public function testListProductMapping(): void
    {
        $searchResult = new BatchProductNumberSearchResult([
            'unit-test-1' => [
                2 => new BaseProduct(2, 2, '2'),
                3 => new BaseProduct(3, 3, '3'),
                4 => new BaseProduct(4, 4, '4'),
            ],
        ]);

        $listProducts = [
            1 => new ListProduct(1, 1, '1'),
            2 => new ListProduct(2, 2, '2'),
            3 => new ListProduct(3, 3, '3'),
            4 => new ListProduct(4, 4, '4'),
            5 => new ListProduct(5, 5, '5'),
        ];

        $result = $this->invokeMethod($this->batchSearch, 'mapListProducts', [$searchResult, $listProducts]);

        $expectedResult = new BatchProductSearchResult([
            'unit-test-1' => [
                2 => new ListProduct(2, 2, '2'),
                3 => new ListProduct(3, 3, '3'),
                4 => new ListProduct(4, 4, '4'),
            ],
        ]);

        static::assertEquals($expectedResult, $result);
    }
}
