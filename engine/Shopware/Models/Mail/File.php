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

namespace Shopware\Models\Mail;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Abstract class to provide proxy methods to the media-model
 *
 * @ORM\MappedSuperclass()
 */
abstract class File extends ModelEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * The role property is the owning side of the association between attachment and media.
     *
     * @var \Shopware\Models\Media\Media
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Media\Media", fetch="EAGER")
     * @ORM\JoinColumn(name="mediaID", referencedColumnName="id")
     */
    protected $media;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Shopware\Models\Media\Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Returns the creation date of the media.
     *
     * @return \DateTimeInterface|null
     */
    public function getCreated()
    {
        if (!$this->getMedia() instanceof \Shopware\Models\Media\Media) {
            return null;
        }

        return $this->getMedia()->getCreated();
    }

    /**
     * Returns the id of the user, who uploaded the file.
     *
     * @return int|null
     */
    public function getUserId()
    {
        if (!$this->getMedia() instanceof \Shopware\Models\Media\Media) {
            return null;
        }

        return $this->getMedia()->getUserId();
    }

    /**
     * Returns the file extension.
     *
     * @return string
     */
    public function getExtension()
    {
        if (!$this->getMedia() instanceof \Shopware\Models\Media\Media) {
            return '';
        }

        return $this->getMedia()->getExtension();
    }

    /**
     * Returns the media type.
     *
     * @return string
     */
    public function getType()
    {
        if (!$this->getMedia() instanceof \Shopware\Models\Media\Media) {
            return '';
        }

        return $this->getMedia()->getType();
    }

    /**
     * Returns the file path of the media
     *
     * @return string
     */
    public function getPath()
    {
        if (!$this->getMedia() instanceof \Shopware\Models\Media\Media) {
            return '';
        }

        return $this->getMedia()->getPath();
    }

    /**
     * Returns the converted file name.
     *
     * @return string
     */
    public function getFileName()
    {
        if (!$this->getMedia() instanceof \Shopware\Models\Media\Media) {
            return 'Media not found';
        }

        return $this->getMedia()->getFileName();
    }

    /**
     * Returns the media description.
     *
     * @return string
     */
    public function getDescription()
    {
        if (!$this->getMedia() instanceof \Shopware\Models\Media\Media) {
            return '';
        }

        return $this->getMedia()->getDescription();
    }

    /**
     * Returns the id of the assigned album.
     *
     * @return int|null
     */
    public function getAlbumId()
    {
        if (!$this->getMedia() instanceof \Shopware\Models\Media\Media) {
            return null;
        }

        return $this->getMedia()->getAlbumId();
    }

    /**
     * Returns the name of the media, also used as file name
     *
     * @return string
     */
    public function getName()
    {
        if (!$this->getMedia() instanceof \Shopware\Models\Media\Media) {
            return 'Media not found';
        }

        return $this->getMedia()->getName();
    }

    /**
     * Returns the memory size of the file.
     *
     * @return float|string
     */
    public function getFileSize()
    {
        if (!$this->getMedia() instanceof \Shopware\Models\Media\Media) {
            return '';
        }

        return $this->getMedia()->getFileSize();
    }

    /**
     * Returns the memory size of the file.
     *
     * @return int|string
     */
    public function getFormattedFileSize()
    {
        if (!$this->getMedia() instanceof \Shopware\Models\Media\Media) {
            return 0;
        }

        return $this->getMedia()->getFormattedFileSize();
    }
}
