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

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

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
 * @ORM\Entity()
 * @ORM\Table(name="s_media_album_settings")
 * @ORM\HasLifecycleCallbacks()
 */
class Settings extends ModelEntity
{
    /**
     * @var \Shopware\Models\Media\Album
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Media\Album", inversedBy="settings")
     * @ORM\JoinColumn(name="albumID", referencedColumnName="id")
     */
    protected $album;

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
     * Id of the album
     *
     * @var int
     *
     * @ORM\Column(name="albumID", type="integer", nullable=false)
     */
    private $albumId;

    /**
     * Flag whether thumbnails will be created on this album.
     *
     * @var int
     *
     * @ORM\Column(name="create_thumbnails", type="integer", nullable=false)
     */
    private $createThumbnails;

    /**
     * Sizes of the thumbnails. Format => WIDTHxHEIGHT;
     *
     * @var string
     *
     * @ORM\Column(name="thumbnail_size", type="text", nullable=false)
     */
    private $thumbnailSize;

    /**
     * Css class for the album
     *
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=50, nullable=false)
     */
    private $icon;

    /**
     * Generate high dpi thumbnails
     *
     * @var bool
     *
     * @ORM\Column(name="thumbnail_high_dpi", type="boolean", nullable=false)
     */
    private $thumbnailHighDpi;

    /**
     * Thumbnail quality
     *
     * @var int
     *
     * @ORM\Column(name="thumbnail_quality", type="integer", nullable=false)
     */
    private $thumbnailQuality;

    /**
     * High DPI thumbnails quality
     *
     * @var int
     *
     * @ORM\Column(name="thumbnail_high_dpi_quality", type="integer", nullable=false)
     */
    private $thumbnailHighDpiQuality;

    /**
     * Sets whether thumbnails will be created on this album.
     *
     * @param int $createThumbnails
     *
     * @return \Shopware\Models\Media\Settings
     */
    public function setCreateThumbnails($createThumbnails)
    {
        $this->createThumbnails = $createThumbnails;

        return $this;
    }

    /**
     * Returns whether thumbnails will be created on this album.
     *
     * @return int
     */
    public function getCreateThumbnails()
    {
        return $this->createThumbnails;
    }

    /**
     * Sets the icon css class
     *
     * @param string $icon
     *
     * @return \Shopware\Models\Media\Settings
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Returns the icon css class
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
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
     * Sets the property for the thumbnail sizes.
     *
     * @param string|array $thumbnailSize
     *
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
     *
     * @return array
     */
    public function getThumbnailSize()
    {
        if (empty($this->thumbnailSize)) {
            return [];
        }

        return explode(';', $this->thumbnailSize);
    }

    /**
     * @return \Shopware\Models\Media\Album
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     * @param \Shopware\Models\Media\Album $album
     */
    public function setAlbum($album)
    {
        $this->album = $album;
    }

    /**
     * @return int
     */
    public function getThumbnailHighDpiQuality()
    {
        return $this->thumbnailHighDpiQuality;
    }

    /**
     * @param int $thumbnailHighDpiQuality
     */
    public function setThumbnailHighDpiQuality($thumbnailHighDpiQuality)
    {
        $this->thumbnailHighDpiQuality = $thumbnailHighDpiQuality;
    }

    /**
     * @return bool
     */
    public function isThumbnailHighDpi()
    {
        return $this->thumbnailHighDpi;
    }

    /**
     * @param bool $thumbnailHighDpi
     */
    public function setThumbnailHighDpi($thumbnailHighDpi)
    {
        $this->thumbnailHighDpi = $thumbnailHighDpi;
    }

    /**
     * @return int
     */
    public function getThumbnailQuality()
    {
        return $this->thumbnailQuality;
    }

    /**
     * @param int $thumbnailQuality
     */
    public function setThumbnailQuality($thumbnailQuality)
    {
        $this->thumbnailQuality = $thumbnailQuality;
    }
}
