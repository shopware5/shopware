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

use Shopware\Components\Api\Resource\Media;

class MediaTest extends TestCase
{
    /**
     * @var Media
     */
    protected $resource;

    /**
     * @return Media
     */
    public function createResource()
    {
        return new Media();
    }

    public function testUploadName()
    {
        $data = $this->getSimpleTestData();
        $source = __DIR__ . '/fixtures/test-bild.jpg';
        $dest = __DIR__ . '/fixtures/test-bild-used.jpg';

        //copy image to execute test case multiple times.
        @unlink($dest);
        copy($source, $dest);

        $data['file'] = $dest;
        $path = Shopware()->DocPath('media_image') . 'test-bild-used.jpg';
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        if ($mediaService->has($path)) {
            $mediaService->delete($path);
        }

        $this->resource->create($data);
        static::assertTrue($mediaService->has($path));

        //check if the thumbnails are generated
        $path = Shopware()->DocPath('media_image_thumbnail') . 'test-bild-used_140x140.jpg';
        static::assertTrue($mediaService->has($path));

        unlink($dest);
    }

    public function testUploadNameWithOver50Characters()
    {
        $data = $this->getSimpleTestData();
        $source = __DIR__ . '/fixtures/test-bild.jpg';
        $dest = __DIR__ . '/fixtures/test-bild-with-more-than-50-characaters-more-more-more-more-used.jpg';

        //copy image to execute test case multiple times.
        @unlink($dest);
        copy($source, $dest);

        $data['file'] = $dest;
        $media = $this->resource->create($data);

        $pathPicture = Shopware()->DocPath('media_image') . $media->getFileName();
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        static::assertTrue($mediaService->has($pathPicture));

        //check if the thumbnails are generated
        $path = Shopware()->DocPath('media_image_thumbnail') . $media->getName() . '_140x140.jpg';
        static::assertTrue($mediaService->has($path));

        $mediaService->delete(Shopware()->DocPath('media_image') . $media->getFileName());
        $mediaService->delete($path);

        unlink($dest);
    }

    public function testSubmittedNameIsUsed()
    {
        $data = $this->getExtendedTestData();
        $base64Data = base64_encode(file_get_contents(__DIR__ . '/fixtures/shopware_logo.png'));
        $data['file'] = 'data:image/png;base64,' . $base64Data;
        $ids = [];

        // Assert that the given name is used
        $media = $this->resource->create($data);
        $ids[] = $media->getId();
        static::assertEquals($data['name'], $media->getName());

        // On the second pass the given name should still be used (extended with a random string)
        $media = $this->resource->create($data);
        $ids[] = $media->getId();
        static::assertContains($data['name'], $media->getName());

        // Delete the created media
        foreach ($ids as $id) {
            $this->resource->delete($id);
        }
    }

    public function testReplaceMedia()
    {
        $data = $this->getSimpleTestData();
        $base64Data = base64_encode(file_get_contents(__DIR__ . '/fixtures/shopware_logo.png'));
        $updateData = [
            'file' => 'data:image/png;base64,' . $base64Data,
        ];

        $source = __DIR__ . '/fixtures/test-bild.jpg';
        $dest = __DIR__ . '/fixtures/test-bild-used.jpg';

        //copy image to execute test case multiple times.
        @unlink($dest);
        copy($source, $dest);

        $data['file'] = $dest;
        $deletePath = Shopware()->DocPath('media_image') . 'test-bild-used.jpg';
        $readPath = Shopware()->DocPath('media_image') . 'test-bild-used.png';
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        if ($mediaService->has($deletePath)) {
            $mediaService->delete($deletePath);
        }

        $media = $this->resource->create($data);

        //check if the thumbnails are generated
        $this->resource->update($media->getId(), $updateData);

        $content = base64_encode($mediaService->read($readPath));

        $mediaService->delete($readPath);

        static::assertEquals($content, $base64Data, 'Replaced file was not persisted correctly.');
    }

    public function testUploadMediaWithNonWhitelistedExtension()
    {
        $this->expectException('Shopware\Bundle\MediaBundle\Exception\MediaFileExtensionNotAllowedException');
        $this->expectExceptionMessage('The media file extension "foo" is not allowed.');
        $source = __DIR__ . '/fixtures/test-bild.jpg';
        $dest = __DIR__ . '/fixtures/test-bild-used.foo';

        //copy image to execute test case multiple times.
        @unlink($dest);
        copy($source, $dest);

        $data = $this->getSimpleTestData();
        $data['file'] = $dest;

        $path = Shopware()->DocPath('media_unknown') . 'test-bild-used.foo';
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        if ($mediaService->has($path)) {
            $mediaService->delete($path);
        }

        $this->resource->create($data);

        unlink($dest);
    }

    protected function getSimpleTestData()
    {
        return [
            'album' => -1,
            'description' => 'Test description',
        ];
    }

    protected function getExtendedTestData()
    {
        $temp = $this->getSimpleTestData();
        $temp['name'] = 'some-name-lorem-ipsum';

        return $temp;
    }
}
