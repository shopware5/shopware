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

namespace Shopware\Tests\Components\Thumbnail;

use Error;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Thumbnail\Manager;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Media;
use Symfony\Component\HttpFoundation\File\File;

class ManagerTest extends TestCase
{
    public function testManagerInstance(): void
    {
        $manager = Shopware()->Container()->get(Manager::class);
        static::assertInstanceOf(Manager::class, $manager);
    }

    public function testThumbnailGeneration(): void
    {
        $manager = Shopware()->Container()->get(Manager::class);
        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);

        $media = $this->getMediaModel();

        $sizes = [
            '100x110',
            [120, 130],
            [140],
            [
                'width' => 150,
                'height' => 160,
            ],
        ];

        $manager->createMediaThumbnail($media, $sizes);

        $thumbnailDir = Shopware()->DocPath('media_' . strtolower($media->getType()) . '_thumbnail');

        $path = $thumbnailDir . $media->getName();
        static::assertTrue($mediaService->has($path . '_100x110.jpg'));
        static::assertTrue($mediaService->has($path . '_120x130.jpg'));
        static::assertTrue($mediaService->has($path . '_140x140.jpg'));
        static::assertTrue($mediaService->has($path . '_150x160.jpg'));

        $mediaService->delete($media->getPath());
    }

    public function testGenerationWithoutPassedSizes(): void
    {
        $manager = Shopware()->Container()->get(Manager::class);

        $media = $this->getMediaModel();

        $sizes = [
            '200x210',
            '220x230',
            '240x250',
        ];

        static::assertNotNull($media->getAlbum()->getSettings());
        $media->getAlbum()->getSettings()->setThumbnailSize($sizes);

        $manager->createMediaThumbnail($media);

        $thumbnailDir = Shopware()->DocPath('media_' . strtolower($media->getType()) . '_thumbnail');
        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);

        $path = $thumbnailDir . $media->getName();

        foreach ($sizes as $size) {
            static::assertTrue($mediaService->has($path . '_' . $size . '.jpg'));
            static::assertTrue($mediaService->has($path . '_' . $size . '.png'));
        }
    }

    public function testGenerationWithoutPassedSizesButProportion(): void
    {
        $manager = Shopware()->Container()->get(Manager::class);

        $media = $this->getMediaModel();

        $sizes = [
            '300x310',
            '320x330',
            '340x350',
        ];

        $proportionalSizes = [
            '300x298',
            '320x318',
            '340x337',
        ];

        static::assertNotNull($media->getAlbum()->getSettings());
        $media->getAlbum()->getSettings()->setThumbnailSize($sizes);

        $manager->createMediaThumbnail($media, [], true);

        $thumbnailDir = Shopware()->DocPath('media_' . strtolower($media->getType()) . '_thumbnail');
        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);

        $path = $thumbnailDir . $media->getName();

        foreach ($sizes as $key => $size) {
            static::assertTrue($mediaService->has($path . '_' . $size . '.jpg'));
            static::assertTrue($mediaService->has($path . '_' . $size . '.png'));

            $image = imagecreatefromstring($mediaService->read($path . '_' . $size . '.jpg'));
            $width = imagesx($image);
            $height = imagesy($image);

            static::assertSame($proportionalSizes[$key], $width . 'x' . $height);
        }
    }

    public function testGenerationWithoutAlbum(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage('Call to a member function getSettings() on null');
        $media = new Media();

        $sourcePath = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'sw_icon.png';
        $imagePath = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'sw_icon_copy.png';
        copy($sourcePath, $imagePath);

        $file = new File($imagePath);
        /** @var string $projectDir */
        $projectDir = Shopware()->Container()->getParameter('shopware.app.rootDir');

        $media->setFile($file);
        $media->setPath(str_replace($projectDir, '', $imagePath));

        @unlink($file->getRealPath());

        $manager = Shopware()->Container()->get(Manager::class);
        $manager->createMediaThumbnail($media);
    }

    public function testGenerationWithEmptyMedia(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessageMatches('/File .* is not an image/');
        $media = new Media();

        $manager = Shopware()->Container()->get(Manager::class);
        $manager->createMediaThumbnail($media);
    }

    public function testThumbnailCleanUp(): void
    {
        $media = $this->getMediaModel();

        $defaultSizes = $media->getDefaultThumbnails();
        $defaultSize = $defaultSizes[0];
        $defaultSize = $defaultSize[0] . 'x' . $defaultSize[1];

        $manager = Shopware()->Container()->get(Manager::class);
        $manager->createMediaThumbnail($media, [$defaultSize]);

        $thumbnailDir = Shopware()->DocPath('media_' . strtolower($media->getType()) . '_thumbnail');
        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);
        $path = $thumbnailDir . $media->getName();

        static::assertTrue($mediaService->has($path . '_' . $defaultSize . '.' . $media->getExtension()));

        $manager->removeMediaThumbnails($media);

        static::assertFalse($mediaService->has($path . '_' . $defaultSize . '.' . $media->getExtension()));

        $mediaService->delete($media->getPath());

        static::assertFalse($mediaService->has($media->getPath()));
    }

    private function getMediaModel(): Media
    {
        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);
        $media = new Media();

        $sourcePath = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'sw_icon.png';
        $imagePath = 'media/unknown/sw_icon.png';
        $mediaService->write($imagePath, file_get_contents($sourcePath));

        $file = new File($sourcePath);

        $media->setFile($file);
        $media->setAlbumId(-10);
        $album = Shopware()->Models()->find(Album::class, -10);
        static::assertNotNull($album);
        $media->setAlbum($album);
        $media->setPath(str_replace(Shopware()->DocPath(), '', $imagePath));
        $media->setDescription('');
        $media->setUserId(0);

        return $media;
    }
}
