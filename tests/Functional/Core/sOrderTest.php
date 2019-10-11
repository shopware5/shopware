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

use Shopware\Models\Order\Order;

class sOrderTest extends PHPUnit\Framework\TestCase
{
    public static $sessionId;

    /**
     * @var sOrder
     */
    private $module;

    public static function setUpBeforeClass(): void
    {
        self::$sessionId = mt_rand(111111111, 999999999);
    }

    public function setUp(): void
    {
        $this->module = Shopware()->Modules()->Order();
        Shopware()->Session()->offsetSet('sessionId', self::$sessionId);
    }

    public function testGetOrderNumber()
    {
        $current = (int) Shopware()->Db()->fetchOne(
            "SELECT number FROM s_order_number WHERE name='invoice'"
        );

        $next = $this->module->sGetOrderNumber();

        static::assertEquals($next, $current + 1);
    }

    /**
     * @covers \sOrder::sendMail()
     * @ticket SW-8261
     */
    public function testSendMailPaymentData()
    {
        // First, block email sending, so we don't get an exception or spam someone.
        $config = $this->invokeMethod(
            $this->module,
            'getConfig'
        );

        $config->offsetSet('sendOrderMail', false);

        $this->invokeMethod(
            $this->module,
            'setConfig',
            [
                $config,
            ]
        );

        // Register the event listener, so that we test the value of "$context"
        Shopware()->Events()->addListener(
            'Shopware_Modules_Order_SendMail_Create',
            [$this, 'validatePaymentContextData']
        );

        $variables = [
            'additional' => [
                'payment' => Shopware()->Modules()->Admin()->sGetPaymentMeanById(
                    Shopware()->Db()->fetchRow('SELECT * FROM s_core_paymentmeans WHERE name LIKE "debit"')
                ),
            ],
        ];

        $this->module->sendMail($variables);
    }

    public function validatePaymentContextData(Enlight_Event_EventArgs $args)
    {
        $context = $args->get('context');
        static::assertIsArray($context['sPaymentTable']);
        static::assertCount(0, $context['sPaymentTable']);
    }

    public function testTransactionExistTrue()
    {
        $orderId = $this->createDummyOrder();
        $transaction = uniqid('TRANS-');
        Shopware()->Db()->query(
            'UPDATE s_order SET transactionID = :transaction WHERE id = :id',
            [
                ':id' => $orderId,
                ':transaction' => $transaction,
            ]
        );

        static::assertTrue($this->invokeMethod($this->module, 'isTransactionExist', [$transaction]));
    }

    public function testTransactionExistFalse()
    {
        static::assertFalse(
            $this->invokeMethod($this->module, 'isTransactionExist', [uniqid('TRANS-', true)])
        );
    }

    public function testTransactionExistInvalid()
    {
        static::assertFalse(
            $this->invokeMethod($this->module, 'isTransactionExist', ['ABC'])
        );
    }

    public function testRefreshOrderedVariant()
    {
        $detail = Shopware()->Db()->fetchRow('SELECT * FROM s_articles_details WHERE instock > 10 LIMIT 1');

        $this->invokeMethod($this->module, 'refreshOrderedVariant', [
            $detail['ordernumber'],
            10,
        ]);

        $updated = Shopware()->Db()->fetchRow('SELECT * FROM s_articles_details WHERE id = :id', [
            ':id' => $detail['id'],
        ]);

        static::assertEquals($updated['sales'], $detail['sales'] + 10);
        static::assertEquals($updated['instock'], $detail['instock'] - 10);
    }

    public function testGetOrderDetailsForMail()
    {
        $rows = [
            ['articlename' => 'Lorem &euro; ipsum'],
            ['articlename' => 'Lorem <br> ipsum'],
            ['articlename' => 'Lorem <br /> ipsum'],
            ['articlename' => ' Lorem &euro; ipsum '],
        ];

        $rows = $this->invokeMethod(
            $this->module,
            'getOrderDetailsForMail',
            [$rows]
        );

        static::assertTrue(
            strpos($rows[0]['articlename'], '€') > 0
        );
        static::assertTrue(
            strpos($rows[1]['articlename'], "\n") > 0
        );
        static::assertTrue(
            strpos($rows[2]['articlename'], "\n") > 0
        );
        static::assertTrue(
            strpos($rows[3]['articlename'], ' ') > 0
        );
    }

    public function testGetOrderForStatusMail()
    {
        $dummyOrderId = $this->createDummyOrder();
        $order = $this->invokeMethod(
            $this->module,
            'getOrderForStatusMail',
            [$dummyOrderId]
        );

        static::assertArrayNotHasKey('orderID', $order['attributes']);
        static::assertArrayNotHasKey('id', $order['attributes']);
        static::assertArrayHasKey('attribute1', $order['attributes']);
        static::assertArrayHasKey('attribute2', $order['attributes']);
        static::assertArrayHasKey('attribute3', $order['attributes']);
        static::assertArrayHasKey('attribute4', $order['attributes']);
        static::assertArrayHasKey('attribute5', $order['attributes']);
        static::assertArrayHasKey('attribute6', $order['attributes']);

        static::assertEquals('attribute1', $order['attributes']['attribute1']);
        static::assertEquals('attribute2', $order['attributes']['attribute2']);
        static::assertEquals('attribute3', $order['attributes']['attribute3']);
        static::assertEquals('attribute4', $order['attributes']['attribute4']);
        static::assertEquals('attribute5', $order['attributes']['attribute5']);
        static::assertEquals('attribute6', $order['attributes']['attribute6']);
    }

    public function testGetUserDataForMail()
    {
        $rawUserData = [
            'billingaddress' => [
                1 => "I'll &quot;walk&quot; the &lt;b&gt;dog&lt;/b&gt; now",
                2 => "I'll &quot;walk&quot; the &lt;b&gt;dog&lt;/b&gt; later",
                'attributes' => [
                    'foo' => "I'll &quot;walk&quot; the &lt;b&gt;dog&lt;/b&gt; now",
                    'bar' => "I'll &quot;walk&quot; the &lt;b&gt;dog&lt;/b&gt; later",
                ],
            ],
            'shippingaddress' => [
                1 => "I won't &quot;walk&quot; the &lt;b&gt;dog&lt;/b&gt; now",
                2 => "I won't &quot;walk&quot; the &lt;b&gt;dog&lt;/b&gt; later",
                'attributes' => [
                    'foo' => "I'll &quot;walk&quot; the &lt;b&gt;dog&lt;/b&gt; now",
                    'bar' => "I'll &quot;walk&quot; the &lt;b&gt;dog&lt;/b&gt; later",
                ],
            ],
            'country' => [
                1 => '&lt;span&gt;dog&lt;/span&gt;',
            ],
            'additional' => [
                'payment' => [
                    'description' => '&lt;span&gt;dog&lt;/span&gt;',
                ],
            ],
        ];

        $processedUserData = $this->invokeMethod(
            $this->module,
            'getUserDataForMail',
            [$rawUserData]
        );

        static::assertArrayHasKey('billingaddress', $processedUserData);
        static::assertArrayHasKey('attributes', $processedUserData['billingaddress']);
        static::assertArrayHasKey('shippingaddress', $processedUserData);
        static::assertArrayHasKey('attributes', $processedUserData['shippingaddress']);
        static::assertArrayHasKey('additional', $processedUserData);
        static::assertArrayHasKey('country', $processedUserData);

        static::assertEquals("I'll \"walk\" the <b>dog</b> now", $processedUserData['billingaddress'][1]);
        static::assertEquals("I'll \"walk\" the <b>dog</b> later", $processedUserData['billingaddress'][2]);

        static::assertEquals("I'll \"walk\" the <b>dog</b> now", $processedUserData['billingaddress']['attributes']['foo']);
        static::assertEquals("I'll \"walk\" the <b>dog</b> later", $processedUserData['billingaddress']['attributes']['bar']);

        static::assertEquals("I won't \"walk\" the <b>dog</b> now", $processedUserData['shippingaddress'][1]);
        static::assertEquals("I won't \"walk\" the <b>dog</b> later", $processedUserData['shippingaddress'][2]);

        static::assertEquals("I'll \"walk\" the <b>dog</b> now", $processedUserData['shippingaddress']['attributes']['foo']);
        static::assertEquals("I'll \"walk\" the <b>dog</b> later", $processedUserData['shippingaddress']['attributes']['bar']);

        static::assertEquals('<span>dog</span>', $processedUserData['country'][1]);
        static::assertEquals('<span>dog</span>', $processedUserData['additional']['payment']['description']);
    }

    public function testFormatBasketRow()
    {
        $rawBasketRowOne = [
            'articlename' => 'This is a very &lt;tag&gt;fancy&lt;/tag&gt; <br /> article name',
        ];

        $processedBasketRowOne = $this->invokeMethod(
            $this->module,
            'formatBasketRow',
            [$rawBasketRowOne]
        );

        static::assertArrayHasKey('articlename', $processedBasketRowOne);
        static::assertArrayHasKey('price', $processedBasketRowOne);
        static::assertArrayHasKey('esdarticle', $processedBasketRowOne);
        static::assertArrayHasKey('modus', $processedBasketRowOne);
        static::assertArrayHasKey('taxID', $processedBasketRowOne);

        static::assertEquals('This is a very fancy article name', $processedBasketRowOne['articlename']);
        static::assertEquals('0,00', $processedBasketRowOne['price']);
        static::assertEquals('0', $processedBasketRowOne['esdarticle']);
        static::assertEquals('0', $processedBasketRowOne['modus']);
        static::assertEquals('0', $processedBasketRowOne['taxID']);

        $rawBasketRowTwo = [
            'articlename' => 'This is a very &lt;tag&gt;fancy&lt;/tag&gt; <br /> article name',
            'price' => '1,00',
            'esdarticle' => '3',
            'modus' => '2',
            'taxID' => '4',
        ];

        $processedBasketRowTwo = $this->invokeMethod(
            $this->module,
            'formatBasketRow',
            [$rawBasketRowTwo]
        );

        static::assertArrayHasKey('articlename', $processedBasketRowTwo);
        static::assertArrayHasKey('price', $processedBasketRowTwo);
        static::assertArrayHasKey('esdarticle', $processedBasketRowTwo);
        static::assertArrayHasKey('modus', $processedBasketRowTwo);
        static::assertArrayHasKey('taxID', $processedBasketRowTwo);

        static::assertEquals('This is a very fancy article name', $processedBasketRowTwo['articlename']);
        static::assertEquals('1,00', $processedBasketRowTwo['price']);
        static::assertEquals('3', $processedBasketRowTwo['esdarticle']);
        static::assertEquals('2', $processedBasketRowTwo['modus']);
        static::assertEquals('4', $processedBasketRowTwo['taxID']);
    }

    public function testSSaveBillingAddress()
    {
        $user = $this->getRandomUser();
        $originalBillingAddress = $user['billingaddress'];
        $orderNumber = mt_rand(111111111, 999999999);

        static::assertEquals(1, $this->module->sSaveBillingAddress($originalBillingAddress, $orderNumber));

        $billing = Shopware()->Models()->getRepository(\Shopware\Models\Order\Billing::class)->findOneBy(['order' => $orderNumber]);

        static::assertEquals($originalBillingAddress['userID'], $billing->getCustomer()->getId());
        static::assertEquals($originalBillingAddress['company'], $billing->getCompany());
        static::assertEquals($originalBillingAddress['firstname'], $billing->getFirstName());
        static::assertEquals($originalBillingAddress['lastname'], $billing->getLastName());
        static::assertEquals($originalBillingAddress['street'], $billing->getStreet());

        $billingAttr = $billing->getAttribute();

        if ($billingAttr !== null) {
            static::assertEquals($originalBillingAddress['text1'], $billingAttr->getText1());
            static::assertEquals($originalBillingAddress['text2'], $billingAttr->getText2());
            static::assertEquals($originalBillingAddress['text3'], $billingAttr->getText3());
            static::assertEquals($originalBillingAddress['text4'], $billingAttr->getText4());
            static::assertEquals($originalBillingAddress['text5'], $billingAttr->getText5());
            static::assertEquals($originalBillingAddress['text6'], $billingAttr->getText6());
            Shopware()->Models()->remove($billingAttr);
        }
        Shopware()->Models()->remove($billing);
        Shopware()->Models()->flush();
    }

    public function testSaveShippingAddress()
    {
        $user = $this->getRandomUser();
        $originalBillingAddress = $user['shippingaddress'];

        $orderNumber = mt_rand(111111111, 999999999);

        static::assertEquals(1, $this->module->sSaveShippingAddress($originalBillingAddress, $orderNumber));

        $shipping = Shopware()->Models()->getRepository(\Shopware\Models\Order\Shipping::class)->findOneBy(['order' => $orderNumber]);

        static::assertEquals($originalBillingAddress['userID'], $shipping->getCustomer()->getId());
        static::assertEquals($originalBillingAddress['company'], $shipping->getCompany());
        static::assertEquals($originalBillingAddress['firstname'], $shipping->getFirstName());
        static::assertEquals($originalBillingAddress['lastname'], $shipping->getLastName());
        static::assertEquals($originalBillingAddress['street'], $shipping->getStreet());

        $shippingAttr = $shipping->getAttribute();

        if ($shippingAttr !== null) {
            static::assertEquals($originalBillingAddress['text1'], $shippingAttr->getText1());
            static::assertEquals($originalBillingAddress['text2'], $shippingAttr->getText2());
            static::assertEquals($originalBillingAddress['text3'], $shippingAttr->getText3());
            static::assertEquals($originalBillingAddress['text4'], $shippingAttr->getText4());
            static::assertEquals($originalBillingAddress['text5'], $shippingAttr->getText5());
            static::assertEquals($originalBillingAddress['text6'], $shippingAttr->getText6());
            Shopware()->Models()->remove($shippingAttr);
        }

        Shopware()->Models()->remove($shipping);
        Shopware()->Models()->flush();
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on
     * @param string $methodName Method name to call
     * @param array  $parameters array of parameters to pass into method
     *
     * @return mixed method return
     */
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function testSCreateTemporaryOrder()
    {
        $order = Shopware()->Models()->getRepository(Order::class)->findOneBy(['temporaryId' => self::$sessionId]);

        static::assertNull($order);

        $this->createOrder();

        $this->module->sCreateTemporaryOrder();

        $order = Shopware()->Models()->getRepository(Order::class)->findOneBy(['temporaryId' => self::$sessionId]);

        static::assertNotNull($order);
        static::assertNotNull($order->getAttribute());
        static::assertEquals('1113', $order->getInvoiceAmount());
        static::assertEquals('1113', $order->getInvoiceAmountNet());
        static::assertEquals('0', $order->getNumber());

        foreach ($order->getDetails() as $orderDetail) {
            static::assertNotNull($orderDetail->getAttribute());
        }
    }

    /**
     * @depends testSCreateTemporaryOrder
     */
    public function testSDeleteTemporaryOrder()
    {
        $order = Shopware()->Models()->createQueryBuilder()
            ->select(['orders'])
            ->from('Shopware\Models\Order\Order', 'orders')
            ->where('orders.temporaryId = :orderId')
            ->setParameter('orderId', self::$sessionId)
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        static::assertEquals('1113', $order['invoiceAmount']);
        static::assertEquals('1113', $order['invoiceAmountNet']);
        static::assertEquals('0', $order['number']);

        $this->module->sDeleteTemporaryOrder();

        $order = Shopware()->Models()->getRepository(Order::class)->findOneBy(['temporaryId' => self::$sessionId]);

        static::assertNull($order);
    }

    public function testHandleESDOrder()
    {
        $config = $this->invokeMethod(
            $this->module,
            'getConfig'
        );

        // force 0 minimum serials, so we can test the default case
        $config->offsetSet('esdMinSerials', 0);

        $this->invokeMethod(
            $this->module,
            'setConfig',
            [
                $config,
            ]
        );

        $basketRows = $this->getBasketRows();

        foreach ($basketRows['content'] as $basketRow) {
            $esdArticle = $this->invokeMethod(
                $this->module,
                'getVariantEsd',
                [$basketRow['ordernumber']]
            );

            $availableSerials = $this->invokeMethod(
                $this->module,
                'getAvailableSerialsOfEsd',
                [$esdArticle['id']]
            );

            $basketRow = $this->module->handleESDOrder($basketRow, 1234, 4567);

            if (!$esdArticle['id']) {
                // Not ESD, ensure nothing happened
                static::assertFalse(Shopware()->Db()->fetchRow(
                    'SELECT id FROM s_order_esd WHERE orderID = ? AND orderdetailsID = ?',
                    [1234, 4567]
                ));
            } elseif (!$esdArticle['serials']) {
                // ESD without serial
                static::assertFalse(Shopware()->Db()->fetchRow(
                    'SELECT id FROM s_order_esd WHERE orderID = ? AND orderdetailsID = ? AND serialID = 0',
                    [1234, 4567]
                ));
            } elseif (count($availableSerials) < $basketRow['quantity']) {
                // ESD with serial but not enough available, ensure nothing is done
                static::assertFalse(Shopware()->Db()->fetchRow(
                    'SELECT id FROM s_order_esd WHERE orderID = ? AND orderdetailsID = ?',
                    [1234, 4567]
                ));
            } else {
                // ESD with serial and enough available
                // Assert serial is used
                static::assertEquals($basketRow['quantity'], Shopware()->Db()->fetchRow(
                    'SELECT id FROM s_order_esd WHERE orderID = ? AND orderdetailsID = ?',
                    [1234, 4567]
                ));
                static::assertCount($basketRow['quantity'], $basketRow['assignedSerials']);
            }

            // Restore previous state
            Shopware()->Db()->query(
                'DELETE FROM s_order_esd WHERE orderID = ? AND orderdetailsID = ?',
                [1234, 4567]
            );
        }
    }

    public function testSSaveOrder()
    {
        $this->createOrder();

        $orderNumber = $this->module->sSaveOrder();

        $order = Shopware()->Models()->getRepository(Order::class)->findOneBy(['number' => $orderNumber]);

        static::assertEquals('1113', $order->getInvoiceAmount());
        static::assertEquals('1113', $order->getInvoiceAmountNet());
        static::assertNotEquals('0', $order->getNumber());

        Shopware()->Models()->remove($order);
        Shopware()->Models()->flush();
    }

    public function testSTellFriend()
    {
        $user = $this->getRandomUser();

        $this->module->sUserData = $user;

        $previousTellAFriendCount = Shopware()->Db()->fetchAll(
            'SELECT * FROM s_emarketing_tellafriend WHERE recipient=?',
            [$user['additional']['user']['email']]
        );

        static::assertCount(0, $previousTellAFriendCount);

        Shopware()->Db()->insert('s_emarketing_tellafriend', [
            'recipient' => $user['additional']['user']['email'],
            'confirmed' => 0,
            'sender' => $user['additional']['user']['id'],
        ]);

        $this->module->sTellFriend();

        $afterTellAFriendCount = Shopware()->Db()->fetchAll(
            'SELECT * FROM s_emarketing_tellafriend WHERE confirmed=1 AND recipient=?',
            [$user['additional']['user']['email']]
        );

        static::assertCount(1, $afterTellAFriendCount);

        Shopware()->Db()->query(
            'DELETE FROM s_emarketing_tellafriend WHERE recipient=?',
            [$user['additional']['user']['email']]
        );

        $cleanTellAFriendCount = Shopware()->Db()->fetchAll(
            'SELECT * FROM s_emarketing_tellafriend WHERE recipient=?',
            [$user['additional']['user']['email']]
        );

        static::assertCount(0, $cleanTellAFriendCount);
    }

    public function testSetPaymentStatus()
    {
        $this->createOrder();

        $orderNumber = $this->module->sSaveOrder();

        $order = Shopware()->Models()->getRepository(Order::class)->findOneBy(['number' => $orderNumber]);

        $orderId = $order->getId();

        $orderHistory = Shopware()->Db()->fetchAll(
            'SELECT * FROM s_order_history WHERE orderID=?',
            [$orderId]
        );

        static::assertCount(0, $orderHistory);
        static::assertEquals(17, $order->getPaymentStatus()->getId());

        $this->module->setPaymentStatus($orderId, 10, false, 'random payment status comment');

        Shopware()->Models()->refresh($order);

        $orderHistory = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_order_history WHERE orderID=? LIMIT 1',
            [$orderId]
        );

        static::assertNotEmpty($orderHistory);
        static::assertEquals(10, $order->getPaymentStatus()->getId());
        static::assertEquals(17, $orderHistory['previous_payment_status_id']);
        static::assertEquals(10, $orderHistory['payment_status_id']);
        static::assertEquals('random payment status comment', $orderHistory['comment']);

        Shopware()->Models()->remove($order);
        Shopware()->Models()->flush();
    }

    public function testSetOrderStatus()
    {
        $this->createOrder();

        $orderNumber = $this->module->sSaveOrder();

        $order = Shopware()->Models()->getRepository(Order::class)->findOneBy(['number' => $orderNumber]);

        $orderId = $order->getId();

        $orderHistory = Shopware()->Db()->fetchAll(
            'SELECT * FROM s_order_history WHERE orderID=?',
            [$orderId]
        );

        static::assertCount(0, $orderHistory);
        static::assertEquals(0, $order->getOrderStatus()->getId());

        $this->module->setOrderStatus($orderId, 10, false, 'random order status comment');

        Shopware()->Models()->refresh($order);

        $orderHistory = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_order_history WHERE orderID=? LIMIT 1',
            [$orderId]
        );

        static::assertNotEmpty($orderHistory);
        static::assertEquals(10, $order->getOrderStatus()->getId());
        static::assertEquals(0, $orderHistory['previous_order_status_id']);
        static::assertEquals(10, $orderHistory['order_status_id']);
        static::assertEquals('random order status comment', $orderHistory['comment']);

        Shopware()->Models()->remove($order);
        Shopware()->Models()->flush();
    }

    public function testEKOrder()
    {
        $this->module->sUserData = $this->getDummyUserDataForBasket();
        $this->module->sNet = $this->module->sUserData['additional']['charge_vat'];

        $this->module->sAmount = 105.83;
        $this->module->sAmountWithTax = 105.83;
        $this->module->sAmountNet = 88.936134453780994;

        $this->module->sShippingcosts = 3.8999999999999999;
        $this->module->sShippingcostsNumeric = 3.8999999999999999;
        $this->module->sShippingcostsNumericNet = 3.2799999999999998;
        $this->module->dispatchId = 9;
        $this->module->sBasketData = $this->getBasketRows();
        $this->module->sSaveOrder();
    }

    protected function createOrder()
    {
        $articles = $this->getRandomArticles();

        $articlesAmount = $this->getArticlesAmount($articles);
        $highestTax = $this->getHighestArticlesTax($articles);

        $user = $this->getRandomUser();
        $dispatch = $this->getRandomDispatch();
        $costs = $this->getShippingCosts($dispatch['id'], $articlesAmount);

        $this->module->sUserData = $user;
        $this->module->sNet = $user['additional']['charge_vat'];
        $this->module->dispatchId = $dispatch['id'];
        $this->module->sComment = '';

        $this->module->sShippingcosts = $costs['value'];
        $this->module->sShippingcostsNumeric = $costs['value'];
        $this->module->sShippingcostsNumericNet = $costs['value'] * 100 / (100 + $highestTax);

        $this->module->sBasketData = [
            'content' => $articles,
            'AmountNumeric' => 1116,
            'AmountWithTaxNumeric' => 1,
            'AmountNetNumeric' => 1113,
        ];
    }

    protected function createDummyOrder()
    {
        $number = 'SW-' . uniqid(mt_rand(), true);
        Shopware()->Db()->insert('s_order', [
            'id' => null,
            'userID' => 1,
            'ordernumber' => $number,
            'invoice_amount' => 100,
            'invoice_amount_net' => 100 / 1.19,
            'invoice_shipping' => 3.9,
            'invoice_shipping_net' => 3.28,
            'ordertime' => new Zend_Db_Expr('NOW()'),
            'status' => 0,
            'cleared' => 10,
            'paymentID' => 4,
            'net' => 0,
            'taxfree' => 0,
        ]);
        $id = Shopware()->Db()->lastInsertId('s_order');

        Shopware()->Db()->insert('s_order_attributes', [
            'orderID' => $id,
            'attribute1' => 'attribute1',
            'attribute2' => 'attribute2',
            'attribute3' => 'attribute3',
            'attribute4' => 'attribute4',
            'attribute5' => 'attribute5',
            'attribute6' => 'attribute6',
        ]);

        return $id;
    }

    protected function createDummyPosition($orderId, $number = null)
    {
        if ($number) {
            $article = $this->getArticleResource()->getOneByNumber($number);
        } else {
            $id = Shopware()->Db()->fetchOne('SELECT id FROM s_articles LIMIT 1');
            $article = $this->getArticleResource()->getOne($id);
        }

        $mainDetail = $article->getMainDetail();

        /** @var \Shopware\Models\Article\Price $price */
        $price = $mainDetail->getPrices()->first();

        $quantity = mt_rand(1, 10);

        Shopware()->Db()->insert('s_order_details', [
            'orderID' => $orderId,
            'ordernumber' => '0',
            'articleID' => $article->getId(),
            'articleordernumber' => $mainDetail->getNumber(),
            'price' => $quantity * $price->getPrice(),
            'quantity' => $quantity,
            'name' => $article->getName(),
            'modus' => '0',
            'taxID' => $article->getTax()->getId(),
            'tax_rate' => $article->getTax()->getTax(),
        ]);

        $detailId = Shopware()->Db()->lastInsertId('s_order_details');

        Shopware()->Db()->insert('s_order_details_attributes', [
            'detailID' => $detailId,
            'attribute1' => uniqid('SW-', true),
            'attribute2' => uniqid('SW-', true),
            'attribute3' => uniqid('SW-', true),
            'attribute4' => uniqid('SW-', true),
            'attribute5' => uniqid('SW-', true),
            'attribute6' => uniqid('SW-', true),
        ]);

        return $detailId;
    }

    /**
     * @return \Shopware\Components\Api\Resource\Article
     */
    private function getArticleResource()
    {
        $resource = new \Shopware\Components\Api\Resource\Article();
        $resource->setManager(Shopware()->Models());
        $resource->setResultMode(1);

        return $resource;
    }

    private function getHighestArticlesTax($articles)
    {
        $articleTax = array_map(function ($article) {
            return $article['tax_rate'];
        }, $articles);

        return max($articleTax);
    }

    private function getArticlesAmount($articles)
    {
        $articleAmount = array_map(function ($article) {
            return $article['priceNumeric'];
        }, $articles);

        return array_sum($articleAmount);
    }

    private function getShippingCosts($id, $amount)
    {
        return Shopware()->Db()->fetchRow(
            'SELECT s.*
            FROM  s_premium_shippingcosts s
            WHERE s.dispatchID = :id
            AND   s.from <= :amount
            ORDER BY s.from DESC
            LIMIT 1',
            [':id' => $id, ':amount' => $amount]
        );
    }

    private function getRandomDispatch()
    {
        return Shopware()->Db()->fetchRow(
            'SELECT * FROM s_premium_dispatch LIMIT 1'
        );
    }

    private function getRandomArticles()
    {
        $details = Shopware()->Db()->fetchAll(
            "SELECT
                a.id as id,
                a.name      as articlename,
                a.id        as articleID,
                0           as modus,
                1           as quantity,
                a.laststock as laststock,
                a.taxID     as taxID,
                t.tax       as tax_rate,
                0           as esdarticle,
                (p.price * (100 + t.tax) / 100) as price,
                (p.price * (100 + t.tax) / 100) as priceNumeric,
                p.price as priceNet,
                d.ordernumber
             FROM s_articles a
               INNER JOIN s_articles_details d
                 ON d.id = a.main_detail_id
               INNER JOIN s_articles_prices p
                 ON p.articledetailsID = d.id
                 AND p.from = 1
                 AND p.pricegroup = 'EK'
               INNER JOIN s_core_tax t
                 ON t.id = a.taxID
             WHERE a.id = 162
             LIMIT 2
            "
        );

        return $details;
    }

    private function getRandomUser()
    {
        $user = Shopware()->Db()->fetchRow('SELECT * FROM s_user WHERE id = 1 LIMIT 1');

        $billing = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_user_addresses WHERE user_id = :id',
            [':id' => $user['id']]
        );

        $billing = $this->convertToLegacyAddressArray($billing);

        $shipping = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_user_addresses WHERE user_id = :id',
            [':id' => $user['id']]
        );

        $shipping = $this->convertToLegacyAddressArray($shipping);

        $country = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_core_countries WHERE id = :id',
            [':id' => $billing['countryID']]
        );
        $state = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_core_countries_states WHERE id = :id',
            [':id' => $billing['stateID']]
        );
        $countryShipping = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_core_countries WHERE id = :id',
            [':id' => $shipping['countryID']]
        );
        $payment = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_core_paymentmeans WHERE id = :id',
            [':id' => $user['paymentID']]
        );
        $customerGroup = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_core_customergroups WHERE groupkey = :key',
            [':key' => $user['customergroup']]
        );

        $taxFree = (bool) ($countryShipping['taxfree']);
        if ($countryShipping['taxfree_ustid']) {
            if ($countryShipping['id'] == $country['id'] && $billing['ustid']) {
                $taxFree = true;
            }
        }

        if ($taxFree) {
            $customerGroup['tax'] = 0;
        }

        $this->module->sSYSTEM->sUSERGROUPDATA = $customerGroup;
        Shopware()->Session()->sUserGroupData = $customerGroup;

        return [
            'user' => $user,
            'billingaddress' => $billing,
            'shippingaddress' => $shipping,
            'customerGroup' => $customerGroup,
            'additional' => [
                'country' => $country,
                'state' => $state,
                'user' => $user,
                'countryShipping' => $countryShipping,
                'payment' => $payment,
                'charge_vat' => !$taxFree,
            ],
        ];
    }

    /**
     * Converts an address to the array key structure of a legacy billing or shipping address
     *
     * @param array $address
     *
     * @return array
     */
    private function convertToLegacyAddressArray($address)
    {
        $output = array_merge($address, [
            'userID' => $address['user_id'],
            'countryID' => $address['country_id'],
            'stateID' => $address['state_id'],
        ]);

        return $output;
    }

    private function getBasketRows()
    {
        return [
            'AmountNumeric' => 105.83,
            'AmountNetNumeric' => 88.936134453780994,
            'AmountWithTaxNumeric' => 0,
            'sCurrencyId' => 1,
            'sCurrencyFactor' => 10,
            'content' => [
                [
                    'id' => 1,
                    'articlename' => 'Strandtuch "Ibiza"',
                    'articleID' => '178',
                    'articleDetailsID' => 407,
                    'modus' => '0',
                    'tax_rate' => '19',
                    'price' => '19,95',
                    'priceNumeric' => '19.95',
                    'ordernumber' => 'SW10178',
                    'quantity' => '1',
                    'taxID' => '1',
                    'esdarticle' => '0',
                    'laststock' => '0',
                    'priceNet' => 16.764705882353,
                ],
                [
                    'id' => 2,
                    'articlename' => 'Strandtuch Sunny',
                    'articleID' => '175',
                    'articleDetailsID' => 404,
                    'ordernumber' => 'SW10175',
                    'quantity' => '1',
                    'price' => '59,99',
                    'tax_rate' => '19',
                    'esdarticle' => '0',
                    'taxID' => '1',
                    'laststock' => '1',
                    'priceNumeric' => '59.99',
                    'priceNet' => 50.411764705882,
                ],
                [
                    'id' => 3,
                    'articlename' => 'Sommer-Sandale Pink 36',
                    'articleID' => '162',
                    'articleDetailsID' => 380,
                    'ordernumber' => 'SW10162.1',
                    'quantity' => '1',
                    'price' => '23,99',
                    'tax_rate' => '19',
                    'esdarticle' => '0',
                    'taxID' => '1',
                    'laststock' => '1',
                    'priceNumeric' => '23.99',
                    'priceNet' => 20.159663865546,
                ],
                [
                    'id' => 4,
                    'articlename' => 'ESD Download Artikel',
                    'articleID' => '197',
                    'articleDetailsID' => 437,
                    'ordernumber' => 'SW10196',
                    'quantity' => '3',
                    'price' => '29,99',
                    'tax_rate' => '19',
                    'esdarticle' => '1',
                    'taxID' => '1',
                    'laststock' => '1',
                    'priceNumeric' => '29.99',
                    'priceNet' => 25, 201680672,
                ],
                [
                    'id' => 5,
                    'articlename' => 'Warenkorbrabatt',
                    'articleID' => '0',
                    'articleDetailsID' => 0,
                    'ordernumber' => 'SHIPPINGDISCOUNT',
                    'quantity' => '1',
                    'price' => '-2,00',
                    'tax_rate' => '19',
                    'esdarticle' => '0',
                    'taxID' => null,
                    'laststock' => null,
                    'priceNumeric' => '-2',
                    'priceNet' => 1.68067226891,
                ],
            ],
        ];
    }

    private function getDummyUserDataForBasket()
    {
        return [
            'billingaddress' => [
                'id' => '1',
                'userID' => '1',
                'company' => '',
                'phone' => '',
                'department' => '',
                'salutation' => 'mr',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'street' => 'Musterstr. 55',
                'zipcode' => '55555',
                'city' => 'Musterhausen',
                'countryID' => '2',
                'stateID' => '3',
                'ustid' => '',
            ],
            'additional' => [
                'country' => [
                    'id' => '2',
                    'countryname' => 'Deutschland',
                    'countryiso' => 'DE',
                    'areaID' => '1',
                    'taxfree' => '0',
                    'taxfree_ustid' => '0',
                    'taxfree_ustid_checked' => '0',
                    'active' => '1',
                    'iso3' => 'DEU',
                ],
                'state' => [
                    'id' => '3',
                    'countryID' => '2',
                    'name' => 'Nordrhein-Westfalen',
                ],
                'user' => [
                    'id' => '1',
                    'email' => 'test@example.com',
                    'customernumber' => '20001',
                    'active' => '1',
                    'accountmode' => '0',
                    'confirmationkey' => '',
                    'paymentID' => '5',
                    'firstlogin' => '2011-11-23',
                    'lastlogin' => '2014-01-13 16:56:22',
                    'sessionID' => 'l45nd0gmndihu1t29in7jeq346',
                    'newsletter' => 0,
                    'validation' => '',
                    'affiliate' => '0',
                    'customergroup' => 'EK',
                    'paymentpreset' => '0',
                    'language' => '1',
                    'subshopID' => '1',
                    'referer' => '',
                    'pricegroupID' => null,
                    'internalcomment' => '',
                    'failedlogins' => '0',
                    'lockeduntil' => null,
                ],
                'countryShipping' => [
                    'id' => '2',
                    'countryname' => 'Deutschland',
                    'countryiso' => 'DE',
                    'areaID' => '1',
                    'countryen' => 'GERMANY',
                    'position' => '1',
                    'notice' => '',
                    'taxfree' => '0',
                    'taxfree_ustid' => '0',
                    'taxfree_ustid_checked' => '0',
                    'active' => '1',
                    'iso3' => 'DEU',
                    'display_state_in_registration' => '0',
                    'force_state_in_registration' => '0',
                    'countryarea' => 'deutschland',
                ],
                'stateShipping' => [],
                'payment' => [
                    'id' => '5',
                    'name' => 'prepayment',
                    'description' => 'Vorkasse',
                    'template' => 'prepayment.tpl',
                    'class' => 'prepayment.php',
                    'table' => '',
                    'hide' => '0',
                    'additionaldescription' => 'Sie zahlen einfach vorab und erhalten die Ware bequem und günstig bei Zahlungseingang nach Hause geliefert.',
                    'debit_percent' => '0',
                    'surcharge' => '0',
                    'surchargestring' => '',
                    'position' => '1',
                    'active' => '1',
                    'esdactive' => '0',
                    'embediframe' => '',
                    'hideprospect' => '0',
                    'action' => null,
                    'pluginID' => null,
                    'source' => null,
                ],
                'charge_vat' => true,
                'show_net' => true,
            ],
            'shippingaddress' => [
                'id' => '2',
                'userID' => '1',
                'company' => 'shopware AG',
                'department' => '',
                'salutation' => 'mr',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'street' => 'Mustermannstraße 92',
                'zipcode' => '48624',
                'city' => 'Schöppingen',
                'countryID' => '2',
                'stateID' => null,
            ],
        ];
    }
}
