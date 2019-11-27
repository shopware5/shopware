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

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Enlight_Event_EventManager;
use Shopware\Bundle\MediaBundle\Exception\MediaFileExtensionIsBlacklistedException;
use Shopware\Bundle\MediaBundle\Exception\MediaFileExtensionNotAllowedException;
use Shopware\Components\Random;
use Shopware\Components\Thumbnail\Manager;
use Shopware\Models\Article\Image;
use Shopware\Models\Media\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaModelService implements MediaModelServiceInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $_em;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var Manager
     */
    private $thumbnailManager;

    /**
     * @var MediaExtensionMappingServiceInterface
     */
    private $mediaExtensionMappingService;

    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var string
     */
    private $rootDir;

    public function __construct(
        MediaServiceInterface $mediaService,
        Manager $thumbnailManager,
        MediaExtensionMappingServiceInterface $mediaExtensionMappingService,
        Connection $dbalConnection,
        EntityManagerInterface $entityManager,
        Enlight_Event_EventManager $eventManager,
        string $rootDir
    ) {
        $this->mediaService = $mediaService;
        $this->thumbnailManager = $thumbnailManager;
        $this->mediaExtensionMappingService = $mediaExtensionMappingService;
        $this->dbalConnection = $dbalConnection;
        $this->_em = $entityManager;
        $this->eventManager = $eventManager;
        $this->rootDir = $rootDir;
    }

    /**
     * {@inheritdoc}
     */
    public function getThumbnailFilePaths(Media $media, bool $highDpi = false, array $initialSizes = []): array
    {
        $type = $media->getType();
        $defaultThumbnails = $media->getDefaultThumbnails();
        $album = $media->getAlbum();
        $extension = $media->getExtension();

        if ($type !== Media::TYPE_IMAGE) {
            return [];
        }

        $sizes = $initialSizes;

        // Check if the album has loaded correctly.
        if ($album !== null && $album->getSettings() !== null && $album->getSettings()->getCreateThumbnails() === 1) {
            $sizes = array_merge($sizes, $album->getSettings()->getThumbnailSize());
            $sizes = array_unique($sizes);
        }

        // Concat default sizes
        foreach ($defaultThumbnails as $size) {
            if (count($size) === 1) {
                $sizes[] = $size . 'x' . $size;
            } else {
                $sizes[] = $size[0] . 'x' . $size[1];
            }
        }

        $thumbnails = [];
        $suffix = $highDpi ? '@2x' : '';

        // Iterate thumbnail sizes
        foreach ($sizes as $size) {
            if (strpos($size, 'x') === false) {
                $size .= 'x' . $size;
            }

            $fileName = str_replace(
                '.' . $extension,
                '_' . $size . $suffix . '.' . $extension,
                $media->getFileName()
            );

            $path = $this->getThumbnailDir($type) . $fileName;
            if (DIRECTORY_SEPARATOR !== '/') {
                $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
            }
            $thumbnails[$size] = $path;
        }

        return $thumbnails;
    }

    /**
     * {@inheritdoc}
     */
    public function removeSpecialCharacters(string $name): string
    {
        $name = iconv('utf-8', 'ascii//translit', $name);
        $name = preg_replace('#[^A-Za-z0-9\-_]#', '-', $name);
        $name = preg_replace('#-{2,}#', '-', $name);
        $name = trim($name, '-');

        return mb_substr($name, 0, 180);
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadDir(string $type): string
    {
        return 'media' . DIRECTORY_SEPARATOR . strtolower($type) . DIRECTORY_SEPARATOR;
    }

    /*
     * {@inheritDoc}
     */
    public function getThumbnailDir(string $type): string
    {
        $path = $this->getUploadDir($type) . 'thumbnail' . DIRECTORY_SEPARATOR;
        $path = $this->mediaService->normalize($path);

        return $path;
    }

    /**
     * Internal helper function which updates all associated data which has the image path as own property.
     */
    public function updateAssociations(Media $media)
    {
        /** @var Image $article */
        foreach ($media->getArticles() as $article) {
            $article->setPath($media->getName());
            $this->getEntityManager()->persist($article);
        }
        $this->getEntityManager()->flush();
    }

    /**
     * Moves the uploaded file to the correctly directory.
     */
    public function uploadFile(Media $media): bool
    {
        $file = $media->getFile();

        // Move the file to the upload directory
        if ($file !== null) {
            // File already exists?
            if ($this->mediaService->has($media->getPath())) {
                $this->eventManager->notify('Shopware_Bundle_MediaBundle_uploadFile_FileExists', ['subject' => $media]);

                $media->setName($media->getName() . Random::getAlphanumericString(13));
                /*
                 * Path in setFileInfo is set, before the file gets a unique ID here
                 * Therefore the path is updated here SW-2889
                 * SW-3805 - Hotfix for windows paths
                 */
                $media->setPath(str_replace('\\', '/', $this->getUploadDir($media->getType()) . $media->getFileName()));
            }
            $tempPath = $this->rootDir . 'media' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . $file->getFilename();

            $this->mediaService->write($media->getPath(), file_get_contents($file->getRealPath()));
            if (file_exists($tempPath) || is_uploaded_file($file->getPathname())) {
                unlink($file->getPathname());
            }
        }

        return true;
    }

    /**
     * Creates the default thumbnails 70x70 and 153x153 to display the images
     * in the media manager listing.
     */
    public function createDefaultThumbnails(Media $media): void
    {
        // Create only thumbnails for image media
        if ($media->getType() !== Media::TYPE_IMAGE) {
            return;
        }

        $this->thumbnailManager->createMediaThumbnail($media, $media->getDefaultThumbnails(), true);
    }

    /**
     * Removes the default thumbnail files. The file name have to be passed, because on update the internal
     * file name property is already changed to the new name.
     */
    public function removeDefaultThumbnails(Media $media): void
    {
        if ($media->getType() !== Media::TYPE_IMAGE) {
            return;
        }

        foreach ($media->getDefaultThumbnails() as $size) {
            if (count($size) === 1) {
                $sizeString = $size . 'x' . $size;
            } else {
                $sizeString = $size[0] . 'x' . $size[1];
            }
            $names = $this->getThumbnailNames($media, $sizeString);

            foreach ($names as $name) {
                if ($this->mediaService->has($name)) {
                    $this->mediaService->delete($name);
                }
            }
        }
    }

    /**
     * Create a thumbnail file for the internal file with the passed width and height.
     */
    public function createThumbnail(Media $media, int $width, int $height): void
    {
        // Create only thumbnails for image media
        if ($media->getType() !== Media::TYPE_IMAGE) {
            return;
        }

        $newSize = [
            'width' => $width,
            'height' => $height,
        ];

        $this->thumbnailManager->createMediaThumbnail($media, [$newSize], true);
    }

    /**
     * Create the new names for the jpg file and the file with the original extension
     * Also returns high dpi paths
     */
    public function getThumbnailNames(Media $media, string $suffix): array
    {
        $jpgName = str_replace('.' . $media->getExtension(), '_' . $suffix . '.jpg', $media->getFileName());
        $jpgHDName = str_replace('.' . $media->getExtension(), '_' . $suffix . '@2x.jpg', $media->getFileName());
        $originalName = str_replace('.' . $media->getExtension(), '_' . $suffix . '.' . $media->getExtension(), $media->getFileName());
        $originalHDName = str_replace('.' . $media->getExtension(), '_' . $suffix . '@2x.' . $media->getExtension(), $media->getFileName());

        return [
            'jpg' => $this->getThumbnailDir($media->getType()) . $jpgName,
            'jpgHD' => $this->getThumbnailDir($media->getType()) . $jpgHDName,
            'original' => $this->getThumbnailDir($media->getType()) . $originalName,
            'originalHD' => $this->getThumbnailDir($media->getType()) . $originalHDName,
        ];
    }

    /**
     * Extract the file information from the uploaded file, into the internal properties
     */
    public function setFileInfo(Media $media): void
    {
        $file = $media->getFile();
        if ($file === null) {
            return;
        }

        $extension = $file->guessExtension();
        $name = $file->getBasename();

        if ($file instanceof UploadedFile) {
            // Load file information
            $fileInfo = pathinfo($file->getClientOriginalName());
            $name = $fileInfo['filename'];

            if (isset($fileInfo['extension'])) {
                $extension = $fileInfo['extension'];
            }
        }

        $extension = strtolower($extension);

        // Validate extension
        // #1 - whitelist
        if (!$this->mediaExtensionMappingService->isAllowed($extension)) {
            throw new MediaFileExtensionNotAllowedException($extension);
        }

        // #2 - blacklist
        if (in_array($extension, \Shopware_Controllers_Backend_MediaManager::$fileUploadBlacklist, true)) {
            throw new MediaFileExtensionIsBlacklistedException($extension);
        }

        // Make sure that the name doesn't contain the file extension.
        $name = str_ireplace('.' . $extension, '', $name);
        if ($extension === 'jpeg') {
            $name = str_ireplace('.jpg', '', $name);
        }

        // Set the file type using the type mapping
        $media->setType($this->mediaExtensionMappingService->getType($extension));

        // The filesize in bytes.
        $media->setFileSize($file->getSize());
        $media->setName($this->removeSpecialCharacters($name));
        $media->setExtension(str_replace('jpeg', 'jpg', $extension));

        $media->setPath($this->getUploadDir($media->getType()) . $media->getFileName());

        if (DIRECTORY_SEPARATOR !== '/') {
            $media->setPath(str_replace(DIRECTORY_SEPARATOR, '/', $media->getPath()));
        }
    }

    /**
     * Searches all album settings for thumbnail sizes
     */
    public function getAllThumbnailSizes(): array
    {
        $connection = $this->dbalConnection;
        $joinedSizes = $connection
            ->query('SELECT DISTINCT thumbnail_size FROM s_media_album_settings WHERE thumbnail_size != ""')
            ->fetchAll(\PDO::FETCH_COLUMN);

        $sizes = [];
        foreach ($joinedSizes as $sizeItem) {
            $explodedSizes = explode(';', $sizeItem);
            if (empty($explodedSizes)) {
                continue;
            }

            $sizes = array_merge($sizes, array_flip($explodedSizes));
        }

        return array_keys($sizes);
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager()
    {
        return $this->_em;
    }
}
