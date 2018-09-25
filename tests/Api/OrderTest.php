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

class Shopware_Tests_Api_OrderTest extends PHPUnit\Framework\TestCase
{
    public $apiBaseUrl = '';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $helper = Shopware();

        $hostname = $helper->Shop()->getHost();
        if (empty($hostname)) {
            $this->markTestSkipped(
                'Hostname is not available.'
            );
        }

        $this->apiBaseUrl = 'http://' . $hostname . $helper->Shop()->getBasePath() . '/api';

        Shopware()->Db()->query('UPDATE s_core_auth SET apiKey = ? WHERE username LIKE "demo"', [sha1('demo')]);
    }

    /**
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        $username = 'demo';
        $password = sha1('demo');

        $adapter = new Zend_Http_Client_Adapter_Curl();
        $adapter->setConfig(
            [
                'curloptions' => [
                    CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
                    CURLOPT_USERPWD => "$username:$password",
                ],
            ]
        );

        $client = new Zend_Http_Client();
        $client->setAdapter($adapter);

        return $client;
    }

    public function testRequestWithoutAuthenticationShouldReturnError()
    {
        $client = new Zend_Http_Client($this->apiBaseUrl . '/orders/');
        $response = $client->request('GET');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(401, $response->getStatus());

        $result = $response->getBody();

        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);

        $this->assertArrayHasKey('message', $result);
    }

    public function testGetOrdersWithInvalidIdShouldReturnMessage()
    {
        $id = 99999999;
        $response = $this->getHttpClient()
                         ->setUri($this->apiBaseUrl . '/orders/' . $id)
                         ->request('GET');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(404, $response->getStatus());

        $result = $response->getBody();

        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);

        $this->assertArrayHasKey('message', $result);
    }

    public function testPostOrdersShouldBeSuccessful()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/orders/');

        $requestData = [
            'customerId' => 1,
            'paymentId' => 4,
            'dispatchId' => 9,
            'partnerId' => '',
            'shopId' => 1,
            'invoiceAmount' => 201.86,
            'invoiceAmountNet' => 169.63,
            'invoiceShipping' => 0,
            'invoiceShippingNet' => 0,
            'orderTime' => '2012-08-31 08:51:46',
            'net' => 0,
            'taxFree' => 0,
            'languageIso' => '1',
            'currency' => 'EUR',
            'currencyFactor' => 1,
            'remoteAddress' => '217.86.205.141',
            'details' => [
                [
                    'articleId' => 220,
                    'taxId' => 1,
                    'taxRate' => 19,
                    'statusId' => 0,
                    'articleNumber' => 'SW10001',
                    'price' => 35.99,
                    'quantity' => 1,
                    'articleName' => 'Versandkostenfreier Artikel',
                    'shipped' => 0,
                    'shippedGroup' => 0,
                    'mode' => 0,
                    'esdArticle' => 0,
                ],
            ],
            'documents' => [],
            'billing' => [
                'id' => 2,
                'customerId' => 1,
                'countryId' => 2,
                'stateId' => 3,
                'company' => 'shopware AG',
                'salutation' => 'mr',
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
                'street' => "Mustermannstra\u00dfe 92",
                'zipCode' => '48624',
                'city' => "Sch\u00f6ppingen",
            ],
            'shipping' => [
                'id' => 2,
                'countryId' => 2,
                'stateId' => 3,
                'customerId' => 1,
                'company' => 'shopware AG',
                'salutation' => 'mr',
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
                'street' => "Mustermannstra\u00dfe 92",
                'zipCode' => '48624',
                'city' => "Sch\u00f6ppingen",
            ],
            'paymentStatusId' => 17,
            'orderStatusId' => 0,
        ];

        $requestData = Zend_Json::encode($requestData);
        $client->setRawData($requestData, 'application/json; charset=UTF-8');

        $response = $client->request('POST');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(201, $response->getStatus());
        $this->assertArrayHasKey('Location', $response->getHeaders());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        $location = $response->getHeader('Location');
        $identifier = (int) array_pop(explode('/', $location));

        $this->assertGreaterThan(0, $identifier);

        return $identifier;
    }

    /**
     * @depends testPostOrdersShouldBeSuccessful
     */
    public function testPostAttributesShouldBeSuccessful($id)
    {
        $setAttributes = Shopware()->Models()->createQueryBuilder()
            ->select(['count(attributes.id)'])
            ->from('Shopware\Models\Attribute\OrderDetail', 'attributes')
            ->join('Shopware\Models\Order\Detail', 'order_detail', 'WITH', 'attributes.orderDetailId = order_detail.id')
            ->where('order_detail.orderId = :orderId')
            ->setParameter('orderId', $id)
            ->getQuery()
            ->getSingleScalarResult();

        $this->assertEquals(1, $setAttributes);
    }

    public function testPostOrdersWithInvalidDataShouldReturnError()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/orders/');

        $requestData = [
            'customerId' => 'string',
            'paymentId' => 4,
            'dispatchId' => 9,
            'partnerId' => '',
        ];
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('POST');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(400, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    /**
     * @depends testPostOrdersShouldBeSuccessful
     */
    public function testGetOrdersWithIdShouldBeSuccessful($id)
    {
        $response = $this->getHttpClient()
                         ->setUri($this->apiBaseUrl . '/orders/' . $id)
                         ->request('GET');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(200, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        $this->assertArrayHasKey('data', $result);

        $data = $result['data'];
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('shopId', $data);

        $this->assertEquals('Mustermann', $data['billing']['lastName']);
        $this->assertEquals(1, $data['billing']['customerId']);
    }

    public function testPutBatchOrdersShouldFail()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/orders/');

        $requestData = [
            'paymentStatusId' => 5,
            'orderStatusId' => 0,
        ];
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('PUT');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(405, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertEquals('This resource has no support for batch operations.', $result['message']);
    }

    /**
     * @depends testPostOrdersShouldBeSuccessful
     */
    public function testPutOrdersWithInvalidDataShouldReturnError($id)
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/orders/' . $id);

        $requestData = [
            'orderStatusId' => [],
        ];
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('PUT');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(404, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);

        $this->assertArrayHasKey('message', $result);
    }

    /**
     * @depends testPostOrdersShouldBeSuccessful
     */
    public function testPutOrdersShouldBeSuccessful($id)
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/orders/' . $id);

        $order = Shopware()->Models()->getRepository(Order::class)->find($id);

        $detailId = Shopware()->Models()->createQueryBuilder()
            ->select(['order_detail.id'])
            ->from('Shopware\Models\Order\Detail', 'order_detail')
            ->where('order_detail.orderId = :orderId')
            ->setParameter('orderId', $id)
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();

        $requestData = [
            'orderStatusId' => 1,
            'shopId' => $order->getShop(),
            'details' => [
                [
                    'id' => $detailId['id'],
                    'attribute' => [
                        'attribute1' => 1,
                    ],
                ],
            ],
        ];
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('PUT');

        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertNull(
            $response->getHeader(
                'location',
                'There should be no location header set.'
            )
        );

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        return $detailId;
    }

    /**
     * @depends testPutOrdersShouldBeSuccessful
     */
    public function testPutAttributesShouldBeSuccessful($detailId)
    {
        $attributes = Shopware()->Models()->createQueryBuilder()
            ->select(['attributes.attribute1'])
            ->from('Shopware\Models\Attribute\OrderDetail', 'attributes')
            ->where('attributes.orderDetailId = :detailId')
            ->setParameter('detailId', $detailId['id'])
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $this->assertEquals(1, $attributes['attribute1']);
    }

    /**
     * @depends testPostOrdersShouldBeSuccessful
     */
    public function testDeleteOrdersShouldBeSuccessful($id)
    {
        // Make sure that no delete method for orders will be implemented in the future
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/orders/' . $id);

        $response = $client->request('DELETE');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(404, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);

        return $id;
    }

    public function testDeleteOrdersWithInvalidIdShouldReturnMessage()
    {
        $id = 99999999;
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/orders/' . $id);

        $response = $client->request('DELETE');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(404, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);

        $this->assertArrayHasKey('message', $result);
    }

    public function testPutOrdersWithInvalidIdShouldReturnMessage()
    {
        $id = 99999999;
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/orders/' . $id);

        $requestData = [
            'paymentStatusId' => 17,
            'orderStatusId' => 0,
        ];
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('PUT');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(404, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);

        $this->assertArrayHasKey('message', $result);
    }

    public function testGetOrdersShouldBeSuccessful()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/orders');
        $result = $client->request('GET');

        $this->assertEquals('application/json', $result->getHeader('Content-Type'));
        $this->assertEquals(200, $result->getStatus());

        $result = $result->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        $this->assertArrayHasKey('data', $result);

        $this->assertArrayHasKey('total', $result);
        $this->assertInternalType('int', $result['total']);

        $data = $result['data'];
        $this->assertInternalType('array', $data);
    }
}
