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
use Doctrine\ORM\Mapping as ORM;
use Shopware\Bundle\MediaBundle\MediaModelServiceInterface;
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
     * @var MediaAttribute|null
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
     * @var File|null
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
     * @var int|null
     *
     * @ORM\Column(name="width", type="integer", nullable=true)
     */
    private $width;

    /**
     * Height of the file in px if it's an image
     *
     * @var int|null
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

    /**
     * Contains helper functions for media
     *
     * @var MediaModelServiceInterface
     */
    private $mediaModelService;

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
        $this->name = $this->getMediaModelService()->removeSpecialCharacters($name);

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
     * @return File|null
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Setter method for the file property. If the file is set, the file information will be extracted
     * and set into the internal properties.
     *
     * @param File $file
     *
     * @return Media
     */
    public function setFile(File $file)
    {
        $this->file = $file;
        $this->getMediaModelService()->setFileInfo($this);

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
        $this->getMediaModelService()->uploadFile($this);
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
            $name = $this->getMediaModelService()->removeSpecialCharacters($name);
            $name = $name . '.' . $this->extension;

            // To remove the old album thumbnails, use the old album
            $album = isset($changeSet['album']) ? $changeSet['album'][0] : $this->album;

            if ($isNameChanged) {
                // Remove default thumbnails
                $this->getMediaModelService()->removeDefaultThumbnails($this);

                // Create default thumbnails
                $this->getMediaModelService()->createDefaultThumbnails($this);
            }

            // Remove the configured album thumbnail files
            $settings = $album->getSettings();
            if ($settings !== null) {
                $this->removeAlbumThumbnails($settings->getThumbnailSize(), $name);
            }

            $this->getMediaModelService()->updateAssociations($this);

            // Create album thumbnails
            $this->createAlbumThumbnails($this->album);
        }

        // Name changed? Then rename the file and set the new path
        if ($isNameChanged) {
            $mediaService = Shopware()->Container()->get('shopware_media.media_service');
            $newName = $this->getFileName();
            $newPath = $this->getMediaModelService()->getUploadDir($this->type) . $newName;

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

        $thumbnailSizes = $this->getMediaModelService()->getAllThumbnailSizes();

        $this->getMediaModelService()->removeDefaultThumbnails($this);
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
            $this->getMediaModelService()->createThumbnail($this, (int) $data['width'], (int) $data['height']);
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
                $size .= 'x' . $size;
            }
            $names = $this->getMediaModelService()->getThumbnailNames($this, $size);

            foreach ($names as $name) {
                if ($mediaService->has($name)) {
                    $mediaService->delete($name);
                }
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
            return $this->getMediaModelService()->removeSpecialCharacters($this->name) . '.' . $this->extension;
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
                    $this->getMediaModelService()->createThumbnail($this, (int) $size[0], (int) $size[1]);
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
        return $this->getMediaModelService()->getThumbnailFilePaths($this, $highDpi);
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
     * @return int|null
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int|null $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return int|null
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int|null $height
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
     * @return MediaAttribute|null
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
        $thumbnailSizes = $this->getMediaModelService()->getAllThumbnailSizes();

        $this->getMediaModelService()->removeDefaultThumbnails($this);
        $this->removeAlbumThumbnails($thumbnailSizes, $this->getFileName());
    }

    private function getMediaModelService()
    {
        if ($this->mediaModelService === null) {
            $this->mediaModelService = Shopware()->Container()->get(MediaModelServiceInterface::class);
        }

        return $this->mediaModelService;
    }
}
