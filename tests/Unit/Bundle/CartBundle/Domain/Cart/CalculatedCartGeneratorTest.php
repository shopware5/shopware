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

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Cart;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCartGenerator;
use Shopware\Bundle\CartBundle\Domain\Cart\CartContainer;
use Shopware\Bundle\CartBundle\Domain\Cart\ProcessorCart;
use Shopware\Bundle\CartBundle\Domain\Delivery\Delivery;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryCollection;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryDate;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryPositionCollection;
use Shopware\Bundle\CartBundle\Domain\Delivery\ShippingLocation;
use Shopware\Bundle\CartBundle\Domain\Error\ErrorCollection;
use Shopware\Bundle\CartBundle\Domain\Error\VoucherNotFoundError;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemCollection;
use Shopware\Bundle\CartBundle\Domain\Price\AmountCalculator;
use Shopware\Bundle\CartBundle\Domain\Price\CartPrice;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTaxCollection;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;
use Shopware\Bundle\StoreFrontBundle\Struct\ShippingMethod;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\DummyProduct;

class CalculatedCartGeneratorTest extends TestCase
{
    public function test()
    {
        $processorCart = new ProcessorCart(
            new CalculatedLineItemCollection(),
            new DeliveryCollection()
        );

        $price = new CartPrice(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection());

        $amountCalculator = $this->createMock(AmountCalculator::class);
        $amountCalculator->method('calculateAmount')->will($this->returnValue($price));

        $generator = new CalculatedCartGenerator($amountCalculator);

        $container = CartContainer::createNew('test');

        $context = $this->createMock(ShopContext::class);

        $this->assertCalculatedCart(
            new CalculatedCart(
                $container,
                new CalculatedLineItemCollection(),
                $price,
                new DeliveryCollection(),
                new ErrorCollection()
            ),
            $generator->create($container, $context, $processorCart)
        );
    }

    public function testUsesLineItemsOfProcessorCart()
    {
        $processorCart = new ProcessorCart(
            new CalculatedLineItemCollection([
                new DummyProduct('SW1'),
                new DummyProduct('SW2'),
            ]),
            new DeliveryCollection()
        );

        $price = new CartPrice(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection());

        $amountCalculator = $this->createMock(AmountCalculator::class);
        $amountCalculator->method('calculateAmount')->will($this->returnValue($price));

        $generator = new CalculatedCartGenerator($amountCalculator);

        $container = CartContainer::createNew('test');

        $context = $this->createMock(ShopContext::class);

        $this->assertCalculatedCart(
            new CalculatedCart(
                $container,
                new CalculatedLineItemCollection([
                    new DummyProduct('SW1'),
                    new DummyProduct('SW2'),
                ]),
                $price,
                new DeliveryCollection(),
                new ErrorCollection()
            ),
            $generator->create($container, $context, $processorCart)
        );
    }

    public function testUsesDeliveriesOfProcessorCart()
    {
        $delivery = new Delivery(
            new DeliveryPositionCollection(),
            new DeliveryDate(new \DateTime(), new \DateTime()),
            new ShippingMethod(1, 'prime', 'prime', 1, true, 1),
            $this->createMock(ShippingLocation::class)
        );

        $processorCart = new ProcessorCart(
            new CalculatedLineItemCollection(),
            new DeliveryCollection([$delivery])
        );

        $price = new CartPrice(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection());

        $amountCalculator = $this->createMock(AmountCalculator::class);
        $amountCalculator->method('calculateAmount')->will($this->returnValue($price));

        $generator = new CalculatedCartGenerator($amountCalculator);

        $container = CartContainer::createNew('test');

        $context = $this->createMock(ShopContext::class);

        $this->assertCalculatedCart(
            new CalculatedCart(
                $container,
                new CalculatedLineItemCollection(),
                $price,
                new DeliveryCollection([$delivery]),
                new ErrorCollection()
            ),
            $generator->create($container, $context, $processorCart)
        );
    }

    public function testUsesErrorsOfProcessorCart()
    {
        $processorCart = new ProcessorCart(
            new CalculatedLineItemCollection(),
            new DeliveryCollection()
        );
        $processorCart->getErrors()->add(new VoucherNotFoundError('1'));

        $price = new CartPrice(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection());

        $amountCalculator = $this->createMock(AmountCalculator::class);
        $amountCalculator->method('calculateAmount')->will($this->returnValue($price));

        $generator = new CalculatedCartGenerator($amountCalculator);

        $container = CartContainer::createNew('test');

        $context = $this->createMock(ShopContext::class);

        $this->assertCalculatedCart(
            new CalculatedCart(
                $container,
                new CalculatedLineItemCollection(),
                $price,
                new DeliveryCollection(),
                new ErrorCollection([
                    new VoucherNotFoundError('1'),
                ])
            ),
            $generator->create($container, $context, $processorCart)
        );
    }

    private function assertCalculatedCart(CalculatedCart $expected, CalculatedCart $actual)
    {
        $this->assertEquals($expected->getErrors(), $actual->getErrors());
        $this->assertEquals($expected->getName(), $actual->getName());
        $this->assertEquals($expected->getCartContainer(), $actual->getCartContainer());
        $this->assertEquals($expected->getCalculatedLineItems(), $actual->getCalculatedLineItems());
        $this->assertEquals($expected->getPrice(), $actual->getPrice());
        $this->assertEquals($expected->getDeliveries(), $actual->getDeliveries());
        $this->assertEquals($expected->getToken(), $actual->getToken());
        $this->assertEquals($expected, $actual);
    }
}
