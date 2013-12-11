<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace   Shopware\Models\Media;
use         Shopware\Components\Model\ModelEntity,
            Doctrine\ORM\Mapping AS ORM;

/**
 * The album settings model contains all settings around one album.
 * <br>
 * The album Settings model contains the thumbnail configuration and icon css class definition.
 * <code>
 *   - Album  =>  Shopware\Models\Media\Album  [1:1] [s_media_album]
 * </code>
 * The s_media_album_settings table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 *   - UNIQUE KEY `albumID` (`albumID`)
 * </code>
 *
 * @ORM\Entity
 * @ORM\Table(name="s_media_album_settings")
 * @ORM\HasLifecycleCallbacks
 */
class Settings extends ModelEntity
{
    /**
     * Unique identifier
     * @var integer $id
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Id of the album
     * @var integer $albumId
     * @ORM\Column(name="albumID", type="integer", nullable=false)
     */
    private $albumId;

    /**
     * Flag whether thumbnails will be created on this album.
     * @var integer $createThumbnails
     * @ORM\Column(name="create_thumbnails", type="integer", nullable=false)
     */
    private $createThumbnails;

    /**
     * Sizes of the thumbnails. Format => WIDTHxHEIGHT;
     * @var string $thumbnailSize
     * @ORM\Column(name="thumbnail_size", type="text", nullable=false)
     */
    private $thumbnailSize;

    /**
     * Css class for the album
     * @var string $icon
     * @ORM\Column(name="icon", type="string", length=50, nullable=false)
     */
    private $icon;

    /**
     * @var \Shopware\Models\Media\Album $album
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Media\Album", inversedBy="settings")
     * @ORM\JoinColumn(name="albumID", referencedColumnName="id")
     */
    private $album;

    /**
     * Sets the id of the assigned album.
     * @param int $albumId
     * @return \Shopware\Models\Media\Settings
     */
    public function setAlbumId($albumId)
    {
        $this->albumId = $albumId;
        return $this;
    }

    /**
     * Returns the id assigned album
     * @return int
     */
    public function getAlbumId()
    {
        return $this->albumId;
    }

    /**
     * Sets whether thumbnails will be created on this album.
     * @param int $createThumbnails
     * @return \Shopware\Models\Media\Settings
     */
    public function setCreateThumbnails($createThumbnails)
    {
        $this->createThumbnails = $createThumbnails;
        return $this;
    }

    /**
     * Returns whether thumbnails will be created on this album.
     * @return int
     */
    public function getCreateThumbnails()
    {
        return $this->createThumbnails;
    }

    /**
     * Sets the icon css class
     * @param $icon
     * @return \Shopware\Models\Media\Settings
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Returns the icon css class
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Returns the identifier id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the property for the thumbnail sizes.
     * @param string|array $thumbnailSize
     * @return \Shopware\Models\Media\Settings
     */
    public function setThumbnailSize($thumbnailSize)
    {
        if (is_array($thumbnailSize)) {
            $thumbnailSize = implode(';', $thumbnailSize);
        }
        $this->thumbnailSize = $thumbnailSize;
        return $this;
    }

    /**
     * Returns the thumbnail sizes as array exploded by ";".
     * Example:
     *  Database value: '70x70;150x150;90x90'
     *  Return   value: array('70x70', '150x150', '90x90')
     * @return array
     */
    public function getThumbnailSize()
    {
        return explode(';', $this->thumbnailSize);
    }

    /**
     * If the album settings updated and the thumbnail configuration changed,
     * the new configuration have to be executed on each assigned media.
     * To notify the configuration change the model gets the entity change set
     * over the doctrine unit of work instance.
     * When update is checked whether the old thumbnails should be deleted,
     * and whether new thumbnail files must be generated.
     *
     * @ORM\PreUpdate
     */
    public function onUpdate()
    {
        if ($this->albumId === null || $this->albumId === 0) {
            return;
        }
        /**@var $album \Shopware\Models\Media\Album*/
        $album = Shopware()->Models()->find('\Shopware\Models\Media\Album', $this->albumId);

        //album correctly loaded?
        if ($album === null) {
            return;
        }

        //load media
        $media = $album->getMedia();
        if ($media === null) {
            return;
        }

        //returns a change set for the model, which contains all changed properties with the old and new value.
        $changeSet = Shopware()->Models()->getUnitOfWork()->getEntityChangeSet($this);

        //thumbnail configuration changed?
        if (!isset($changeSet['createThumbnails']) && !isset($changeSet['thumbnailSize'])) {
            return;
        }
        //Check whether it is necessary to delete the thumbnails first.
        $removeThumbnails = (isset($changeSet['createThumbnails'])) ? $changeSet['createThumbnails'][0] : $this->createThumbnails;
        $sizes = $this->thumbnailSize;
        if ($changeSet['thumbnailSize'][0] !== $changeSet['thumbnailSize'][1]) {
            $removeThumbnails = true;
            $sizes = $changeSet['thumbnailSize'][0];
        }

        /**@var $image \Shopware\Models\Media\Media*/
        foreach ($media as $image) {
            //only remove and create thumbnail for image media
            if ($image->getType() !== Media::TYPE_IMAGE) {
                continue;
            }
            if ($removeThumbnails) {
                $image->removeAlbumThumbnails($sizes, $image->getFileName());
            }
            if ($this->createThumbnails) {
                $image->createAlbumThumbnails($album);
            }
        }
    }

    /**
     * @return
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     * @param  $album
     */
    public function setAlbum($album)
    {
        $this->album = $album;
    }
}
