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

namespace Shopware\Bundle\StoreFrontBundle\Struct\Blog;

use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Media;

class Blog extends Extendable
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var int
     */
    protected $authorId;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @var string
     */
    protected $short_description;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var int
     */
    protected $views;

    /**
     * @var \DateTimeInterface
     */
    protected $displayDate;

    /**
     * @var int
     */
    protected $categoryId;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var string
     */
    protected $metaKeywords;

    /**
     * @var string
     */
    protected $metaDescription;

    /**
     * @var string
     */
    protected $metaTitle;

    /**
     * @var string[]
     */
    protected $tags = [];

    /**
     * @var int[]
     */
    protected $productNumbers = [];

    /**
     * @var ListProduct[]
     */
    protected $products = [];

    /**
     * @var int[]
     */
    protected $mediaIds = [];

    /**
     * @var Media[]
     */
    protected $medias;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getAuthorId()
    {
        return $this->authorId;
    }

    /**
     * @param int $authorId
     */
    public function setAuthorId($authorId)
    {
        $this->authorId = $authorId;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getShortDescription()
    {
        return $this->short_description;
    }

    /**
     * @param string $short_description
     */
    public function setShortDescription($short_description)
    {
        $this->short_description = $short_description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * @param int $views
     */
    public function setViews($views)
    {
        $this->views = $views;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDisplayDate()
    {
        return $this->displayDate;
    }

    /**
     * @param \DateTimeInterface $displayDate
     */
    public function setDisplayDate($displayDate)
    {
        $this->displayDate = $displayDate;
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param int $categoryId
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * @param string $metaKeywords
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * @param string $metaTitle
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;
    }

    /**
     * @return string[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param string[] $tags
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return int[]
     */
    public function getProductNumbers()
    {
        return $this->productNumbers;
    }

    /**
     * @param int[] $productNumbers
     */
    public function setProductNumbers(array $productNumbers)
    {
        $this->productNumbers = $productNumbers;
    }

    /**
     * @return ListProduct[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param ListProduct[] $products
     */
    public function setProducts(array $products)
    {
        $this->products = $products;
    }

    /**
     * @return int[]
     */
    public function getMediaIds()
    {
        return $this->mediaIds;
    }

    /**
     * @param int[] $mediaIds
     */
    public function setMediaIds(array $mediaIds)
    {
        $this->mediaIds = $mediaIds;
    }

    /**
     * @return Media[]
     */
    public function getMedias()
    {
        return $this->medias;
    }

    /**
     * @param Media[] $medias
     */
    public function setMedias(array $medias)
    {
        $this->medias = $medias;
    }
}
