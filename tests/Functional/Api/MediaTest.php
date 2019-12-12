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

use Shopware\Models\Media\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @covers \Shopware_Controllers_Api_Media
 */
class MediaTest extends AbstractApiTestCase
{
    private const UPLOAD_FILE_NAME = 'test-bild';
    private const UPLOAD_OVERWRITTEN_FILE_NAME = 'a-different-file-name';

    public function testRequestWithoutAuthenticationShouldReturnError(): void
    {
        $this->client->request('GET', '/api/media');
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

    public function testGetMediaWithInvalidIdShouldReturnMessage(): void
    {
        $id = 99999999;

        $this->authenticatedApiRequest('GET', '/api/media/' . $id);
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

    public function testGetMediaShouldBeSuccessful(): void
    {
        $this->authenticatedApiRequest('GET', '/api/media/');
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('content-type'));
        static::assertEquals(null, $response->headers->get('set-cookie'));
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

    public function testPostMediaWithoutImageShouldFailWithMessage(): void
    {
        $requestData = [
            'album' => -1,
            'description' => 'flipflops',
        ];

        $this->authenticatedApiRequest('POST', '/api/media/', [], $requestData);
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

    public function testPostMediaShouldBeSuccessful(): int
    {
        $requestData = [
            'album' => -1,
            'file' => 'http://assets.shopware.com/sw_logo_white.png',
            'description' => 'flipflops',
        ];

        $this->authenticatedApiRequest('POST', '/api/media/', [], $requestData);
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

        $location = $response->headers->get('location');
        $identifier = (int) array_pop(explode('/', $location));

        static::assertGreaterThan(0, $identifier);

        // Check userId
        $media = Shopware()->Models()->find(Media::class, $identifier);
        static::assertGreaterThan(0, $media->getUserId());

        return $identifier;
    }

    /**
     * @depends testPostMediaShouldBeSuccessful
     */
    public function testGetMediaWithIdShouldBeSuccessful($id)
    {
        $this->authenticatedApiRequest('GET', '/api/media/' . $id);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(200, $response->getStatusCode());

        $response = $response->getContent();
        $response = json_decode($response, true);

        static::assertArrayHasKey('success', $response);
        static::assertTrue($response['success']);

        static::assertArrayHasKey('data', $response);

        $data = $response['data'];
        static::assertIsArray($data);
        static::assertArrayHasKey('attribute', $data);
    }

    /**
     * @depends testPostMediaShouldBeSuccessful
     */
    public function testDeleteMediaWithIdShouldBeSuccessful($id)
    {
        $this->authenticatedApiRequest('DELETE', '/api/media/' . $id);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(200, $response->getStatusCode());

        $result = $response->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);
    }

    public function testPostMediaWithFileUploadShouldBeSuccessful(): int
    {
        $fileSource = __DIR__ . '/fixtures/' . self::UPLOAD_FILE_NAME . '.jpg';
        $requestData = [
            'album' => -1,
            'description' => 'flipflops',
        ];

        $upload = new UploadedFile($fileSource, self::UPLOAD_FILE_NAME . '.jpg');

        $files = [
            'file' => $upload,
        ];

        $this->authenticatedApiRequest('POST', '/api/media/', $requestData, null, $files);
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
     * @depends testPostMediaWithFileUploadShouldBeSuccessful
     */
    public function testGetMediaWithUploadedFileByIdShouldBeSuccessful($id): void
    {
        $this->authenticatedApiRequest('GET', '/api/media/' . $id);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(200, $response->getStatusCode());

        $response = $response->getContent();
        $response = json_decode($response, true);

        static::assertArrayHasKey('success', $response);
        static::assertTrue($response['success']);

        static::assertArrayHasKey('data', $response);

        $data = $response['data'];
        static::assertIsArray($data);
        static::assertArrayHasKey('name', $data);
        static::assertEquals(0, strpos($data['name'], self::UPLOAD_FILE_NAME));
    }

    public function testPostMediaWithFileUploadAndOverwrittenNameShouldBeSuccessful(): int
    {
        $fileSource = __DIR__ . '/fixtures/' . self::UPLOAD_FILE_NAME . '.jpg';
        $requestData = [
            'album' => -1,
            'description' => 'flipflops',
            'name' => self::UPLOAD_OVERWRITTEN_FILE_NAME,
        ];

        $upload = new UploadedFile($fileSource, self::UPLOAD_FILE_NAME . '.jpg');

        $files = [
            'file' => $upload,
        ];

        $this->authenticatedApiRequest('POST', '/api/media/', $requestData, null, $files);
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

        $location = $response->headers->get('location');
        $identifier = (int) array_pop(explode('/', $location));

        static::assertGreaterThan(0, $identifier);

        return $identifier;
    }

    /**
     * @depends testPostMediaWithFileUploadAndOverwrittenNameShouldBeSuccessful
     */
    public function testGetMediaWithUploadedFileAndOverwrittenNameByIdShouldBeSuccessful($identifier): void
    {
        $this->authenticatedApiRequest('GET', '/api/media/' . $identifier);
        $response = $this->client->getResponse();

        static::assertEquals('application/json', $response->headers->get('Content-Type'));
        static::assertEquals(null, $response->headers->get('Set-Cookie'));
        static::assertEquals(200, $response->getStatusCode());

        $response = $response->getContent();
        $response = json_decode($response, true);

        static::assertArrayHasKey('success', $response);
        static::assertTrue($response['success']);

        static::assertArrayHasKey('data', $response);

        $data = $response['data'];
        static::assertIsArray($data);
        static::assertArrayHasKey('name', $data);
        static::assertEquals(0, strpos($data['name'], self::UPLOAD_OVERWRITTEN_FILE_NAME));
    }

    public function testDeleteMediaWithInvalidIdShouldFailWithMessage(): void
    {
        $id = 9999999;

        $this->authenticatedApiRequest('DELETE', '/api/media/' . $id);
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

    public function testMediaUploadTraversal(): void
    {
        $file = '../../image.jpg';
        $media = new \Shopware\Components\Api\Resource\Media();

        static::assertEquals('image.jpg', $media->getUniqueFileName('/tmp', $file));
    }
}
