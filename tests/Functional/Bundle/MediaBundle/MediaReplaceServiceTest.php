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

namespace Shopware\Tests\Functional\Bundle\MediaBundle;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\MediaBundle\MediaReplaceService;
use Shopware\Models\Media\Media;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaReplaceServiceTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    /**
     * @var MediaReplaceService
     */
    private $service;

    public function setUp(): void
    {
        $this->service = Shopware()->Container()->get('shopware_media.replace_service');
    }

    public function testInvalidId(): void
    {
        $source = __DIR__ . '/../../Components/Api/fixtures/test-bild.jpg';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Media with id -1 not found');
        $this->service->replace(-1, new UploadedFile($source, 'test-bild.jpg'));
    }

    public function testReplaceImage(): void
    {
        $source = __DIR__ . '/../../Components/Api/fixtures/test-bild.jpg';
        $newImage = __DIR__ . '/../../Components/Api/fixtures/variant-image.png';

        $apiResource = Shopware()->Container()->get('shopware.api.media');
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        /** @var Media $media */
        $media = $apiResource->create([
            'album' => -1,
            'file' => $source,
            'description' => 'Test',
        ]);

        static::assertInstanceOf(Media::class, $media);

        static::assertEquals(md5_file($source), md5($mediaService->read($media->getPath())));

        $this->service->replace($media->getId(), new UploadedFile($newImage, 'variant-image.png'));

        $media = Shopware()->Models()->find(Media::class, $media->getId());
        static::assertEquals('png', $media->getExtension());
        static::assertEquals('png', pathinfo($media->getPath(), PATHINFO_EXTENSION));
    }
}
