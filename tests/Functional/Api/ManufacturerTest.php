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
 * @covers \Shopware_Controllers_Api_Manufacturers
 */
class ManufacturerTest extends AbstractApiTestCase
{
    public function testGetManufacturersShouldBeSuccessful(): void
    {
        $this->authenticatedApiRequest('GET', '/api/manufacturers');
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
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

    public function testPostManufacturersWithoutNameShouldFailWithMessage(): void
    {
        $requestData = [
            'description' => 'flipflops',
            'image' => [
                'link' => 'http://assets.shopware.com/sw_logo_white.png',
            ],
        ];

        $this->authenticatedApiRequest('POST', '/api/manufacturers', [], $requestData);
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

    public function testPostManufacturersShouldBeSuccessful(): string
    {
        $requestData = [
            'name' => 'Foo Bar',
            'description' => 'flipflops',
            'image' => [
                'link' => 'http://assets.shopware.com/sw_logo_white.png',
            ],
        ];

        $this->authenticatedApiRequest('POST', '/api/manufacturers', [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals(201, $response->getStatusCode());
        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertNull(
            $response->headers->get('Set-Cookie'),
            'There should be no set-cookie header set.'
        );

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        $location = $response->headers->get('Location');
        $identifier = (int) array_pop(explode('/', $location));

        static::assertGreaterThan(0, $identifier);

        return $identifier;
    }

    /**
     * @depends testPostManufacturersShouldBeSuccessful
     */
    public function testPutManufacturersWithInvalidDataShouldReturnError($id)
    {
        $requestData = [
            'description' => 'invalid',
            'image' => null,
        ];

        $this->authenticatedApiRequest('PUT', '/api/manufacturers/' . $id, [], $requestData);
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
     * @depends testPostManufacturersShouldBeSuccessful
     */
    public function testPutManufacturersShouldBeSuccessful($id): string
    {
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

        $this->authenticatedApiRequest('PUT', '/api/manufacturers/' . $id, [], $requestData);
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
     * @depends testPutManufacturersShouldBeSuccessful
     */
    public function testPutManufacturersImageShouldBeSuccessful($id)
    {
        $requestData = [
            'name' => 'Foo Bar',
            'description' => 'updated image assignment',
            'image' => [
                'link' => 'http://assets.shopware.com/sw_logo_white.png',
            ],
        ];

        $this->authenticatedApiRequest('PUT', '/api/manufacturers/' . $id, [], $requestData);
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
}
