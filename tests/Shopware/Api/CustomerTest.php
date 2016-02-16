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

class Shopware_Tests_Api_CustomerTest extends PHPUnit_Framework_TestCase
{
    public $apiBaseUrl = '';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $helper = TestHelper::Instance();

        $hostname = $helper->Shop()->getHost();
        if (empty($hostname)) {
            $this->markTestSkipped(
                'Hostname is not available.'
            );
        }

        $this->apiBaseUrl =  'http://' . $hostname . $helper->Shop()->getBasePath() . '/api';

        Shopware()->Db()->query('UPDATE s_core_auth SET apiKey = ? WHERE username LIKE "demo"', array(sha1('demo')));
    }

    /**
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        $username = 'demo';
        $password = sha1('demo');

        $adapter = new Zend_Http_Client_Adapter_Curl();
        $adapter->setConfig(array(
            'curloptions' => array(
                CURLOPT_HTTPAUTH    => CURLAUTH_DIGEST,
                CURLOPT_USERPWD     => "$username:$password"
            )
        ));

        $client = new Zend_Http_Client();
        $client->setAdapter($adapter);

        return $client;
    }

    public function testRequestWithoutAuthenticationShouldReturnError()
    {
        $client = new Zend_Http_Client($this->apiBaseUrl . '/customers/');
        $response = $client->request('GET');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(null, $response->getHeader('Set-Cookie'));
        $this->assertEquals(401, $response->getStatus());

        $result = $response->getBody();

        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);

        $this->assertArrayHasKey('message', $result);
    }

    public function testGetCustomersWithInvalidIdShouldReturnMessage()
    {
        $id = 99999999;
        $response = $this->getHttpClient()
                         ->setUri($this->apiBaseUrl . '/customers/' . $id)
                         ->request('GET');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(null, $response->getHeader('Set-Cookie'));
        $this->assertEquals(404, $response->getStatus());

        $result = $response->getBody();

        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);

        $this->assertArrayHasKey('message', $result);
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

        $requestData = array(
            "password" => "fooobar",
            "active"   => true,
            "email"    => 'test@foobar.com',

            "firstlogin" => $firstlogin,
            "lastlogin"  => $lastlogin,
            "paymentId"  => 2,

            "billing" => array(
                "firstName" => "Max",
                "lastName"  => "Mustermann",
                "birthday"  => $birthday,
            ),

            "shipping" => array(
                "salutation" => "Mr",
                "company"    => "Widgets Inc.",
                "firstName"  => "Max",
                "lastName"   => "Mustermann",
            ),

            "debit" => array(
                "account"       => "Fake Account",
                "bankCode"      => "55555555",
                "bankName"      => "Fake Bank",
                "accountHolder" => "Max Mustermann",
            ),
        );

        $requestData = Zend_Json::encode($requestData);
        $client->setRawData($requestData, 'application/json; charset=UTF-8');

        $response = $client->request('POST');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(null, $response->getHeader('Set-Cookie'));
        $this->assertEquals(201, $response->getStatus());
        $this->assertArrayHasKey('Location', $response->getHeaders());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        $location   = $response->getHeader('Location');
        $identifier = (int) array_pop(explode('/', $location));

        $this->assertGreaterThan(0, $identifier);

        return $identifier;
    }

    /**
     * Tests that a Post with debit info also creates a PaymentData instance
     * Can be removed after s_user_debit is removed
     *
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

        $requestData = array(
            "password" => "fooobar",
            "active"   => true,
            "email"    => 'test1@foobar.com',

            "firstlogin" => $firstlogin,
            "lastlogin"  => $lastlogin,

            "billing" => array(
                "firstName" => "Max",
                "lastName"  => "Mustermann",
                "birthday"  => $birthday,
            ),

            "shipping" => array(
                "salutation" => "Mr",
                "company"    => "Widgets Inc.",
                "firstName"  => "Max",
                "lastName"   => "Mustermann",
            ),

            "debit" => array(
                "account"       => "Fake Account",
                "bankCode"      => "55555555",
                "bankName"      => "Fake Bank",
                "accountHolder" => "Max Mustermann",
            ),
        );

        $requestData = Zend_Json::encode($requestData);
        $client->setRawData($requestData, 'application/json; charset=UTF-8');

        $response = $client->request('POST');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(null, $response->getHeader('Set-Cookie'));
        $this->assertEquals(201, $response->getStatus());
        $this->assertArrayHasKey('Location', $response->getHeaders());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        $location   = $response->getHeader('Location');
        $identifier = (int) array_pop(explode('/', $location));

        $this->assertGreaterThan(0, $identifier);

        $customer = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->find($identifier);
        $paymentData = array_shift($customer->getPaymentData()->toArray());
        $debitData = $customer->getDebit();

        $this->assertNotNull($paymentData);
        $this->assertEquals('Max Mustermann', $paymentData->getAccountHolder());
        $this->assertEquals('Fake Account', $paymentData->getAccountNumber());
        $this->assertEquals('Fake Bank', $paymentData->getBankName());
        $this->assertEquals('55555555', $paymentData->getBankCode());

        $this->assertNotNull($debitData);
        $this->assertEquals('Max Mustermann', $debitData->getAccountHolder());
        $this->assertEquals('Fake Account', $debitData->getAccount());
        $this->assertEquals('Fake Bank', $debitData->getBankName());
        $this->assertEquals('55555555', $debitData->getBankCode());

        $this->testDeleteCustomersShouldBeSuccessful($identifier);
    }

    /**
     * Tests that a Post with payment data info for debit also creates debit data in s_user_debit
     * Can be removed after s_user_debit is removed
     *
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

        $requestData = array(
            "password" => "fooobar",
            "active"   => true,
            "email"    => 'test2@foobar.com',

            "firstlogin" => $firstlogin,
            "lastlogin"  => $lastlogin,

            "billing" => array(
                "firstName" => "Max",
                "lastName"  => "Mustermann",
                "birthday"  => $birthday,
            ),

            "shipping" => array(
                "salutation" => "Mr",
                "company"    => "Widgets Inc.",
                "firstName"  => "Max",
                "lastName"   => "Mustermann",
            ),

            "paymentData" => array(
                array(
                    "paymentMeanId"   => 2,
                    "accountNumber" => "Fake Account",
                    "bankCode"      => "55555555",
                    "bankName"      => "Fake Bank",
                    "accountHolder" => "Max Mustermann",
                ),
            ),
        );

        $requestData = Zend_Json::encode($requestData);
        $client->setRawData($requestData, 'application/json; charset=UTF-8');

        $response = $client->request('POST');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(null, $response->getHeader('Set-Cookie'));
        $this->assertEquals(201, $response->getStatus());
        $this->assertArrayHasKey('Location', $response->getHeaders());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        $location   = $response->getHeader('Location');
        $identifier = (int) array_pop(explode('/', $location));

        $this->assertGreaterThan(0, $identifier);

        $customer = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->find($identifier);
        $paymentData = array_shift($customer->getPaymentData()->toArray());
        $debitData = $customer->getDebit();

        $this->assertNotNull($paymentData);
        $this->assertEquals('Max Mustermann', $paymentData->getAccountHolder());
        $this->assertEquals('Fake Account', $paymentData->getAccountNumber());
        $this->assertEquals('Fake Bank', $paymentData->getBankName());
        $this->assertEquals('55555555', $paymentData->getBankCode());

        $this->assertNotNull($debitData);
        $this->assertEquals('Max Mustermann', $debitData->getAccountHolder());
        $this->assertEquals('Fake Account', $debitData->getAccount());
        $this->assertEquals('Fake Bank', $debitData->getBankName());
        $this->assertEquals('55555555', $debitData->getBankCode());

        $this->testDeleteCustomersShouldBeSuccessful($identifier);
    }

    public function testPostCustomersWithInvalidDataShouldReturnError()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/customers/');

        $requestData = array(
            'active'  => true,
            'email'   => 'invalid',
            'billing' => array(
                'firstName' => 'Max',
                'lastName'  => 'Mustermann',
            ),
        );
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('POST');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(null, $response->getHeader('Set-Cookie'));
        $this->assertEquals(400, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    /**
     * @depends testPostCustomersShouldBeSuccessful
     */
    public function testGetCustomersWithIdShouldBeSuccessful($id)
    {
        $response = $this->getHttpClient()
                         ->setUri($this->apiBaseUrl . '/customers/' . $id)
                         ->request('GET');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(null, $response->getHeader('Set-Cookie'));
        $this->assertEquals(200, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        $this->assertArrayHasKey('data', $result);

        $data = $result['data'];
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('active', $data);
        $this->assertArrayHasKey('paymentData', $data);

        $this->assertEquals('test@foobar.com', $data['email']);

        $paymentInfo = array_shift($data['paymentData']);

        $this->assertEquals('Max Mustermann', $paymentInfo['accountHolder']);
        $this->assertEquals('55555555', $paymentInfo['bankCode']);
        $this->assertEquals('Fake Bank', $paymentInfo['bankName']);
        $this->assertEquals('Fake Account', $paymentInfo['accountNumber']);
    }

    public function testPutBatchCustomersShouldFail()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/customers/');

        $requestData = array(
            'active'  => true,
            'email'   => 'test@foobar.com'
        );
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('PUT');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(null, $response->getHeader('Set-Cookie'));
        $this->assertEquals(405, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertEquals('This resource has no support for batch operations.', $result['message']);
    }

    /**
     * @depends testPostCustomersShouldBeSuccessful
     */
    public function testPutCustomersWithInvalidDataShouldReturnError($id)
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/customers/' . $id);

        $requestData = array(
            'active'  => true,
            'email'  => 'invalid',
        );
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('PUT');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(null, $response->getHeader('Set-Cookie'));
        $this->assertEquals(400, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);

        $this->assertArrayHasKey('message', $result);
    }

    /**
     * @depends testPostCustomersShouldBeSuccessful
     */
    public function testPutCustomersShouldBeSuccessful($id)
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/customers/' . $id);

        $requestData = array(
            'active'  => true,
            'email'   => 'test@foobar.com'
        );
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('PUT');

        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertNull(
            $response->getHeader('Set-Cookie'),
            'There should be no set-cookie header set.'
        );
        $this->assertNull(
            $response->getHeader('location',
            'There should be no location header set.'
        ));

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        return $id;
    }

    /**
     * @depends testPostCustomersShouldBeSuccessful
     */
    public function testDeleteCustomersShouldBeSuccessful($id)
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/customers/' . $id);

        $response = $client->request('DELETE');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(null, $response->getHeader('Set-Cookie'));
        $this->assertEquals(200, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        return $id;
    }

    public function testDeleteCustomersWithInvalidIdShouldReturnMessage()
    {
        $id = 99999999;
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/customers/' . $id);

        $response = $client->request('DELETE');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(null, $response->getHeader('Set-Cookie'));
        $this->assertEquals(404, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);

        $this->assertArrayHasKey('message', $result);
    }

    public function testPutCustomersWithInvalidIdShouldReturnMessage()
    {
        $id = 99999999;
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/customers/' . $id);

        $requestData = array(
            'active'  => true,
            'email'   => 'test@foobar.com'
        );
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('PUT');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(null, $response->getHeader('Set-Cookie'));
        $this->assertEquals(404, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);

        $this->assertArrayHasKey('message', $result);
    }

    public function testGetCustomersShouldBeSuccessful()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/customers');
        $result = $client->request('GET');

        $this->assertEquals('application/json', $result->getHeader('Content-Type'));
        $this->assertEquals(null, $result->getHeader('Set-Cookie'));
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
