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

namespace Shopware\Tests\Functional\Components\Api;

use Shopware\Components\Api\Resource\Manufacturer;
use Shopware\Components\Api\Resource\Resource;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Media;

class ManufacturerTest extends TestCase
{
    /**
     * @var Manufacturer
     */
    protected $resource;

    /**
     * @return Manufacturer
     */
    public function createResource()
    {
        return new Manufacturer();
    }

    public function testCreateShouldBeSuccessful()
    {
        $date = new \DateTime();
        $date->modify('-3 day');
        $changed = $date->format(\DateTime::ISO8601);

        $testData = [
            'name' => 'fooobar',
            'description' => 'foobar description with exceptionell long text',
            'link' => 'http://shopware.com',
            'image' => [
                'link' => 'http://assets.shopware.com/sw_logo_white.png',
            ],

            'metaTitle' => 'test, test',
            'metaKeywords' => 'test, test',
            'metaDescription' => 'Description Test',

            'changed' => $changed,
        ];

        $manufacturer = $this->resource->create($testData);

        static::assertInstanceOf('\Shopware\Models\Article\Supplier', $manufacturer);
        static::assertGreaterThan(0, $manufacturer->getId());
        static::assertNotEmpty($manufacturer->getImage());

        static::assertEquals($manufacturer->getMetaDescription(), $testData['metaDescription']);

        return $manufacturer->getId();
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeSuccessful($id)
    {
        $manufacturer = $this->resource->getOne($id);
        static::assertGreaterThan(0, $manufacturer['id']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldBeSuccessful()
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
    public function testUpdateShouldBeSuccessful($id)
    {
        $testData = [
            'name' => uniqid(rand()) . 'foobar supplier',
        ];

        $manufacturer = $this->resource->update($id, $testData);

        static::assertInstanceOf('\Shopware\Models\Article\Supplier', $manufacturer);
        static::assertEquals($id, $manufacturer->getId());

        static::assertEquals($manufacturer->getName(), $testData['name']);

        return $id;
    }

    public function testUpdateWithInvalidIdShouldThrowNotFoundException()
    {
        $this->expectException('Shopware\Components\Api\Exception\NotFoundException');
        $this->resource->update(9999999, []);
    }

    public function testUpdateWithMissingIdShouldThrowParameterMissingException()
    {
        $this->expectException('Shopware\Components\Api\Exception\ParameterMissingException');
        $this->resource->update('', []);
    }

    /**
     * @depends testUpdateShouldBeSuccessful
     */
    public function testDeleteShouldBeSuccessful($id)
    {
        $manufacturer = $this->resource->delete($id);

        static::assertInstanceOf('\Shopware\Models\Article\Supplier', $manufacturer);
        static::assertEquals(null, $manufacturer->getId());
    }

    public function testDeleteWithInvalidIdShouldThrowNotFoundException()
    {
        $this->expectException('Shopware\Components\Api\Exception\NotFoundException');
        $this->resource->delete(9999999);
    }

    public function testDeleteWithMissingIdShouldThrowParameterMissingException()
    {
        $this->expectException('Shopware\Components\Api\Exception\ParameterMissingException');
        $this->resource->delete('');
    }

    public function testMediaUploadOnCreate()
    {
        $manufacturer = $this->resource->create([
            'name' => 'foo',
            'image' => [
                'link' => 'file://' . __DIR__ . '/fixtures/test-bild.jpg',
            ],
        ]);

        static::assertNotEmpty($manufacturer->getImage());

        /** @var ModelRepository $repo */
        $repo = Shopware()->Container()->get('models')->getRepository(Media::class);
        /** @var Media $media */
        $media = $repo->findOneBy(['path' => $manufacturer->getImage()]);

        static::assertEquals($media->getAlbumId(), Album::ALBUM_SUPPLIER);
    }

    public function testMediaUploadOnUpdate()
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
        static::assertNotEmpty($manufacturer->getImage());

        /** @var ModelRepository $repo */
        $repo = Shopware()->Container()->get('models')->getRepository(Media::class);

        /** @var Media $media */
        $media = $repo->findOneBy(['path' => $manufacturer->getImage()]);

        static::assertEquals($media->getAlbumId(), Album::ALBUM_SUPPLIER);
    }
}
