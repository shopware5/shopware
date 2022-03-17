<?php

declare(strict_types=1);
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

        static::assertSame('application/json', $response->headers->get('Content-Type'));
        static::assertNull($response->headers->get('Set-Cookie'));
        static::assertSame(200, $response->getStatusCode());

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
                'link' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAABhGlDQ1BJQ0MgcHJvZmlsZQAAKJF9kT1Iw0AcxV9bpSJVh3YQcchQnSyI36NWoQgVQq3QqoPJpV/QpCFJcXEUXAsOfixWHVycdXVwFQTBDxA3NydFFynxf0mhRYwHx/14d+9x9w7w18tMNTtGAVWzjFQiLmSyq0LwFQGE0YtJTEvM1OdEMQnP8XUPH1/vYjzL+9yfo0fJmQzwCcSzTDcs4g3iqU1L57xPHGFFSSE+Jx4x6ILEj1yXXX7jXHDYzzMjRjo1TxwhFgptLLcxKxoq8QRxVFE1yvdnXFY4b3FWy1XWvCd/YSinrSxzneYgEljEEkQIkFFFCWVYiNGqkWIiRftxD/+A4xfJJZOrBEaOBVSgQnL84H/wu1szPz7mJoXiQOeLbX8MAcFdoFGz7e9j226cAIFn4Epr+St1YOaT9FpLix4BfdvAxXVLk/eAyx2g/0mXDMmRAjT9+TzwfkbflAXCt0D3mttbcx+nD0CaukreAAeHwHCBstc93t3V3tu/Z5r9/QDUInLOjro6CQAAAAlwSFlzAAAuIwAALiMBeKU/dgAAAAd0SU1FB+UDEw42F48Am4gAAAAZdEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVBXgQ4XAAAADElEQVQI12NgmPsfAAI9AZ115ELHAAAAAElFTkSuQmCC',
            ],
        ];

        $this->authenticatedApiRequest('POST', '/api/manufacturers', [], $requestData);
        $response = $this->client->getResponse();

        static::assertSame('application/json', $response->headers->get('Content-Type'));
        static::assertNull($response->headers->get('Set-Cookie'));
        static::assertSame(400, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testPostManufacturersShouldBeSuccessful(): int
    {
        $requestData = [
            'name' => 'Foo Bar',
            'description' => 'flipflops',
            'image' => [
                'link' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAABhGlDQ1BJQ0MgcHJvZmlsZQAAKJF9kT1Iw0AcxV9bpSJVh3YQcchQnSyI36NWoQgVQq3QqoPJpV/QpCFJcXEUXAsOfixWHVycdXVwFQTBDxA3NydFFynxf0mhRYwHx/14d+9x9w7w18tMNTtGAVWzjFQiLmSyq0LwFQGE0YtJTEvM1OdEMQnP8XUPH1/vYjzL+9yfo0fJmQzwCcSzTDcs4g3iqU1L57xPHGFFSSE+Jx4x6ILEj1yXXX7jXHDYzzMjRjo1TxwhFgptLLcxKxoq8QRxVFE1yvdnXFY4b3FWy1XWvCd/YSinrSxzneYgEljEEkQIkFFFCWVYiNGqkWIiRftxD/+A4xfJJZOrBEaOBVSgQnL84H/wu1szPz7mJoXiQOeLbX8MAcFdoFGz7e9j226cAIFn4Epr+St1YOaT9FpLix4BfdvAxXVLk/eAyx2g/0mXDMmRAjT9+TzwfkbflAXCt0D3mttbcx+nD0CaukreAAeHwHCBstc93t3V3tu/Z5r9/QDUInLOjro6CQAAAAlwSFlzAAAuIwAALiMBeKU/dgAAAAd0SU1FB+UDEw42F48Am4gAAAAZdEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVBXgQ4XAAAADElEQVQI12NgmPsfAAI9AZ115ELHAAAAAElFTkSuQmCC',
            ],
        ];

        $this->authenticatedApiRequest('POST', '/api/manufacturers', [], $requestData);
        $response = $this->client->getResponse();

        static::assertSame(201, $response->getStatusCode());
        static::assertSame('application/json', $response->headers->get('Content-Type'));
        static::assertNull(
            $response->headers->get('Set-Cookie'),
            'There should be no set-cookie header set.'
        );

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        $location = $response->headers->get('location');
        static::assertIsString($location);
        $location = explode('/', $location);
        $identifier = (int) array_pop($location);

        static::assertGreaterThan(0, $identifier);

        return $identifier;
    }

    /**
     * @depends testPostManufacturersShouldBeSuccessful
     */
    public function testPutManufacturersWithInvalidDataShouldReturnError(int $id): void
    {
        $requestData = [
            'description' => 'invalid',
            'image' => null,
        ];

        $this->authenticatedApiRequest('PUT', '/api/manufacturers/' . $id, [], $requestData);
        $response = $this->client->getResponse();

        static::assertSame('application/json', $response->headers->get('Content-Type'));
        static::assertSame(400, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    /**
     * @depends testPostManufacturersShouldBeSuccessful
     */
    public function testPutManufacturersShouldBeSuccessful(int $id): int
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

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('application/json', $response->headers->get('Content-Type'));
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
    public function testPutManufacturersImageShouldBeSuccessful(int $id): int
    {
        $requestData = [
            'name' => 'Foo Bar',
            'description' => 'updated image assignment',
            'image' => [
                'link' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAABhGlDQ1BJQ0MgcHJvZmlsZQAAKJF9kT1Iw0AcxV9bpSJVh3YQcchQnSyI36NWoQgVQq3QqoPJpV/QpCFJcXEUXAsOfixWHVycdXVwFQTBDxA3NydFFynxf0mhRYwHx/14d+9x9w7w18tMNTtGAVWzjFQiLmSyq0LwFQGE0YtJTEvM1OdEMQnP8XUPH1/vYjzL+9yfo0fJmQzwCcSzTDcs4g3iqU1L57xPHGFFSSE+Jx4x6ILEj1yXXX7jXHDYzzMjRjo1TxwhFgptLLcxKxoq8QRxVFE1yvdnXFY4b3FWy1XWvCd/YSinrSxzneYgEljEEkQIkFFFCWVYiNGqkWIiRftxD/+A4xfJJZOrBEaOBVSgQnL84H/wu1szPz7mJoXiQOeLbX8MAcFdoFGz7e9j226cAIFn4Epr+St1YOaT9FpLix4BfdvAxXVLk/eAyx2g/0mXDMmRAjT9+TzwfkbflAXCt0D3mttbcx+nD0CaukreAAeHwHCBstc93t3V3tu/Z5r9/QDUInLOjro6CQAAAAlwSFlzAAAuIwAALiMBeKU/dgAAAAd0SU1FB+UDEw42F48Am4gAAAAZdEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVBXgQ4XAAAADElEQVQI12NgmPsfAAI9AZ115ELHAAAAAElFTkSuQmCC',
            ],
        ];

        $this->authenticatedApiRequest('PUT', '/api/manufacturers/' . $id, [], $requestData);
        $response = $this->client->getResponse();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('application/json', $response->headers->get('Content-Type'));
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
