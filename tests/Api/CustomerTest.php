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

namespace Shopware\Tests\Api;

use DateTime;
use PHPUnit\Framework\TestCase;
use Shopware\Models\Customer\Customer;
use Zend_Http_Client;
use Zend_Http_Client_Adapter_Curl;
use Zend_Http_Client_Adapter_Exception;
use Zend_Json;

class CustomerTest extends TestCase
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
            static::markTestSkipped(
                'Hostname is not available.'
            );
        }

        $this->apiBaseUrl = 'http://' . $hostname . $helper->Shop()->getBasePath() . '/api';

        Shopware()->Db()->query('UPDATE s_core_auth SET apiKey = ? WHERE username LIKE "demo"', [sha1('demo')]);
    }

    /**
     * @throws Zend_Http_Client_Adapter_Exception
     *
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        $username = 'demo';
        $password = sha1('demo');

        $adapter = new Zend_Http_Client_Adapter_Curl();
        $adapter->setConfig([
            'curloptions' => [
                CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
                CURLOPT_USERPWD => "$username:$password",
            ],
        ]);

        $client = new Zend_Http_Client();
        $client->setAdapter($adapter);

        return $client;
    }

    public function testRequestWithoutAuthenticationShouldReturnError()
    {
        $client = new Zend_Http_Client($this->apiBaseUrl . '/customers/');
        $response = $client->request('GET');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(401, $response->getStatus());

        $result = $response->getBody();

        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testGetCustomersWithInvalidIdShouldReturnMessage()
    {
        $id = 99999999;
        $response = $this->getHttpClient()
                         ->setUri($this->apiBaseUrl . '/customers/' . $id)
                         ->request('GET');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(404, $response->getStatus());

        $result = $response->getBody();

        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testPostCustomersShouldBeSuccessful()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/customers/');

        $date = new DateTime();
        $date->modify('-10 days');
        $firstlogin = $date->format(DateTime::ISO8601);

        $date->modify('+2 day');
        $lastlogin = $date->format(DateTime::ISO8601);

        $birthday = DateTime::createFromFormat('Y-m-d', '1986-12-20')->format(DateTime::ISO8601);

        $requestData = [
            'password' => 'fooobar',
            'active' => true,
            'email' => uniqid('', true) . 'test@foobar.com',

            'firstlogin' => $firstlogin,
            'lastlogin' => $lastlogin,
            'paymentId' => 2,

            'salutation' => 'mr',
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'birthday' => $birthday,

            'billing' => [
                'salutation' => 'Mr',
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
                'country' => 2,
                'street' => 'Fakesreet 123',
                'city' => 'City',
                'zipcode' => 55555,
            ],

            'shipping' => [
                'salutation' => 'Mr',
                'company' => 'Widgets Inc.',
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
                'country' => 2,
                'street' => 'Fakesreet 123',
                'city' => 'City',
                'zipcode' => 55555,
            ],

            'debit' => [
                'account' => 'Fake Account',
                'bankCode' => '55555555',
                'bankName' => 'Fake Bank',
                'accountHolder' => 'Max Mustermann',
            ],
        ];

        $requestData = Zend_Json::encode($requestData);
        $client->setRawData($requestData, 'application/json; charset=UTF-8');

        $response = $client->request('POST');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(201, $response->getStatus());
        static::assertArrayHasKey('Location', $response->getHeaders());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        $location = $response->getHeader('Location');
        $identifier = (int) array_pop(explode('/', $location));

        static::assertGreaterThan(0, $identifier);

        return $identifier;
    }

    /**
     * @return int
     */
    public function testPostCustomersWithDebitShouldCreatePaymentData()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/customers/');

        $date = new DateTime();
        $date->modify('-10 days');
        $firstlogin = $date->format(DateTime::ISO8601);

        $date->modify('+2 day');
        $lastlogin = $date->format(DateTime::ISO8601);

        $birthday = DateTime::createFromFormat('Y-m-d', '1986-12-20')->format(DateTime::ISO8601);

        $requestData = [
            'password' => 'fooobar',
            'active' => true,
            'email' => uniqid('', true) . 'test@foobar.com',

            'firstlogin' => $firstlogin,
            'lastlogin' => $lastlogin,

            'salutation' => 'mr',
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'birthday' => $birthday,

            'billing' => [
                'salutation' => 'Mr',
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
                'country' => 2,
                'street' => 'Fakesreet 123',
                'city' => 'City',
                'zipcode' => 55555,
            ],

            'shipping' => [
                'salutation' => 'Mr',
                'company' => 'Widgets Inc.',
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
                'country' => 2,
                'street' => 'Fakesreet 123',
                'city' => 'City',
                'zipcode' => 55555,
            ],

            'debit' => [
                'account' => 'Fake Account',
                'bankCode' => '55555555',
                'bankName' => 'Fake Bank',
                'accountHolder' => 'Max Mustermann',
            ],
        ];

        $requestData = Zend_Json::encode($requestData);
        $client->setRawData($requestData, 'application/json; charset=UTF-8');

        $response = $client->request('POST');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(201, $response->getStatus());
        static::assertArrayHasKey('Location', $response->getHeaders());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        $location = $response->getHeader('Location');
        $identifier = (int) array_pop(explode('/', $location));

        static::assertGreaterThan(0, $identifier);

        $customer = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->find($identifier);
        $paymentData = array_shift($customer->getPaymentData()->toArray());

        static::assertNotNull($paymentData);
        static::assertEquals('Max Mustermann', $paymentData->getAccountHolder());
        static::assertEquals('Fake Account', $paymentData->getAccountNumber());
        static::assertEquals('Fake Bank', $paymentData->getBankName());
        static::assertEquals('55555555', $paymentData->getBankCode());

        $this->testDeleteCustomersShouldBeSuccessful($identifier);
    }

    /**
     * @return int
     */
    public function testPostCustomersWithDebitPaymentDataShouldCreateDebitData()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/customers/');

        $date = new DateTime();
        $date->modify('-10 days');
        $firstlogin = $date->format(DateTime::ISO8601);

        $date->modify('+2 day');
        $lastlogin = $date->format(DateTime::ISO8601);

        $birthday = DateTime::createFromFormat('Y-m-d', '1986-12-20')->format(DateTime::ISO8601);

        $requestData = [
            'password' => 'fooobar',
            'active' => true,
            'email' => uniqid('', true) . 'test@foobar.com',

            'firstlogin' => $firstlogin,
            'lastlogin' => $lastlogin,

            'salutation' => 'mr',
            'firstname' => 'Max',
            'lastname' => 'Mustermann',
            'birthday' => $birthday,

            'billing' => [
                'salutation' => 'Mr',
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
                'country' => 2,
                'street' => 'Fakesreet 123',
                'city' => 'City',
                'zipcode' => 55555,
            ],

            'shipping' => [
                'salutation' => 'Mr',
                'company' => 'Widgets Inc.',
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
                'country' => 2,
                'street' => 'Fakesreet 123',
                'city' => 'City',
                'zipcode' => 55555,
            ],

            'paymentData' => [
                [
                    'paymentMeanId' => 2,
                    'accountNumber' => 'Fake Account',
                    'bankCode' => '55555555',
                    'bankName' => 'Fake Bank',
                    'accountHolder' => 'Max Mustermann',
                ],
            ],
        ];

        $requestData = Zend_Json::encode($requestData);
        $client->setRawData($requestData, 'application/json; charset=UTF-8');

        $response = $client->request('POST');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(201, $response->getStatus());
        static::assertArrayHasKey('Location', $response->getHeaders());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        $location = $response->getHeader('Location');
        $identifier = (int) array_pop(explode('/', $location));

        static::assertGreaterThan(0, $identifier);

        $customer = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->find($identifier);
        $paymentData = array_shift($customer->getPaymentData()->toArray());

        static::assertNotNull($paymentData);
        static::assertEquals('Max Mustermann', $paymentData->getAccountHolder());
        static::assertEquals('Fake Account', $paymentData->getAccountNumber());
        static::assertEquals('Fake Bank', $paymentData->getBankName());
        static::assertEquals('55555555', $paymentData->getBankCode());

        $this->testDeleteCustomersShouldBeSuccessful($identifier);
    }

    public function testPostCustomersWithInvalidDataShouldReturnError()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/customers/');

        $requestData = [
            'active' => true,
            'email' => 'invalid',
            'billing' => [
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
            ],
        ];
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('POST');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(400, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);
        static::assertArrayHasKey('message', $result);
    }

    /**
     * @depends testPostCustomersShouldBeSuccessful
     */
    public function testGetCustomersWithIdShouldBeSuccessful($id)
    {
        $response = $this->getHttpClient()
                         ->setUri($this->apiBaseUrl . '/customers/' . $id)
                         ->request('GET');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(200, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        static::assertArrayHasKey('data', $result);

        $data = $result['data'];
        static::assertInternalType('array', $data);
        static::assertArrayHasKey('id', $data);
        static::assertArrayHasKey('active', $data);
        static::assertArrayHasKey('paymentData', $data);

        static::assertContains('test@foobar.com', $data['email']);

        $paymentInfo = array_shift($data['paymentData']);

        static::assertEquals('Max Mustermann', $paymentInfo['accountHolder']);
        static::assertEquals('55555555', $paymentInfo['bankCode']);
        static::assertEquals('Fake Bank', $paymentInfo['bankName']);
        static::assertEquals('Fake Account', $paymentInfo['accountNumber']);
    }

    public function testPutBatchCustomersShouldFail()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/customers/');

        $requestData = [
            'active' => true,
            'email' => 'test@foobar.com',
        ];
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('PUT');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(405, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);
        static::assertEquals('This resource has no support for batch operations.', $result['message']);
    }

    /**
     * @depends testPostCustomersShouldBeSuccessful
     */
    public function testPutCustomersWithInvalidDataShouldReturnError($id)
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/customers/' . $id);

        $requestData = [
            'active' => true,
            'email' => 'invalid',
        ];
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('PUT');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(400, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    /**
     * @depends testPostCustomersShouldBeSuccessful
     */
    public function testPutCustomersShouldBeSuccessful($id)
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/customers/' . $id);

        $customer = Shopware()->Models()->getRepository(Customer::class)->find($id);

        $requestData = [
            'active' => true,
            'email' => $customer->getEmail(),
        ];
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('PUT');

        static::assertEquals(200, $response->getStatus());
        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertNull(
            $response->getHeader(
                'location',
                'There should be no location header set.'
            )
        );

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        return $id;
    }

    /**
     * @depends testPostCustomersShouldBeSuccessful
     */
    public function testDeleteCustomersShouldBeSuccessful($id)
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/customers/' . $id);

        $response = $client->request('DELETE');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(200, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        return $id;
    }

    public function testDeleteCustomersWithInvalidIdShouldReturnMessage()
    {
        $id = 99999999;
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/customers/' . $id);

        $response = $client->request('DELETE');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(404, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testPutCustomersWithInvalidIdShouldReturnMessage()
    {
        $id = 99999999;
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/customers/' . $id);

        $requestData = [
            'active' => true,
            'email' => 'test@foobar.com',
        ];
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('PUT');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(404, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testGetCustomersShouldBeSuccessful()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/customers');
        $result = $client->request('GET');

        static::assertEquals('application/json', $result->getHeader('Content-Type'));
        static::assertEquals(200, $result->getStatus());

        $result = $result->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        static::assertArrayHasKey('data', $result);

        static::assertArrayHasKey('total', $result);
        static::assertInternalType('int', $result['total']);

        $data = $result['data'];
        static::assertInternalType('array', $data);
    }
}
