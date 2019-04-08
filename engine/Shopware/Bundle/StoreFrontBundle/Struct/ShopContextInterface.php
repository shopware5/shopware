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

namespace Shopware\Bundle\StoreFrontBundle\Struct;

use Shopware\Bundle\StoreFrontBundle\Struct\Country\Area;
use Shopware\Bundle\StoreFrontBundle\Struct\Country\State;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceGroup;

interface ShopContextInterface
{
    /**
     * Contains the current shop object of the store front.
     * The shop is used to build links or to select the
     * resource translations.
     *
     * @return Shop
     */
    public function getShop();

    /**
     * Contains the currency of the store front.
     * This struct is required for the price calculation.
     *
     * For example, the shop prices are defined in Euro,
     * the current store front displays Dollars.
     * The currency is required to calculate the Dollar
     * value of 100,- Euro.
     *
     * @return Currency
     */
    public function getCurrency();

    /**
     * Contains the current customer group for the store front.
     * If the customer isn't logged in, the current customer group
     * is equal to the fallback customer group of the shop.
     *
     * @return Group
     */
    public function getCurrentCustomerGroup();

    /**
     * Contains the fallback customer group for the current shop.
     * This customer group is required for price selections.
     * If the customer group of the logged in customer has no
     * own defined product prices, the prices of the fallback customer
     * group are displayed.
     *
     * @return Group
     */
    public function getFallbackCustomerGroup();

    /**
     * @return string
     */
    public function getBaseUrl();

    /**
     * Returns all tax rules
     *
     * @return Tax[]
     */
    public function getTaxRules();

    /**
     * Returns the active tax rule for the provided tax id.
     *
     * @param int $taxId
     *
     * @return Tax|null
     */
    public function getTaxRule($taxId);

    /**
     * Returns the active price groups
     *
     * @return PriceGroup[]
     */
    public function getPriceGroups();

    /**
     * @return Area|null
     */
    public function getArea();

    /**
     * @return Country|null
     */
    public function getCountry();

    /**
     * @return State|null
     */
    public function getState();

    /**
     * @return int[]
     */
    public function getActiveCustomerStreamIds();
}
