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

namespace Shopware\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use sArticles;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ListProductService;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Tests\TestReflectionHelper;
use Shopware_Components_Config;

class sArticlesTest extends TestCase
{
    private const RANDOM_CATEGORY_ID = 100;
    private const RANDOM_PRODUCT_ID = 100;
    private const RANDOM_PRODUCT_ID_2 = 200;
    private const RANDOM_ORDER_NUMBER = 'existingOrderNumber';
    private const RANDOM_ORDER_NUMBER_2 = 'notExistingOrderNumber';
    private const RANDOM_PRODUCT_NAME = 'foobar';

    public function provideData()
    {
        return [
            ['foo', 'foo'],
            ['foo ', 'foo'],
            [' foo', 'foo'],
            [' foo ', 'foo'],
            ['   foo   ', 'foo'],
            ['foo<>bar', 'foo bar'],
            ['<h2>foo</h2>', 'foo'],
            ['<h2>foo</h2>bar', 'foo bar'],
            ['bar<h2>foo</h2>bar', 'bar foo bar'],
            ['ελληνικά ', 'ελληνικά'],
            ['foo"bar', 'foo"bar'],
            ['foo\'bar', 'foo\'bar'],
            ['foo&bar', 'foo&bar'],
            ['foo&amp;bar', 'foo&bar'],
            ['A \'quote\' is &lt;b&gt;bold&lt;/b&gt;', 'A \'quote\' is bold'],
            ['<style>body: 1px solid red;</style>', ''],
            ['<script>alert("foo");</script>', ''],
            ['foo<script>alert("foo");</script>bar', 'foobar'],
            ['foo<style>body: 1px solid red;</style>bar', 'foobar'],
        ];
    }

    /**
     * Test case method
     *
     * @dataProvider provideData
     *
     * @param string $input
     * @param string $expectedResult
     */
    public function testStrings($input, $expectedResult)
    {
        /** @var sArticles $sArticles */
        $sArticles = $this->createPartialMock(sArticles::class, []);

        static::assertSame($expectedResult, $sArticles->sOptimizeText($input));
    }

    public function testItHandlesNullResultsProperlyWhileBuildingTheNavigation(): void
    {
        $productNumberSearchResult = new ProductNumberSearchResult(
            [
                new BaseProduct(
                    self::RANDOM_PRODUCT_ID,
                    self::RANDOM_PRODUCT_ID,
                    self::RANDOM_ORDER_NUMBER_2
                ),
                new BaseProduct(
                    self::RANDOM_PRODUCT_ID_2,
                    self::RANDOM_PRODUCT_ID_2,
                    self::RANDOM_ORDER_NUMBER
                ),
            ],
            2,
            []
        );

        $listProductService = $this->getMockBuilder(ListProductService::class)->disableOriginalConstructor()->getMock();
        $listProductService->expects(static::once())->method('get')->willReturn(null);
        $shopContext = $this->getMockBuilder(ShopContext::class)->disableOriginalConstructor()->getMock();

        $sArticles = $this->createPartialMock(sArticles::class, []);
        $property = TestReflectionHelper::getProperty(sArticles::class, 'listProductService');
        $property->setValue($sArticles, $listProductService);

        $result = TestReflectionHelper::getMethod(sArticles::class, 'buildNavigation')->invokeArgs($sArticles, [
            $productNumberSearchResult,
            self::RANDOM_ORDER_NUMBER,
            self::RANDOM_CATEGORY_ID,
            $shopContext,
        ]);

        static::assertEquals(2, $result['currentListing']['position']);
        static::assertEquals(2, $result['currentListing']['totalCount']);
        static::assertArrayNotHasKey('previousProduct', $result);
    }

    public function testItBuildsTheNavigationProperlyWithPreviousResults(): void
    {
        $config = $this->getMockBuilder(Shopware_Components_Config::class)->disableOriginalConstructor()->getMock();
        $config->expects(static::once())->method('get')->with('sBASEFILE')->willReturn('foo');

        $productNumberSearchResult = new ProductNumberSearchResult(
            [
                new BaseProduct(
                    self::RANDOM_PRODUCT_ID,
                    self::RANDOM_PRODUCT_ID,
                    self::RANDOM_ORDER_NUMBER_2
                ),
                new BaseProduct(
                    self::RANDOM_PRODUCT_ID_2,
                    self::RANDOM_PRODUCT_ID_2,
                    self::RANDOM_ORDER_NUMBER
                ),
            ],
            2,
            []
        );

        $listProduct = new ListProduct(
            self::RANDOM_PRODUCT_ID,
            self::RANDOM_PRODUCT_ID,
            self::RANDOM_ORDER_NUMBER_2
        );
        $listProduct->setName(self::RANDOM_PRODUCT_NAME);

        $listProductService = $this->getMockBuilder(ListProductService::class)->disableOriginalConstructor()->getMock();
        $listProductService->expects(static::once())->method('get')->willReturn($listProduct);
        $shopContext = $this->getMockBuilder(ShopContext::class)->disableOriginalConstructor()->getMock();

        $sArticles = $this->createPartialMock(sArticles::class, []);
        $property = TestReflectionHelper::getProperty(sArticles::class, 'listProductService');
        $property->setValue($sArticles, $listProductService);

        $property = TestReflectionHelper::getProperty(sArticles::class, 'config');
        $property->setValue($sArticles, $config);

        $result = TestReflectionHelper::getMethod(sArticles::class, 'buildNavigation')->invokeArgs($sArticles, [
            $productNumberSearchResult,
            self::RANDOM_ORDER_NUMBER,
            self::RANDOM_CATEGORY_ID,
            $shopContext,
        ]);

        static::assertEquals(2, $result['currentListing']['position']);
        static::assertEquals(2, $result['currentListing']['totalCount']);
        static::assertEquals(self::RANDOM_PRODUCT_NAME, $result['previousProduct']['name']);
    }
}
