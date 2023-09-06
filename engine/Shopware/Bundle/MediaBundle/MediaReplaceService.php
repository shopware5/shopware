<?php
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

namespace Shopware\Bundle\MediaBundle;

use DateTime;
use Exception;
use InvalidArgumentException;
use Shopware\Bundle\MediaBundle\Exception\MediaFileExtensionNotAllowedException;
use Shopware\Bundle\MediaBundle\Exception\WrongMediaTypeForReplaceException;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Thumbnail\Manager;
use Shopware\Models\Media\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use UnexpectedValueException;

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
     * @throws Exception
     */
    public function replace($mediaId, UploadedFile $file)
    {
        /** @var Media|null $media */
        $media = $this->modelManager->find(Media::class, $mediaId);

        if ($media === null) {
            throw new InvalidArgumentException(sprintf('Media with id %s not found', $mediaId));
        }

        $filePath = $file->getRealPath();
        if (!\is_string($filePath)) {
            throw new UnexpectedValueException(sprintf('Could not get path of file "%s"', $file->getFilename()));
        }

        $uploadedFileExtension = $this->getExtension($file);

        if ($media->getType() !== $this->mappingService->getType($uploadedFileExtension)) {
            throw new WrongMediaTypeForReplaceException($media->getType());
        }

        if ($this->mappingService->isAllowed($uploadedFileExtension) === false) {
            throw new MediaFileExtensionNotAllowedException($uploadedFileExtension);
        }

        $fileContent = file_get_contents($filePath);
        $oldExtension = strtolower($media->getExtension());
        $newExtension = strtolower($this->getExtension($file));

        $newFileName = null;
        if ($oldExtension === $newExtension) {
            $this->mediaService->write($media->getPath(), $fileContent);
        } else {
            $pathInfo = pathinfo($media->getPath());
            $newFileName = sprintf('%s/%s.%s', $pathInfo['dirname'] ?? '', $pathInfo['filename'], $newExtension);
            $this->mediaService->delete($media->getPath());
            $this->mediaService->write($newFileName, $fileContent);
            $this->modelManager->getConnection()->update('s_articles_img', [
                'extension' => $newExtension,
            ], [
                'media_id' => $media->getId(),
            ]);
        }

        $media->setExtension($this->getExtension($file));
        $media->setFileSize(filesize($filePath));
        $media->setCreated(new DateTime());

        if ($media->getType() === Media::TYPE_IMAGE) {
            $imageSize = getimagesize($filePath);

            if ($imageSize) {
                $media->setWidth($imageSize[0]);
                $media->setHeight($imageSize[1]);
            }

            $media->removeThumbnails();

            if ($newFileName) {
                $media->setPath($newFileName);
            }

            $this->thumbnailManager->createMediaThumbnail($media, $media->getDefaultThumbnails(), true);
            $media->createAlbumThumbnails($media->getAlbum());
        } elseif ($newFileName) {
            $media->setPath($newFileName);
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
            $extension = (string) $file->guessExtension();
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
