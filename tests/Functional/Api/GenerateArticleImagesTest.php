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

use Enlight_Controller_Response_ResponseTestCase;

class GenerateArticleImagesTest extends AbstractApiTestCase
{
    public function testRequestWithoutAuthenticationShouldReturnError(): void
    {
        $this->client->request('GET', '/api/generateArticleImages');
        $response = $this->client->getResponse();
        static::assertInstanceOf(Enlight_Controller_Response_ResponseTestCase::class, $response);

        static::assertSame('application/json', $response->getHeader('Content-Type'));
        static::assertSame(401, $response->getStatusCode());

        $result = $response->getBody();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);
        static::assertArrayHasKey('message', $result);
    }

    public function testBatchDeleteShouldFail(): void
    {
        $this->authenticatedApiRequest('DELETE', '/api/generateArticleImages');
        $response = $this->client->getResponse();

        static::assertSame('application/json', $response->headers->get('Content-Type'));
        static::assertNull($response->headers->get('Set-Cookie'));
        static::assertSame(405, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);
        static::assertSame('This resource has no support for batch operations', $result['message']);
    }

    public function testBatchPutShouldFail(): void
    {
        $this->authenticatedApiRequest('PUT', '/api/generateArticleImages');
        $response = $this->client->getResponse();

        static::assertSame('application/json', $response->headers->get('content-type'));
        static::assertNull($response->headers->get('set-cookie'));
        static::assertSame(405, $response->getStatusCode());

        $result = $response->getContent();
        static::assertIsString($result);
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertFalse($result['success']);
        static::assertSame('This resource has no support for batch operations', $result['message']);
    }
}
