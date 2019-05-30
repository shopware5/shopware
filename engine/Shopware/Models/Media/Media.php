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

namespace Shopware\Models\Media;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Bundle\MediaBundle\Exception\MediaFileExtensionIsBlacklistedException;
use Shopware\Bundle\MediaBundle\Exception\MediaFileExtensionNotAllowedException;
use Shopware\Components\Model\ModelEntity;
use Shopware\Components\Random;
use Shopware\Models\Attribute\Media as MediaAttribute;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * In Shopware all media resources are represented in the media model.
 * <br>
 * The uploaded media is assigned to albums. Each media can assigned to only one album.
 * The uploaded media can be different types such as images, PDF files or videos.
 * One media has the following associations:
 * <code>
 *   - Album  =>  Shopware\Models\Media\Album  [n:1] [s_media_album]
 * </code>
 * The s_media table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 *   - KEY `Album` (`albumID`)
 * </code>
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_media")
 * @ORM\HasLifecycleCallbacks()
 */
class Media extends ModelEntity
{
    /**
     * Flag for an image media
     */
    const TYPE_IMAGE = 'IMAGE';

    /**
     * Flag for a vector media
     */
    const TYPE_VECTOR = 'VECTOR';

    /**
     * Flag for a video media
     */
    const TYPE_VIDEO = 'VIDEO';

    /**
     * Flag for a music media
     */
    const TYPE_MUSIC = 'MUSIC';

    /**
     * Flag for an archive media
     */
    const TYPE_ARCHIVE = 'ARCHIVE';

    /**
     * Flag for a pdf media
     */
    const TYPE_PDF = 'PDF';

    /**
     * Flag for a 3D model media
     */
    const TYPE_MODEL = 'MODEL';

    /**
     * Flag for an unknown media
     */
    const TYPE_UNKNOWN = 'UNKNOWN';

    /**
     * INVERSE SIDE
     *
     * @var MediaAttribute
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Media", mappedBy="media", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * @var ArrayCollection<\Shopware\Models\Article\Image>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Image", mappedBy="media")
     */
    protected $articles;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<\Shopware\Models\Blog\Media>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Blog\Media", mappedBy="media", orphanRemoval=true, cascade={"persist"})
     */
    protected $blogMedia;

    /**
     * @var ArrayCollection<\Shopware\Models\Property\Value>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Property\Value", mappedBy="media")
     */
    protected $properties;

    /**
     * Contains the default thumbnail sizes which used for backend modules.
     *
     * @var array
     */
    private $defaultThumbnails = [
        [140, 140],
    ];

    /**
     * Unique identifier
     *
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Id of the assigned album
     *
     * @var int
     *
     * @ORM\Column(name="albumID", type="integer", nullable=false)
     */
    private $albumId;

    /**
     * Name of the media, also used as a file name
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * Description for the media.
     *
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

    /**
     * Path of the uploaded file.
     *
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255, nullable=false)
     */
    private $path;

    /**
     * Flag for the media type.
     *
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=50, nullable=false)
     */
    private $type;

    /**
     * Extension of the uploaded file
     *
     * @var string
     *
     * @ORM\Column(name="extension", type="string", length=20, nullable=false)
     */
    private $extension;

    /**
     * Id of the user, who uploaded the file.
     *
     * @var int
     *
     * @ORM\Column(name="userID", type="integer", nullable=false)
     */
    private $userId;

    /**
     * Creation date of the media
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="created", type="date", nullable=false)
     */
    private $created;

    /**
     * Internal container for the uploaded file.
     *
     * @var UploadedFile
     */
    private $file;

    /**
     * Filesize of the file in bytes
     *
     * @var int
     *
     * @ORM\Column(name="file_size", type="integer", nullable=false)
     */
    private $fileSize;

    /**
     * Width of the file in px if it's an image
     *
     * @var int
     *
     * @ORM\Column(name="width", type="integer", nullable=true)
     */
    private $width;

    /**
     * Height of the file in px if it's an image
     *
     * @var int
     *
     * @ORM\Column(name="height", type="integer", nullable=true)
     */
    private $height;

    /**
     * Assigned album association. Is automatically loaded when the standard functions "find" ... be used,
     * or if the Query Builder is specified with the association.
     *
     * @var Album
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Media\Album", inversedBy="media")
     * @ORM\JoinColumn(name="albumID", referencedColumnName="id")
     */
    private $album;

    /**
     * Contains the thumbnails paths.
     * Contains all created thumbnails
     *
     * @var array
     */
    private $thumbnails;

    /**
     * Contains the high dpi thumbnails paths.
     *
     * @var array
     */
    private $highDpiThumbnails;

    /****************************************************************
     *                  Property Getter & Setter                    *
     ****************************************************************/

    /**
     * Returns the identifier "id"
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the id of the assigned album.
     *
     * @param int $albumId
     *
     * @return Media
     */
    public function setAlbumId($albumId)
    {
        $this->albumId = $albumId;

        return $this;
    }

    /**
     * Returns the id of the assigned album.
     *
     * @return int
     */
    public function getAlbumId()
    {
        return $this->albumId;
    }

    /**
     * Sets the name of the media, also used as file name
     *
     * @param string $name
     *
     * @return Media
     */
    public function setName($name)
    {
        $this->name = $this->removeSpecialCharacters($name);

        return $this;
    }

    /**
     * Returns the name of the media, also used as file name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the description of the media.
     *
     * @param string $description
     *
     * @return Media
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Returns the media description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the file path of the media.
     *
     * @param string $path
     *
     * @return Media
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Returns the file path of the media
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets the internal type of the media.
     *
     * @param string $type
     *
     * @return Media
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Returns the media type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the file extension.
     *
     * @param string $extension
     *
     * @return Media
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Returns the file extension.
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Sets the id of the user, who uploaded the file.
     *
     * @param int $userId
     *
     * @return Media
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Returns the id of the user, who uploaded the file.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Sets the creation date of the media.
     *
     * @param \DateTimeInterface $created
     *
     * @return Media
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Returns the creation date of the media.
     *
     * @return \DateTimeInterface
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Sets the memory size of the file.
     *
     * @param int $fileSize
     *
     * @return Media
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * Returns the filesize of the file in bytes.
     *
     * @return int
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * Returns the filesize of the file in human readable format
     *
     * @return string
     */
    public function getFormattedFileSize()
    {
        $size = $this->fileSize;
        $filesize = 'unknown';

        if ($size < 1024) {
            $filesize = $size . ' bytes';
        } elseif ($size >= 1024 && $size < 1048576) {
            $filesize = round($size / 1024, 2) . ' KB';
        } elseif ($size >= 1048576) {
            $filesize = round($size / 1048576, 2) . ' MB';
        }

        return $filesize;
    }

    /**
     * Returns the instance of the assigned album
     *
     * @return Album|null
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     * Sets the assigned album.
     *
     * @return Media
     */
    public function setAlbum(Album $album)
    {
        $this->album = $album;

        return $this;
    }

    /**
     * Returns the file
     *
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Setter method for the file property. If the file is set, the file information will be extracted
     * and set into the internal properties.
     *
     * @param UploadedFile $file
     *
     * @return Media
     */
    public function setFile(File $file)
    {
        $this->file = $file;
        $this->setFileInfo();

        return $this;
    }

    /**
     * Returns the thumbnail paths in an array
     *
     * @return array
     */
    public function getThumbnails()
    {
        if (empty($this->thumbnails)) {
            $this->thumbnails = $this->loadThumbnails();
        }

        return $this->thumbnails;
    }

    /**
     * Returns the high dpi thumbnail paths in an array
     *
     * @return array
     */
    public function getHighDpiThumbnails()
    {
        if (empty($this->highDpiThumbnails)) {
            $this->highDpiThumbnails = $this->loadThumbnails(true);
        }

        return $this->highDpiThumbnails;
    }

    /**
     * Returns the thumbnail paths of already generated thumbnails
     *
     * @return array
     */
    public function getCreatedThumbnails()
    {
        return $this->thumbnails;
    }

    /****************************************************************
     *                  Lifecycle Callbacks                         *
     ****************************************************************/

    /**
     * Moves the uploaded file into the correctly media directory,
     * creates the default thumbnails for image media to display the
     * media in the media manager and creates the thumbnails for the
     * configured album thumbnail sizes.
     *
     * @ORM\PrePersist()
     */
    public function onSave()
    {
        // Upload file
        $this->uploadFile();
    }

    /**
     * Checks if the name changed, if this is the case, the uploaded file
     * has to be renamed.
     * Removes the thumbnail files if the album or the name changed.
     * Creates the default and album thumbnails if the name or the album changed.
     *
     * @ORM\PostUpdate()
     */
    public function onUpdate()
    {
        // Returns a change set for the model, which contains all changed properties with the old and new value.
        $changeSet = Shopware()->Models()->getUnitOfWork()->getEntityChangeSet($this);

        $isNameChanged = isset($changeSet['name']) && $changeSet['name'][0] !== $changeSet['name'][1];
        $isAlbumChanged = isset($changeSet['albumId']) && $changeSet['albumId'][0] !== $changeSet['albumId'][1];

        // Name changed || album changed?
        if ($isNameChanged || $isAlbumChanged) {
            // To remove the old thumbnails, use the old name.
            $name = isset($changeSet['name']) ? $changeSet['name'][0] : $this->name;
            $name = $this->removeSpecialCharacters($name);
            $name = $name . '.' . $this->extension;

            // To remove the old album thumbnails, use the old album
            $album = isset($changeSet['album']) ? $changeSet['album'][0] : $this->album;

            if ($isNameChanged) {
                // Remove default thumbnails
                $this->removeDefaultThumbnails($name);

                // Create default thumbnails
                $this->createDefaultThumbnails();
            }

            // Remove the configured album thumbnail files
            $settings = $album->getSettings();
            if ($settings !== null) {
                $this->removeAlbumThumbnails($settings->getThumbnailSize(), $name);
            }

            $this->updateAssociations();

            // Create album thumbnails
            $this->createAlbumThumbnails($this->album);
        }

        // Name changed? Then rename the file and set the new path
        if ($isNameChanged) {
            $mediaService = Shopware()->Container()->get('shopware_media.media_service');
            $newName = $this->getFileName();
            $newPath = $this->getUploadDir() . $newName;

            // Rename the file
            $mediaService->rename($this->path, $newPath);

            $newPath = str_replace(Shopware()->DocPath(), '', $newPath);

            // Set the new path to save it.
            $this->path = $newPath;
        }
    }

    /**
     * Model event function, which called when the model is loaded.
     *
     * @ORM\PostLoad()
     */
    public function onLoad()
    {
        $this->thumbnails = $this->loadThumbnails();
    }

    /**
     * Removes the media files from the file system
     *
     * @ORM\PostRemove()
     */
    public function onRemove()
    {
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        //check if file exist and remove it
        if ($mediaService->has($this->path)) {
            $mediaService->delete($this->path);
        }

        if ($this->type !== self::TYPE_IMAGE) {
            return;
        }

        $thumbnailSizes = $this->getAllThumbnailSizes();

        $this->removeDefaultThumbnails($this->getFileName());
        $this->removeAlbumThumbnails($thumbnailSizes, $this->getFileName());
    }

    /****************************************************************
     *                  Global functions                            *
     ****************************************************************/

    /**
     * Creates the thumbnail files in the different sizes which configured in the album settings.
     */
    public function createAlbumThumbnails(Album $album)
    {
        // Is image media?
        if ($this->type !== self::TYPE_IMAGE) {
            return;
        }

        // Check if the album has loaded correctly and should be created for the album thumbnails?
        if ($album->getSettings() === null || !$album->getSettings()->getCreateThumbnails()) {
            return;
        }

        $defaultSizes = $this->getDefaultThumbnails();
        $defaultSize = implode('x', $defaultSizes[0]);
        // Load the configured album thumbnail sizes
        $sizes = $album->getSettings()->getThumbnailSize();
        $sizes[] = $defaultSize;

        // Iterate the sizes and create the thumbnails
        foreach ($sizes as $size) {
            // Split the width and height (example: $size = 70x70)
            $data = explode('x', $size);

            // To avoid any confusing, we're mapping the index based to an association based array and remove the index based elements.
            $data['width'] = $data[0];
            $data['height'] = $data[1];
            unset($data[0], $data[1]);

            // Continue if configured size is not numeric
            if (!is_numeric($data['width'])) {
                continue;
            }
            // If no height configured, set 0
            $data['height'] = isset($data['height']) ? $data['height'] : 0;

            // Create thumbnail with the configured size
            $this->createThumbnail((int) $data['width'], (int) $data['height']);
        }
    }

    /**
     * Removes the configured album thumbnails for the passed album instance and with the
     * passed file name. The file name have to be passed, because on update the internal
     * file name property is already changed to the new name.
     *
     * @param array|null $thumbnailSizes
     * @param string     $fileName
     */
    public function removeAlbumThumbnails($thumbnailSizes, $fileName)
    {
        if ($this->type !== self::TYPE_IMAGE) {
            return;
        }
        if ($thumbnailSizes === null || empty($thumbnailSizes)) {
            return;
        }

        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        foreach ($thumbnailSizes as $size) {
            if (strpos($size, 'x') === false) {
                $size = $size . 'x' . $size;
            }
            $names = $this->getThumbnailNames($size, $fileName);

            if ($mediaService->has($names['jpg'])) {
                $mediaService->delete($names['jpg']);
            }

            if ($mediaService->has($names['jpgHD'])) {
                $mediaService->delete($names['jpgHD']);
            }

            if ($mediaService->has($names['original'])) {
                $mediaService->delete($names['original']);
            }

            if ($mediaService->has($names['originalHD'])) {
                $mediaService->delete($names['originalHD']);
            }
        }
    }

    /**
     * Returns the converted file name.
     *
     * @return bool|string
     */
    public function getFileName()
    {
        if ($this->name !== '') {
            return $this->removeSpecialCharacters($this->name) . '.' . $this->extension;
        }

        // Do whatever you want to generate a unique name
        return Random::getAlphanumericString(13) . '.' . $this->extension;
    }

    /**
     * Loads the thumbnails paths via the configured thumbnail sizes.
     *
     * @param bool $highDpi - If true, loads high dpi thumbnails instead
     *
     * @return array
     */
    public function loadThumbnails($highDpi = false)
    {
        $thumbnails = $this->getThumbnailFilePaths($highDpi);
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        if (!$mediaService->has($this->getPath())) {
            return $thumbnails;
        }

        foreach ($thumbnails as $size => $thumbnail) {
            $size = explode('x', $size);

            if (!$mediaService->has($thumbnail)) {
                try {
                    $this->createThumbnail((int) $size[0], (int) $size[1]);
                } catch (\Exception $e) {
                    // Ignore for now
                    // Exception might be thrown when thumbnails can not
                    // be generated due to invalid image files
                }
            }
        }

        return $thumbnails;
    }

    /**
     * Returns an array of all thumbnail paths the media object can have
     *
     * @param bool $highDpi - If true, returns the file path for the high dpi thumbnails instead
     *
     * @return array
     */
    public function getThumbnailFilePaths($highDpi = false)
    {
        if ($this->type !== self::TYPE_IMAGE) {
            return [];
        }
        $sizes = [];

        // Concat default sizes
        foreach ($this->defaultThumbnails as $size) {
            if (count($size) === 1) {
                $sizes[] = $size . 'x' . $size;
            } else {
                $sizes[] = $size[0] . 'x' . $size[1];
            }
        }

        // Check if the album has loaded correctly.
        if ($this->album !== null && $this->album->getSettings() !== null && $this->album->getSettings()->getCreateThumbnails() === 1) {
            $sizes = array_merge($this->album->getSettings()->getThumbnailSize(), $sizes);
            $sizes = array_unique($sizes);
        }
        $thumbnails = [];
        $suffix = $highDpi ? '@2x' : '';

        // Iterate thumbnail sizes
        foreach ($sizes as $size) {
            if (strpos($size, 'x') === false) {
                $size = $size . 'x' . $size;
            }

            $fileName = str_replace(
                '.' . $this->extension,
                '_' . $size . $suffix . '.' . $this->extension,
                $this->getFileName()
            );

            $path = $this->getThumbnailDir() . $fileName;
            $path = str_replace(Shopware()->DocPath(), '', $path);
            if (DIRECTORY_SEPARATOR !== '/') {
                $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
            }
            $thumbnails[$size] = $path;
        }

        return $thumbnails;
    }

    /**
     * @return ArrayCollection
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param ArrayCollection $properties
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    public function getDefaultThumbnails()
    {
        return $this->defaultThumbnails;
    }

    public function setDefaultThumbnails($defaultThumbnails)
    {
        $this->defaultThumbnails = $defaultThumbnails;
    }

    /**
     * @return MediaAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param MediaAttribute|array|null $attribute
     *
     * @return Media
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, MediaAttribute::class, 'attribute', 'media');
    }

    /**
     * @return ArrayCollection
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * @param ArrayCollection $articles
     */
    public function setArticles($articles)
    {
        $this->articles = $articles;
    }

    public function removeThumbnails()
    {
        $thumbnailSizes = $this->getAllThumbnailSizes();

        $this->removeDefaultThumbnails($this->getFileName());
        $this->removeAlbumThumbnails($thumbnailSizes, $this->getFileName());
    }

    /**
     * Internal helper function which updates all associated data which has the image path as own property.
     */
    private function updateAssociations()
    {
        /** @var \Shopware\Models\Article\Image $article */
        foreach ($this->articles as $article) {
            $article->setPath($this->getName());
            Shopware()->Models()->persist($article);
        }
        Shopware()->Models()->flush();
    }

    /****************************************************************
     *                  Internal functions                          *
     ****************************************************************/

    /**
     * Moves the uploaded file to the correctly directory.
     *
     * @return bool
     */
    private function uploadFile()
    {
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        $projectDir = Shopware()->Container()->getParameter('shopware.app.rootdir');

        // Move the file to the upload directory
        if ($this->file !== null) {
            // File already exists?
            if ($mediaService->has($this->getPath())) {
                $this->name .= Random::getAlphanumericString(13);
                // Path in setFileInfo is set, before the file gets a unique ID here
                // Therefore the path is updated here SW-2889
                $this->path = str_replace($projectDir, '', $this->getUploadDir() . $this->getFileName());

                /*
                 * SW-3805 - Hotfix for windows paths
                 */
                $this->path = str_replace('\\', '/', $this->path);
            }
            $tempPath = $projectDir . 'media' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . $this->file->getFilename();

            $mediaService->write($this->path, file_get_contents($this->file->getRealPath()));
            if (file_exists($tempPath) || is_uploaded_file($this->file->getPathname())) {
                unlink($this->file->getPathname());
            }
        }

        return true;
    }

    /**
     * Creates the default thumbnails 70x70 and 153x153 to display the images
     * in the media manager listing.
     */
    private function createDefaultThumbnails()
    {
        // Create only thumbnails for image media
        if ($this->type !== self::TYPE_IMAGE) {
            return;
        }

        /** @var \Shopware\Components\Thumbnail\Manager $generator */
        $generator = Shopware()->Container()->get('thumbnail_manager');

        $generator->createMediaThumbnail($this, $this->defaultThumbnails, true);
    }

    /**
     * Removes the default thumbnail files. The file name have to be passed, because on update the internal
     * file name property is already changed to the new name.
     *
     * @param string $fileName
     */
    private function removeDefaultThumbnails($fileName)
    {
        if ($this->type !== self::TYPE_IMAGE) {
            return;
        }

        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        foreach ($this->defaultThumbnails as $size) {
            if (count($size) === 1) {
                $sizeString = $size . 'x' . $size;
            } else {
                $sizeString = $size[0] . 'x' . $size[1];
            }
            $names = $this->getThumbnailNames($sizeString, $fileName);

            if ($mediaService->has($names['jpg'])) {
                $mediaService->delete($names['jpg']);
            }

            if ($mediaService->has($names['jpgHD'])) {
                $mediaService->delete($names['jpgHD']);
            }

            if ($mediaService->has($names['original'])) {
                $mediaService->delete($names['original']);
            }

            if ($mediaService->has($names['originalHD'])) {
                $mediaService->delete($names['originalHD']);
            }
        }
    }

    /**
     * Returns the directory to upload
     *
     * @return string
     */
    private function getUploadDir()
    {
        // The absolute directory path where uploaded documents should be saved
        $projectDir = Shopware()->Container()->getParameter('shopware.app.rootdir');

        return $projectDir . 'media' . DIRECTORY_SEPARATOR . strtolower($this->type) . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the directory of the thumbnail files.
     *
     * @return string
     */
    private function getThumbnailDir()
    {
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        $path = $this->getUploadDir() . 'thumbnail' . DIRECTORY_SEPARATOR;
        $path = $mediaService->normalize($path);

        return $path;
    }

    /**
     * Create a thumbnail file for the internal file with the passed width and height.
     *
     * @param int $width
     * @param int $height
     */
    private function createThumbnail($width, $height)
    {
        // Create only thumbnails for image media
        if ($this->type !== self::TYPE_IMAGE) {
            return;
        }

        /** @var \Shopware\Components\Thumbnail\Manager $manager */
        $manager = Shopware()->Container()->get('thumbnail_manager');

        $newSize = [
            'width' => $width,
            'height' => $height,
        ];

        $manager->createMediaThumbnail($this, [$newSize], true);
    }

    /**
     * Create the new names for the jpg file and the file with the original extension
     * Also returns high dpi paths
     *
     * @param string $suffix
     * @param string $fileName
     *
     * @return array
     */
    private function getThumbnailNames($suffix, $fileName)
    {
        $jpgName = str_replace('.' . $this->extension, '_' . $suffix . '.jpg', $fileName);
        $jpgHDName = str_replace('.' . $this->extension, '_' . $suffix . '@2x.jpg', $fileName);
        $originalName = str_replace('.' . $this->extension, '_' . $suffix . '.' . $this->extension, $fileName);
        $originalHDName = str_replace('.' . $this->extension, '_' . $suffix . '@2x.' . $this->extension, $fileName);

        return [
            'jpg' => $this->getThumbnailDir() . $jpgName,
            'jpgHD' => $this->getThumbnailDir() . $jpgHDName,
            'original' => $this->getThumbnailDir() . $originalName,
            'originalHD' => $this->getThumbnailDir() . $originalHDName,
        ];
    }

    /**
     * Extract the file information from the uploaded file, into the internal properties
     */
    private function setFileInfo()
    {
        if ($this->file === null) {
            return;
        }

        $extension = $this->file->guessExtension();
        $name = $this->file->getBasename();

        if ($this->file instanceof UploadedFile) {
            // Load file information
            $fileInfo = pathinfo($this->file->getClientOriginalName());
            $name = $fileInfo['filename'];

            if (isset($fileInfo['extension'])) {
                $extension = $fileInfo['extension'];
            }
        }

        $extension = strtolower($extension);

        // Validate extension
        // #1 - whitelist
        $mappingService = Shopware()->Container()->get('shopware_media.extension_mapping');
        if (!$mappingService->isAllowed($extension)) {
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
        $this->type = $mappingService->getType($extension);

        // The filesize in bytes.
        $this->fileSize = $this->file->getSize();
        $this->name = $this->removeSpecialCharacters($name);
        $this->extension = str_replace('jpeg', 'jpg', $extension);

        $projectDir = Shopware()->Container()->getParameter('shopware.app.rootdir');
        $this->path = str_replace($projectDir, '', $this->getUploadDir() . $this->getFileName());

        if (DIRECTORY_SEPARATOR !== '/') {
            $this->path = str_replace(DIRECTORY_SEPARATOR, '/', $this->path);
        }
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function removeSpecialCharacters($name)
    {
        $name = iconv('utf-8', 'ascii//translit', $name);
        $name = preg_replace('#[^A-Za-z0-9\-_]#', '-', $name);
        $name = preg_replace('#-{2,}#', '-', $name);
        $name = trim($name, '-');

        return mb_substr($name, 0, 180);
    }

    /**
     * Searches all album settings for thumbnail sizes
     *
     * @return array
     */
    private function getAllThumbnailSizes()
    {
        /** @var Connection $connection */
        $connection = Shopware()->Container()->get('dbal_connection');
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
}
