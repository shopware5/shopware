<?php
declare(strict_types=1);

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

namespace Shopware\Category\Struct;

use Shopware\Framework\Struct\Struct;
use Shopware\Media\Struct\Media;
use Shopware\ProductStream\Struct\ProductStream;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Category extends Struct
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
     * @var \Shopware\Media\Struct\Media
     */
    protected $media;

    /**
     * @var int[]
     */
    protected $blockedCustomerGroupIds = [];

    /**
     * @var null|string
     */
    protected $productBoxLayout;

    /**
     * @var null|\Shopware\ProductStream\Struct\ProductStream
     */
    protected $productStream;

    /**
     * @var bool
     */
    protected $hideSortings;

    /**
     * @var Category[]
     */
    protected $children = [];

    /**
     * @var bool
     */
    protected $isShopCategory;

    public function __construct(int $id, ?int $parentId, array $path, string $name)
    {
        $this->id = $id;
        $this->parentId = $parentId;
        $this->path = $path;
        $this->name = $name;
    }

    /**
     * @param int $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param array $path
     */
    public function setPath($path): void
    {
        $this->path = $path;
    }

    /**
     * @return array
     */
    public function getPath(): array
    {
        return $this->path;
    }

    /**
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $cmsHeadline
     */
    public function setCmsHeadline($cmsHeadline): void
    {
        $this->cmsHeadline = $cmsHeadline;
    }

    /**
     * @return string
     */
    public function getCmsHeadline(): string
    {
        return $this->cmsHeadline;
    }

    /**
     * @param string $cmsText
     */
    public function setCmsText($cmsText): void
    {
        $this->cmsText = $cmsText;
    }

    /**
     * @return string
     */
    public function getCmsText(): string
    {
        return $this->cmsText;
    }

    /**
     * @return string
     */
    public function getMetaTitle(): string
    {
        return $this->metaTitle;
    }

    /**
     * @param string $metaTitle
     */
    public function setMetaTitle($metaTitle): void
    {
        $this->metaTitle = $metaTitle;
    }

    /**
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * @return string
     */
    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    /**
     * @param string $metaKeywords
     */
    public function setMetaKeywords($metaKeywords): void
    {
        $this->metaKeywords = $metaKeywords;
    }

    /**
     * @return string
     */
    public function getMetaKeywords(): string
    {
        return $this->metaKeywords;
    }

    /**
     * @param string $template
     */
    public function setTemplate($template): void
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @param string $externalLink
     */
    public function setExternalLink($externalLink): void
    {
        $this->externalLink = $externalLink;
    }

    /**
     * @return string
     */
    public function getExternalLink(): string
    {
        return $this->externalLink;
    }

    /**
     * @param bool $displayFacets
     */
    public function setDisplayFacets($displayFacets): void
    {
        $this->displayFacets = $displayFacets;
    }

    /**
     * @param bool $displayInNavigation
     */
    public function setDisplayInNavigation($displayInNavigation): void
    {
        $this->displayInNavigation = $displayInNavigation;
    }

    /**
     * @param bool $blog
     */
    public function setBlog($blog): void
    {
        $this->blog = $blog;
    }

    /**
     * @param \Shopware\Media\Struct\Media $media
     */
    public function setMedia($media): void
    {
        $this->media = $media;
    }

    /**
     * @return \Shopware\Media\Struct\Media
     */
    public function getMedia(): Media
    {
        return $this->media;
    }

    /**
     * @return bool
     */
    public function isBlog(): bool
    {
        return $this->blog;
    }

    /**
     * @return bool
     */
    public function displayFacets(): bool
    {
        return $this->displayFacets;
    }

    /**
     * @return bool
     */
    public function displayInNavigation(): bool
    {
        return $this->displayInNavigation;
    }

    /**
     * @return int[]
     */
    public function getBlockedCustomerGroupIds(): array
    {
        return $this->blockedCustomerGroupIds;
    }

    /**
     * @param int[] $blockedCustomerGroupIds
     */
    public function setBlockedCustomerGroupIds(array $blockedCustomerGroupIds): void
    {
        $this->blockedCustomerGroupIds = $blockedCustomerGroupIds;
    }

    /**
     * @return int|null
     */
    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    /**
     * @param int|null $parentId
     */
    public function setParentId($parentId): void
    {
        $this->parentId = $parentId;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @return null|string
     */
    public function getProductBoxLayout(): ?string
    {
        return $this->productBoxLayout;
    }

    /**
     * @param null|string $productBoxLayout
     */
    public function setProductBoxLayout($productBoxLayout): void
    {
        $this->productBoxLayout = $productBoxLayout;
    }

    /**
     * @return null|\Shopware\ProductStream\Struct\ProductStream
     */
    public function getProductStream(): ?ProductStream
    {
        return $this->productStream;
    }

    /**
     * @param null|\Shopware\ProductStream\Struct\ProductStream $productStream
     */
    public function setProductStream(ProductStream $productStream = null): void
    {
        $this->productStream = $productStream;
    }

    /**
     * @return bool
     */
    public function hideSortings(): bool
    {
        return $this->hideSortings;
    }

    /**
     * @param bool $hideSortings
     */
    public function setHideSortings($hideSortings): void
    {
        $this->hideSortings = $hideSortings;
    }

    /**
     * @return Category[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param Category[] $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    /**
     * @param Category $category
     */
    public function addChildren(Category $category): void
    {
        $this->children[] = $category;
    }

    public function isShopCategory(): bool
    {
        return $this->isShopCategory;
    }

    public function setIsShopCategory(bool $isShopCategory): void
    {
        $this->isShopCategory = $isShopCategory;
    }
}
