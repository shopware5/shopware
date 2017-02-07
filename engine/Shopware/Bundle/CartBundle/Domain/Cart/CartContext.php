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


namespace Shopware\Bundle\CartBundle\Domain\Cart;

use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryService;
use Shopware\Bundle\CartBundle\Domain\Customer\Address;
use Shopware\Bundle\CartBundle\Domain\Customer\Customer;
use Shopware\Bundle\CartBundle\Domain\Payment\PaymentService;
use Shopware\Bundle\CartBundle\Domain\CloneTrait;
use Shopware\Bundle\CartBundle\Domain\JsonSerializableTrait;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

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
     * @var PaymentService
     */
    protected $paymentService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * @var ShopContextInterface
     */
    protected $shopContext;

    /**
     * @param ShopContextInterface $shopContext
     * @param PaymentService $paymentService
     * @param DeliveryService $deliveryService
     * @param Customer|null $customer
     * @param Address|null $billingAddress
     * @param Address|null $shippingAddress
     */
    public function __construct(
        ShopContextInterface $shopContext,
        PaymentService $paymentService,
        DeliveryService $deliveryService,
        Customer $customer = null,
        Address $billingAddress = null,
        Address $shippingAddress = null
    ) {
        $this->customer = $customer;
        $this->shippingAddress = $shippingAddress;
        $this->billingAddress = $billingAddress;
        $this->paymentService = $paymentService;
        $this->deliveryService = $deliveryService;
        $this->shopContext = $shopContext;
    }

    /**
     * @return Customer|null
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return Address|null
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @return Address|null
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @return PaymentService
     */
    public function getPaymentService()
    {
        return $this->paymentService;
    }

    /**
     * @return DeliveryService
     */
    public function getDeliveryService()
    {
        return $this->deliveryService;
    }

    /**
     * @inheritdoc
     */
    public function getShop()
    {
        return $this->shopContext->getShop();
    }

    /**
     * @inheritdoc
     */
    public function getCurrency()
    {
        return $this->shopContext->getCurrency();
    }

    /**
     * @inheritdoc
     */
    public function getCurrentCustomerGroup()
    {
        return $this->shopContext->getCurrentCustomerGroup();
    }

    /**
     * @inheritdoc
     */
    public function getFallbackCustomerGroup()
    {
        return $this->shopContext->getFallbackCustomerGroup();
    }

    /**
     * @inheritdoc
     */
    public function getBaseUrl()
    {
        return $this->shopContext->getBaseUrl();
    }

    /**
     * @inheritdoc
     */
    public function getTaxRules()
    {
        return $this->shopContext->getTaxRules();
    }

    /**
     * @inheritdoc
     */
    public function getTaxRule($taxId)
    {
        return $this->shopContext->getTaxRule($taxId);
    }

    /**
     * @inheritdoc
     */
    public function getPriceGroups()
    {
        return $this->shopContext->getPriceGroups();
    }

    /**
     * @inheritdoc
     */
    public function getArea()
    {
        return $this->shopContext->getArea();
    }

    /**
     * @inheritdoc
     */
    public function getCountry()
    {
        return $this->shopContext->getCountry();
    }

    /**
     * @inheritdoc
     */
    public function getState()
    {
        return $this->shopContext->getState();
    }

    /**
     * @inheritdoc
     */
    public function addAttribute($name, Attribute $attribute)
    {
        $this->shopContext->addAttribute($name, $attribute);
    }

    /**
     * @inheritdoc
     */
    public function addAttributes(array $attributes)
    {
        $this->shopContext->addAttributes($attributes);
    }

    /**
     * @inheritdoc
     */
    public function getAttribute($name)
    {
        return $this->shopContext->getAttribute($name);
    }

    /**
     * @inheritdoc
     */
    public function hasAttribute($name)
    {
        return $this->shopContext->hasAttribute($name);
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return $this->shopContext->getAttributes();
    }
}
