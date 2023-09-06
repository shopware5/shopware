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

namespace Shopware\Models\Media;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Zend_Db_Adapter_Exception;

/**
 * The Shopware album model is used to structure the media data.
 * <br>
 * The uploaded media is organized into albums.
 * Each album can have multiple child albums, which in turn may again involve media.
 * The Album has an album settings instance which contains the thumbnail configuration
 * and css icon class name.
 * <code>
 *   - Media    =>  Shopware\Models\Media\Media     [1:n] [s_media]
 *   - Album    =>  Shopware\Models\Media\Album     [1:n] [s_media_album]
 *   - Settings =>  Shopware\Models\Media\Settings  [1:1] [s_media_album_settings]
 * </code>
 * The s_media_album table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 * </code>
 *
 * @license    http://enlight.de/license     New BSD License
 *
 * @ORM\Entity()
 * @ORM\Table(name="s_media_album")
 * @ORM\HasLifecycleCallbacks()
 */
class Album extends ModelEntity
{
    public const ALBUM_ARTICLE = -1;
    public const ALBUM_BANNER = -2;
    public const ALBUM_EMOTION = -3;
    public const ALBUM_PROMOTIONS = -4;
    public const ALBUM_NEWSLETTER = -5;
    public const ALBUM_FILES = -6;
    public const ALBUM_VIDEO = -7;
    public const ALBUM_MUSIC = -8;
    public const ALBUM_OTHER = -9;
    public const ALBUM_UNSORTED = -10;
    public const ALBUM_BLOG = -11;
    public const ALBUM_SUPPLIER = -12;
    public const ALBUM_GARBAGE = -13;

    /**
     * Settings of the album.
     *
     * @var Settings|null
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Media\Settings", mappedBy="album", orphanRemoval=true, cascade={"persist"})
     */
    protected $settings;

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
     * Name of the album, displayed in the tree, used to filter the tree.
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * Id of the parent album
     *
     * @var int|null
     *
     * @ORM\Column(name="parentID", type="integer", nullable=true)
     */
    private $parentId;

    /**
     * Position of the album to configure the display order
     *
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position;

    /**
     * Defines if this album is allowed to be garbage collected using the GarbageCollector
     *
     * @var bool
     *
     * @ORM\Column(name="garbage_collectable", type="boolean", nullable=false)
     */
    private $garbageCollectable;

    /**
     * An album can have multiple sub-albums.
     *
     * @var ArrayCollection<Album>
     *
     * @ORM\OneToMany(targetEntity="\Shopware\Models\Media\Album", mappedBy="parent")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $children;

    /**
     * An album can only be subordinated to another album.
     *
     * @var Album|null
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Media\Album", inversedBy="children")
     * @ORM\JoinColumn(name="parentID", referencedColumnName="id")
     */
    private $parent;

    /**
     * An album can be assigned to multiple media.
     *
     * @var ArrayCollection<Media>
     *
     * @ORM\OneToMany(targetEntity="\Shopware\Models\Media\Media", mappedBy="album")
     */
    private $media;

    /**
     * Initials the children and media collection
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->media = new ArrayCollection();
    }

    /**
     * Returns the identifier id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the album name
     *
     * @param string $name
     *
     * @return Album
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the name of the album.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the position of the album
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Sets the position of the album.
     *
     * @param int $position
     *
     * @return Album
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Sets if this album is to be garbage collected
     *
     * @return bool
     */
    public function getGarbageCollectable()
    {
        return $this->garbageCollectable;
    }

    /**
     * @param bool $garbageCollectable
     *
     * @return Album
     */
    public function setGarbageCollectable($garbageCollectable)
    {
        $this->garbageCollectable = $garbageCollectable;

        return $this;
    }

    /**
     * Returns the child albums.
     *
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Sets the child albums.
     *
     * @param ArrayCollection<Album> $children
     *
     * @return array|Album
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Returns the parent album instance
     *
     * @return Album|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Sets the parent album instance
     *
     * @param Album|null $parent
     *
     * @return Album
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Returns the associated media.
     *
     * @return ArrayCollection
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Sets the associated media
     *
     * @param ArrayCollection<Media> $media
     */
    public function setMedia($media)
    {
        $this->media = $media;
    }

    /**
     * Returns the album settings
     *
     * @return Settings|null
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Sets the album settings
     *
     * @return Album
     */
    public function setSettings(Settings $settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * Model lifecycle callback function, fired when the model is removed from the database.
     * All assigned media set to the unsorted album.
     *
     * @ORM\PreRemove()
     *
     * @throws Zend_Db_Adapter_Exception
     */
    public function onRemove()
    {
        // Change the associated media to the unsorted album.
        Shopware()->Db()->query('UPDATE s_media SET albumID = ? WHERE albumID = ?', [-10, $this->id]);
    }
}
