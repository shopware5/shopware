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

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Delivery;

use Shopware\Bundle\CartBundle\Domain\Customer\Address;
use Shopware\Bundle\CartBundle\Domain\Delivery\Delivery;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryCollection;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryDate;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryInformation;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryMethod;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryPosition;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryPositionCollection;
use Shopware\Bundle\CartBundle\Domain\Delivery\ShippingLocation;
use Shopware\Bundle\CartBundle\Domain\Delivery\StockDeliverySeparator;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemCollection;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItem;
use Shopware\Bundle\CartBundle\Domain\Price\Price;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCalculator;
use Shopware\Bundle\CartBundle\Domain\Price\PriceRounding;
use Shopware\Bundle\CartBundle\Domain\Product\CalculatedProduct;
use Shopware\Bundle\CartBundle\Domain\Product\ProductProcessor;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTax;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTaxCollection;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxCalculator;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRule;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCalculator;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;
use Shopware\Bundle\CartBundle\Domain\Voucher\CalculatedVoucher;
use Shopware\Bundle\StoreFrontBundle\Struct\Country;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\Generator;

class StockDeliverySeparatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StockDeliverySeparator
     */
    private $separator;

    protected function setUp()
    {
        parent::setUp();

        $this->separator = new StockDeliverySeparator(
            new PriceCalculator(
                new TaxCalculator(
                    new PriceRounding(2),
                    [new TaxRuleCalculator(new PriceRounding(2))]
                ),
                new PriceRounding(2),
                Generator::createGrossPriceDetector()
            )
        );
    }

    public function testAnEmptyCartHasNoDeliveries()
    {
        static::assertEquals(
            new DeliveryCollection(),
            $this->separator->addItemsToDeliveries(
                new DeliveryCollection(),
                new CalculatedLineItemCollection(),
                Generator::createContext()
            )
        );
    }

    public function testDeliverableItemCanBeAddedToDelivery()
    {
        $location = self::createShippingLocation();

        $item = new CalculatedProduct(
            'A',
            1,
            new LineItem('A', ProductProcessor::TYPE_PRODUCT, 100),
            new Price(1, 1, new CalculatedTaxCollection(), new TaxRuleCollection()),
            new DeliveryInformation(
                1, 0, 0, 0, 0,
                new DeliveryDate(new \DateTime('2012-01-01'), new \DateTime('2012-01-02')),
                new DeliveryDate(new \DateTime('2012-01-04'), new \DateTime('2012-01-05'))
            )
        );

        static::assertEquals(
            new DeliveryCollection([
                new Delivery(
                    new DeliveryPositionCollection([
                        DeliveryPosition::createByLineItemForInStockDate($item),
                    ]),
                    new DeliveryDate(new \DateTime('2012-01-01'), new \DateTime('2012-01-02')),
                    new DeliveryMethod(1, '', '', 1, true, 1),
                    $location
                ),
            ]),
            $this->separator->addItemsToDeliveries(
                new DeliveryCollection(),
                new CalculatedLineItemCollection([$item]),
                Generator::createContext(null, null, null, null, null, null, $location->getArea(), $location->getCountry(), $location->getState())
            )
        );
    }

    public function testCanDeliveryItemsWithSameDeliveryDateTogether()
    {
        $location = self::createShippingLocation();

        $deliveryInformation = new DeliveryInformation(
            5, 0, 0, 0, 0,
            new DeliveryDate(new \DateTime('2012-01-01'), new \DateTime('2012-01-02')),
            new DeliveryDate(new \DateTime('2012-01-04'), new \DateTime('2012-01-05'))
        );

        $itemA = new CalculatedProduct('A', 5,
            new LineItem('A', ProductProcessor::TYPE_PRODUCT, 5),
            new Price(1, 1, new CalculatedTaxCollection(), new TaxRuleCollection()),
            $deliveryInformation
        );

        $itemB = new CalculatedProduct('B', 5,
            new LineItem('B', ProductProcessor::TYPE_PRODUCT, 5),
            new Price(1, 1, new CalculatedTaxCollection(), new TaxRuleCollection()),
            $deliveryInformation
        );

        static::assertEquals(
            new DeliveryCollection([
                new Delivery(
                    new DeliveryPositionCollection([
                        DeliveryPosition::createByLineItemForInStockDate($itemA),
                        DeliveryPosition::createByLineItemForInStockDate($itemB),
                    ]),
                    $deliveryInformation->getInStockDeliveryDate(),
                    new DeliveryMethod(1, '', '', 1, true, 1),
                    $location
                ),
            ]),
            $this->separator->addItemsToDeliveries(
                new DeliveryCollection(),
                new CalculatedLineItemCollection([$itemA, $itemB]),
                Generator::createContext(null, null, null, null, null, null, $location->getArea(), $location->getCountry(), $location->getState())
            )
        );
    }

    public function testOutOfStockItemsCanBeDelivered()
    {
        $location = self::createShippingLocation();

        $itemA = new CalculatedProduct('A', 5,
            new LineItem('A', ProductProcessor::TYPE_PRODUCT, 5),
            new Price(1, 1, new CalculatedTaxCollection(), new TaxRuleCollection()),
            new DeliveryInformation(
                0, 0, 0, 0, 0,
                new DeliveryDate(new \DateTime('2012-01-01'), new \DateTime('2012-01-03')),
                new DeliveryDate(new \DateTime('2012-01-04'), new \DateTime('2012-01-05'))
            )
        );
        $itemB = new CalculatedProduct('B', 5,
            new LineItem('B', ProductProcessor::TYPE_PRODUCT, 5),
            new Price(1, 1, new CalculatedTaxCollection(), new TaxRuleCollection()),
            new DeliveryInformation(
                0, 0, 0, 0, 0,
                new DeliveryDate(new \DateTime('2012-01-01'), new \DateTime('2012-01-02')),
                new DeliveryDate(new \DateTime('2012-01-04'), new \DateTime('2012-01-05'))
            )
        );

        static::assertEquals(
            new DeliveryCollection([
                new Delivery(
                    new DeliveryPositionCollection([
                        DeliveryPosition::createByLineItemForOutOfStockDate($itemA),
                        DeliveryPosition::createByLineItemForOutOfStockDate($itemB),
                    ]),
                    new DeliveryDate(new \DateTime('2012-01-04'), new \DateTime('2012-01-05')),
                    new DeliveryMethod(1, '', '', 1, true, 1),
                    $location
                ),
            ]),
            $this->separator->addItemsToDeliveries(
                new DeliveryCollection(),
                new CalculatedLineItemCollection([$itemA, $itemB]),
                Generator::createContext(null, null, null, null, null, null, $location->getArea(), $location->getCountry(), $location->getState())
            )
        );
    }

    public function testNoneDeliverableItemBeIgnored()
    {
        $location = self::createShippingLocation();
        $product = new CalculatedProduct('A', 5,
            new LineItem('A', ProductProcessor::TYPE_PRODUCT, 5),
            new Price(1, 1, new CalculatedTaxCollection(), new TaxRuleCollection()),
            new DeliveryInformation(
                10, 0, 0, 0, 0,
                new DeliveryDate(new \DateTime('2012-01-01'), new \DateTime('2012-01-03')),
                new DeliveryDate(new \DateTime('2012-01-04'), new \DateTime('2012-01-05'))
            )
        );
        $voucher = new CalculatedVoucher(
            new LineItem('B', 'discount', 1),
            new Price(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection())
        );

        static::assertEquals(
            new DeliveryCollection([
                new Delivery(
                    new DeliveryPositionCollection([
                        DeliveryPosition::createByLineItemForInStockDate($product),
                    ]),
                    $product->getInStockDeliveryDate(),
                    new DeliveryMethod(1, '', '', 1, true, 1),
                    $location
                ),
            ]),
            $this->separator->addItemsToDeliveries(
                new DeliveryCollection(),
                new CalculatedLineItemCollection([$product, $voucher]),
                Generator::createContext(null, null, null, null, null, null, $location->getArea(), $location->getCountry(), $location->getState())
            )
        );
    }

    public function testPositionWithMoreQuantityThanStockWillBeSplitted()
    {
        $location = self::createShippingLocation();

        $product = new CalculatedProduct('A', 12,
            new LineItem('A', ProductProcessor::TYPE_PRODUCT, 10),
            new Price(1.19, 11.90, new CalculatedTaxCollection([new CalculatedTax(1.9, 19, 11.90)]), new TaxRuleCollection([new TaxRule(19)]), 10),
            new DeliveryInformation(5, 0, 0, 0, 0,
                new DeliveryDate(new \DateTime('2012-01-01'), new \DateTime('2012-01-03')),
                new DeliveryDate(new \DateTime('2012-01-04'), new \DateTime('2012-01-06'))
            )
        );

        static::assertEquals(
            new DeliveryCollection([
                new Delivery(
                    new DeliveryPositionCollection([
                        new DeliveryPosition('A', $product, 5,
                            new Price(1.19, 5.95, new CalculatedTaxCollection([new CalculatedTax(0.95, 19, 5.95)]), new TaxRuleCollection([new TaxRule(19)]), 5),
                            $product->getInStockDeliveryDate()
                        ),
                    ]),
                    $product->getInStockDeliveryDate(),
                    new DeliveryMethod(1, '', '', 1, true, 1),
                    $location
                ),
                new Delivery(
                    new DeliveryPositionCollection([
                        new DeliveryPosition('A', $product, 7,
                            new Price(1.19, 8.33, new CalculatedTaxCollection([new CalculatedTax(1.33, 19, 8.33)]), new TaxRuleCollection([new TaxRule(19)]), 7),
                            $product->getOutOfStockDeliveryDate()
                        ),
                    ]),
                    $product->getOutOfStockDeliveryDate(),
                    new DeliveryMethod(1, '', '', 1, true, 1),
                    $location
                ),
            ]),
            $this->separator->addItemsToDeliveries(
                new DeliveryCollection(),
                new CalculatedLineItemCollection([$product]),
                Generator::createContext(null, null, null, null, null, null, $location->getArea(), $location->getCountry(), $location->getState())
            )
        );
    }

    private static function createShippingLocation()
    {
        $address = new Address();
        $address->setState(new Country\State());

        $country = new Country();
        $country->setArea(new Country\Area());

        $address->setCountry($country);
        $address->getState()->setCountry($country);

        return ShippingLocation::createFromAddress($address);
    }
}
