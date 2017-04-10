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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\CartBundle\Domain\Delivery\ShippingLocation;
use Shopware\Bundle\StoreFrontBundle\Address\AddressGateway;
use Shopware\Bundle\StoreFrontBundle\Country\CountryGateway;
use Shopware\Bundle\StoreFrontBundle\Currency\Currency;
use Shopware\Bundle\StoreFrontBundle\Currency\CurrencyGateway;
use Shopware\Bundle\StoreFrontBundle\Customer\Customer;
use Shopware\Bundle\StoreFrontBundle\Customer\CustomerService;
use Shopware\Bundle\StoreFrontBundle\CustomerGroup\CustomerGroupGateway;
use Shopware\Bundle\StoreFrontBundle\PaymentMethod\PaymentMethod;
use Shopware\Bundle\StoreFrontBundle\PaymentMethod\PaymentMethodGateway;
use Shopware\Bundle\StoreFrontBundle\PriceGroup\PriceGroupGateway;
use Shopware\Bundle\StoreFrontBundle\ShippingMethod\ShippingMethod;
use Shopware\Bundle\StoreFrontBundle\ShippingMethod\ShippingMethodGateway;
use Shopware\Bundle\StoreFrontBundle\Shop\Shop;
use Shopware\Bundle\StoreFrontBundle\Shop\ShopGateway;
use Shopware\Bundle\StoreFrontBundle\Tax\TaxGateway;

class ContextFactory implements ContextFactoryInterface
{
    /**
     * @var ShopGateway
     */
    private $shopGateway;

    /**
     * @var CurrencyGateway
     */
    private $currencyGateway;

    /**
     * @var CustomerService
     */
    private $customerService;

    /**
     * @var CustomerGroupGateway
     */
    private $customerGroupGateway;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Country\CountryGateway
     */
    private $countryGateway;

    /**
     * @var TaxGateway
     */
    private $taxGateway;

    /**
     * @var PriceGroupGateway
     */
    private $priceGroupDiscountGateway;

    /**
     * @var AddressGateway
     */
    private $addressGateway;

    /**
     * @var PaymentMethodGateway
     */
    private $paymentMethodGateway;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\ShippingMethod\ShippingMethodGateway
     */
    private $shippingMethodGateway;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Shop\ShopGateway                     $shopGateway
     * @param CurrencyGateway                                                        $currencyGateway
     * @param CustomerService                                                        $customerService
     * @param \Shopware\Bundle\StoreFrontBundle\CustomerGroup\CustomerGroupGateway   $customerGroupGateway
     * @param \Shopware\Bundle\StoreFrontBundle\Country\CountryGateway               $countryGateway
     * @param \Shopware\Bundle\StoreFrontBundle\Tax\TaxGateway                       $taxGateway
     * @param PriceGroupGateway                                                      $priceGroupDiscountGateway
     * @param \Shopware\Bundle\StoreFrontBundle\Address\AddressGateway               $addressGateway
     * @param PaymentMethodGateway                                                   $paymentMethodGateway
     * @param \Shopware\Bundle\StoreFrontBundle\ShippingMethod\ShippingMethodGateway $shippingMethodGateway
     * @param Connection                                                             $connection
     */
    public function __construct(
        ShopGateway $shopGateway,
        CurrencyGateway $currencyGateway,
        CustomerService $customerService,
        CustomerGroupGateway $customerGroupGateway,
        CountryGateway $countryGateway,
        TaxGateway $taxGateway,
        PriceGroupGateway $priceGroupDiscountGateway,
        AddressGateway $addressGateway,
        PaymentMethodGateway $paymentMethodGateway,
        ShippingMethodGateway $shippingMethodGateway,
        Connection $connection
    ) {
        $this->shopGateway = $shopGateway;
        $this->currencyGateway = $currencyGateway;
        $this->customerService = $customerService;
        $this->customerGroupGateway = $customerGroupGateway;
        $this->countryGateway = $countryGateway;
        $this->taxGateway = $taxGateway;
        $this->priceGroupDiscountGateway = $priceGroupDiscountGateway;
        $this->addressGateway = $addressGateway;
        $this->paymentMethodGateway = $paymentMethodGateway;
        $this->shippingMethodGateway = $shippingMethodGateway;
        $this->connection = $connection;
    }

    public function create(
        ShopScope $shopScope,
        CustomerScope $customerScope,
        CheckoutScope $checkoutScope
    ): ShopContextInterface {
        $translationContext = $this->getTranslationContext($shopScope->getShopId());

        //select shop with all fallbacks
        $shop = $this->shopGateway->getList([$shopScope->getShopId()], $translationContext);
        $shop = array_shift($shop);

        if (!$shop) {
            throw new \Exception(sprintf('Shop with id %s not found or not valid!', $shopScope->getShopId()));
        }

        //load active currency, fallback to shop currency
        $currency = $this->getCurrency($shop, $shopScope->getCurrencyId());

        //fallback customer group is hard coded to 'EK'
        $fallbackGroup = $this->customerGroupGateway->getList([ContextService::FALLBACK_CUSTOMER_GROUP]);
        $fallbackGroup = array_shift($fallbackGroup);

        $customer = null;

        $customerGroup = $shop->getCustomerGroup();

        if ($customerScope->getCustomerId() !== null) {
            //load logged in customer and set active addresses
            $customer = $this->loadCustomer($customerScope, $translationContext);

            $shippingLocation = ShippingLocation::createFromAddress($customer->getActiveShippingAddress());

            $customerGroup = $customer->getCustomerGroup();
        } else {
            //load not logged in customer with default shop configuration or with provided checkout scopes
            $shippingLocation = $this->loadShippingLocation($shop, $translationContext, $checkoutScope);
        }

        //customer group switched?
        if ($customerScope->getCustomerGroupKey()) {
            $customerGroup = $this->customerGroupGateway->getList([$customerScope->getCustomerGroupKey()]);
            $customerGroup = array_shift($customerGroup);
        }

        //loads tax rules based on active customer group and delivery address
        $taxRules = $this->taxGateway->getRules($customerGroup, $shippingLocation);

        //price group discounts has to be loaded for current customer group, used for product graduations
        $priceGroups = $this->priceGroupDiscountGateway->getPriceGroups($customerGroup);

        //detect active payment method, first check if checkout defined other payment method, otherwise validate if customer logged in, at least use shop default
        $payment = $this->getPayment($customer, $shop, $translationContext, $checkoutScope);

        //detect active delivery method, at first checkout scope, at least shop default method
        $delivery = $this->getDelivery($shop, $translationContext, $checkoutScope);

        return new ShopContext(
            $shop,
            $currency,
            $customerGroup,
            $fallbackGroup,
            $taxRules,
            $priceGroups,
            $payment,
            $delivery,
            $shippingLocation,
            $customer
        );
    }

    private function getCurrency(Shop $shop, ?int $currencyId): Currency
    {
        if ($currencyId === null) {
            return $shop->getCurrency();
        }

        $currency = $this->currencyGateway->getList([$currencyId]);
        $currency = array_shift($currency);

        if (!$currency) {
            throw new \Exception(sprintf('Currency by id %s not found', $currencyId));
        }

        return $currency;
    }

    private function getPayment(
        ?Customer $customer,
        Shop $shop,
        TranslationContext $context,
        CheckoutScope $checkoutScope
    ): PaymentMethod {
        //payment switched in checkout?
        if ($checkoutScope->getPaymentId()) {
            $services = $this->paymentMethodGateway->getList(
                [$checkoutScope->getPaymentId()],
                $context
            );

            return array_shift($services);
        }

        //customer has a last payment method from previous order?
        if ($customer && $customer->getLastPaymentMethod()) {
            return $customer->getLastPaymentMethod();
        }

        //customer selected a default payment method in registration
        if ($customer && $customer->getPresetPaymentMethod()) {
            return $customer->getPresetPaymentMethod();
        }

        //at least use default payment method which defined for current shop
        return $shop->getPaymentMethod();
    }

    private function getDelivery(
        Shop $shop,
        TranslationContext $context,
        CheckoutScope $checkoutScope
    ): ShippingMethod {
        if ($checkoutScope->getDispatchId()) {
            $delivery = $this->shippingMethodGateway->getList(
                [$checkoutScope->getDispatchId()],
                $context
            );

            return array_shift($delivery);
        }

        return $shop->getShippingMethod();
    }

    private function getTranslationContext(int $shopId): TranslationContext
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['id', '`default`', 'fallback_id']);
        $query->from('s_core_shops', 'shop');
        $query->where('shop.id = :id');
        $query->setParameter(':id', $shopId);

        $data = $query->execute()->fetch(\PDO::FETCH_ASSOC);

        return new TranslationContext(
            (int) $data['id'],
            (bool) $data['default'],
            $data['fallback_id'] ? (int) $data['fallback_id'] : null
        );
    }

    private function loadCustomer(CustomerScope $customerScope, TranslationContext $translationContext): Customer
    {
        $customers = $this->customerService->getList(
            [$customerScope->getCustomerId()],
            $translationContext
        );

        $customer = array_shift($customers);

        //billing address changed within checkout?
        if ($customerScope->getBillingId()) {
            $addresses = $this->addressGateway->getList(
                [$customerScope->getBillingId()],
                $translationContext
            );

            $customer->setActiveBillingAddress(array_shift($addresses));
        }

        //shipping address changed within checkout?
        if ($customerScope->getShippingId()) {
            $addresses = $this->addressGateway->getList(
                [$customerScope->getShippingId()],
                $translationContext
            );

            $customer->setActiveShippingAddress(array_shift($addresses));
        }

        return $customer;
    }

    private function loadShippingLocation(
        Shop $shop,
        TranslationContext $translationContext,
        CheckoutScope $checkoutScope
    ): ShippingLocation {
        //allows to preview cart calculation for a specify state for not logged in customers
        if ($checkoutScope->getStateId()) {
            $state = $this->countryGateway->getStates(
                [$checkoutScope->getStateId()],
                $translationContext
            );

            return ShippingLocation::createFromState(array_shift($state));

        //allows to preview cart calculation for a specify country for not logged in customers
        } elseif ($checkoutScope->getCountryId()) {
            $country = $this->countryGateway->getCountries(
                [$checkoutScope->getCountryId()],
                $translationContext
            );

            return ShippingLocation::createFromCountry(array_shift($country));
        }

        return ShippingLocation::createFromCountry($shop->getCountry());
    }
}
