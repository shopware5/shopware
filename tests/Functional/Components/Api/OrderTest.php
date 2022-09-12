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

use DateTime;
use Doctrine\DBAL\Connection;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Resource\Order as OrderResource;
use Shopware\Components\Api\Resource\Resource;
use Shopware\Models\Order\Detail;
use Shopware\Models\Order\Order as OrderModel;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class OrderTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    /**
     * @var OrderResource
     */
    protected $resource;

    /**
     * @var array<string, mixed>
     */
    private array $order;

    private Connection $connection;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->getContainer()->get(Connection::class);
        $order = $this->connection->executeQuery('SELECT * FROM `s_order` ORDER BY id DESC LIMIT 1')->fetchAssociative();
        static::assertIsArray($order);
        $this->order = $order;
    }

    public function createResource(): OrderResource
    {
        return new OrderResource();
    }

    public function testGetOneShouldBeSuccessful(): void
    {
        $order = $this->resource->getOne($this->order['id']);
        static::assertIsArray($order);
        static::assertEquals($this->order['id'], $order['id']);
    }

    public function testGetOneByNumberWithInvalidNumberShouldThrowNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->resource->getOneByNumber('SW9999999');
    }

    public function testGetOneByNumberWithMissinNumberShouldThrowParameterMissingException(): void
    {
        $this->expectException(ParameterMissingException::class);
        $this->resource->getOneByNumber('');
    }

    public function testGetOneByNumberShouldBeSuccessful(): void
    {
        $order = $this->resource->getOneByNumber($this->order['ordernumber']);
        static::assertIsArray($order);
        static::assertEquals($this->order['ordernumber'], $order['number']);
    }

    public function testGetOneShouldBeAbleToReturnObject(): void
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $order = $this->resource->getOne($this->order['id']);

        static::assertInstanceOf(OrderModel::class, $order);
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

        static::assertGreaterThanOrEqual(1, \count($result['data']));

        $firstOrder = $result['data'][0];
        static::assertIsArray($firstOrder);

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

        static::assertInstanceOf(OrderModel::class, $result['data'][0]);
    }

    public function testUpdateWithInvalidIdShouldThrowNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->resource->update(9999999, []);
    }

    public function testUpdateWithMissingIdShouldThrowParameterMissingException(): void
    {
        $this->expectException(ParameterMissingException::class);
        $this->resource->update(0, []);
    }

    public function testCreateOrderFailsOnMissingShippingAddress(): void
    {
        $this->expectException(ParameterMissingException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);
        static::assertIsArray($order);

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
        static::assertIsArray($order);

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
        static::assertIsArray($order);

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
        static::assertIsArray($order);

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
        static::assertIsArray($order);

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
        static::assertIsArray($order);

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
        static::assertIsArray($order);

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
        static::assertIsArray($order);

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
        static::assertIsArray($order);

        $order = $this->filterOrderId($order);

        $order['details'][0]['statusId'] = 4711;

        $this->resource->create($order);
    }

    public function testCreateOrderOnEmptyStateIdInBillingAddress(): void
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);
        static::assertIsArray($order);

        $order = $this->filterOrderId($order);
        unset($order['billing']['stateId']);

        $billing = $this->resource->create($order)->getBilling();
        static::assertNotNull($billing);
        static::assertNull($billing->getState());
    }

    public function testCreateOrderOnEmptyStateIdInShippingAddress(): void
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);
        static::assertIsArray($order);

        $order = $this->filterOrderId($order);
        unset($order['shipping']['stateId']);

        $shipping = $this->resource->create($order)->getShipping();
        static::assertNotNull($shipping);
        static::assertNull($shipping->getState());
    }

    public function testCreateOrderOnInvalidStateId(): void
    {
        $this->expectException(NotFoundException::class);
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);
        static::assertIsArray($order);

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
        static::assertIsArray($order);

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
        static::assertIsArray($order);

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
        static::assertIsArray($order);

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
        static::assertIsArray($order);

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
        static::assertIsArray($order);

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
        static::assertIsArray($order);

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
        static::assertIsArray($order);

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
        static::assertIsArray($order);

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
        static::assertIsArray($order);

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
        static::assertIsArray($order);

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
        static::assertIsArray($order);

        $order = $this->filterOrderId($order);

        $order['shopId'] = 4711;

        $this->resource->create($order);
    }

    public function testCreateOrderByCopy(): void
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);
        static::assertIsArray($order);

        $oldOrderNumber = (int) $order['number'];

        $order = $this->filterOrderId($order);
        $order['invoiceShippingTaxRate'] = 19.0;

        $newOrder = $this->resource->create($order);

        // Checking some fields in all models
        static::assertGreaterThan($this->order['id'], $newOrder->getId());
        static::assertNotNull($newOrder->getNumber());
        static::assertNotSame($oldOrderNumber, (int) $newOrder->getNumber());
        static::assertNotNull($newOrder->getCustomer());
        static::assertEquals($order['customer']['id'], $newOrder->getCustomer()->getId());
        static::assertEquals($order['invoiceAmount'], $newOrder->getInvoiceAmount());
        static::assertNotNull($newOrder->getBilling());
        static::assertEquals($order['billing']['city'], $newOrder->getBilling()->getCity());
        static::assertNotNull($newOrder->getShipping());
        static::assertEquals($order['shipping']['city'], $newOrder->getShipping()->getCity());
        static::assertCount(\count($order['details']), $newOrder->getDetails());
        $firstOrderDetail = $newOrder->getDetails()->first();
        static::assertInstanceOf(Detail::class, $firstOrderDetail);
        static::assertEquals($order['details'][0]['articleName'], $firstOrderDetail->getArticleName());
        static::assertEquals($oldOrderNumber + 1, (int) $firstOrderDetail->getNumber());
        static::assertSame($order['invoiceShippingTaxRate'], $newOrder->getInvoiceShippingTaxRate());
    }

    public function testUpdateOrderPositionStatusShouldBeSuccessful(): void
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);
        static::assertIsArray($order);

        // Update the order details of that order
        $updateArray = [];
        foreach ($order['details'] as $detail) {
            $updateArray['details'][$detail['id']] = ['id' => $detail['id'], 'status' => random_int(0, 3), 'shipped' => 1];
        }
        $this->resource->update($this->order['id'], $updateArray);

        // Reload the order and check the result
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);
        static::assertIsArray($order);
        static::assertArrayHasKey('details', $updateArray);
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
        static::assertIsArray($order);

        $order = $this->filterOrderId($order);
        $order['documents'] = [
            [
                'date' => new DateTime(),
                'amount' => 47.11,
                'typeId' => 1,
                'customerId' => 1,
                'documentId' => 1,
                'hash' => 4711,
            ],
            [
                'date' => new DateTime(),
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
        $orderId = (int) $this->connection
            ->executeQuery('SELECT `orderID`, COUNT(1) `counter` FROM `s_order_details` GROUP BY `orderId` HAVING `counter` > 1 ORDER BY id DESC LIMIT 1')
            ->fetchOne();
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $order = $this->resource->getOne($orderId);
        static::assertInstanceOf(OrderModel::class, $order);

        $firstDetail = $order->getDetails()->first();
        static::assertInstanceOf(Detail::class, $firstDetail);
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

        $firstDetail = $order->getDetails()->filter(static function (Detail $detail) use ($firstDetail): bool {
            return $detail->getId() === $firstDetail->getId();
        })->first();

        static::assertEquals($orderCount, $order->getDetails()->count());
        static::assertInstanceOf(Detail::class, $firstDetail);
        static::assertEquals($newShipped, $firstDetail->getShipped());
    }

    public function testSingleOrderDetailUpdateToDeleteOtherItems(): void
    {
        // Get existing order with at least two order items
        $orderId = (int) $this->connection
            ->executeQuery('SELECT `orderID`, COUNT(1) `counter` FROM `s_order_details` GROUP BY `orderId` HAVING `counter` > 1 ORDER BY id DESC LIMIT 1')
            ->fetchOne();
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $order = $this->resource->getOne($orderId);
        static::assertInstanceOf(OrderModel::class, $order);

        $firstDetail = $order->getDetails()->first();
        static::assertInstanceOf(Detail::class, $firstDetail);

        $order = $this->resource->update($order->getId(), [
            'details' => [
                ['id' => $firstDetail->getId()],
            ],
        ]);

        $firstDetail = $order->getDetails()->filter(static function (Detail $detail) use ($firstDetail): bool {
            return $detail->getId() === $firstDetail->getId();
        })->first();

        static::assertEquals(1, $order->getDetails()->count());
        static::assertNotNull($firstDetail);
    }

    /**
     * @param array<string, mixed> $order
     *
     * @return array<string, mixed>
     */
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
