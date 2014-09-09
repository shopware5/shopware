<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceGroup;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Struct
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductContext
    extends Extendable
    implements ProductContextInterface, \JsonSerializable
{
    /**
     * @var Tax[]
     */
    protected $taxRules;

    /**
     * @var PriceGroup[]
     */
    protected $priceGroups;

    /**
     * Contains the current customer group for the store front.
     * If the customer isn't logged in, the current customer group
     * is equal to the fallback customer group of the shop.
     *
     * @var Group
     */
    protected $currentCustomerGroup;

    /**
     * Contains the fallback customer group for the current shop.
     * This customer group is required for price selections.
     * If the customer group of the logged in customer has no
     * own defined product prices, the prices of the fallback customer
     * group are displayed.
     *
     * @var Group
     */
    protected $fallbackCustomerGroup;

    /**
     * Contains the currency of the store front.
     * This struct is required for the price calculation.
     *
     * For example, the shop prices are defined in Euro,
     * the current store front displays Dollars.
     * The currency is required to calculate the Dollar
     * value of 100,- Euro.
     *
     * @var Currency
     */
    protected $currency;

    /**
     * Contains the current shop object of the store front.
     * The shop is used to build links or to select the
     * resource translations.
     *
     * @var Shop
     */
    protected $shop;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @param string $baseUrl
     * @param Shop $shop
     * @param Currency $currency
     * @param Group $currentCustomerGroup
     * @param Group $fallbackCustomerGroup
     * @param Tax[] $taxRules
     * @param PriceGroup[] $priceGroups
     */
    public function __construct(
        $baseUrl,
        Shop $shop,
        Currency $currency,
        Group $currentCustomerGroup,
        Group $fallbackCustomerGroup,
        $taxRules,
        $priceGroups
    ) {
        $this->baseUrl = $baseUrl;
        $this->shop = $shop;
        $this->currency = $currency;
        $this->currentCustomerGroup = $currentCustomerGroup;
        $this->fallbackCustomerGroup = $fallbackCustomerGroup;
        $this->taxRules = $taxRules;
        $this->priceGroups = $priceGroups;
    }

    /**
     * @param ShopContextInterface $shopContext
     * @param Tax[] $taxRules
     * @param PriceGroup[] $priceGroups
     * @return ProductContext
     */
    public static function createFromContexts(
        ShopContextInterface $shopContext,
        $taxRules,
        $priceGroups
    ) {
        return new self(
            $shopContext->getBaseUrl(),
            $shopContext->getShop(),
            $shopContext->getCurrency(),
            $shopContext->getCurrentCustomerGroup(),
            $shopContext->getFallbackCustomerGroup(),
            $taxRules,
            $priceGroups
        );
    }

    /**
     * @return Tax[]
     */
    public function getTaxRules()
    {
        return $this->taxRules;
    }

    /**
     * @param $taxId
     * @return Tax
     */
    public function getTaxRule($taxId)
    {
        $key = 'tax_' . $taxId;

        return $this->taxRules[$key];
    }

    /**
     * @return PriceGroup[]
     */
    public function getPriceGroups()
    {
        return $this->priceGroups;
    }

    /**
     * Returns the current active shop of the context.
     * The shop id is used as translation identifier.
     *
     * @return Shop
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return Customer\Group
     */
    public function getCurrentCustomerGroup()
    {
        return $this->currentCustomerGroup;
    }

    /**
     * @return Customer\Group
     */
    public function getFallbackCustomerGroup()
    {
        return $this->fallbackCustomerGroup;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}