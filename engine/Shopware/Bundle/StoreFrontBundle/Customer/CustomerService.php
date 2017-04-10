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

namespace Shopware\Bundle\StoreFrontBundle\Customer;

use Shopware\Bundle\StoreFrontBundle\Address\AddressGateway;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\Context\TranslationContext;
use Shopware\Bundle\StoreFrontBundle\PaymentMethod\PaymentMethodGateway;
use Shopware\Bundle\StoreFrontBundle\Shop\ShopGateway;

class CustomerService
{
    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Customer\CustomerGateway
     */
    private $customerGateway;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Address\AddressGateway
     */
    private $addressGateway;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Shop\ShopGateway
     */
    private $shopGateway;

    /**
     * @var PaymentMethodGateway
     */
    private $paymentMethodGateway;

    /**
     * @param CustomerGateway                                                      $customerGateway
     * @param \Shopware\Bundle\StoreFrontBundle\Address\AddressGateway             $addressGateway
     * @param ShopGateway                                                          $shopGateway
     * @param \Shopware\Bundle\StoreFrontBundle\PaymentMethod\PaymentMethodGateway $paymentMethodGateway
     */
    public function __construct(
        CustomerGateway $customerGateway,
        AddressGateway $addressGateway,
        ShopGateway $shopGateway,
        PaymentMethodGateway $paymentMethodGateway
    ) {
        $this->customerGateway = $customerGateway;
        $this->addressGateway = $addressGateway;
        $this->shopGateway = $shopGateway;
        $this->paymentMethodGateway = $paymentMethodGateway;
    }

    /**
     * @param int[]                $ids
     * @param ShopContextInterface $context
     *
     * @return \Shopware\Bundle\StoreFrontBundle\Customer\Customer[]
     */
    public function getList($ids, TranslationContext $context): array
    {
        if (0 === count($ids)) {
            return [];
        }
        $customers = $this->customerGateway->getList($ids, $context);

        $addresses = $this->addressGateway->getList(
            $this->collectAddressIds($customers),
            $context
        );

        $shops = $this->shopGateway->getList($this->collectShopIds($customers), $context);

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
     * @param \Shopware\Bundle\StoreFrontBundle\Customer\Customer[] $customers
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
     * @param \Shopware\Bundle\StoreFrontBundle\Customer\Customer[] $customers
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
