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

namespace Shopware\Tests\Functional\Api;

use DateTime;
use Shopware\Models\Customer\Customer;

/**
 * @covers \Shopware_Controllers_Api_Customers
 */
class CustomerTest extends AbstractApiTestCase
{
    public function testRequestWithoutAuthenticationShouldReturnError(): void
    {
        $this->client->request('GET', '/api/customers/');
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(401, $response->getStatusCode());

        $result = $response->getContent();

        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testGetCustomersWithInvalidIdShouldReturnMessage(): void
    {
        $id = 99999999;

        $this->authenticatedApiRequest('GET', '/api/customers/' . $id);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(404, $response->getStatusCode());

        $result = $response->getContent();

        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testPostCustomersShouldBeSuccessful(): string
    {
        $date = new DateTime();
        $date->modify('-10 days');
        $firstlogin = $date->format(DateTime::ATOM);

        $date->modify('+2 day');
        $lastlogin = $date->format(DateTime::ATOM);

        $birthday = DateTime::createFromFormat('Y-m-d', '1986-12-20')->format(DateTime::ATOM);

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

        $this->authenticatedApiRequest('POST', '/api/customers/', [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(201, $response->getStatusCode());
        static::assertArrayHasKey('location', $response->headers->all());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        $location = $response->headers->get('location');
        $identifier = (int) array_pop(explode('/', $location));

        static::assertGreaterThan(0, $identifier);

        return $identifier;
    }

    /**
     * @throws \Exception
     */
    public function testPostCustomersWithDebitShouldCreatePaymentData()
    {
        $date = new DateTime();
        $date->modify('-10 days');
        $firstLogin = $date->format(DateTime::ATOM);

        $date->modify('+2 day');
        $lastLogin = $date->format(DateTime::ATOM);

        $birthday = DateTime::createFromFormat('Y-m-d', '1986-12-20')->format(DateTime::ATOM);

        $requestData = [
            'password' => 'fooobar',
            'active' => true,
            'email' => uniqid('', true) . 'test@foobar.com',

            'firstlogin' => $firstLogin,
            'lastlogin' => $lastLogin,

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

        $this->authenticatedApiRequest('POST', '/api/customers/', [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(201, $response->getStatusCode());
        static::assertArrayHasKey('location', $response->headers->all());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        $location = $response->headers->get('location');
        $identifier = (int) array_pop(explode('/', $location));

        static::assertGreaterThan(0, $identifier);

        $customer = Shopware()->Models()->getRepository(Customer::class)->find($identifier);
        $paymentData = array_shift($customer->getPaymentData()->toArray());

        static::assertNotNull($paymentData);
        static::assertEquals('Max Mustermann', $paymentData->getAccountHolder());
        static::assertEquals('Fake Account', $paymentData->getAccountNumber());
        static::assertEquals('Fake Bank', $paymentData->getBankName());
        static::assertEquals('55555555', $paymentData->getBankCode());
    }

    /**
     * @throws \Exception
     */
    public function testPostCustomersWithDebitPaymentDataShouldCreateDebitData()
    {
        $date = new DateTime();
        $date->modify('-10 days');
        $firstlogin = $date->format(DateTime::ATOM);

        $date->modify('+2 day');
        $lastlogin = $date->format(DateTime::ATOM);

        $birthday = DateTime::createFromFormat('Y-m-d', '1986-12-20')->format(DateTime::ATOM);

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

        $this->authenticatedApiRequest('POST', '/api/customers/', [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(201, $response->getStatusCode());
        static::assertArrayHasKey('location', $response->headers->all());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        $location = $response->headers->get('Location');
        $identifier = (int) array_pop(explode('/', $location));

        static::assertGreaterThan(0, $identifier);

        $customer = Shopware()->Models()->getRepository(Customer::class)->find($identifier);
        $paymentData = array_shift($customer->getPaymentData()->toArray());

        static::assertNotNull($paymentData);
        static::assertEquals('Max Mustermann', $paymentData->getAccountHolder());
        static::assertEquals('Fake Account', $paymentData->getAccountNumber());
        static::assertEquals('Fake Bank', $paymentData->getBankName());
        static::assertEquals('55555555', $paymentData->getBankCode());
    }

    public function testPostCustomersWithInvalidDataShouldReturnError()
    {
        $requestData = [
            'active' => true,
            'email' => 'invalid',
            'billing' => [
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
            ],
        ];

        $this->authenticatedApiRequest('POST', '/api/customers/', [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(400, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);
        static::assertArrayHasKey('message', $result);
    }

    /**
     * @depends testPostCustomersShouldBeSuccessful
     */
    public function testGetCustomersWithIdShouldBeSuccessful($id)
    {
        $this->authenticatedApiRequest('GET', '/api/customers/' . $id, []);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(200, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        static::assertArrayHasKey('data', $result);

        $data = $result['data'];
        static::assertIsArray($data);
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
        $requestData = [
            'active' => true,
            'email' => 'test@foobar.com',
        ];

        $this->authenticatedApiRequest('PUT', '/api/customers/', [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(405, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);
        static::assertEquals('This resource has no support for batch operations.', $result['message']);
    }

    /**
     * @depends testPostCustomersShouldBeSuccessful
     */
    public function testPutCustomersWithInvalidDataShouldReturnError($id): void
    {
        $requestData = [
            'active' => true,
            'email' => 'invalid',
        ];

        $this->authenticatedApiRequest('PUT', '/api/customers/' . $id, [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(400, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    /**
     * @depends testPostCustomersShouldBeSuccessful
     */
    public function testPutCustomersShouldBeSuccessful($id)
    {
        $customer = Shopware()->Models()->getRepository(Customer::class)->find($id);

        $requestData = [
            'active' => true,
            'email' => $customer->getEmail(),
        ];

        $this->authenticatedApiRequest('PUT', '/api/customers/' . $id, [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals(200, $response->getStatusCode());
        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertNull(
            $response->headers->get('location'),
            'There should be no location header set.'
        );

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        return $id;
    }

    /**
     * @depends testPostCustomersShouldBeSuccessful
     */
    public function testDeleteCustomersShouldBeSuccessful($id)
    {
        $this->authenticatedApiRequest('DELETE', '/api/customers/' . $id);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(200, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        return $id;
    }

    public function testDeleteCustomersWithInvalidIdShouldReturnMessage()
    {
        $id = 99999999;

        $this->authenticatedApiRequest('DELETE', '/api/customers/' . $id);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(404, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testPutCustomersWithInvalidIdShouldReturnMessage()
    {
        $id = 99999999;

        $requestData = [
            'active' => true,
            'email' => 'test@foobar.com',
        ];

        $this->authenticatedApiRequest('PUT', '/api/customers/' . $id);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(404, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testGetCustomersShouldBeSuccessful(): void
    {
        $this->authenticatedApiRequest('GET', '/api/customers/');
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(200, $response->getStatusCode());

        $response = $response->getBody();
        $response = json_decode($response, true);

        static::assertArrayHasKey('success', $response);
        static::assertTrue($response['success']);

        static::assertArrayHasKey('data', $response);

        static::assertArrayHasKey('total', $response);
        static::assertIsInt($response['total']);

        $data = $response['data'];
        static::assertIsArray($data);
    }
}
