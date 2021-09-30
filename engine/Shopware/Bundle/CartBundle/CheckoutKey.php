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

namespace Shopware\Bundle\CartBundle;

final class CheckoutKey
{
    public const CURRENCY_ID = 'sCurrencyId';
    public const CURRENCY_NAME = 'sCurrencyName';
    public const CURRENCY_FACTOR = 'sCurrencyFactor';

    public const AMOUNT = 'sAmount';
    public const AMOUNT_WITH_TAX = 'sAmountWithTax';
    public const AMOUNT_TAX = 'sAmountTax';

    public const SHIPPING_COSTS = 'sShippingcosts';
    public const SHIPPING_COSTS_NET = 'sShippingcostsNet';
    public const SHIPPING_COSTS_DIFFERENCE = 'sShippingcostsDifference';
    public const SHIPPING_COSTS_WITH_TAX = 'sShippingcostsWithTax';
    public const SHIPPING_COSTS_TAX = 'sShippingcostsTax';
    public const SHIPPING_COSTS_TAX_PROPORTIONAL = 'sShippingcostsTaxProportional';

    public const BASKET_PROPORTIONAL = 'sBasketProportional';
    public const TAX_RATES = 'sTaxRates';

    private function __construct()
    {
    }
}
