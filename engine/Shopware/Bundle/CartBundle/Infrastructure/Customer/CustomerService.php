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

namespace Shopware\Bundle\CartBundle\Infrastructure\Customer;

use Shopware\Bundle\CartBundle\Domain\Customer\Customer;
use Shopware\Bundle\CartBundle\Infrastructure\Payment\PaymentMethodGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\ShopGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class CustomerService
{
    /**
     * @var CustomerGateway
     */
    private $customerGateway;

    /**
     * @var AddressGateway
     */
    private $addressGateway;

    /**
     * @var ShopGatewayInterface
     */
    private $shopGateway;

    /**
     * @var PaymentMethodGateway
     */
    private $paymentMethodGateway;

    /**
     * @param CustomerGateway      $customerGateway
     * @param AddressGateway       $addressGateway
     * @param ShopGatewayInterface $shopGatewayInterface
     * @param PaymentMethodGateway $paymentMethodGateway
     */
    public function __construct(
        CustomerGateway $customerGateway,
        AddressGateway $addressGateway,
        ShopGatewayInterface $shopGatewayInterface,
        PaymentMethodGateway $paymentMethodGateway
    ) {
        $this->customerGateway = $customerGateway;
        $this->addressGateway = $addressGateway;
        $this->shopGateway = $shopGatewayInterface;
        $this->paymentMethodGateway = $paymentMethodGateway;
    }

    /**
     * @param int[]                $ids
     * @param ShopContextInterface $context
     *
     * @return Customer[]
     */
    public function getList($ids, ShopContextInterface $context): array
    {
        if (0 === count($ids)) {
            return [];
        }
        $customers = $this->customerGateway->getList($ids, $context);

        $addresses = $this->addressGateway->getList(
            $this->collectAddressIds($customers),
            $context
        );

        $shops = $this->shopGateway->getList($this->collectShopIds($customers));

        $payments = $this->paymentMethodGateway->getList(
            $this->collectPaymentIds($customers),
            $context
        );

        /** @var Customer $customer */
        foreach ($customers as $customer) {
            $id = $customer->getDefaultBillingAddressId();
            if (array_key_exists($id, $addresses)) {
                $customer->setDefaultBillingAddress($addresses[$id]);
            }

            $id = $customer->getDefaultShippingAddressId();
            if (array_key_exists($id, $addresses)) {
                $customer->setDefaultShippingAddress($addresses[$id]);
            }

            $id = $customer->getAssignedLanguageShopId();
            if (array_key_exists($id, $shops)) {
                $customer->setAssignedLanguageShop($shops[$id]);
            }

            $id = $customer->getAssignedShopId();
            if (array_key_exists($id, $shops)) {
                $customer->setAssignedShop($shops[$id]);
            }

            $id = $customer->getPresetPaymentMethodId();
            if (array_key_exists($id, $payments)) {
                $customer->setPresetPaymentMethod($payments[$id]);
            }

            $id = $customer->getLastPaymentMethodId();
            if (array_key_exists($id, $payments)) {
                $customer->setLastPaymentMethod($payments[$id]);
            }
        }

        return $customers;
    }

    /**
     * @param Customer[] $customers
     *
     * @return int[]
     */
    private function collectAddressIds(array $customers): array
    {
        $ids = [];
        foreach ($customers as $customer) {
            $ids[] = $customer->getDefaultShippingAddressId();
            $ids[] = $customer->getDefaultBillingAddressId();
        }

        return $ids;
    }

    /**
     * @param Customer[] $customers
     *
     * @return int[]
     */
    private function collectShopIds(array $customers): array
    {
        $ids = [];
        foreach ($customers as $customer) {
            $ids[] = $customer->getAssignedShopId();
            $ids[] = $customer->getAssignedLanguageShopId();
        }

        return $ids;
    }

    /**
     * @param Customer[] $customers
     *
     * @return int[]
     */
    private function collectPaymentIds(array $customers): array
    {
        $ids = [];
        foreach ($customers as $customer) {
            $ids[] = $customer->getLastPaymentMethodId();
            $ids[] = $customer->getPresetPaymentMethodId();
        }

        return $ids;
    }
}
