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

namespace Shopware\Bundle\MediaBundle;

use phpDocumentor\Reflection\Types\Integer;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Thumbnail\Manager;
use Shopware\Models\Media\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaReplaceService implements MediaReplaceServiceInterface
{
    /** @var MediaServiceInterface */
    private $mediaService;

    /** @var ModelManager */
    private $modelManager;

    /** @var Manager */
    private $thumbnailManager;

    /**
     * MediaReplaceService constructor.
     *
     * @param MediaServiceInterface $mediaService
     * @param Manager $thumbnailManager
     * @param ModelManager $modelManager
     */
    public function __construct(MediaServiceInterface $mediaService, Manager $thumbnailManager, ModelManager $modelManager)
    {
        $this->mediaService = $mediaService;
        $this->thumbnailManager = $thumbnailManager;
        $this->modelManager = $modelManager;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function replace($mediaId, UploadedFile $file)
    {
        $media = $this->modelManager->find('Shopware\Models\Media\Media', $mediaId);

        if (!$this->validateMediaType($media, $file)) {
            throw new \Exception(sprintf("To replace the media file, an %s file is required", $media->getType()));
        }

        $fileContent = file_get_contents($file->getRealPath());

        $this->mediaService->write($media->getPath(), $fileContent);

        $media->setExtension($file->getClientOriginalExtension());
        $media->setFileSize($file->getClientSize());

        if ($media->getType() == $media::TYPE_IMAGE) {
            $media->setWidth(imagesx($fileContent));
            $media->setHeight(imagesy($fileContent));

            $media->removeThumbnails();
            $this->thumbnailManager->createMediaThumbnail($media, $media->getDefaultThumbnails(), true);
            $media->createAlbumThumbnails($media->getAlbum());
        }

        $this->modelManager->flush();
    }

    /**
     * @param Media $media
     * @param UploadedFile $file
     * @return bool
     */
    private function validateMediaType(Media $media, UploadedFile $file)
    {
        $fileInfo = pathinfo($file->getClientOriginalName());
        $uploadedFileExtension = strtolower($fileInfo['extension']);
        $types = $media->getTypeMapping();
        
        if (!array_key_exists($uploadedFileExtension, $types)) {
            $types[$uploadedFileExtension] = Media::TYPE_UNKNOWN;
        }

        return $media->getType() == $types[$uploadedFileExtension];
    }
}
