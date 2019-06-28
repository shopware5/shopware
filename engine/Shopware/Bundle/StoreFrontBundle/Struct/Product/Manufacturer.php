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

namespace Shopware\Bundle\StoreFrontBundle\Struct\Product;

use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;
use Shopware\Bundle\StoreFrontBundle\Struct\Media;

class Manufacturer extends Extendable
{
    /**
     * Unique identifier of the manufacturer
     *
     * @var int
     */
    protected $id;

    /**
     * Name of the manufacturer.
     *
     * The name isn't translatable.
     *
     * @var string
     */
    protected $name;

    /**
     * Description of the manufacturer.
     * This value can be translated.
     *
     * @var string
     */
    protected $description;

    /**
     * Title for the seo optimization.
     *
     * @var string
     */
    protected $metaTitle;

    /**
     * Description for the seo optimization.
     *
     * @var string
     */
    protected $metaDescription;

    /**
     * Keywords for the seo optimization.
     *
     * @var string
     */
    protected $metaKeywords;

    /**
     * Contains the link to the manufacturer home page.
     *
     * @var string
     */
    protected $link;

    /**
     * Contains the file url for the cover file.
     *
     * @var string
     */
    protected $coverFile;

    /**
     * @var int
     */
    protected $coverId;

    /**
     * Returns a Media struct with the thumbnails
     *
     * @var Media|null
     */
    protected $coverMedia;

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $metaDescription
     *
     * @return $this
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @param string $metaKeywords
     *
     * @return $this
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * @param string $metaTitle
     *
     * @return $this
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getCoverFile()
    {
        return $this->coverFile;
    }

    /**
     * @param string $coverFile
     *
     * @return Manufacturer
     */
    public function setCoverFile($coverFile)
    {
        $this->coverFile = $coverFile;

        return $this;
    }

    /**
     * @return Manufacturer
     */
    public function setCoverMedia(Media $media)
    {
        $this->coverMedia = $media;

        return $this;
    }

    /**
     * @return Media|null
     */
    public function getCoverMedia()
    {
        return $this->coverMedia;
    }

    /**
     * @return int
     */
    public function getCoverId()
    {
        return $this->coverId;
    }

    /**
     * @return Manufacturer
     */
    public function setCoverId(int $coverId)
    {
        $this->coverId = $coverId;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
