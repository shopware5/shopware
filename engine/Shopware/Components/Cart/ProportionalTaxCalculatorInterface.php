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

namespace Shopware\Components\Cart;

use Shopware\Components\Cart\Struct\Price;

interface ProportionalTaxCalculatorInterface
{
    /**
     * Used to calculate proportional taxes of a dynamic calculated price.
     *
     * @param float   $discount   - Price which calculated percentage by the provided prices array. Example: -10€ voucher
     * @param Price[] $prices     - Prices array which used for the "discount" calculation
     * @param bool    $isNetPrice - Net price state of customergroup
     *
     * @return Price[]
     */
    public function calculate($discount, $prices, $isNetPrice);

    /**
     * @param float   $percentage
     * @param Price[] $prices
     * @param bool    $isNetPrice
     *
     * @return Price[]
     */
    public function recalculatePercentageDiscount($percentage, array $prices, $isNetPrice);

    /**
     * @param Price[] $prices
     *
     * @return bool
     */
    public function hasDifferentTaxes(array $prices);
}
