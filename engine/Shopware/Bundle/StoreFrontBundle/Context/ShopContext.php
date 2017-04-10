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

namespace Shopware\Bundle\StoreFrontBundle\Context;

use Shopware\Bundle\CartBundle\Domain\Delivery\ShippingLocation;
use Shopware\Bundle\StoreFrontBundle\Common\Extendable;
use Shopware\Bundle\StoreFrontBundle\Currency\Currency;
use Shopware\Bundle\StoreFrontBundle\Customer\Customer;
use Shopware\Bundle\StoreFrontBundle\CustomerGroup\CustomerGroup;
use Shopware\Bundle\StoreFrontBundle\PaymentMethod\PaymentMethod;
use Shopware\Bundle\StoreFrontBundle\ShippingMethod\ShippingMethod;
use Shopware\Bundle\StoreFrontBundle\Shop\Shop;
use Shopware\Bundle\StoreFrontBundle\Tax\Tax;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ShopContext extends Extendable implements ShopContextInterface
{
    /**
     * @var CustomerGroup
     */
    protected $currentCustomerGroup;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\CustomerGroup\CustomerGroup
     */
    protected $fallbackCustomerGroup;

    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @var Shop
     */
    protected $shop;

    /**
     * @var Tax[]
     */
    protected $taxRules;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\PriceGroup\PriceGroup[]
     */
    protected $priceGroups;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Customer\Customer|null
     */
    protected $customer;

    /**
     * @var PaymentMethod
     */
    protected $paymentMethod;

    /**
     * @var TranslationContext
     */
    protected $translationContext;

    /**
     * @var ShippingMethod
     */
    protected $shippingMethod;

    /**
     * @var ShippingLocation
     */
    protected $shippingLocation;

    /**
     * @param Shop                                                          $shop
     * @param Currency                                                      $currency
     * @param CustomerGroup                                                 $currentCustomerGroup
     * @param \Shopware\Bundle\StoreFrontBundle\CustomerGroup\CustomerGroup $fallbackCustomerGroup
     * @param \Shopware\Bundle\StoreFrontBundle\Tax\Tax[]                   $taxRules
     * @param \Shopware\Bundle\StoreFrontBundle\PriceGroup\PriceGroup[]     $priceGroups
     * @param PaymentMethod                                                 $paymentMethod
     * @param ShippingMethod                                                $shippingMethod
     * @param ShippingLocation                                              $shippingLocation
     * @param Customer                                                      $customer
     */
    public function __construct(
        Shop $shop,
        Currency $currency,
        CustomerGroup $currentCustomerGroup,
        CustomerGroup $fallbackCustomerGroup,
        array $taxRules,
        array $priceGroups,
        PaymentMethod $paymentMethod,
        ShippingMethod $shippingMethod,
        ShippingLocation $shippingLocation,
        ?Customer $customer
    ) {
        $this->currentCustomerGroup = $currentCustomerGroup;
        $this->fallbackCustomerGroup = $fallbackCustomerGroup;
        $this->currency = $currency;
        $this->shop = $shop;
        $this->taxRules = $taxRules;
        $this->priceGroups = $priceGroups;
        $this->customer = $customer;
        $this->paymentMethod = $paymentMethod;
        $this->shippingMethod = $shippingMethod;
        $this->shippingLocation = $shippingLocation;
        $this->translationContext = TranslationContext::createFromShop($this->shop);
    }

    public function getShop(): Shop
    {
        return $this->shop;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function getCurrentCustomerGroup(): CustomerGroup
    {
        return $this->currentCustomerGroup;
    }

    public function getFallbackCustomerGroup(): CustomerGroup
    {
        return $this->fallbackCustomerGroup;
    }

    public function getTaxRules(): array
    {
        return $this->taxRules;
    }

    public function getPriceGroups(): array
    {
        return $this->priceGroups;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxRule(int $taxId): Tax
    {
        $key = 'tax_' . $taxId;

        return $this->taxRules[$key];
    }

    public function getCustomer(): ? Customer
    {
        return $this->customer;
    }

    public function getPaymentMethod(): PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function getShippingMethod(): ShippingMethod
    {
        return $this->shippingMethod;
    }

    public function getTranslationContext(): TranslationContext
    {
        return $this->translationContext;
    }

    public function getShippingLocation(): ShippingLocation
    {
        return $this->shippingLocation;
    }
}
