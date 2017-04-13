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

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Voucher;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCartGenerator;
use Shopware\Bundle\CartBundle\Domain\Cart\CartContainer;
use Shopware\Bundle\CartBundle\Domain\Cart\ProcessorCart;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryCollection;
use Shopware\Bundle\CartBundle\Domain\Error\ErrorCollection;
use Shopware\Bundle\CartBundle\Domain\Error\VoucherModeNotFoundError;
use Shopware\Bundle\CartBundle\Domain\Error\VoucherNotFoundError;
use Shopware\Bundle\CartBundle\Domain\Error\VoucherRuleError;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemCollection;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItem;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemCollection;
use Shopware\Bundle\CartBundle\Domain\Price\PercentagePriceCalculator;
use Shopware\Bundle\CartBundle\Domain\Price\Price;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCalculator;
use Shopware\Bundle\CartBundle\Domain\Price\PriceDefinition;
use Shopware\Bundle\CartBundle\Domain\Product\ProductProcessor;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTaxCollection;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;
use Shopware\Bundle\CartBundle\Domain\Validator\Collector\RuleDataCollectorRegistry;
use Shopware\Bundle\CartBundle\Domain\Validator\Data\RuleDataCollection;
use Shopware\Bundle\CartBundle\Domain\Voucher\CalculatedVoucher;
use Shopware\Bundle\CartBundle\Domain\Voucher\Voucher;
use Shopware\Bundle\CartBundle\Domain\Voucher\VoucherCollection;
use Shopware\Bundle\CartBundle\Domain\Voucher\VoucherProcessor;
use Shopware\Bundle\CartBundle\Infrastructure\Voucher\VoucherGateway;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContext;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\DummyProduct;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\FalseRule;

class VoucherProcessorTest extends TestCase
{
    public function testEmptyCart()
    {
        $processor = new VoucherProcessor(
            $this->createMock(PercentagePriceCalculator::class),
            $this->createMock(CalculatedCartGenerator::class),
            $this->createMock(VoucherGateway::class),
            $this->createMock(RuleDataCollectorRegistry::class),
            $this->createMock(PriceCalculator::class)
        );

        $processorCart = new ProcessorCart(
            new CalculatedLineItemCollection(),
            new DeliveryCollection()
        );

        $processor->process(
            new CartContainer('test', Uuid::uuid4(), new LineItemCollection([])),
            $processorCart,
            $this->createMock(ShopContext::class)
        );

        $this->assertSame(0, $processorCart->getErrors()->count());
        $this->assertSame(0, $processorCart->getCalculatedLineItems()->count());
    }

    public function testCartWithNotVoucher()
    {
        $processor = new VoucherProcessor(
            $this->createMock(PercentagePriceCalculator::class),
            $this->createMock(CalculatedCartGenerator::class),
            $this->createMock(VoucherGateway::class),
            $this->createMock(RuleDataCollectorRegistry::class),
            $this->createMock(PriceCalculator::class)
        );

        $processorCart = new ProcessorCart(
            new CalculatedLineItemCollection([
                new DummyProduct('SW1'),
                new DummyProduct('SW2'),
            ]),
            new DeliveryCollection()
        );

        $processor->process(
            new CartContainer('test', Uuid::uuid4(), new LineItemCollection([
                new LineItem('SW1', ProductProcessor::TYPE_PRODUCT, 1),
                new LineItem('SW2', ProductProcessor::TYPE_PRODUCT, 1),
            ])),
            $processorCart,
            $this->createMock(ShopContext::class)
        );

        $this->assertSame(0, $processorCart->getErrors()->count());
        $this->assertSame(2, $processorCart->getCalculatedLineItems()->count());
    }

    public function testCartWithVoucherAndNoGoods()
    {
        $processorCart = new ProcessorCart(
            new CalculatedLineItemCollection([]),
            new DeliveryCollection()
        );

        $gateway = $this->createMock(VoucherGateway::class);
        $gateway->expects($this->never())->method('get');

        $processor = new VoucherProcessor(
            $this->createMock(PercentagePriceCalculator::class),
            $this->createMock(CalculatedCartGenerator::class),
            $gateway,
            $this->createMock(RuleDataCollectorRegistry::class),
            $this->createMock(PriceCalculator::class)
        );

        $processor->process(
            new CartContainer('test', Uuid::uuid4(), new LineItemCollection([
                new LineItem('voucher', VoucherProcessor::TYPE_VOUCHER, 1, ['code' => 'test']),
            ])),
            $processorCart,
            $this->createMock(ShopContext::class)
        );

        $this->assertSame(0, $processorCart->getErrors()->count());
        $this->assertSame(0, $processorCart->getCalculatedLineItems()->count());
    }

    public function testVoucherNotExists()
    {
        $processorCart = new ProcessorCart(
            new CalculatedLineItemCollection([
                new DummyProduct('SW1'),
                new DummyProduct('SW2'),
            ]),
            new DeliveryCollection()
        );

        $cartContainer = new CartContainer('test', Uuid::uuid4(), new LineItemCollection([
            new LineItem('voucher', VoucherProcessor::TYPE_VOUCHER, 1, ['code' => 'test']),
            new LineItem('SW1', ProductProcessor::TYPE_PRODUCT, 1),
            new LineItem('SW2', ProductProcessor::TYPE_PRODUCT, 1),
        ]));

        $voucherGateway = $this->createMock(VoucherGateway::class);
        $voucherGateway->method('get')->will($this->returnValue(new VoucherCollection([])));

        $ruleRegistry = $this->createMock(RuleDataCollectorRegistry::class);
        $ruleRegistry->method('collect')->will($this->returnValue(new RuleDataCollection()));

        $processor = new VoucherProcessor(
            $this->createMock(PercentagePriceCalculator::class),
            $this->createMock(CalculatedCartGenerator::class),
            $voucherGateway,
            $ruleRegistry,
            $this->createMock(PriceCalculator::class)
        );

        $processor->process($cartContainer, $processorCart, $this->createMock(ShopContext::class));

        $this->assertSame(1, $processorCart->getErrors()->count());
        $this->assertEquals(
            new ErrorCollection([new VoucherNotFoundError('test')]),
            $processorCart->getErrors()
        );

        /** @var VoucherNotFoundError $error */
        $error = $processorCart->getErrors()->get(0);

        $this->assertSame('Voucher with code test not found', $error->getMessage());
        $this->assertSame(VoucherNotFoundError::LEVEL_ERROR, $error->getLevel());
        $this->assertSame(VoucherNotFoundError::class, $error->getMessageKey());
    }

    public function testVoucherRuleMatch()
    {
        $processorCart = new ProcessorCart(
            new CalculatedLineItemCollection([
                new DummyProduct('SW1'),
                new DummyProduct('SW2'),
            ]),
            new DeliveryCollection()
        );

        $cartContainer = new CartContainer('test', Uuid::uuid4(), new LineItemCollection([
            new LineItem('voucher', VoucherProcessor::TYPE_VOUCHER, 1, ['code' => 'test']),
            new LineItem('SW1', ProductProcessor::TYPE_PRODUCT, 1),
            new LineItem('SW2', ProductProcessor::TYPE_PRODUCT, 1),
        ]));

        $voucherGateway = $this->createMock(VoucherGateway::class);
        $voucherGateway->method('get')->will(
            $this->returnValue(
                new VoucherCollection([
                    new Voucher('test', VoucherProcessor::TYPE_ABSOLUTE, 10, null, new FalseRule()),
                ])
            )
        );

        $ruleRegistry = $this->createMock(RuleDataCollectorRegistry::class);
        $ruleRegistry->method('collect')->will($this->returnValue(new RuleDataCollection()));

        $processor = new VoucherProcessor(
            $this->createMock(PercentagePriceCalculator::class),
            $this->createMock(CalculatedCartGenerator::class),
            $voucherGateway,
            $ruleRegistry,
            $this->createMock(PriceCalculator::class)
        );

        $processor->process($cartContainer, $processorCart, $this->createMock(ShopContext::class));

        $this->assertSame(1, $processorCart->getErrors()->count());
        $this->assertEquals(
            new ErrorCollection([new VoucherRuleError('test', new FalseRule())]),
            $processorCart->getErrors()
        );
    }

    public function testPercentage()
    {
        $processorCart = new ProcessorCart(
            new CalculatedLineItemCollection([
                new DummyProduct('SW1'),
                new DummyProduct('SW2'),
            ]),
            new DeliveryCollection()
        );

        $lineItem = new LineItem('voucher', VoucherProcessor::TYPE_VOUCHER, 1, ['code' => 'test']);

        $cartContainer = new CartContainer('test', Uuid::uuid4(), new LineItemCollection([
            $lineItem,
            new LineItem('SW1', ProductProcessor::TYPE_PRODUCT, 1),
            new LineItem('SW2', ProductProcessor::TYPE_PRODUCT, 1),
        ]));

        $voucherGateway = $this->createMock(VoucherGateway::class);
        $voucherGateway->method('get')->will(
            $this->returnValue(
                new VoucherCollection([
                    new Voucher('test', VoucherProcessor::TYPE_PERCENTAGE, 10, null, null),
                ])
            )
        );

        $ruleRegistry = $this->createMock(RuleDataCollectorRegistry::class);
        $ruleRegistry->method('collect')->will($this->returnValue(new RuleDataCollection()));

        $price = new Price(1, 1, new CalculatedTaxCollection(), new TaxRuleCollection());
        $percentageCalculator = $this->createMock(PercentagePriceCalculator::class);
        $percentageCalculator->expects($this->once())->method('calculate')->will(
            $this->returnValue($price)
        );

        $processor = new VoucherProcessor(
            $percentageCalculator,
            $this->createMock(CalculatedCartGenerator::class),
            $voucherGateway,
            $ruleRegistry,
            $this->createMock(PriceCalculator::class)
        );

        $processor->process($cartContainer, $processorCart, $this->createMock(ShopContext::class));
        $this->assertSame(1, $processorCart->getCalculatedLineItems()->filterInstance(CalculatedVoucher::class)->count());

        /** @var CalculatedVoucher $voucher */
        $voucher = $processorCart->getCalculatedLineItems()->get('voucher');
        $this->assertSame('voucher', $voucher->getIdentifier());
        $this->assertSame($lineItem, $voucher->getLineItem());
        $this->assertSame(1, $voucher->getQuantity());
        $this->assertSame($price, $voucher->getPrice());
        $this->assertSame($voucher, $voucher->getCalculatedLineItem());
        $this->assertSame('voucher', $voucher->getLabel());
        $this->assertNull($voucher->getCover());
        $this->assertSame('voucher', $voucher->getCode());
    }

    public function testAbsolute()
    {
        $processorCart = new ProcessorCart(
            new CalculatedLineItemCollection([
                new DummyProduct('SW1'),
                new DummyProduct('SW2'),
            ]),
            new DeliveryCollection()
        );

        $lineItem = new LineItem('voucher', VoucherProcessor::TYPE_VOUCHER, 1, ['code' => 'test']);

        $cartContainer = new CartContainer('test', Uuid::uuid4(), new LineItemCollection([
            $lineItem,
            new LineItem('SW1', ProductProcessor::TYPE_PRODUCT, 1),
            new LineItem('SW2', ProductProcessor::TYPE_PRODUCT, 1),
        ]));

        $voucherGateway = $this->createMock(VoucherGateway::class);
        $voucherGateway->method('get')->will(
            $this->returnValue(
                new VoucherCollection([
                    new Voucher('test', VoucherProcessor::TYPE_ABSOLUTE, null, new PriceDefinition(1, new TaxRuleCollection()), null),
                ])
            )
        );

        $ruleRegistry = $this->createMock(RuleDataCollectorRegistry::class);
        $ruleRegistry->method('collect')->will($this->returnValue(new RuleDataCollection()));

        $percentageCalculator = $this->createMock(PercentagePriceCalculator::class);
        $percentageCalculator->expects($this->never())->method('calculate');

        $priceCalculator = $this->createMock(PriceCalculator::class);
        $priceCalculator->expects($this->once())->method('calculate')->will(
            $this->returnValue(
                new Price(1, 1, new CalculatedTaxCollection(), new TaxRuleCollection())
            )
        );

        $processor = new VoucherProcessor(
            $percentageCalculator,
            $this->createMock(CalculatedCartGenerator::class),
            $voucherGateway,
            $ruleRegistry,
            $priceCalculator
        );

        $processor->process($cartContainer, $processorCart, $this->createMock(ShopContext::class));

        $this->assertSame(1, $processorCart->getCalculatedLineItems()->filterInstance(CalculatedVoucher::class)->count());
    }

    public function testNotSupportedMode()
    {
        $processorCart = new ProcessorCart(
            new CalculatedLineItemCollection([
                new DummyProduct('SW1'),
                new DummyProduct('SW2'),
            ]),
            new DeliveryCollection()
        );

        $lineItem = new LineItem('voucher', VoucherProcessor::TYPE_VOUCHER, 1, ['code' => 'test']);

        $cartContainer = new CartContainer('test', Uuid::uuid4(), new LineItemCollection([
            $lineItem,
            new LineItem('SW1', ProductProcessor::TYPE_PRODUCT, 1),
            new LineItem('SW2', ProductProcessor::TYPE_PRODUCT, 1),
        ]));

        $voucherGateway = $this->createMock(VoucherGateway::class);
        $voucherGateway->method('get')->will(
            $this->returnValue(
                new VoucherCollection([
                    new Voucher('test', 'not supported', null, new PriceDefinition(1, new TaxRuleCollection()), null),
                ])
            )
        );

        $processor = new VoucherProcessor(
            $this->createMock(PercentagePriceCalculator::class),
            $this->createMock(CalculatedCartGenerator::class),
            $voucherGateway,
            $this->createMock(RuleDataCollectorRegistry::class),
            $this->createMock(PriceCalculator::class)
        );

        $processor->process($cartContainer, $processorCart, $this->createMock(ShopContext::class));

        $this->assertSame(1, $processorCart->getErrors()->count());
        $this->assertEquals(
            new ErrorCollection([new VoucherModeNotFoundError('test', 'not supported')]),
            $processorCart->getErrors()
        );
    }
}
