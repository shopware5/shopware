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

class CategoryTest extends TestCase
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
        $client = new Zend_Http_Client($this->apiBaseUrl . '/categories');
        $response = $client->request('GET');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(null, $response->getHeader('Set-Cookie'));
        static::assertEquals(401, $response->getStatus());

        $result = $response->getBody();

        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testGetCategoriesWithInvalidIdShouldReturnMessage()
    {
        $id = 99999999;
        $response = $this->getHttpClient()
            ->setUri($this->apiBaseUrl . '/categories/' . $id)
            ->request('GET');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(null, $response->getHeader('Set-Cookie'));
        static::assertEquals(404, $response->getStatus());

        $result = $response->getBody();

        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testPostCategoriesShouldBeSuccessful()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/categories/');

        $requestData = [
            'name' => 'Test Category',
            'parentId' => 3,
        ];

        $requestData = Zend_Json::encode($requestData);
        $client->setRawData($requestData, 'application/json; charset=UTF-8');

        $response = $client->request('POST');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(null, $response->getHeader('Set-Cookie'));
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

    public function testPostCategoriesWithInvalidDataShouldReturnError()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/categories/');

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
        static::assertEquals(null, $response->getHeader('Set-Cookie'));
        static::assertEquals(400, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);
        static::assertArrayHasKey('message', $result);
    }

    /**
     * @depends testPostCategoriesShouldBeSuccessful
     */
    public function testGetCategoriesWithIdShouldBeSuccessful($id)
    {
        $response = $this->getHttpClient()
            ->setUri($this->apiBaseUrl . '/categories/' . $id)
            ->request('GET');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(null, $response->getHeader('Set-Cookie'));
        static::assertEquals(200, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        static::assertArrayHasKey('data', $result);

        $data = $result['data'];
        static::assertInternalType('array', $data);
        static::assertArrayHasKey('id', $data);
        static::assertArrayHasKey('parentId', $data);
    }

    /**
     * @depends testPostCategoriesShouldBeSuccessful
     */
    public function testPutCategoriesWithInvalidDataShouldReturnError($id)
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/categories/' . $id);

        $requestData = [
            'active' => true,
            'email' => 'invalid',
        ];
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('PUT');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(null, $response->getHeader('Set-Cookie'));
        static::assertEquals(400, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    /**
     * @depends testPostCategoriesShouldBeSuccessful
     */
    public function testPutCategoriesShouldBeSuccessful($id)
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/categories/' . $id);

        $requestData = [
            'name' => 'Changed test category',
        ];
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('PUT');

        static::assertEquals(200, $response->getStatus());
        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertNull(
            $response->getHeader('Set-Cookie'),
            'There should be no set-cookie header set.'
        );
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

        // revalidate
        $response = $this->getHttpClient()
            ->setUri($this->apiBaseUrl . '/categories/' . $id)
            ->request('GET');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(null, $response->getHeader('Set-Cookie'));
        static::assertEquals(200, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        static::assertArrayHasKey('data', $result);

        $data = $result['data'];
        static::assertInternalType('array', $data);
        static::assertArrayHasKey('id', $data);
        static::assertArrayHasKey('name', $data);
        static::assertEquals('Changed test category', $data['name']);

        return $id;
    }

    /**
     * @depends testPostCategoriesShouldBeSuccessful
     */
    public function testDeleteCategoriesShouldBeSuccessful($id)
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/categories/' . $id);

        $response = $client->request('DELETE');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(null, $response->getHeader('Set-Cookie'));
        static::assertEquals(200, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        return $id;
    }

    public function testDeleteCategoryWithInvalidIdShouldReturnMessage()
    {
        $id = 99999999;
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/categories/' . $id);

        $response = $client->request('DELETE');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(null, $response->getHeader('Set-Cookie'));
        static::assertEquals(404, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testPutCategoriesWithInvalidIdShouldReturnMessage()
    {
        $id = 99999999;
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/categories/' . $id);

        $requestData = [
            'active' => true,
            'email' => 'test@foobar.com',
        ];
        $requestData = Zend_Json::encode($requestData);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('PUT');

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(null, $response->getHeader('Set-Cookie'));
        static::assertEquals(404, $response->getStatus());

        $result = $response->getBody();
        $result = Zend_Json::decode($result);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testGetCategoriesShouldBeSuccessful()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/categories');
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

    public function testUpdateCategoriesWithSortingWithInvalidArray()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/categories/3');

        $requestData = json_encode([
            'name' => 'Deutsch',
            'manualSorting' => [
                'id' => 1,
            ],
        ]);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('PUT');

        $result = json_decode($response->getBody(), true);

        static::assertArrayHasKey('success', $result);
        static::assertArrayHasKey('message', $result);
        static::assertEquals('Field product_id is missing in manualSorting array', $result['message']);
    }

    public function testUpdateCategoriesWithSortingWithValidArray()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/categories/3');

        $requestData = json_encode([
            'name' => 'Deutsch',
            'manualSorting' => [
                [
                    'product_id' => 3,
                    'position' => 1,
                ],
            ],
        ]);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $response = $client->request('PUT');

        $result = json_decode($response->getBody(), true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);
        static::assertEquals(3, $result['data']['id']);
    }

    /**
     * @depends testUpdateCategoriesWithSortingWithValidArray
     */
    public function testUpdateCategoriesWithSortingRead()
    {
        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/categories/3');

        $response = $client->request('GET');
        $result = json_decode($response->getBody(), true);

        static::assertArrayHasKey('data', $result);
        static::assertArrayHasKey('manualSorting', $result['data']);
        static::assertNotEmpty($result['data']['manualSorting']);

        $requestData = json_encode([
            'name' => 'Deutsch',
            'manualSorting' => [],
        ]);

        $client->setRawData($requestData, 'application/json; charset=UTF-8');
        $client->request('PUT');

        $client = $this->getHttpClient()->setUri($this->apiBaseUrl . '/categories/3');
        $response = $client->request('GET');
        $result = json_decode($response->getBody(), true);

        static::assertArrayHasKey('data', $result);
        static::assertArrayHasKey('manualSorting', $result['data']);
        static::assertEmpty($result['data']['manualSorting']);
    }
}
