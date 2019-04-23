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

namespace Shopware\Models\Category;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\ProductStream\ProductStream;
use Shopware\Models\Shop\Shop;

/**
 * Shopware Categories
 *
 *
 *
 * @ORM\Table(name="s_categories")
 * @ORM\Entity(repositoryClass="Repository")
 */
class Category extends ModelEntity
{
    /**
     * @var ArrayCollection<\Shopware\Models\Customer\Group>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Customer\Group")
     * @ORM\JoinTable(name="s_categories_avoid_customergroups",
     *     joinColumns={
     *         @ORM\JoinColumn(name="categoryID", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="customergroupID", referencedColumnName="id", unique=true)
     *     }
     * )
     */
    protected $customerGroups;

    /**
     * INVERSE SIDE
     *
     * @var \Shopware\Models\Attribute\Category
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Category", mappedBy="category", cascade={"persist"})
     */
    protected $attribute;

    /**
     * @var ArrayCollection<\Shopware\Models\Emotion\Emotion>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Emotion\Emotion", mappedBy="categories")
     * @ORM\JoinTable(name="s_emotion_categories",
     *     joinColumns={
     *         @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="emotion_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $emotions;

    /**
     * OWNING SIDE
     *
     * @var \Shopware\Models\Media\Media
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Media\Media")
     * @ORM\JoinColumn(name="mediaID", referencedColumnName="id")
     */
    protected $media;

    /**
     * @var string
     *
     * @ORM\Column(name="sorting_ids", type="string", nullable=true)
     */
    protected $sortingIds;

    /**
     * @var bool
     *
     * @ORM\Column(name="hide_sortings", type="boolean", nullable=false)
     */
    protected $hideSortings = false;

    /**
     * @var string
     *
     * @ORM\Column(name="facet_ids", type="string", nullable=true)
     */
    protected $facetIds;

    /**
     * @var string
     *
     * @ORM\Column(name="shops", type="string", length=255, nullable=false)
     */
    protected $shops;

    /**
     * Identifier for a single category. This is an autoincrement value.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * The id of the parent category
     *
     * @var int
     *
     * @ORM\Column(name="parent", type="integer", nullable=true)
     */
    private $parentId;

    /**
     * @var int
     *
     * @ORM\Column(name="stream_id", type="integer", nullable=true)
     */
    private $streamId;

    /**
     * @var ProductStream
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\ProductStream\ProductStream")
     * @ORM\JoinColumn(name="stream_id", referencedColumnName="id")
     */
    private $stream;

    /**
     * The parent category
     *
     * OWNING SIDE
     *
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumn(name="parent", nullable=true, referencedColumnName="id", onDelete="SET NULL")
     */
    private $parent;

    /**
     * String representation of the category
     *
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * Integer value on which the return values are ordered (asc)
     *
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    private $position;

    /**
     * SEO friendly title which is displayed in the HTML page.
     *
     * @var string
     *
     * @ORM\Column(name="meta_title", type="text", nullable=true)
     */
    private $metaTitle;

    /**
     * Keeps the meta keywords which are displayed in the HTML page.
     *
     * @var string
     *
     * @ORM\Column(name="metakeywords", type="text", nullable=true)
     */
    private $metaKeywords;

    /**
     * Keeps the meta description which is displayed in the HTML page.
     *
     * @var string
     *
     * @ORM\Column(name="metadescription", type="text", nullable=true)
     */
    private $metaDescription;

    /**
     * Keeps the CMS Headline for this category
     *
     * Max chars: 255
     *
     * @var string
     *
     * @ORM\Column(name="cmsheadline", type="string", length=255, nullable=true)
     */
    private $cmsHeadline;

    /**
     * Keeps the CMS Text for this category
     *
     * @var string
     *
     * @ORM\Column(name="cmstext", type="text", nullable=true)
     */
    private $cmsText;

    /**
     * Flag which shows if the category is active or not. 1= active otherwise inactive
     *
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = true;

    /**
     * If this field is set the category page will uses this template
     *
     * @var string
     *
     * @ORM\Column(name="template", type="string", length=255, nullable=true)
     */
    private $template;

    /**
     * @var string
     *
     * @ORM\Column(name="product_box_layout", type="string", length=50, nullable=true)
     */
    private $productBoxLayout = null;

    /**
     * @var bool
     *
     * @ORM\Column(name="blog", type="boolean", nullable=false)
     */
    private $blog = false;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", nullable=false)
     */
    private $path = '';

    /**
     * Is this category based outside from the shop?
     *
     * @var string
     *
     * @ORM\Column(name="external", type="string", length=255, nullable=true)
     */
    private $external;

    /**
     * Controls the target attribute if there is an external link set
     *
     * @var string
     *
     * @ORM\Column(name="external_target", type="text", nullable=false)
     */
    private $externalTarget = '';

    /**
     * Should any filter shown on the category page be hidden?
     *
     * @var bool
     *
     * @ORM\Column(name="hidefilter", type="boolean", nullable=false)
     */
    private $hideFilter = false;

    /**
     * Should the top part of that category be displayed?
     *
     * @var bool
     *
     * @ORM\Column(name="hidetop", type="boolean", nullable=false)
     */
    private $hideTop = false;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<Category>
     *
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent", cascade={"all"}))
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $children;

    /**
     * @var ArrayCollection<\Shopware\Models\Article\Article>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Article")
     * @ORM\JoinTable(name="s_articles_categories",
     *     joinColumns={
     *         @ORM\JoinColumn(name="categoryID", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     *     }
     * )
     */
    private $articles;

    /**
     * @var ArrayCollection<\Shopware\Models\Article\Article>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Article")
     * @ORM\JoinTable(name="s_articles_categories_ro",
     *     joinColumns={
     *         @ORM\JoinColumn(name="categoryID", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     *     }
     * )
     */
    private $allArticles;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="changed", type="datetime", nullable=false)
     */
    private $changed;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="added", type="datetime", nullable=false)
     */
    private $added;

    /**
     * @var int
     *
     * @ORM\Column(name="mediaID", type="integer", nullable=true)
     */
    private $mediaId;

    /**
     * INVERSE SIDE
     *
     * @var Collection<ManualSorting>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Category\ManualSorting", mappedBy="category", cascade={"all"}, orphanRemoval=true))
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $manualSorting;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->allArticles = new ArrayCollection();
        $this->emotions = new ArrayCollection();
        $this->manualSorting = new ArrayCollection();
        $this->changed = new \DateTime();
        $this->added = new \DateTime();
    }

    /**
     * Sets the primary key
     *
     * @param int $id
     */
    public function setPrimaryIdentifier($id)
    {
        $this->id = (int) $id;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return int
     */
    public function setId($id)
    {
        $this->id = $id;

        return $id;
    }

    /**
     * Get parent id
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Sets the id of the parent category
     *
     * @param Category $parent
     *
     * @return Category
     */
    public function setParent(Category $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parents category id
     *
     * @return Category|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param int $level
     *
     * @return int
     */
    public function getLevel($level = 0)
    {
        $parent = $this->getParent();

        if ($parent) {
            $level = $parent->getLevel($level + 1);
        }

        return $level;
    }

    /**
     * @param ArrayCollection<Category> $children
     *
     * @return Category
     */
    public function setChildren($children)
    {
        /** @var Category $child */
        foreach ($children as $child) {
            $child->setParent($this);
        }
        $this->children = $children;

        return $this;
    }

    /**
     * Get parents category id
     *
     * @return ArrayCollection<Category>
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return bool
     */
    public function isLeaf()
    {
        return $this->getChildren()->count() === 0;
    }

    /**
     * Sets the string representation of the category
     *
     * @param string $name
     *
     * @return Category
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns description string
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets an integer value on which the return values are ordered (asc)
     *
     * @param int $position
     *
     * @return Category
     */
    public function setPosition($position)
    {
        $this->position = (int) $position;

        return $this;
    }

    /**
     * Returns position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param \DateTimeInterface|string $changed
     *
     * @return Category
     */
    public function setChanged($changed = 'now')
    {
        if (!$changed instanceof \DateTimeInterface) {
            $this->changed = new \DateTime($changed);
        } else {
            $this->changed = $changed;
        }

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getChanged()
    {
        return $this->changed;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * Set the meta keywords.
     *
     * @param string $metaKeywords
     *
     * @return Category
     */
    public function setMetaKeywords($metaKeywords)
    {
        if (empty($metaKeywords)) {
            $metaKeywords = null;
        }

        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    /**
     * Returns the meta keywords
     *
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * Sets the  meta description text.
     *
     * @param string $metaDescription
     *
     * @return Category
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    /**
     * Gets the meta description text.
     *
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * Sets the CMS headline
     *
     * @param string $cmsHeadline
     *
     * @return Category
     */
    public function setCmsHeadline($cmsHeadline)
    {
        $this->cmsHeadline = $cmsHeadline;

        return $this;
    }

    /**
     * Gets the CMS headline
     *
     * @return string
     */
    public function getCmsHeadline()
    {
        return $this->cmsHeadline;
    }

    /**
     * Sets the CMS text
     *
     * @param string $cmsText
     *
     * @return Category
     */
    public function setCmsText($cmsText)
    {
        $this->cmsText = $cmsText;

        return $this;
    }

    /**
     * Gets CMS text
     *
     * @return string
     */
    public function getCmsText()
    {
        return $this->cmsText;
    }

    /**
     * @param string $template
     *
     * @return Category
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param bool $active
     *
     * @return Category
     */
    public function setActive($active)
    {
        $this->active = (bool) $active;

        return $this;
    }

    /**
     * Returns if the category is active or nor
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Returns if the category is blog category or nor
     *
     * @return bool
     */
    public function getBlog()
    {
        return $this->blog;
    }

    /**
     * Set category as a blog category
     *
     * @param bool $blog
     */
    public function setBlog($blog)
    {
        $this->blog = $blog;
    }

    /**
     * Sets the flag if this category goes to an  external source
     *
     * @param string $external
     *
     * @return Category
     */
    public function setExternal($external)
    {
        $this->external = $external;

        return $this;
    }

    /**
     * Gets the flag if this category is linked to an external source
     *
     * @return string
     */
    public function getExternal()
    {
        return $this->external;
    }

    /**
     * Returns the target property for the external link
     *
     * @return string
     */
    public function getExternalTarget()
    {
        return $this->externalTarget;
    }

    /**
     * Sets the target property for the external link
     *
     * @param string $externalTarget
     */
    public function setExternalTarget($externalTarget)
    {
        $this->externalTarget = $externalTarget;
    }

    /**
     * Set the flag which hides the filter
     *
     * @param bool $hideFilter
     *
     * @return Category
     */
    public function setHideFilter($hideFilter)
    {
        $this->hideFilter = (bool) $hideFilter;

        return $this;
    }

    /**
     * Returns if the filters should be displayed
     *
     * @return bool
     */
    public function getHideFilter()
    {
        return $this->hideFilter;
    }

    /**
     * Sets the flag if the top of the category should be hidden
     *
     * @param bool $hideTop
     *
     * @return Category
     */
    public function setHideTop($hideTop)
    {
        $this->hideTop = (bool) $hideTop;

        return $this;
    }

    /**
     * Returns the flag if the should be shown or not
     *
     * @return bool
     */
    public function getHideTop()
    {
        return $this->hideTop;
    }

    /**
     * Return all Articles associated with this category
     *
     * @return ArrayCollection
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * @return array
     */
    public function getAllArticles()
    {
        return $this->allArticles->toArray();
    }

    /**
     * Sets all Articles associated with this category
     *
     * @param ArrayCollection $articles
     *
     * @return Category
     */
    public function setArticles($articles)
    {
        $this->articles = $articles;

        return $this;
    }

    /**
     * Returns the Attributes
     *
     * @return \Shopware\Models\Attribute\Category
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Returns the category attribute
     *
     * @param \Shopware\Models\Attribute\Category|array|null $attribute
     *
     * @return Category
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, \Shopware\Models\Attribute\Category::class, 'attribute', 'category');
    }

    /**
     * Sets all Customer group associated data to this category
     *
     * @return ArrayCollection
     */
    public function getCustomerGroups()
    {
        return $this->customerGroups;
    }

    /**
     * Returns all Customer group associated data
     *
     * @param ArrayCollection $customerGroups
     *
     * @return Category
     */
    public function setCustomerGroups($customerGroups)
    {
        $this->customerGroups = $customerGroups;

        return $this;
    }

    /**
     * Returns the Media model
     *
     * @return \Shopware\Models\Media\Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Sets the Media model
     *
     * @param \Shopware\Models\Media\Media $media
     *
     * @return Category
     */
    public function setMedia($media)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * @return ArrayCollection<\Shopware\Models\Emotion\Emotion>
     */
    public function getEmotions()
    {
        return $this->emotions;
    }

    /**
     * @param ArrayCollection<\Shopware\Models\Emotion\Emotion> $emotions
     *
     * @return Category
     */
    public function setEmotions($emotions)
    {
        $this->emotions = $emotions;

        return $this;
    }

    /**
     * The path is set via Event Listener in \Shopware\Components\Model\CategorySubscriber
     *
     * @param string $path
     *
     * @return Category
     */
    public function internalSetPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Helper function which checks, if this category is child of a given parent category
     *
     * @return bool
     */
    public function isChildOf(\Shopware\Models\Category\Category $parent)
    {
        return $this->isChildOfInternal($this, $parent);
    }

    /**
     * @return string
     */
    public function getProductBoxLayout()
    {
        return $this->productBoxLayout;
    }

    /**
     * @param string $productBoxLayout
     *
     * @return Category
     */
    public function setProductBoxLayout($productBoxLayout)
    {
        $this->productBoxLayout = $productBoxLayout;

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
     * @param string $metaTitle
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;
    }

    /**
     * @return ProductStream
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * @param ProductStream $stream
     */
    public function setStream(ProductStream $stream = null)
    {
        $this->stream = $stream;
    }

    /**
     * @return int
     */
    public function getMediaId()
    {
        return $this->mediaId;
    }

    /**
     * @return string
     */
    public function getSortingIds()
    {
        return $this->sortingIds;
    }

    /**
     * @param string $sortingIds
     */
    public function setSortingIds($sortingIds)
    {
        $this->sortingIds = $sortingIds;
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

    /**
     * @return string
     */
    public function getFacetIds()
    {
        return $this->facetIds;
    }

    /**
     * @param string $facetIds
     */
    public function setFacetIds($facetIds)
    {
        $this->facetIds = $facetIds;
    }

    /**
     * @return string
     */
    public function getShops()
    {
        return $this->shops;
    }

    /**
     * @param string $shops
     *
     * @return $this
     */
    public function setShops($shops)
    {
        $this->shops = $shops;

        return $this;
    }

    /**
     * @return Collection<ManualSorting>
     */
    public function getManualSorting(): Collection
    {
        return $this->manualSorting;
    }

    /**
     * @param ManualSorting[]|null $manualSorting
     *
     * @return $this
     */
    public function setManualSorting(?array $manualSorting): Category
    {
        return $this->setOneToMany($manualSorting, ManualSorting::class, 'manualSorting', 'category');
    }

    /**
     * Helper function for the isChildOf function. This function is used for a recursive call.
     *
     * @return bool
     */
    protected function isChildOfInternal(Category $category, Category $searched)
    {
        if ($category->getParent() && $category->getParent()->getId() === $searched->getId()) {
            return true;
        }

        if ($category->getParent() instanceof self) {
            return $this->isChildOfInternal($category->getParent(), $searched);
        }

        return false;
    }
}
