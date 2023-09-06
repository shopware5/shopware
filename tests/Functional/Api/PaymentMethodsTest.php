<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Api;

use Shopware\Models\Payment\Payment;

/**
 * @covers \Shopware_Controllers_Api_PaymentMethods
 */
class PaymentMethodsTest extends AbstractApiTestCase
{
    public function testRequestWithoutAuthenticationShouldReturnError(): void
    {
        $this->client->request('GET', '/api/paymentmethods');
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertNull($response->headers->get('Set-Cookie'));
        static::assertEquals(401, $response->getStatusCode());

        $result = $response->getBody();

        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testGetPaymentWithInvalidIdShouldReturnMessage(): void
    {
        $id = 99999999;

        $this->authenticatedApiRequest('GET', '/api/paymentmethods/' . $id);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('content-type'));
        static::assertNull($response->headers->get('set-cookie'));
        static::assertEquals(404, $response->getStatusCode());

        $result = $response->getContent();

        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }

    public function testGetPaymentShouldBeSuccessful(): void
    {
        $this->authenticatedApiRequest('GET', '/api/paymentmethods');
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('content-type'));
        static::assertNull($response->headers->get('set-cookie'));
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

    public function testPostPaymentShouldBeSuccessful(): int
    {
        $requestData = [
            'name' => 'debit' . uniqid('foo', true),
            'description' => 'Lastschrift2',
            'position' => '6',
        ];

        $this->authenticatedApiRequest('POST', '/api/paymentmethods', [], $requestData);
        $response = $this->client->getResponse();

        static::assertEquals(201, $response->getStatusCode());
        static::assertEquals('application/json', $response->headers->get('content-type'));
        static::assertNull(
            $response->headers->get('set-cookie'),
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

        // Check ID
        $Payment = Shopware()->Models()->find(Payment::class, $identifier);
        static::assertGreaterThan(0, $Payment->getId());

        return $identifier;
    }

    /**
     * @depends testPostPaymentShouldBeSuccessful
     */
    public function testGetPaymentWithIdShouldBeSuccessful(int $identifier): void
    {
        $this->authenticatedApiRequest('GET', '/api/paymentmethods/' . $identifier);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertNull($response->headers->get('Set-Cookie'));
        static::assertEquals(200, $response->getStatusCode());

        $response = $response->getContent();
        $response = json_decode($response, true);

        static::assertArrayHasKey('success', $response);
        static::assertTrue($response['success']);

        static::assertArrayHasKey('data', $response);

        $data = $response['data'];
        static::assertIsArray($data);
    }

    /**
     * @depends testPostPaymentShouldBeSuccessful
     */
    public function testDeletePaymentWithIdShouldBeSuccessful(int $id): void
    {
        $this->authenticatedApiRequest('DELETE', '/api/paymentmethods/' . $id);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertNull($response->headers->get('Set-Cookie'));
        static::assertEquals(200, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);
    }

    public function testDeletePaymentWithInvalidIdShouldFailWithMessage(): void
    {
        $id = 9999999;

        $this->authenticatedApiRequest('DELETE', '/api/paymentmethods/' . $id);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertNull($response->headers->get('Set-Cookie'));
        static::assertEquals(404, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);

        static::assertArrayHasKey('message', $result);
    }
}
