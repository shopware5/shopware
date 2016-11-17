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

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 *
 * How rounding should be done:
 * Multiplying the quantity before rounding to two digits will create an error of (+- 0..0.4999) * quantity.
 * The error in this case with the previous implementation would be 37 cents: round((5.3 / 1.19 * 100), 2) = 445.37
 * round((5.3 / 1.19), 2) * 100 = 445.0
 */
class Shopware_Tests_Models_Order_TaxCalculationTest extends Enlight_Components_Test_TestCase
{
    /**
     * @var Shopware\Components\Model\ModelManager
     */
    protected $em;

    /**
     * @var Shopware\Models\User\Repository
     */
    protected $repo;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->em = Shopware()->Models();
        $this->repo = Shopware()->Models()->getRepository('Shopware\Models\Order\Order');
        Shopware()->Container()->set('Auth', new ZendAuthMock());
    }

    /**
     * Test that no tax is added to tax-free orders.
     */
    function testCalculationTaxFree() {
        $articlePrice = 5.3;
        $quantity = 100;
        $taxRate = "19";
        $taxFree = true;
        $net = true;
        $detailAmount = 1;

        $expectedInvoiceAmount = 530.0;
        $expectedInvoiceAmountNet = 530.0;

        $order = $this->createTaxTestOrder($articlePrice, $quantity, $taxRate, $detailAmount, $taxFree, $net);
        $order->calculateInvoiceAmount();

        $this->assertEquals($expectedInvoiceAmount, $order->getInvoiceAmount());
        $this->assertEquals($expectedInvoiceAmountNet, $order->getInvoiceAmountNet());
    }

    /**
     * Test basic tax calculation
     */
    function testCalculationWithTax() {
        $articlePrice = 5.3;
        $quantity = 100;
        $taxRate = "19";
        $taxFree = false;
        $net = false;
        $detailAmount = 1;

        $expectedInvoiceAmount = 530.0;
        $expectedInvoiceAmountNet = 445.0;

        $order = $this->createTaxTestOrder($articlePrice, $quantity, $taxRate, $detailAmount, $taxFree, $net);
        $order->calculateInvoiceAmount();

        $this->assertEquals($expectedInvoiceAmount, $order->getInvoiceAmount());
        $this->assertEquals($expectedInvoiceAmountNet, $order->getInvoiceAmountNet());
    }

    /**
     * Tests that values taxes are added to net orders.
     *      `net = true` refers to order details prices are net (values are stored as net)
     */
    function testCalculationWithTaxAndNet() {
        $articlePrice = 4.45;
        $quantity = 100;
        $taxRate = "19";
        $taxFree = false;
        $net = true;
        $detailAmount = 1;

        $expectedInvoiceAmount = 530.00;
        $expectedInvoiceAmountNet = 445.00;

        $order = $this->createTaxTestOrder($articlePrice, $quantity, $taxRate, $detailAmount, $taxFree, $net);
        $order->calculateInvoiceAmount();

        $this->assertEquals($expectedInvoiceAmount, $order->getInvoiceAmount());
        $this->assertEquals($expectedInvoiceAmountNet, $order->getInvoiceAmountNet());
    }

    /**
     * Test that ensures that we don't have to trust the number precision of the database value.
     */
    function testDatabaseNumberPrecisionIsIrrelevant() {
        // Net price from the database with superflous precision
        // to test if price is rounded before multiplied with quantity.
        $articlePrice = 4.45123;
        $quantity = 100;
        $taxRate = "19";
        $taxFree = false;
        $net = true;
        $detailAmount = 1;

        $expectedInvoiceAmount = 530.00;
        $expectedInvoiceAmountNet = 445.00;

        $order = $this->createTaxTestOrder($articlePrice, $quantity, $taxRate, $detailAmount, $taxFree, $net);
        $order->calculateInvoiceAmount();

        $this->assertEquals($expectedInvoiceAmount, $order->getInvoiceAmount());
        $this->assertEquals($expectedInvoiceAmountNet, $order->getInvoiceAmountNet());
    }

    function createTaxTestOrder($articlePrice, $quantity, $taxRate, $detailAmount, $taxFree, $net) {
        $order = $this->createOrder($taxFree, $net);

        $details = [];
        for ($i=0; $i<$detailAmount; $i++) {
            $detail = new \Shopware\Models\Order\Detail();
            $detail->setQuantity($quantity);
            $detail->setNumber("sw-dummy-" . $i);
            $detail->setPrice($articlePrice);
            $detail->setTaxRate($taxRate);
            $details[] = $detail;
        }

        $order->setDetails($details);
        return $order;
    }

    public function createOrder($taxFree, $net)
    {
        $paymentStatusOpen = $this->em->getReference('\Shopware\Models\Order\Status', 17);
        $orderStatusOpen   = $this->em->getReference('\Shopware\Models\Order\Status', 0);
        $paymentDebit      = $this->em->getReference('\Shopware\Models\Payment\Payment', 2);
        $dispatchDefault   = $this->em->getReference('\Shopware\Models\Dispatch\Dispatch', 9);
        $defaultShop       = $this->em->getReference('\Shopware\Models\Shop\Shop', 1);

        $partner = new \Shopware\Models\Partner\Partner();
        $partner->setCompany("Dummy");
        $partner->setIdCode("Dummy");
        $partner->setDate(new \DateTime());
        $partner->setContact('Dummy');
        $partner->setStreet('Dummy');
        $partner->setZipCode('Dummy');
        $partner->setCity('Dummy');
        $partner->setPhone('Dummy');
        $partner->setFax('Dummy');
        $partner->setCountryName('Dummy');
        $partner->setEmail('Dummy');
        $partner->setWeb('Dummy');
        $partner->setProfile('Dummy');

        $this->em->persist($partner);

        $order = new \Shopware\Models\Order\Order();
        $order->setNumber('abc');
        $order->setPaymentStatus($paymentStatusOpen);
        $order->setOrderStatus($orderStatusOpen);
        $order->setPayment($paymentDebit);
        $order->setDispatch($dispatchDefault);
        $order->setPartner($partner);
        $order->setShop($defaultShop);
        $order->setNet($net);
        $order->setTaxFree($taxFree);
        $order->setComment('Dummy');
        $order->setCustomerComment('Dummy');
        $order->setInternalComment('Dummy');
        $order->setTemporaryId(5);
        $order->setReferer('Dummy');
        $order->setTrackingCode("Dummy");
        $order->setLanguageIso("Dummy");
        $order->setCurrency("EUR");
        $order->setCurrencyFactor(1);
        $order->setRemoteAddress("127.0.0.1");
        return $order;
    }
}
