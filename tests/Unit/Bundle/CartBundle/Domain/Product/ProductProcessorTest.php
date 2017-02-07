<?php

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Product;

use Shopware\Bundle\CartBundle\Domain\Cart\Cart;
use Shopware\Bundle\CartBundle\Domain\Cart\ProcessorCart;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryCollection;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryDate;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryInformation;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemCollection;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItem;
use Shopware\Bundle\CartBundle\Domain\Price\Price;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCalculator;
use Shopware\Bundle\CartBundle\Domain\Price\PriceDefinition;
use Shopware\Bundle\CartBundle\Domain\Product\CalculatedProduct;
use Shopware\Bundle\CartBundle\Domain\Product\ProductProcessor;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTaxCollection;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;
use Shopware\Bundle\CartBundle\Infrastructure\Product\ProductDeliveryGateway;
use Shopware\Bundle\CartBundle\Infrastructure\Product\ProductPriceGateway;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\Generator;

class ProductProcessorTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_TAX_NAME = 'test-tax';

    public function testConvertingAnEmptyCart()
    {
        $priceGateway = $this->createMock(ProductPriceGateway::class);
        $priceGateway
            ->expects(static::never())
            ->method('get');

        $calculator = $this->createMock(PriceCalculator::class);
        $calculator
            ->expects(static::never())
            ->method('calculate');

        $deliveryGateway = $this->createMock(ProductDeliveryGateway::class);
        $deliveryGateway
            ->expects(static::never())
            ->method('get');

        $processor = new ProductProcessor($priceGateway, $calculator, $deliveryGateway);

        $cart = new ProcessorCart(
            new CalculatedLineItemCollection(),
            new DeliveryCollection()
        );

        $processor->process(
            Cart::createExisting('test', 'test', []),
            $cart,
            Generator::createContext()
        );

        static::assertCount(0, $cart->getLineItems());
    }

    public function testConvertOneProduct()
    {
        $priceGateway = $this->createMock(ProductPriceGateway::class);
        $priceGateway
            ->expects(static::once())
            ->method('get')
            ->will(
                static::returnValue([
                    'SW1' => new PriceDefinition(0, new TaxRuleCollection())
                ])
            );

        $calculator = $this->createMock(PriceCalculator::class);
        $calculator
            ->expects(static::once())
            ->method('calculate')
            ->will(static::returnValue(
                new Price(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection(), 0)
            ));

        $deliveryGateway = $this->createMock(ProductDeliveryGateway::class);
        $deliveryGateway
            ->expects(static::once())
            ->method('get')
            ->will(
                static::returnValue([
                    'SW1' => new DefaultDeliveryInformation()
                ])
            );


        $processor = new ProductProcessor($priceGateway, $calculator, $deliveryGateway);

        $cart = new ProcessorCart(
            new CalculatedLineItemCollection(),
            new DeliveryCollection()
        );

        $processor->process(
            Cart::createExisting('test', 'test', [
                new LineItem('SW1', ProductProcessor::TYPE_PRODUCT, 1)
            ]),
            $cart,
            Generator::createContext()
        );
        static::assertCount(1, $cart->getLineItems());
    }

    public function testConvertMultipleProducts()
    {
        $priceGateway = $this->createMock(ProductPriceGateway::class);
        $priceGateway
            ->expects(static::any())
            ->method('get')
            ->will(
                static::returnValue([
                    'SW1' => new PriceDefinition(0, new TaxRuleCollection()),
                    'SW2' => new PriceDefinition(0, new TaxRuleCollection()),
                    'SW3' => new PriceDefinition(0, new TaxRuleCollection())
                ])
            );

        $calculator = $this->createMock(PriceCalculator::class);
        $calculator
            ->expects(static::any())
            ->method('calculate')
            ->will(static::returnValue(
                new Price(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection(), 0)
            ));


        $deliveryGateway = $this->createMock(ProductDeliveryGateway::class);
        $deliveryGateway
            ->expects(static::once())
            ->method('get')
            ->will(
                static::returnValue([
                    'SW1' => new DefaultDeliveryInformation(),
                    'SW2' => new DefaultDeliveryInformation(),
                    'SW3' => new DefaultDeliveryInformation()
                ])
            );

        $processor = new ProductProcessor($priceGateway, $calculator, $deliveryGateway);

        $cart = new ProcessorCart(
            new CalculatedLineItemCollection(),
            new DeliveryCollection()
        );

        $processor->process(
            Cart::createExisting('test', 'test', [
                new LineItem('SW1', ProductProcessor::TYPE_PRODUCT, 1),
                new LineItem('SW2', ProductProcessor::TYPE_PRODUCT, 1),
                new LineItem('SW3', ProductProcessor::TYPE_PRODUCT, 1)
            ]),
            $cart,
            Generator::createContext()
        );

        static::assertEquals(
            new CalculatedLineItemCollection([
                new CalculatedProduct(
                    'SW1',
                    1,
                    new LineItem('SW1', ProductProcessor::TYPE_PRODUCT, 1),
                    new Price(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection(), 0),
                    new DefaultDeliveryInformation()
                ),
                new CalculatedProduct(
                    'SW2',
                    1,
                    new LineItem('SW2', ProductProcessor::TYPE_PRODUCT, 1),
                    new Price(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection(), 0),
                    new DefaultDeliveryInformation()
                ),
                new CalculatedProduct(
                    'SW3',
                    1,
                    new LineItem('SW3', ProductProcessor::TYPE_PRODUCT, 1),
                    new Price(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection(), 0),
                    new DefaultDeliveryInformation()
                ),
            ]),
            $cart->getLineItems()
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testConvertProductWhenPricesAreMissing()
    {
        $priceGateway = $this->createMock(ProductPriceGateway::class);
        $priceGateway
            ->expects(static::once())
            ->method('get')
            ->will(
                static::returnValue([
                    'SW1' => new PriceDefinition(0, new TaxRuleCollection())
                ])
            );

        $calculator = $this->createMock(PriceCalculator::class);
        $calculator
            ->expects(static::exactly(1))
            ->method('calculate')
            ->will(static::returnValue(
                new Price(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection(), 0)
            ));

        $deliveryGateway = $this->createMock(ProductDeliveryGateway::class);
        $deliveryGateway
            ->expects(static::once())
            ->method('get')
            ->will(
                static::returnValue([
                    'SW1' => new DefaultDeliveryInformation()
                ])
            );

        $processor = new ProductProcessor($priceGateway, $calculator, $deliveryGateway);

        $processor->process(
            Cart::createExisting('test', 'test', [
                new LineItem('SW1', ProductProcessor::TYPE_PRODUCT, 1),
                new LineItem('SW2', ProductProcessor::TYPE_PRODUCT, 1),
                new LineItem('SW3', ProductProcessor::TYPE_PRODUCT, 1)
            ]),
            new ProcessorCart(
                new CalculatedLineItemCollection(),
                new DeliveryCollection()
            ),
            Generator::createContext()
        );
    }
}

class DefaultDeliveryInformation extends DeliveryInformation
{
    public function __construct()
    {
        parent::__construct(
            0,
            0,
            0,
            0,
            0,
            new DeliveryDate(
                new \DateTime(),
                new \DateTime()
            ),
            new DeliveryDate(
                new \DateTime(),
                new \DateTime()
            )
        );
    }
}
