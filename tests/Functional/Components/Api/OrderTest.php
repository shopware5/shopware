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

use Shopware\Components\Api\Resource\Order;
use Shopware\Components\Api\Resource\Resource;

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
    protected function setUp()
    {
        parent::setUp();
        $this->order = Shopware()->Db()->fetchRow('SELECT * FROM `s_order` ORDER BY id DESC LIMIT 1');
        Shopware()->Container()->get('dbal_connection')->beginTransaction();
    }

    protected function tearDown()
    {
        Shopware()->Container()->get('dbal_connection')->rollback();
    }

    /**
     * @return Order
     */
    public function createResource()
    {
        return new Order();
    }

    public function testGetOneShouldBeSuccessful()
    {
        $order = $this->resource->getOne($this->order['id']);
        static::assertEquals($this->order['id'], $order['id']);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testGetOneByNumberWithInvalidNumberShouldThrowNotFoundException()
    {
        $this->resource->getOneByNumber(9999999);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testGetOneByNumberWithMissinNumberShouldThrowParameterMissingException()
    {
        $this->resource->getOneByNumber('');
    }

    public function testGetOneByNumberShouldBeSuccessful()
    {
        $order = $this->resource->getOneByNumber($this->order['ordernumber']);
        static::assertEquals($this->order['ordernumber'], $order['number']);
    }

    public function testGetOneShouldBeAbleToReturnObject()
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $order = $this->resource->getOne($this->order['id']);

        static::assertInstanceOf('\Shopware\Models\Order\Order', $order);
        static::assertEquals($this->order['id'], $order->getId());
    }

    public function testGetListShouldBeSuccessful()
    {
        $result = $this->resource->getList();

        static::assertInternalType('array', $result);

        static::assertArrayHasKey('total', $result);
        static::assertGreaterThanOrEqual(1, $result['total']);

        static::assertArrayHasKey('data', $result);
        static::assertInternalType('array', $result['data']);

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

        static::assertInternalType('array', $firstOrder['customer']);
        static::assertArrayHasKey('id', $firstOrder['customer']);
        static::assertArrayHasKey('email', $firstOrder['customer']);
    }

    public function testGetListShouldBeAbleToReturnObjects()
    {
        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $result = $this->resource->getList();

        static::assertArrayHasKey('data', $result);
        static::assertArrayHasKey('total', $result);

        static::assertGreaterThanOrEqual(1, $result['total']);
        static::assertGreaterThanOrEqual(1, $result['data']);

        static::assertInstanceOf('\Shopware\Models\Order\Order', $result['data'][0]);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testUpdateWithInvalidIdShouldThrowNotFoundException()
    {
        $this->resource->update(9999999, []);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testUpdateWithMissingIdShouldThrowParameterMissingException()
    {
        $this->resource->update('', []);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testCreateOrderFailsOnMissingShippingAddress()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        unset($order['shipping']);

        $this->resource->create($order);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testCreateOrderFailsOnMissingBillingAddress()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        unset($order['billing']);

        $this->resource->create($order);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testCreateOrderFailsOnMissingCustomerId()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        unset($order['customerId']);

        $this->resource->create($order);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testCreateOrderFailsOnUnknownCustomerId()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['customerId'] = 4711;

        $this->resource->create($order);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testCreateOrderFailsOnUnknownCountryIdInBillingAddress()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['billing']['countryId'] = 4711;

        $this->resource->create($order);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testCreateOrderFailsOnUnknownStateIdInBillingAddress()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['billing']['stateId'] = 4711;

        $this->resource->create($order);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testCreateOrderFailsOnUnknownCountryIdInShippingAddress()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['shipping']['countryId'] = 4711;

        $this->resource->create($order);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testCreateOrderFailsOnUnknownStateIdInShipppingAddress()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['shipping']['stateId'] = 4711;

        $this->resource->create($order);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testCreateOrderFailsOnUnknownStateIdInDetails()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['details'][0]['statusId'] = 4711;

        $this->resource->create($order);
    }

    public function testCreateOrderOnEmptyStateIdInBillingAddress()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);
        unset($order['billing']['stateId']);

        $newOrder = $this->resource->create($order);

        static::assertEquals($newOrder->getBilling()->getState(), null);
    }

    public function testCreateOrderOnEmptyStateIdInShippingAddress()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);
        unset($order['shipping']['stateId']);

        $newOrder = $this->resource->create($order);

        static::assertEquals($newOrder->getShipping()->getState(), null);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testCreateOrderOnInvalidStateId()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);
        $order['shipping']['stateId'] = 9999;

        $this->resource->create($order);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testCreateOrderFailsOnUnknownTaxIdInDetails()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['details'][0]['taxId'] = 4711;

        $this->resource->create($order);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testCreateOrderFailsOnMissingOrderStatusId()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        unset($order['orderStatusId']);

        $this->resource->create($order);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testCreateOrderFailsOnUnknownOrderStatusId()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['orderStatusId'] = 4711;

        $this->resource->create($order);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testCreateOrderFailsOnMissingPaymentId()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        unset($order['paymentId']);

        $this->resource->create($order);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testCreateOrderFailsOnUnknownPaymentId()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['paymentId'] = 4711;

        $this->resource->create($order);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testCreateOrderFailsOnMissingPaymentStatusId()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        unset($order['paymentStatusId']);

        $this->resource->create($order);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testCreateOrderFailsOnUnknownPaymentStatusId()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['paymentStatusId'] = 4711;

        $this->resource->create($order);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testCreateOrderFailsOnMissingDispatchId()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        unset($order['dispatchId']);

        $this->resource->create($order);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testCreateOrderFailsOnUnknownDispatchId()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['dispatchId'] = 4711;

        $this->resource->create($order);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testCreateOrderFailsOnMissingShopId()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        unset($order['shopId']);

        $this->resource->create($order);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testCreateOrderFailsOnUnknownShopId()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        $order = $this->filterOrderId($order);

        $order['shopId'] = 4711;

        $this->resource->create($order);
    }

    public function testCreateOrderByCopy()
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
        static::assertEquals(count($newOrder->getDetails()), count($order['details']));
        static::assertEquals($newOrder->getDetails()[0]->getArticleName(), $order['details'][0]['articleName']);
        static::assertEquals((int) $newOrder->getDetails()[0]->getNumber(), ($oldOrderNumber + 1));
    }

    public function testUpdateOrderPositionStatusShouldBeSuccessful()
    {
        // Get existing order
        $this->resource->setResultMode(Resource::HYDRATE_ARRAY);
        $order = $this->resource->getOne($this->order['id']);

        // Update the order details of that order
        $updateArray = [];
        foreach ($order['details'] as $detail) {
            $updateArray['details'][$detail['id']] = ['id' => $detail['id'], 'status' => rand(0, 3), 'shipped' => 1];
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

    public function testCreateOrderWithDocuments()
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

    /**
     * @param array $order
     *
     * @return array
     */
    private function filterOrderId($order)
    {
        // Remove order Ids and order numbers
        unset($order['id']);
        unset($order['number']);

        foreach ($order['details'] as &$detail) {
            unset($detail['id']);
            unset($detail['number']);
        }

        return $order;
    }
}
