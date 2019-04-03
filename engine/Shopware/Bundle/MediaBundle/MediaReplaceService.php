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

use Shopware\Bundle\MediaBundle\Exception\MediaFileExtensionNotAllowedException;
use Shopware\Bundle\MediaBundle\Exception\WrongMediaTypeForReplaceException;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Thumbnail\Manager;
use Shopware\Models\Media\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaReplaceService implements MediaReplaceServiceInterface
{
    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Manager
     */
    private $thumbnailManager;

    /**
     * @var MediaExtensionMappingServiceInterface
     */
    private $mappingService;

    public function __construct(MediaServiceInterface $mediaService, Manager $thumbnailManager, ModelManager $modelManager, MediaExtensionMappingServiceInterface $mappingService)
    {
        $this->mediaService = $mediaService;
        $this->thumbnailManager = $thumbnailManager;
        $this->modelManager = $modelManager;
        $this->mappingService = $mappingService;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function replace($mediaId, UploadedFile $file)
    {
        /** @var Media|null $media */
        $media = $this->modelManager->find(Media::class, $mediaId);

        if ($media === null) {
            throw new \InvalidArgumentException(sprintf('Media with id %s not found', $mediaId));
        }

        $uploadedFileExtension = $this->getExtension($file);

        if ($media->getType() !== $this->mappingService->getType($uploadedFileExtension)) {
            throw new WrongMediaTypeForReplaceException($media->getType());
        }

        if ($this->mappingService->isAllowed($uploadedFileExtension) === false) {
            throw new MediaFileExtensionNotAllowedException($uploadedFileExtension);
        }

        $fileContent = file_get_contents($file->getRealPath());

        $this->mediaService->write($media->getPath(), $fileContent);

        $media->setExtension($this->getExtension($file));
        $media->setFileSize(filesize($file->getRealPath()));
        $media->setCreated(new \DateTime());

        if ($media->getType() === Media::TYPE_IMAGE) {
            $imageSize = getimagesize($file->getRealPath());

            if ($imageSize) {
                $media->setWidth($imageSize[0]);
                $media->setHeight($imageSize[1]);
            }

            $media->removeThumbnails();
            $this->thumbnailManager->createMediaThumbnail($media, $media->getDefaultThumbnails(), true);
            $media->createAlbumThumbnails($media->getAlbum());
        }

        $this->modelManager->flush();
    }

    /**
     * @return string
     */
    private function getExtension(UploadedFile $file)
    {
        $extension = $file->getClientOriginalExtension();
        if (!$extension) {
            $extension = $file->guessExtension();
        }

        $extension = strtolower($extension);

        switch ($extension) {
            case 'jpeg':
                $extension = 'jpg';
                break;
        }

        return (string) $extension;
    }
}
