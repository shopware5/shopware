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

namespace Shopware\Tests\Functional\Components\Api;

use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Resource\Order;
use Shopware\Components\Api\Resource\Resource;
use Shopware\Models\Order\Detail;

class OrderTest extends TestCase
{
    /**
     * @var Order
     */
    protected $resource;

    /**
     * @var array
     */
    private $order;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->order = Shopware()->Db()->fetchRow('SELECT * FROM `s_order` ORDER BY id DESC LIMIT 1');
        Shopware()->Container()->get('dbal_connection')->beginTransaction();
    }

    protected function tearDown(): void
    {
        Shopware()->Container()->get('dbal_connection')->rollBack();
    }

    public function createResource(): Order
    {
        return new Order();
    }

    public function testGetOneShouldBeSuccessful(): void
    {
        $order = $this->resource->getOne($this->order['id']);
        static::assertEquals($this->order['id'], $order['id']);
    }

    public function testGetOneByNumberWithInvalidNumberShouldThrowNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->resource->getOneByNumber(9999999);
    }

    public function testGetOneByNumberWithMissinNumberShouldThrowParameterMissingException(): void
    {
        $this->expectException(ParameterMissingException::class);
        $this->resource->getOneByNumber('');
    }

    public function testGetOneByNumberShouldBeSuccessful(): void
    {
        $order = $this->resource->getOneByNumber($this->order['ordernumber']);
        static::assertEquals($this->order['ordernumber'], $order['number']);
    }

    public function testGetOneShouldBeAbleToReturnObject(): void
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $order = $this->resource->getOne($this->order['id']);

        static::assertInstanceOf(\Shopware\Models\Order\Order::class, $order);
        static::assertEquals($this->order['id'], $order->getId());
    }

    public function testGetListShouldBeSuccessful(): void
    {
        $result = $this->resource->getList();

        static::assertIsArray($result);

        static::assertArrayHasKey('total', $result);
        static::assertGreaterThanOrEqual(1, $result['total']);

        static::assertArrayHasKey('data', $result);
        static::assertIsArray($result['data']);

        static::assertGreaterThanOrEqual(1, count($result['data']));

        $firstOrder = $result['data'][0];

        $expectedKeys = [
            'id',
            'number',
            'customerId',
            'paymentId',
            'dispatchId',
            'partnerId',
            'shopId',
            'invoiceAmount',
            'invoiceAmountNet',
            'invoiceShipping',
            'invoiceShippingNet',
            'orderTime',
            'transactionId',
            'comment',
            'customerComment',
            'internalComment',
            'net',
            'taxFree',
            'temporaryId',
            'referer',
            'clearedDate',
            'trackingCode',
            'languageIso',
            'currency',
            'currencyFactor',
            'remoteAddress',
            'deviceType',
            'customer',
            'paymentStatusId',
            'orderStatusId',
        ];

        foreach ($expectedKeys as $expectedKey) {
            static::assertArrayHasKey($expectedKey, $firstOrder);
        }

        static::assertIsArray($firstOrder['customer']);
        static::assertArrayHasKey('id', $firstOrder['customer']);
        static::assertArrayHasKey('email', $firstOrder['customer']);
    }

    public function testGetListShouldBeAbleToReturnObjects(): void
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $result = $this->resource->getList();

        static::assertArrayHasKey('data', $result);
        static::assertArrayHasKey('total', $result);

        static::assertGreaterThanOrEqual(1, $result['total']);
        static::assertGreaterThanOrEqual(1, $result['data']);

        static::assertInstanceOf(\Shopware\Models\Order\Order::class, $result['data'][0]);
    }

    public function testUpdateWithInvalidIdShouldThrowNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->resource->update(9999999, []);
    }

    public function testUpdateWithMissingIdShouldThrowParameterMissingException(): void
    {
        $this->expectException(ParameterMissingException::class);
        $this->resource->update('', []);
    }

    public function testCreateOrderFailsOnMissingShippingAddress(): void
    {
        $this->expectException(ParameterMissingException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        unset($order['shipping']);

        $this->resource->create($order);
    }

    public function testCreateOrderFailsOnMissingBillingAddress(): void
    {
        $this->expectException(ParameterMissingException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        unset($order['billing']);

        $this->resource->create($order);
    }

    public function testCreateOrderFailsOnMissingCustomerId(): void
    {
        $this->expectException(ParameterMissingException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        unset($order['customerId']);

        $this->resource->create($order);
    }

    public function testCreateOrderFailsOnUnknownCustomerId(): void
    {
        $this->expectException(NotFoundException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['customerId'] = 4711;

        $this->resource->create($order);
    }

    public function testCreateOrderFailsOnUnknownCountryIdInBillingAddress(): void
    {
        $this->expectException(NotFoundException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['billing']['countryId'] = 4711;

        $this->resource->create($order);
    }

    public function testCreateOrderFailsOnUnknownStateIdInBillingAddress(): void
    {
        $this->expectException(NotFoundException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['billing']['stateId'] = 4711;

        $this->resource->create($order);
    }

    public function testCreateOrderFailsOnUnknownCountryIdInShippingAddress(): void
    {
        $this->expectException(NotFoundException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['shipping']['countryId'] = 4711;

        $this->resource->create($order);
    }

    public function testCreateOrderFailsOnUnknownStateIdInShipppingAddress(): void
    {
        $this->expectException(NotFoundException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['shipping']['stateId'] = 4711;

        $this->resource->create($order);
    }

    public function testCreateOrderFailsOnUnknownStateIdInDetails(): void
    {
        $this->expectException(NotFoundException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['details'][0]['statusId'] = 4711;

        $this->resource->create($order);
    }

    public function testCreateOrderOnEmptyStateIdInBillingAddress(): void
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);
        unset($order['billing']['stateId']);

        $newOrder = $this->resource->create($order);

        static::assertEquals($newOrder->getBilling()->getState(), null);
    }

    public function testCreateOrderOnEmptyStateIdInShippingAddress(): void
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);
        unset($order['shipping']['stateId']);

        $newOrder = $this->resource->create($order);

        static::assertEquals($newOrder->getShipping()->getState(), null);
    }

    public function testCreateOrderOnInvalidStateId(): void
    {
        $this->expectException(NotFoundException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);
        $order['shipping']['stateId'] = 9999;

        $this->resource->create($order);
    }

    public function testCreateOrderFailsOnUnknownTaxIdInDetails(): void
    {
        $this->expectException(NotFoundException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['details'][0]['taxId'] = 4711;

        $this->resource->create($order);
    }

    public function testCreateOrderFailsOnMissingOrderStatusId(): void
    {
        $this->expectException(ParameterMissingException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        unset($order['orderStatusId']);

        $this->resource->create($order);
    }

    public function testCreateOrderFailsOnUnknownOrderStatusId(): void
    {
        $this->expectException(NotFoundException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['orderStatusId'] = 4711;

        $this->resource->create($order);
    }

    public function testCreateOrderFailsOnMissingPaymentId(): void
    {
        $this->expectException(ParameterMissingException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        unset($order['paymentId']);

        $this->resource->create($order);
    }

    public function testCreateOrderFailsOnUnknownPaymentId(): void
    {
        $this->expectException(NotFoundException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['paymentId'] = 4711;

        $this->resource->create($order);
    }

    public function testCreateOrderFailsOnMissingPaymentStatusId(): void
    {
        $this->expectException(ParameterMissingException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        unset($order['paymentStatusId']);

        $this->resource->create($order);
    }

    public function testCreateOrderFailsOnUnknownPaymentStatusId(): void
    {
        $this->expectException(NotFoundException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['paymentStatusId'] = 4711;

        $this->resource->create($order);
    }

    public function testCreateOrderFailsOnMissingDispatchId(): void
    {
        $this->expectException(ParameterMissingException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        unset($order['dispatchId']);

        $this->resource->create($order);
    }

    public function testCreateOrderFailsOnUnknownDispatchId(): void
    {
        $this->expectException(NotFoundException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['dispatchId'] = 4711;

        $this->resource->create($order);
    }

    public function testCreateOrderFailsOnMissingShopId(): void
    {
        $this->expectException(ParameterMissingException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        unset($order['shopId']);

        $this->resource->create($order);
    }

    public function testCreateOrderFailsOnUnknownShopId(): void
    {
        $this->expectException(NotFoundException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['shopId'] = 4711;

        $this->resource->create($order);
    }

    public function testCreateOrderByCopy(): void
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $oldOrderNumber = (int) $order['number'];

        $order = $this->filterOrderId($order);

        $newOrder = $this->resource->create($order);

        // Checking some fields in all models
        static::assertGreaterThan($this->order['id'], $newOrder->getId());
        static::assertNotNull($newOrder->getNumber());
        static::assertNotSame((int) $newOrder->getNumber(), $oldOrderNumber);
        static::assertEquals($newOrder->getCustomer()->getId(), $order['customer']['id']);
        static::assertEquals($newOrder->getInvoiceAmount(), $order['invoiceAmount']);
        static::assertEquals($newOrder->getBilling()->getCity(), $order['billing']['city']);
        static::assertEquals($newOrder->getShipping()->getCity(), $order['shipping']['city']);
        static::assertCount(count($newOrder->getDetails()), $order['details']);
        static::assertEquals($newOrder->getDetails()[0]->getArticleName(), $order['details'][0]['articleName']);
        static::assertEquals((int) $newOrder->getDetails()[0]->getNumber(), ($oldOrderNumber + 1));
    }

    public function testUpdateOrderPositionStatusShouldBeSuccessful(): void
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        // Update the order details of that order
        $updateArray = [];
        foreach ($order['details'] as $detail) {
            $updateArray['details'][$detail['id']] = ['id' => $detail['id'], 'status' => random_int(0, 3), 'shipped' => 1];
        }
        $this->resource->update($this->order['id'], $updateArray);

        // Reload the order and check the result
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);
        foreach ($order['details'] as $detail) {
            $currentId = $detail['id'];

            static::assertEquals($updateArray['details'][$currentId]['status'], $detail['statusId']);
            static::assertEquals($updateArray['details'][$currentId]['shipped'], $detail['shipped']);
        }
    }

    public function testCreateOrderWithDocuments(): void
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);
        $order['documents'] = [
            [
                'date' => new \DateTime(),
                'amount' => 47.11,
                'typeId' => 1,
                'customerId' => 1,
                'documentId' => 1,
                'hash' => 4711,
            ],
            [
                'date' => new \DateTime(),
                'amount' => 47.12,
                'typeId' => 1,
                'customerId' => 1,
                'documentId' => 1,
                'hash' => 4711,
            ],
        ];

        $order = $this->resource->create($order);

        static::assertCount(2, $order->getDocuments());
    }

    public function testSingleOrderDetailUpdate(): void
    {
        // Get existing order with at least two order items
        $orderId = (int) Shopware()->Db()
            ->query('SELECT `orderID`, COUNT(1) `counter` FROM `s_order_details` GROUP BY `orderId` HAVING `counter` > 1 ORDER BY id DESC LIMIT 1')
            ->fetchColumn();
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $order = $this->resource->getOne($orderId);

        /** @var Detail $firstDetail */
        $firstDetail = $order->getDetails()->first();
        $newShipped = $firstDetail->getShipped() + 1;
        $orderCount = $order->getDetails()->count();

        $order = $this->resource->update($order->getId(), [
            'details' => [
                [
                    'id' => $firstDetail->getId(),
                    'shipped' => $newShipped,
                ],
            ],
            '__options_details' => [
                'replace' => false,
            ],
        ]);

        /** @var Detail|null $firstDetail */
        $firstDetail = $order->getDetails()->filter(static function (Detail $detail) use ($firstDetail): bool {
            return $detail->getId() === $firstDetail->getId();
        })->first();

        static::assertEquals($orderCount, $order->getDetails()->count());
        static::assertNotNull($firstDetail);
        static::assertEquals($newShipped, $firstDetail->getShipped());
    }

    public function testSingleOrderDetailUpdateToDeleteOtherItems(): void
    {
        // Get existing order with at least two order items
        $orderId = (int) Shopware()->Db()
            ->query('SELECT `orderID`, COUNT(1) `counter` FROM `s_order_details` GROUP BY `orderId` HAVING `counter` > 1 ORDER BY id DESC LIMIT 1')
            ->fetchColumn();
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $order = $this->resource->getOne($orderId);

        /** @var Detail $firstDetail */
        $firstDetail = $order->getDetails()->first();

        $order = $this->resource->update($order->getId(), [
            'details' => [
                ['id' => $firstDetail->getId()],
            ],
        ]);

        /** @var Detail|null $firstDetail */
        $firstDetail = $order->getDetails()->filter(static function (Detail $detail) use ($firstDetail): bool {
            return $detail->getId() === $firstDetail->getId();
        })->first();

        static::assertEquals(1, $order->getDetails()->count());
        static::assertNotNull($firstDetail);
    }

    private function filterOrderId(array $order): array
    {
        // Remove order Ids and order numbers
        unset($order['id'], $order['number']);

        foreach ($order['details'] as &$detail) {
            unset($detail['id'], $detail['number']);
        }

        return $order;
    }
}
