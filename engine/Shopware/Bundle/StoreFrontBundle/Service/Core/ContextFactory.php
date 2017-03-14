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

namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\CartBundle\Domain\Customer\Customer;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryMethod;
use Shopware\Bundle\CartBundle\Domain\Delivery\ShippingLocation;
use Shopware\Bundle\CartBundle\Domain\Payment\PaymentMethod;
use Shopware\Bundle\CartBundle\Infrastructure\Customer\AddressGateway;
use Shopware\Bundle\CartBundle\Infrastructure\Customer\CustomerService;
use Shopware\Bundle\CartBundle\Infrastructure\Delivery\DeliveryMethodGateway;
use Shopware\Bundle\CartBundle\Infrastructure\Payment\PaymentMethodGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\CountryGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\CurrencyGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\CustomerGroupGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\PriceGroupDiscountGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\ShopGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\TaxGateway;
use Shopware\Bundle\StoreFrontBundle\Service\ContextFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\CheckoutDefinition;
use Shopware\Bundle\StoreFrontBundle\Struct\Country;
use Shopware\Bundle\StoreFrontBundle\Struct\Currency;
use Shopware\Bundle\StoreFrontBundle\Struct\CustomerDefinition;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopDefinition;
use Shopware\Bundle\StoreFrontBundle\Struct\TranslationContext;

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
     * @var CountryGateway
     */
    private $countryGateway;

    /**
     * @var TaxGateway
     */
    private $taxGateway;

    /**
     * @var PriceGroupDiscountGateway
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
     * @var DeliveryMethodGateway
     */
    private $deliveryMethodGateway;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param ShopGateway               $shopGateway
     * @param CurrencyGateway           $currencyGateway
     * @param CustomerService           $customerService
     * @param CustomerGroupGateway      $customerGroupGateway
     * @param CountryGateway            $countryGateway
     * @param TaxGateway                $taxGateway
     * @param PriceGroupDiscountGateway $priceGroupDiscountGateway
     * @param AddressGateway            $addressGateway
     * @param PaymentMethodGateway      $paymentMethodGateway
     * @param DeliveryMethodGateway     $deliveryMethodGateway
     * @param Connection                $connection
     */
    public function __construct(
        ShopGateway $shopGateway,
        CurrencyGateway $currencyGateway,
        CustomerService $customerService,
        CustomerGroupGateway $customerGroupGateway,
        CountryGateway $countryGateway,
        TaxGateway $taxGateway,
        PriceGroupDiscountGateway $priceGroupDiscountGateway,
        AddressGateway $addressGateway,
        PaymentMethodGateway $paymentMethodGateway,
        DeliveryMethodGateway $deliveryMethodGateway,
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
        $this->deliveryMethodGateway = $deliveryMethodGateway;
        $this->connection = $connection;
    }

    public function create(
        ShopDefinition $shopDefinition,
        CustomerDefinition $customerDefinition,
        CheckoutDefinition $checkoutDefinition
    ): ShopContextInterface {
        $translationContext = $this->getTranslationContext($shopDefinition->getShopId());

        //select shop with all fallbacks
        $shop = $this->shopGateway->getList([$shopDefinition->getShopId()], $translationContext);
        $shop = array_shift($shop);

        //load active currency, fallback to shop currency
        $currency = $this->getCurrency($shop, $shopDefinition->getCurrencyId());

        //fallback customer group is hard coded to 'EK'
        $fallbackGroup = $this->customerGroupGateway->getList([ContextService::FALLBACK_CUSTOMER_GROUP]);
        $fallbackGroup = array_shift($fallbackGroup);

        $customer = null;

        if ($customerDefinition->getCustomerId() !== null) {
            //load logged in customer and set active addresses
            $customer = $this->loadCustomer($customerDefinition, $translationContext);

            $shippingLocation = ShippingLocation::createFromAddress($customer->getActiveShippingAddress());
        } else {
            //load not logged in customer with default shop configuration or with provided checkout definitions
            $shippingLocation = $this->loadShippingLocation($shop, $translationContext, $checkoutDefinition);
        }

        //customer group switched?
        $customerGroup = $shop->getCustomerGroup();
        if ($customerDefinition->getCustomerGroupKey()) {
            $customerGroup = $this->customerGroupGateway->getList([$customerDefinition->getCustomerGroupKey()]);
            $customerGroup = array_shift($customerGroup);
        }

        //loads tax rules based on active customer group and delivery address
        $taxRules = $this->taxGateway->getRules($customerGroup, $shippingLocation);

        //price group discounts has to be loaded for current customer group, used for product graduations
        $priceGroups = $this->priceGroupDiscountGateway->getPriceGroups($customerGroup);

        //detect active payment method, first check if checkout defined other payment method, otherwise validate if customer logged in, at least use shop default
        $payment = $this->getPayment($customer, $shop, $translationContext, $checkoutDefinition);

        //detect active delivery method, at first checkout definition, at least shop default method
        $delivery = $this->getDelivery($shop, $translationContext, $checkoutDefinition);

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
        CheckoutDefinition $checkoutDefinition
    ): PaymentMethod {
        //payment switched in checkout?
        if ($checkoutDefinition->getPaymentId()) {
            $services = $this->paymentMethodGateway->getList(
                [$checkoutDefinition->getPaymentId()],
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
        CheckoutDefinition $checkoutDefinition
    ): DeliveryMethod {
        if ($checkoutDefinition->getDispatchId()) {
            $delivery = $this->deliveryMethodGateway->getList(
                [$checkoutDefinition->getDispatchId()],
                $context
            );

            return array_shift($delivery);
        }

        return $shop->getDeliveryMethod();
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

    private function loadCustomer(CustomerDefinition $customerDefinition, TranslationContext $translationContext): Customer
    {
        $customers = $this->customerService->getList(
            [$customerDefinition->getCustomerId()],
            $translationContext
        );

        $customer = array_shift($customers);

        //billing address changed within checkout?
        if ($customerDefinition->getBillingId()) {
            $addresses = $this->addressGateway->getList(
                [$customerDefinition->getBillingId()],
                $translationContext
            );

            $customer->setActiveBillingAddress(array_shift($addresses));
        }

        //shipping address changed within checkout?
        if ($customerDefinition->getShippingId()) {
            $addresses = $this->addressGateway->getList(
                [$customerDefinition->getShippingId()],
                $translationContext
            );

            $customer->setActiveShippingAddress(array_shift($addresses));
        }

        return $customer;
    }

    private function loadShippingLocation(
        Shop $shop,
        TranslationContext $translationContext,
        CheckoutDefinition $checkoutDefinition
    ): ShippingLocation {
        //allows to preview cart calculation for a specify state for not logged in customers
        if ($checkoutDefinition->getStateId()) {
            $state = $this->countryGateway->getStates(
                [$checkoutDefinition->getStateId()],
                $translationContext
            );

            return ShippingLocation::createFromState(array_shift($state));

        //allows to preview cart calculation for a specify country for not logged in customers
        } elseif ($checkoutDefinition->getCountryId()) {
            $country = $this->countryGateway->getCountries(
                [$checkoutDefinition->getCountryId()],
                $translationContext
            );

            return ShippingLocation::createFromCountry(array_shift($country));
        }

        return ShippingLocation::createFromCountry($shop->getCountry());
    }
}
