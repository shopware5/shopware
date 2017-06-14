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

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Payment;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCartGenerator;
use Shopware\Bundle\CartBundle\Domain\Cart\CartContainer;
use Shopware\Bundle\CartBundle\Domain\Cart\ProcessorCart;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryCollection;
use Shopware\Bundle\CartBundle\Domain\Error\ErrorCollection;
use Shopware\Bundle\CartBundle\Domain\Error\PaymentBlockedError;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemCollection;
use Shopware\Bundle\CartBundle\Domain\Payment\PaymentValidatorProcessor;
use Shopware\Bundle\CartBundle\Domain\Rule\Collector\RuleDataCollectorRegistry;
use Shopware\Bundle\CartBundle\Domain\Rule\ValidatableFilter;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContext;
use Shopware\Bundle\StoreFrontBundle\Customer\Customer;
use Shopware\Bundle\StoreFrontBundle\PaymentMethod\PaymentMethod;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\FalseRule;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\TrueRule;

class PaymentValidatorProcessorTest extends TestCase
{
    //    public function testWithNoCustomer(): void
//    {
//        $context = $this->createMock(ShopContext::class);
//        $context->expects($this->once())->method('getCustomer')->will($this->returnValue(null));
//
//        $filter = $this->createMock(ValidatableFilter::class);
//        $generator = $this->createMock(CalculatedCartGenerator::class);
//
//        $processor = new PaymentValidatorProcessor($filter, $generator);
//
//        $processorCart = new ProcessorCart(new CalculatedLineItemCollection(), new DeliveryCollection());
//
//        $processor->process(CartContainer::createNew('test'), $processorCart, $context);
//
//        $this->assertSame(0, $processorCart->getErrors()->count());
//    }
//
//    public function testWithoutPaymentRule(): void
//    {
//        $context = $this->createMock(ShopContext::class);
//        $customer = new Customer();
//        $context->expects($this->once())->method('getCustomer')->will($this->returnValue($customer));
//
//        $paymentMethod = new PaymentMethod(1, 'test', 'test', 'test');
//        $paymentMethod->setRule(null);
//
//        $context->expects($this->once())->method('getPaymentMethod')->will($this->returnValue($paymentMethod));
//
//        $filter = new ValidatableFilter(
//            new RuleDataCollectorRegistry([])
//        );
//        $generator = $this->createMock(CalculatedCartGenerator::class);
//
//        $processor = new PaymentValidatorProcessor($filter, $generator);
//
//        $processorCart = new ProcessorCart(new CalculatedLineItemCollection(), new DeliveryCollection());
//
//        $processor->process(CartContainer::createNew('test'), $processorCart, $context);
//
//        $this->assertSame(0, $processorCart->getErrors()->count());
//    }
//
//    public function testValid(): void
//    {
//        $context = $this->createMock(ShopContext::class);
//        $customer = new Customer();
//        $context->expects($this->once())->method('getCustomer')->will($this->returnValue($customer));
//
//        $paymentMethod = new PaymentMethod(1, 'test', 'test', 'test');
//        $paymentMethod->setRule(new FalseRule());
//
//        $context->expects($this->once())->method('getPaymentMethod')->will($this->returnValue($paymentMethod));
//
//        $filter = new ValidatableFilter(
//            new RuleDataCollectorRegistry([])
//        );
//
//        $generator = $this->createMock(CalculatedCartGenerator::class);
//        $generator->method('create')->will($this->returnValue($this->createMock(CalculatedCart::class)));
//
//        $processor = new PaymentValidatorProcessor($filter, $generator);
//
//        $processorCart = new ProcessorCart(new CalculatedLineItemCollection(), new DeliveryCollection());
//
//        $processor->process(CartContainer::createNew('test'), $processorCart, $context);
//
//        $this->assertSame(0, $processorCart->getErrors()->count());
//    }
//
//    public function testInvalid(): void
//    {
//        $context = $this->createMock(ShopContext::class);
//        $customer = new Customer();
//        $context->expects($this->once())->method('getCustomer')->will($this->returnValue($customer));
//
//        $paymentMethod = new PaymentMethod(1, 'test', 'test', 'test');
//        $paymentMethod->setRule(new TrueRule());
//
//        $context->expects($this->once())->method('getPaymentMethod')->will($this->returnValue($paymentMethod));
//
//        $filter = new ValidatableFilter(
//            new RuleDataCollectorRegistry([])
//        );
//
//        $generator = $this->createMock(CalculatedCartGenerator::class);
//        $generator->method('create')->will($this->returnValue($this->createMock(CalculatedCart::class)));
//
//        $processor = new PaymentValidatorProcessor($filter, $generator);
//
//        $processorCart = new ProcessorCart(new CalculatedLineItemCollection(), new DeliveryCollection());
//
//        $processor->process(CartContainer::createNew('test'), $processorCart, $context);
//
//        $this->assertSame(1, $processorCart->getErrors()->count());
//        $this->assertEquals(
//            new ErrorCollection([
//                new PaymentBlockedError(1, 'test'),
//            ]),
//            $processorCart->getErrors()
//        );
//    }
}
