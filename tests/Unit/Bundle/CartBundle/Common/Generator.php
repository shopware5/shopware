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

namespace Shopware\Tests\Unit\Bundle\CartBundle\Common;

use Shopware\Bundle\CartBundle\Domain\Cart\CartContext;
use Shopware\Bundle\CartBundle\Domain\Customer\Address;
use Shopware\Bundle\CartBundle\Domain\Customer\Customer;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryService;
use Shopware\Bundle\CartBundle\Domain\Payment\PaymentMethod;
use Shopware\Bundle\CartBundle\Domain\Price\PriceDefinition;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxDetector;
use Shopware\Bundle\CartBundle\Infrastructure\Product\ProductPriceGateway;
use Shopware\Bundle\StoreFrontBundle\Struct\Country;
use Shopware\Bundle\StoreFrontBundle\Struct\Currency;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceGroup;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Bundle\StoreFrontBundle\Struct\Tax;

class Generator extends \PHPUnit_Framework_TestCase
{
    /**
     * @param null|Shop            $shop
     * @param null|Currency        $currency
     * @param null|Group           $currentCustomerGroup
     * @param null|Group           $fallbackCustomerGroup
     * @param null|PriceGroup[]    $priceGroups
     * @param null|Tax[]           $taxes
     * @param null|Country\Area    $area
     * @param null|Country         $country
     * @param null|Country\State   $state
     * @param null|PaymentMethod   $paymentService
     * @param null|DeliveryService $deliveryService
     * @param null|Customer        $customer
     * @param null|Address         $billing
     * @param null|Address         $shipping
     *
     * @return CartContext
     */
    public static function createContext(
        $currentCustomerGroup = null,
        $fallbackCustomerGroup = null,
        $shop = null,
        $currency = null,
        $priceGroups = null,
        $taxes = null,
        $area = null,
        $country = null,
        $state = null,
        $paymentService = null,
        $deliveryService = null,
        $customer = null,
        $billing = null,
        $shipping = null
    ) {
        $shop = $shop ?: new Shop();
        $currency = $currency ?: new Currency();

        if (!$currentCustomerGroup) {
            $currentCustomerGroup = new Group();
            $currentCustomerGroup->setKey('EK2');
        }

        if (!$fallbackCustomerGroup) {
            $fallbackCustomerGroup = new Group();
            $fallbackCustomerGroup->setKey('EK1');
        }

        $priceGroups = $priceGroups ?: [new PriceGroup()];
        $taxes = $taxes ?: [new Tax(1, 'test', 19.0)];
        $area = $area ?: new Country\Area();
        $country = $country ?: new Country();
        $state = $state ?: new Country\State();
        $paymentService = $paymentService ?: new PaymentMethod(1, 'cash', 'Cash', 'CashPayment');
        $deliveryService = $deliveryService ?: new DeliveryService();
        $customer = $customer ?: new Customer();
        $billing = $billing ?: new Address();
        $shipping = $shipping ?: new Address();

        $shopContext = new ShopContext(
            '',
            $shop,
            $currency,
            $currentCustomerGroup,
            $fallbackCustomerGroup,
            $taxes,
            $priceGroups,
            $area,
            $country,
            $state
        );

        return new CartContext(
            $shopContext,
            $paymentService,
            $deliveryService,
            $customer,
            $billing,
            $shipping
        );
    }

    public static function createGrossPriceDetector()
    {
        $self = new self();

        return $self->createTaxDetector(true, false);
    }

    public static function createNetPriceDetector()
    {
        $self = new self();

        return $self->createTaxDetector(false, false);
    }

    public static function createNetDeliveryDetector()
    {
        $self = new self();

        return $self->createTaxDetector(false, true);
    }

    /**
     * @param PriceDefinition[] $priceDefinitions indexed by product number
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|ProductPriceGateway
     */
    public function createProductPriceGateway($priceDefinitions)
    {
        $mock = $this->createMock(ProductPriceGateway::class);
        $mock->expects(static::any())
            ->method('get')
            ->will(static::returnValue($priceDefinitions));

        return $mock;
    }

    private function createTaxDetector($useGross, $isNetDelivery)
    {
        $mock = $this->createMock(TaxDetector::class);
        $mock->expects(static::any())
            ->method('useGross')
            ->will(static::returnValue($useGross));

        $mock->expects(static::any())
            ->method('isNetDelivery')
            ->will(static::returnValue($isNetDelivery));

        return $mock;
    }
}
