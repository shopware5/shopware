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

namespace Shopware\Bundle\CartBundle\Infrastructure\Cart;

use Shopware\Bundle\CartBundle\Domain\Cart\CartContext;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryService;
use Shopware\Bundle\CartBundle\Domain\Customer\Address;
use Shopware\Bundle\CartBundle\Infrastructure\Customer\AddressGateway;
use Shopware\Bundle\CartBundle\Domain\Customer\Customer;
use Shopware\Bundle\CartBundle\Infrastructure\Customer\CustomerService;
use Shopware\Bundle\CartBundle\Infrastructure\Delivery\DeliveryServiceGateway;
use Shopware\Bundle\CartBundle\Domain\Payment\PaymentService;
use Shopware\Bundle\CartBundle\Infrastructure\Payment\PaymentServiceGateway;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class CartContextService implements CartContextServiceInterface
{
    /**
     * @var ContextServiceInterface
     */
    private $shopContextService;

    /**
     * @var \Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * @var DeliveryServiceGateway
     */
    private $deliveryServiceGateway;

    /**
     * @var PaymentServiceGateway
     */
    private $paymentServiceGateway;

    /**
     * @var AddressGateway
     */
    private $addressGateway;

    /**
     * @var CustomerService
     */
    private $customerService;

    /**
     * @var CartContext
     */
    private $context;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @param ContextServiceInterface $shopContextService
     * @param \Enlight_Components_Session_Namespace $session
     * @param DeliveryServiceGateway $deliveryServiceGateway
     * @param PaymentServiceGateway $paymentServiceGateway
     * @param AddressGateway $addressGateway
     * @param  CustomerService $customerService
     * @param \Shopware_Components_Config $config
     */
    public function __construct(
        ContextServiceInterface $shopContextService,
        \Enlight_Components_Session_Namespace $session,
        DeliveryServiceGateway $deliveryServiceGateway,
        PaymentServiceGateway $paymentServiceGateway,
        AddressGateway $addressGateway,
        CustomerService $customerService,
        \Shopware_Components_Config $config
    ) {
        $this->shopContextService = $shopContextService;
        $this->session = $session;
        $this->deliveryServiceGateway = $deliveryServiceGateway;
        $this->paymentServiceGateway = $paymentServiceGateway;
        $this->addressGateway = $addressGateway;
        $this->customerService = $customerService;
        $this->config = $config;
    }

    public function getCartContext()
    {
        if ($this->context) {
            return $this->context;
        }

        $shopContext = $this->shopContextService->getShopContext();
        $customer = $this->getStoreFrontCustomer($shopContext);
        $addresses = $this->getStoreFrontCheckoutAddresses($shopContext, $customer);

        $this->context = new CartContext(
            $shopContext,
            $this->getStoreFrontPaymentService($shopContext, $customer),
            $this->getStoreFrontDeliveryService($shopContext),
            $customer,
            $addresses['billing'],
            $addresses['shipping']
        );
        return $this->context;
    }

    /**
     * @param ShopContextInterface $context
     * @return null|Customer
     */
    private function getStoreFrontCustomer(ShopContextInterface $context)
    {
        if (!($id = $this->session->get('sUserId'))) {
            return null;
        }

        $customer = $this->customerService->getList([$id], $context);
        return array_shift($customer);
    }

    /**
     * @param ShopContextInterface $context
     * @param Customer $customer
     * @return Address[]
     */
    private function getStoreFrontCheckoutAddresses(
        ShopContextInterface $context,
        Customer $customer = null
    ) {
        $ids = [];

        if (($shippingId = $this->session->get('checkoutShippingAddressId')) != null) {
            $ids[] = $shippingId;
        }

        if (($billingId = $this->session->get('checkoutBillingAddressId')) !== null) {
            $ids[] = $billingId;
        }

        $result = [
            'billing' => $customer? $customer->getDefaultBillingAddress() : null,
            'shipping' => $customer ? $customer->getDefaultShippingAddress() : null
        ];

        if (0 === count($ids)) {
            return $result;
        }

        $addresses = $this->addressGateway->getList($ids, $context);

        if ($billingId) {
            $result['billing'] = $addresses[$billingId];
        }
        if ($shippingId) {
            $result['shipping'] = $addresses[$shippingId];
        }
        return $result;
    }

    /**
     * @param ShopContextInterface $context
     * @return DeliveryService
     */
    private function getStoreFrontDeliveryService(ShopContextInterface $context)
    {
        if (!($id = $this->session->get('sDispatch'))) {
            $id = 9;
        }

        $services = $this->deliveryServiceGateway->getList([$id], $context);
        return array_shift($services);
    }

    /**
     * @param ShopContextInterface $context
     * @param Customer $customer
     * @return PaymentService
     */
    private function getStoreFrontPaymentService(ShopContextInterface $context, Customer $customer = null)
    {
        $id = $this->session->get('sPaymentID');

        //preselect last payment of customer if not selected
        if (!$id && $this->hasLastPayment($customer)) {
            return $customer->getLastPaymentService();

        //preselect payment of customer registration
        } elseif (!$id && $this->hasPresetPayment($customer)) {
            return $customer->getPresetPaymentService();
        }

        //customer not logged in
        if (!$id) {
            $id = $this->config->offsetGet('paymentdefault');
        }

        $services = $this->paymentServiceGateway->getList([$id], $context);
        return array_shift($services);
    }

    /**
     * @param Customer|null $customer
     * @return bool
     */
    private function hasPresetPayment(Customer $customer = null)
    {
        if ($customer === null) {
            return false;
        }
        return $customer->getPresetPaymentService() !== null;
    }

    /**
     * @param Customer|null $customer
     * @return bool
     */
    private function hasLastPayment($customer)
    {
        if ($customer === null) {
            return false;
        }
        return $customer->getLastPaymentService() !== null;
    }
}
