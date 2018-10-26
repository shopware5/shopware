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
class Shopware_Tests_Api_PaymentMethodsTest extends PHPUnit\Framework\TestCase
{
    const API_PATH = '/PaymentMethods/';
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
        $client = new Zend_Http_Client($this->apiBaseUrl . self::API_PATH);
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

    public function testGetPaymentWithInvalidIdShouldReturnMessage()
    {
        $id = 99999999;
        $response = $this->getHttpClient()
            ->setUri($this->apiBaseUrl . self::API_PATH . $id)
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

    public function testGetPaymentShouldBeSuccessful()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . self::API_PATH);
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

    public function testPostPaymentShouldBeSuccessful()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . self::API_PATH);

        $requestData = [
            'name' => 'debit2',
            'description' => 'Lastschrift2',
            'position' => '6',
        ];
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('POST');

        $this->assertEquals(201, $response->getStatus());
        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertNull(
            $response->getHeader('Set-Cookie'),
            'There should be no set-cookie header set.'
        );

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        $location = $response->getHeader('Location');
        $identifier = (int) array_pop(explode('/', $location));

        $this->assertGreaterThan(0, $identifier);

        // Check ID
        $Payment = Shopware()->Models()->find('Shopware\Models\Payment\Payment', $identifier);
        $this->assertGreaterThan(0, $Payment->getId());

        return $identifier;
    }

    /**
     * @depends testPostPaymentShouldBeSuccessful
     *
     * @param $identifier
     */
    public function testGetPaymentWithIdShouldBeSuccessful($identifier)
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . self::API_PATH . $identifier);
        $result = $client->request('GET');

        $this->assertEquals('application/json', $result->getHeader('Content-Type'));
        $this->assertEquals(null, $result->getHeader('Set-Cookie'));
        $this->assertEquals(200, $result->getStatus());

        $result = $result->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        $this->assertArrayHasKey('data', $result);

        $data = $result['data'];
        $this->assertInternalType('array', $data);
    }

    /**
     * @depends testPostPaymentShouldBeSuccessful
     *
     * @param $id
     */
    public function testDeletePaymentWithIdShouldBeSuccessful($id)
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . self::API_PATH . $id);

        $response = $client->request('DELETE');

        $this->assertEquals('application/json', $response->getHeader('Content-Type'));
        $this->assertEquals(null, $response->getHeader('Set-Cookie'));
        $this->assertEquals(200, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }

    public function testDeletePaymentWithInvalidIdShouldFailWithMessage()
    {
        $id = 9999999;
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . self::API_PATH . $id);

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
}
