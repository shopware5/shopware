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

namespace Shopware\Tests\Functional\Components\Api;

use DateTime;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Resource\Manufacturer;
use Shopware\Components\Api\Resource\Resource;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Media;

class ManufacturerTest extends TestCase
{
    /**
     * @var Manufacturer
     */
    protected $resource;

    public function createResource(): Manufacturer
    {
        return new Manufacturer();
    }

    public function testCreateShouldBeSuccessful(): int
    {
        $date = new DateTime();
        $date->modify('-3 day');
        $changed = $date->format(DateTime::ISO8601);

        $testData = [
            'name' => 'fooobar',
            'description' => 'foobar description with exceptionell long text',
            'link' => 'http://shopware.com',
            'image' => [
                'link' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAABhGlDQ1BJQ0MgcHJvZmlsZQAAKJF9kT1Iw0AcxV9bpSJVh3YQcchQnSyI36NWoQgVQq3QqoPJpV/QpCFJcXEUXAsOfixWHVycdXVwFQTBDxA3NydFFynxf0mhRYwHx/14d+9x9w7w18tMNTtGAVWzjFQiLmSyq0LwFQGE0YtJTEvM1OdEMQnP8XUPH1/vYjzL+9yfo0fJmQzwCcSzTDcs4g3iqU1L57xPHGFFSSE+Jx4x6ILEj1yXXX7jXHDYzzMjRjo1TxwhFgptLLcxKxoq8QRxVFE1yvdnXFY4b3FWy1XWvCd/YSinrSxzneYgEljEEkQIkFFFCWVYiNGqkWIiRftxD/+A4xfJJZOrBEaOBVSgQnL84H/wu1szPz7mJoXiQOeLbX8MAcFdoFGz7e9j226cAIFn4Epr+St1YOaT9FpLix4BfdvAxXVLk/eAyx2g/0mXDMmRAjT9+TzwfkbflAXCt0D3mttbcx+nD0CaukreAAeHwHCBstc93t3V3tu/Z5r9/QDUInLOjro6CQAAAAlwSFlzAAAuIwAALiMBeKU/dgAAAAd0SU1FB+UDEw42F48Am4gAAAAZdEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVBXgQ4XAAAADElEQVQI12NgmPsfAAI9AZ115ELHAAAAAElFTkSuQmCC',
            ],

            'metaTitle' => 'test, test',
            'metaKeywords' => 'test, test',
            'metaDescription' => 'Description Test',

            'changed' => $changed,
        ];

        $manufacturer = $this->resource->create($testData);

        static::assertInstanceOf(Supplier::class, $manufacturer);
        static::assertGreaterThan(0, $manufacturer->getId());
        static::assertNotEmpty($manufacturer->getImage());

        static::assertEquals($manufacturer->getMetaDescription(), $testData['metaDescription']);

        return $manufacturer->getId();
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeSuccessful(int $id): void
    {
        $manufacturer = $this->resource->getOne($id);
        static::assertIsArray($manufacturer);
        static::assertGreaterThan(0, $manufacturer['id']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldBeSuccessful(): void
    {
        $result = $this->resource->getList();

        static::assertArrayHasKey('data', $result);
        static::assertArrayHasKey('total', $result);

        static::assertGreaterThanOrEqual(1, $result['total']);
        static::assertGreaterThanOrEqual(1, $result['data']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateShouldBeSuccessful(int $id): int
    {
        $testData = [
            'name' => uniqid((string) rand()) . 'foobar supplier',
        ];

        $manufacturer = $this->resource->update($id, $testData);

        static::assertInstanceOf(Supplier::class, $manufacturer);
        static::assertEquals($id, $manufacturer->getId());

        static::assertEquals($manufacturer->getName(), $testData['name']);

        return $id;
    }

    public function testUpdateWithInvalidIdShouldThrowNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->resource->update(9999999, []);
    }

    public function testUpdateWithMissingIdShouldThrowParameterMissingException(): void
    {
        $this->expectException(ParameterMissingException::class);
        $this->resource->update(0, []);
    }

    /**
     * @depends testUpdateShouldBeSuccessful
     */
    public function testDeleteShouldBeSuccessful(int $id): void
    {
        $manufacturer = $this->resource->delete($id);

        static::assertInstanceOf(Supplier::class, $manufacturer);
        static::assertSame(0, (int) $manufacturer->getId());
    }

    public function testDeleteWithInvalidIdShouldThrowNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->resource->delete(9999999);
    }

    public function testDeleteWithMissingIdShouldThrowParameterMissingException(): void
    {
        $this->expectException(ParameterMissingException::class);
        $this->resource->delete(0);
    }

    public function testMediaUploadOnCreate(): void
    {
        $manufacturer = $this->resource->create([
            'name' => 'foo',
            'image' => [
                'link' => 'file://' . __DIR__ . '/fixtures/test-bild.jpg',
            ],
        ]);

        static::assertNotEmpty($manufacturer->getImage());

        $repo = Shopware()->Container()->get(ModelManager::class)->getRepository(Media::class);
        $media = $repo->findOneBy(['path' => $manufacturer->getImage()]);
        static::assertNotNull($media);

        static::assertEquals(Album::ALBUM_SUPPLIER, $media->getAlbumId());
    }

    public function testMediaUploadOnUpdate(): void
    {
        $manufacturer = $this->resource->create([
            'name' => 'bar',
        ]);

        $this->resource->update($manufacturer->getId(), [
            'name' => 'bar',
            'image' => [
                'link' => 'file://' . __DIR__ . '/fixtures/test-bild.jpg',
            ],
        ]);

        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $manufacturer = $this->resource->getOne($manufacturer->getId());
        static::assertInstanceOf(Supplier::class, $manufacturer);
        static::assertNotEmpty($manufacturer->getImage());

        $repo = Shopware()->Container()->get(ModelManager::class)->getRepository(Media::class);

        $media = $repo->findOneBy(['path' => $manufacturer->getImage()]);
        static::assertNotNull($media);

        static::assertEquals(Album::ALBUM_SUPPLIER, $media->getAlbumId());
    }
}
