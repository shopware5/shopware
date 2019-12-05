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

/**
 * @covers \Shopware_Controllers_Api_Categories
 */
class CategoryTest extends AbstractApiTestCase
{
    public function testRequestWithoutAuthenticationShouldReturnError(): void
    {
        $this->client->request('GET', '/api/categories/');
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(401, $response->getStatusCode());

        $result = $response->getContent();

        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testGetCategoriesWithInvalidIdShouldReturnMessage(): void
    {
        $id = 99999999;

        $this->authenticatedApiRequest('GET', '/api/categories/' . $id);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(404, $response->getStatusCode());

        $result = $response->getContent();

        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testPostCategoriesShouldBeSuccessful(): string
    {
        $requestData = [
            'name' => 'Test Category',
            'parentId' => 3,
        ];

        $this->authenticatedApiRequest('POST', '/api/categories/', [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(201, $response->getStatusCode());
        static::assertArrayHasKey('location', $response->headers->all());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        $location = $response->headers->get('Location');
        $identifier = (int) array_pop(explode('/', $location));

        static::assertGreaterThan(0, $identifier);

        return $identifier;
    }

    public function testPostCategoriesWithInvalidDataShouldReturnError(): void
    {
        $requestData = [
            'active' => true,
            'email' => 'invalid',
            'billing' => [
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
            ],
        ];

        $this->authenticatedApiRequest('POST', '/api/categories/', [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(400, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);
        static::assertArrayHasKey('message', $result);
    }

    /**
     * @depends testPostCategoriesShouldBeSuccessful
     */
    public function testGetCategoriesWithIdShouldBeSuccessful($id): void
    {
        $this->authenticatedApiRequest('GET', '/api/categories/' . $id, []);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(200, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        static::assertArrayHasKey('data', $result);

        $data = $result['data'];
        static::assertIsArray($data);
        static::assertArrayHasKey('id', $data);
        static::assertArrayHasKey('parentId', $data);
    }

    /**
     * @depends testPostCategoriesShouldBeSuccessful
     */
    public function testPutCategoriesWithInvalidDataShouldReturnError(string $id): void
    {
        $requestData = [
            'active' => true,
            'email' => 'invalid',
        ];

        $this->authenticatedApiRequest('PUT', '/api/categories/' . $id, [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(400, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    /**
     * @depends testPostCategoriesShouldBeSuccessful
     *
     * @return string
     */
    public function testPutCategoriesShouldBeSuccessful(string $id)
    {
        $requestData = [
            'name' => 'Changed test category',
        ];

        $this->authenticatedApiRequest('PUT', '/api/categories/' . $id, [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals(200, $response->getStatusCode());
        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertNull(
            $response->headers->get('Set-Cookie'),
            'There should be no set-cookie header set.'
        );
        static::assertNull(
            $response->headers->get('location'),
            'There should be no location header set.'
        );

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        // revalidate
        $this->authenticatedApiRequest('GET', '/api/categories/' . $id, []);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(200, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        static::assertArrayHasKey('data', $result);

        $data = $result['data'];
        static::assertIsArray($data);
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
        $this->authenticatedApiRequest('DELETE', '/api/categories/' . $id, []);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(200, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        return $id;
    }

    public function testDeleteCategoryWithInvalidIdShouldReturnMessage()
    {
        $id = 99999999;

        $this->authenticatedApiRequest('DELETE', '/api/categories/' . $id, []);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(404, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testPutCategoriesWithInvalidIdShouldReturnMessage(): void
    {
        $id = 99999999;

        $requestData = [
            'active' => true,
            'email' => 'test@foobar.com',
        ];

        $this->authenticatedApiRequest('PUT', '/api/categories/' . $id, [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(404, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testGetCategoriesShouldBeSuccessful()
    {
        $this->authenticatedApiRequest('GET', '/api/categories/', []);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->getHeader('Content-Type'));
        static::assertEquals(null, $response->getHeader('Set-Cookie'));
        static::assertEquals(200, $response->getStatusCode());

        $response = $response->getContent();
        $response = json_decode($response, true);

        static::assertArrayHasKey('success', $response);
        static::assertTrue($response['success']);

        static::assertArrayHasKey('data', $response);

        static::assertArrayHasKey('total', $response);
        static::assertIsInt($response['total']);

        $data = $response['data'];
        static::assertIsArray($data);
    }

    public function testUpdateCategoriesWithSortingWithInvalidArray(): void
    {
        $requestData = [
            'name' => 'Deutsch',
            'manualSorting' => [
                'id' => 1,
            ],
        ];

        $this->authenticatedApiRequest('PUT', '/api/categories/3', [], $requestData);
        $response = $this->client->getResponse();

        $result = json_decode($response->getContent(), true);

        static::assertArrayHasKey('success', $result);
        static::assertArrayHasKey('message', $result);
        static::assertEquals('Field product_id is missing in manualSorting array', $result['message']);
    }

    public function testUpdateCategoriesWithSortingWithValidArray(): void
    {
        $requestData = [
            'name' => 'Deutsch',
            'manualSorting' => [
                [
                    'product_id' => 3,
                    'position' => 1,
                ],
            ],
        ];

        $this->authenticatedApiRequest('PUT', '/api/categories/3', [], $requestData);
        $response = $this->client->getResponse();

        $result = json_decode($response->getContent(), true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);
        static::assertEquals(3, $result['data']['id']);
    }

    /**
     * @depends testUpdateCategoriesWithSortingWithValidArray
     */
    public function testUpdateCategoriesWithSortingRead(): void
    {
        $this->authenticatedApiRequest('GET', '/api/categories/3', []);
        $response = $this->client->getResponse();

        $result = json_decode($response->getContent(), true);

        static::assertArrayHasKey('data', $result);
        static::assertArrayHasKey('manualSorting', $result['data']);
        static::assertNotEmpty($result['data']['manualSorting']);

        $requestData = [
            'name' => 'Deutsch',
            'manualSorting' => [],
        ];

        $this->authenticatedApiRequest('PUT', '/api/categories/3', [], $requestData);

        $this->authenticatedApiRequest('GET', '/api/categories/3', []);
        $response = $this->client->getResponse();

        $result = json_decode($response->getContent(), true);

        static::assertArrayHasKey('data', $result);
        static::assertArrayHasKey('manualSorting', $result['data']);
        static::assertEmpty($result['data']['manualSorting']);
    }
}
