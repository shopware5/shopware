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

namespace Shopware\Bundle\CartBundle\Domain\Cart;

use Shopware\Bundle\CartBundle\Domain\CloneTrait;
use Shopware\Bundle\CartBundle\Domain\Customer\Address;
use Shopware\Bundle\CartBundle\Domain\Customer\Customer;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryMethod;
use Shopware\Bundle\CartBundle\Domain\JsonSerializableTrait;
use Shopware\Bundle\CartBundle\Domain\Payment\PaymentMethod;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\Country;
use Shopware\Bundle\StoreFrontBundle\Struct\Country\Area;
use Shopware\Bundle\StoreFrontBundle\Struct\Country\State;
use Shopware\Bundle\StoreFrontBundle\Struct\Currency;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Tax;
use Shopware\Bundle\StoreFrontBundle\Struct\TranslationContext;

class CartContext implements CartContextInterface, \JsonSerializable
{
    use JsonSerializableTrait, CloneTrait;

    /**
     * @var Customer|null
     */
    protected $customer;

    /**
     * @var Address|null
     */
    protected $shippingAddress;

    /**
     * @var Address|null
     */
    protected $billingAddress;

    /**
     * @var PaymentMethod
     */
    protected $paymentMethod;

    /**
     * @var DeliveryMethod
     */
    protected $deliveryMethod;

    /**
     * @var ShopContextInterface
     */
    protected $shopContext;

    /**
     * @param ShopContextInterface $shopContext
     * @param PaymentMethod        $paymentMethod
     * @param DeliveryMethod       $deliveryMethod
     * @param Customer|null        $customer
     * @param Address|null         $billingAddress
     * @param Address|null         $shippingAddress
     */
    public function __construct(
        ShopContextInterface $shopContext,
        PaymentMethod $paymentMethod,
        DeliveryMethod $deliveryMethod,
        ?Customer $customer,
        ?Address $billingAddress,
        ?Address $shippingAddress
    ) {
        $this->customer = $customer;
        $this->shippingAddress = $shippingAddress;
        $this->billingAddress = $billingAddress;
        $this->paymentMethod = $paymentMethod;
        $this->deliveryMethod = $deliveryMethod;
        $this->shopContext = $shopContext;
    }

    public function getCustomer(): ? Customer
    {
        return $this->customer;
    }

    public function getShippingAddress(): ? Address
    {
        return $this->shippingAddress;
    }

    public function getBillingAddress(): ? Address
    {
        return $this->billingAddress;
    }

    public function getPaymentMethod(): PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function getDeliveryMethod(): DeliveryMethod
    {
        return $this->deliveryMethod;
    }

    public function addAttribute(string $name, Attribute $attribute): void
    {
        $this->shopContext->addAttribute($name, $attribute);
    }

    public function addAttributes(array $attributes): void
    {
        $this->shopContext->addAttributes($attributes);
    }

    public function getAttribute(string $name): Attribute
    {
        return $this->shopContext->getAttribute($name);
    }

    public function hasAttribute(string $name): bool
    {
        return $this->shopContext->hasAttribute($name);
    }

    public function getAttributes(): array
    {
        return $this->shopContext->getAttributes();
    }

    public function getShop(): Shop
    {
        return $this->shopContext->getShop();
    }

    public function getCurrency(): Currency
    {
        return $this->shopContext->getCurrency();
    }

    public function getCurrentCustomerGroup(): Group
    {
        return $this->shopContext->getCurrentCustomerGroup();
    }

    public function getFallbackCustomerGroup(): Group
    {
        return $this->shopContext->getFallbackCustomerGroup();
    }

    public function getBaseUrl(): string
    {
        return $this->shopContext->getBaseUrl();
    }

    public function getTaxRules(): array
    {
        return $this->shopContext->getTaxRules();
    }

    public function getTaxRule(int $taxId): Tax
    {
        return $this->shopContext->getTaxRule($taxId);
    }

    public function getPriceGroups(): array
    {
        return $this->shopContext->getPriceGroups();
    }

    public function getArea(): ? Area
    {
        return $this->shopContext->getArea();
    }

    public function getCountry(): ? Country
    {
        return $this->shopContext->getCountry();
    }

    public function getState(): ? State
    {
        return $this->shopContext->getState();
    }

    public function getTranslationContext(): TranslationContext
    {
        return $this->shopContext->getTranslationContext();
    }
}
