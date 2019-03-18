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

namespace Shopware\Bundle\StoreFrontBundle\Struct;

use Shopware\Models\Category\Category as CategoryEntity;

class Category extends Extendable
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int|null
     */
    protected $parentId;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var array
     */
    protected $path = [];

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $metaTitle;

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
    protected $cmsHeadline;

    /**
     * @var string
     */
    protected $cmsText;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var bool
     */
    protected $blog;

    /**
     * @var bool
     */
    protected $displayFacets;

    /**
     * @var bool
     */
    protected $displayInNavigation;

    /**
     * @var string
     */
    protected $externalLink;

    /**
     * @var string
     */
    protected $externalTarget;

    /**
     * @var Media
     */
    protected $media;

    /**
     * @var int[]
     */
    protected $blockedCustomerGroupIds = [];

    /**
     * @var string|null
     */
    protected $productBoxLayout = null;

    /**
     * @var ProductStream|null
     */
    protected $productStream;

    /**
     * @var bool
     */
    protected $hideSortings;

    /**
     * @return Category
     */
    public static function createFromCategoryEntity(CategoryEntity $category)
    {
        $struct = new self();

        $struct->setId($category->getId());
        $struct->setName($category->getName());
        $struct->setPosition($category->getPosition());
        $struct->setParentId($category->getParentId());

        $path = $category->getPath();
        if ($path) {
            $path = ltrim($path, '|');
            $path = rtrim($path, '|');

            $path = explode('|', $path);

            $struct->setPath(array_reverse($path));
        }

        return $struct;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param array $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return array
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $cmsHeadline
     */
    public function setCmsHeadline($cmsHeadline)
    {
        $this->cmsHeadline = $cmsHeadline;
    }

    /**
     * @return string
     */
    public function getCmsHeadline()
    {
        return $this->cmsHeadline;
    }

    /**
     * @param string $cmsText
     */
    public function setCmsText($cmsText)
    {
        $this->cmsText = $cmsText;
    }

    /**
     * @return string
     */
    public function getCmsText()
    {
        return $this->cmsText;
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
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
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
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
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
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $externalLink
     */
    public function setExternalLink($externalLink)
    {
        $this->externalLink = $externalLink;
    }

    /**
     * @return string
     */
    public function getExternalLink()
    {
        return $this->externalLink;
    }

    /**
     * @param string $externalTarget
     */
    public function setExternalTarget($externalTarget)
    {
        $this->externalTarget = $externalTarget;
    }

    /**
     * @return string
     */
    public function getExternalTarget()
    {
        return $this->externalTarget;
    }

    /**
     * @param bool $displayFacets
     */
    public function setDisplayFacets($displayFacets)
    {
        $this->displayFacets = $displayFacets;
    }

    /**
     * @param bool $displayInNavigation
     */
    public function setDisplayInNavigation($displayInNavigation)
    {
        $this->displayInNavigation = $displayInNavigation;
    }

    /**
     * @param bool $blog
     */
    public function setBlog($blog)
    {
        $this->blog = $blog;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Media $media
     */
    public function setMedia($media)
    {
        $this->media = $media;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Media|null
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @return bool
     */
    public function isBlog()
    {
        return $this->blog;
    }

    /**
     * @return bool
     */
    public function displayFacets()
    {
        return $this->displayFacets;
    }

    /**
     * @return bool
     */
    public function displayInNavigation()
    {
        return $this->displayInNavigation;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @return int[]
     */
    public function getBlockedCustomerGroupIds()
    {
        return $this->blockedCustomerGroupIds;
    }

    /**
     * @param int[] $blockedCustomerGroupIds
     */
    public function setBlockedCustomerGroupIds(array $blockedCustomerGroupIds)
    {
        $this->blockedCustomerGroupIds = $blockedCustomerGroupIds;
    }

    /**
     * @return int|null
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param int|null $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return string|null
     */
    public function getProductBoxLayout()
    {
        return $this->productBoxLayout;
    }

    /**
     * @param string|null $productBoxLayout
     */
    public function setProductBoxLayout($productBoxLayout)
    {
        $this->productBoxLayout = $productBoxLayout;
    }

    /**
     * @return ProductStream|null
     */
    public function getProductStream()
    {
        return $this->productStream;
    }

    public function setProductStream(ProductStream $productStream = null)
    {
        $this->productStream = $productStream;
    }

    /**
     * @return bool
     */
    public function hideSortings()
    {
        return $this->hideSortings;
    }

    /**
     * @param bool $hideSortings
     */
    public function setHideSortings($hideSortings)
    {
        $this->hideSortings = $hideSortings;
    }
}
