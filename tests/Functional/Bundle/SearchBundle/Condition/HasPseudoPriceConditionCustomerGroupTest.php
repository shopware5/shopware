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

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Condition;

use Generator;
use Shopware\Bundle\SearchBundle\Condition\HasPseudoPriceCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class HasPseudoPriceConditionCustomerGroupTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    private const PRODUCT_NUMBER = 'A';

    private const CURRENT_CUSTOMER_GROUP = 'PHP';
    private const FALLBACK_CUSTOMER_GROUP = 'EK';

    /**
     * @dataProvider customerGroupPseudoPrices
     *
     * @param array<self::PRODUCT_NUMBER> $expectedNumber
     */
    public function testPseudoPriceOnlyAvailableForSpecificCustomerGroup(
        bool $hasCurrentCustomerGroupPseudoPrice,
        bool $hasFallbackCustomerGroupPseudoPrice,
        array $expectedNumber
    ): void {
        $context = $this->getContext();
        $fallbackCustomerGroup = $this->converter->convertCustomerGroup($this->helper->createCustomerGroup(['key' => self::FALLBACK_CUSTOMER_GROUP]));
        $context->setFallbackCustomerGroup($fallbackCustomerGroup);
        $this->search(
            [
                self::PRODUCT_NUMBER => compact('hasCurrentCustomerGroupPseudoPrice', 'hasFallbackCustomerGroupPseudoPrice'),
            ],
            $expectedNumber,
            null,
            [new HasPseudoPriceCondition()],
            [],
            [],
            $context
        );
    }

    public function customerGroupPseudoPrices(): Generator
    {
        yield 'No customer group has pseudo price' => [
            false,
            false,
            [],
        ];
        yield 'Only current customer group has pseudo price' => [
            true,
            false,
            [self::PRODUCT_NUMBER],
        ];
        yield 'Only fallback customer group has pseudo price' => [
            false,
            true,
            [],
        ];
        yield 'Current and fallback customer groups have pseudo price' => [
            true,
            true,
            [self::PRODUCT_NUMBER],
        ];
    }

    protected function getProduct(
        string $number,
        ShopContext $context,
        Category $category = null,
        $additionally = []
    ): array {
        static::assertIsArray($additionally);

        $product = parent::getProduct($number, $context, $category, $additionally);

        $currentCustomerGroupPrice = [
            'from' => 1,
            'to' => 'beliebig',
            'price' => 7,
            'customerGroupKey' => self::CURRENT_CUSTOMER_GROUP,
            'pseudoPrice' => $additionally['hasCurrentCustomerGroupPseudoPrice'] ? 10 : 0,
        ];
        $fallbackCustomerGroupPrice = [
            'from' => 1,
            'to' => 'beliebig',
            'price' => 8,
            'customerGroupKey' => self::FALLBACK_CUSTOMER_GROUP,
            'pseudoPrice' => $additionally['hasFallbackCustomerGroupPseudoPrice'] ? 10 : 0,
        ];
        $product['mainDetail']['prices'] = [
            $currentCustomerGroupPrice,
            $fallbackCustomerGroupPrice,
        ];

        return $product;
    }
}
