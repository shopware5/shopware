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

use PHPUnit\Framework\TestCase;
use Zend_Http_Client;
use Zend_Http_Client_Adapter_Curl;
use Zend_Json;

class ManufacturerTest extends TestCase
{
    public $apiBaseUrl = '';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
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

    public function testGetManufacturersShouldBeSuccessful()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/manufacturers');
        $result = $client->request('GET');

        static::assertEquals('application/json', $result->getHeader('Content-Type'));
        static::assertEquals(null, $result->getHeader('Set-Cookie'));
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

    public function testPostManufacturersWithoutNameShouldFailWithMessage()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/manufacturers');

        $requestData = [
            'description' => 'flipflops',
            'image' => [
                'link' => 'http://assets.shopware.com/sw_logo_white.png',
            ],
        ];
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('POST');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(null, $response->getHeader('Set-Cookie'));
        static::assertEquals(400, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testPostManufacturersShouldBeSuccessful()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/manufacturers');

        $requestData = [
            'name' => 'Foo Bar',
            'description' => 'flipflops',
            'image' => [
                'link' => 'http://assets.shopware.com/sw_logo_white.png',
            ],
        ];
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('POST');

        static::assertEquals(201, $response->getStatus());
        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertNull(
            $response->getHeader('Set-Cookie'),
            'There should be no set-cookie header set.'
        );

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        $location = $response->getHeader('Location');
        $identifier = (int) array_pop(explode('/', $location));

        static::assertGreaterThan(0, $identifier);

        // Check supplier id
        $supplier = Shopware()->Models()->find('Shopware\Models\Article\Supplier', $identifier);
        static::assertGreaterThan(0, $supplier->getId());

        return $identifier;
    }

    /**
     * @depends testPostManufacturersShouldBeSuccessful
     */
    public function testPutManufacturersWithInvalidDataShouldReturnError($id)
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/manufacturers/' . $id);

        $requestData = [
            'description' => 'invalid',
            'image' => null,
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
     * @depends testPostManufacturersShouldBeSuccessful
     */
    public function testPutManufacturersShouldBeSuccessful($id)
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/manufacturers/' . $id);

        $requestData = [
            'name' => 'Bar Foo',
            'image' => null,
            'link' => 'http://www.shopware.com',
            'description' => 'A valid description',
            'metaTitle' => 'This is my meta title',
            'metaKeywords' => 'some meta keywords',
            'metaDescription' => 'Some meta description',
            'changed' => date('Y-m-d H:i:s'),
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
     * @depends testPutManufacturersShouldBeSuccessful
     */
    public function testPutManufacturersImageShouldBeSuccessful($id)
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/manufacturers/' . $id);

        $requestData = [
            'name' => 'Foo Bar',
            'description' => 'updated image assignment',
            'image' => [
                'link' => 'http://assets.shopware.com/sw_logo_white.png',
            ],
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
}
