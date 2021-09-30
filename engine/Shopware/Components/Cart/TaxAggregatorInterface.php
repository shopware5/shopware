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

namespace Shopware\Components\Cart;

use Shopware\Components\Cart\Struct\Price;

interface TaxAggregatorInterface
{
    /**
     * @param array{content?: array} $cart
     *
     * @return array<int|numeric-string, float>|null Sum of taxes for all cart items contained in $cart
     */
    public function positionsTaxSum(array $cart, float $maximumTaxRate): ?array;

    /**
     * @param array{sShippingcostsTax?: float, sShippingcostsTaxProportional?: array<Price>, sShippingcostsNet: float, sShippingcostsWithTax: float} $cart
     *
     * @return array<int|numeric-string, float>|null Sum of taxes for all shipping cost positions contained in $cart
     */
    public function shippingCostsTaxSum(array $cart): ?array;

    /**
     * Returns tax rates for all cart positions.
     *
     * @param array{content?: non-empty-array, sShippingcostsTax?: float, sShippingcostsTaxProportional?: array<Price>, sShippingcostsNet: float, sShippingcostsWithTax: float} $cart
     *
     * @return array<int|numeric-string, float> Sum of taxes for all positions contained in $basket
     */
    public function taxSum(array $cart, float $maximumTaxRate): array;
}
