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

use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryMethod;
use Shopware\Bundle\CartBundle\Domain\Delivery\ShippingLocation;
use Shopware\Bundle\CartBundle\Domain\Payment\PaymentMethod;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceGroup;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ShopContext extends Extendable implements ShopContextInterface, \JsonSerializable
{
    /**
     * @var Group
     */
    protected $currentCustomerGroup;

    /**
     * @var Group
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
     * @var PriceGroup[]
     */
    protected $priceGroups;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Struct\Customer|null
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
     * @var DeliveryMethod
     */
    protected $deliveryMethod;

    /**
     * @var ShippingLocation
     */
    protected $shippingLocation;

    /**
     * @param Shop             $shop
     * @param Currency         $currency
     * @param Group            $currentCustomerGroup
     * @param Group            $fallbackCustomerGroup
     * @param Tax[]            $taxRules
     * @param PriceGroup[]     $priceGroups
     * @param PaymentMethod    $paymentMethod
     * @param DeliveryMethod   $deliveryMethod
     * @param ShippingLocation $shippingLocation
     * @param Customer         $customer
     */
    public function __construct(
        Shop $shop,
        Currency $currency,
        Group $currentCustomerGroup,
        Group $fallbackCustomerGroup,
        array $taxRules,
        array $priceGroups,
        PaymentMethod $paymentMethod,
        DeliveryMethod $deliveryMethod,
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
        $this->deliveryMethod = $deliveryMethod;
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

    public function getCurrentCustomerGroup(): Group
    {
        return $this->currentCustomerGroup;
    }

    public function getFallbackCustomerGroup(): Group
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

    public function getDeliveryMethod(): DeliveryMethod
    {
        return $this->deliveryMethod;
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
