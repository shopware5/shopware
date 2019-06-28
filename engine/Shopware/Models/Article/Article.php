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

namespace Shopware\Models\Article;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Category\Category as ArticleCategory;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Shopware Article Model
 *
 *
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_articles")
 * @ORM\HasLifecycleCallbacks()
 */
class Article extends ModelEntity
{
    /**
     * OWNING SIDE
     *
     * @var \Shopware\Models\Tax\Tax
     *
     * @Assert\NotBlank()
     * @Assert\Valid()
     *
     * @var \Shopware\Models\Tax\Tax
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Tax\Tax")
     * @ORM\JoinColumn(name="taxID", referencedColumnName="id")
     */
    protected $tax;

    /**
     * @var ArrayCollection<\Shopware\Models\Category\Category>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Category\Category")
     * @ORM\JoinTable(name="s_articles_categories",
     *     joinColumns={
     *         @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="categoryID", referencedColumnName="id")
     *     }
     * )
     */
    protected $categories;

    /**
     * @var ArrayCollection<\Shopware\Models\Category\Category>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Category\Category")
     * @ORM\JoinTable(name="s_articles_categories_ro",
     *     joinColumns={
     *         @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="categoryID", referencedColumnName="id")
     *     }
     * )
     */
    protected $allCategories;

    /**
     * @var ArrayCollection<\Shopware\Models\Article\SeoCategory>
     *
     * @ORM\OneToMany(
     *     targetEntity="Shopware\Models\Article\SeoCategory",
     *     mappedBy="article",
     *     orphanRemoval=true,
     *     cascade={"persist"}
     * )
     */
    protected $seoCategories;

    /**
     * @var ArrayCollection<\Shopware\Models\Customer\Group>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Customer\Group")
     * @ORM\JoinTable(name="s_articles_avoid_customergroups",
     *     joinColumns={
     *         @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="customergroupID", referencedColumnName="id", unique=true)
     *     }
     * )
     */
    protected $customerGroups;

    /**
     * @var ArrayCollection<\Shopware\Models\ProductStream\ProductStream>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\ProductStream\ProductStream")
     * @ORM\JoinTable(name="s_product_streams_articles",
     *     joinColumns={
     *         @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="stream_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $relatedProductStreams;

    /**
     * OWNING SIDE
     *
     * @var \Shopware\Models\Property\Group
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Property\Group", inversedBy="articles")
     * @ORM\JoinColumn(name="filtergroupID", referencedColumnName="id")
     */
    protected $propertyGroup;

    /**
     * @var ArrayCollection<\Shopware\Models\Article\Article>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Article")
     * @ORM\JoinTable(name="s_articles_relationships",
     *     joinColumns={
     *         @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="relatedarticle", referencedColumnName="id")
     *     }
     * )
     */
    protected $related;

    /**
     * @var ArrayCollection<\Shopware\Models\Article\Article>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Article")
     * @ORM\JoinTable(name="s_articles_similar",
     *     joinColumns={
     *         @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="relatedarticle", referencedColumnName="id")
     *     }
     * )
     */
    protected $similar;

    /**
     * OWNING SIDE
     *
     * @var Supplier
     *
     * @Assert\Valid()
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Supplier", inversedBy="articles", cascade={"persist"})
     * @ORM\JoinColumn(name="supplierID", referencedColumnName="id")
     */
    protected $supplier;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<\Shopware\Models\Article\Detail>
     *
     * @Assert\Valid()
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Detail", mappedBy="article", cascade={"persist"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $details;

    /**
     * OWNING SIDE
     *
     * @var Detail
     *
     * @Assert\NotBlank()
     * @Assert\Valid()
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Detail", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="main_detail_id", referencedColumnName="id")
     */
    protected $mainDetail;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<\Shopware\Models\Article\Link>
     *
     * @Assert\Valid()
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Link", mappedBy="article", orphanRemoval=true, cascade={"persist"})
     */
    protected $links;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<\Shopware\Models\Article\Download>
     *
     * @Assert\Valid()
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Download", mappedBy="article", orphanRemoval=true, cascade={"persist"})
     */
    protected $downloads;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<\Shopware\Models\Article\Image>
     *
     * @Assert\Valid()
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Image", mappedBy="article", orphanRemoval=true, cascade={"persist"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $images;

    /**
     * OWNING SIDE
     *
     * @var \Shopware\Models\Price\Group
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Price\Group")
     * @ORM\JoinColumn(name="pricegroupID", referencedColumnName="id")
     */
    protected $priceGroup;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<\Shopware\Models\Article\Vote>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Vote", mappedBy="article", orphanRemoval=true, cascade={"persist"})
     */
    protected $votes;

    /**
     * OWNING SIDE
     *
     * @var Configurator\Set
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Configurator\Set", inversedBy="articles", cascade={"persist"})
     * @ORM\JoinColumn(name="configurator_set_id", referencedColumnName="id")
     */
    protected $configuratorSet;

    /**
     * @var ArrayCollection<\Shopware\Models\Property\Value>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Property\Value", inversedBy="articles", cascade={"persist"})
     * @ORM\JoinTable(name="s_filter_articles",
     *     joinColumns={
     *         @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="valueID", referencedColumnName="id")
     *     }
     * )
     */
    protected $propertyValues;

    /**
     * INVERSE SIDE
     *
     * @var Configurator\Template\Template
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Configurator\Template\Template", mappedBy="article", orphanRemoval=true, cascade={"persist"})
     */
    protected $configuratorTemplate;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<\Shopware\Models\Article\Esd>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Esd", mappedBy="article", orphanRemoval=true, cascade={"persist"})
     */
    protected $esds;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="main_detail_id", type="integer", nullable=true)
     */
    private $mainDetailId;

    /**
     * @var int
     *
     * @ORM\Column(name="supplierID", type="integer", nullable=true)
     */
    private $supplierId;

    /**
     * @var int
     *
     * @ORM\Column(name="taxID", type="integer", nullable=true)
     */
    private $taxId;

    /**
     * @var int
     *
     * @ORM\Column(name="pricegroupID", type="integer", nullable=true)
     */
    private $priceGroupId;

    /**
     * @var int
     *
     * @ORM\Column(name="filtergroupID", type="integer", nullable=true)
     */
    private $filterGroupId;

    /**
     * @var int
     *
     * @ORM\Column(name="configurator_set_id", type="integer", nullable=true)
     */
    private $configuratorSetId;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="description_long", type="text", nullable=true)
     */
    private $descriptionLong;

    /**
     * @var \DateTimeInterface
     *
     * @Assert\DateTime()
     *
     * @ORM\Column(name="datum", type="date", nullable=true)
     */
    private $added;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = false;

    /**
     * @var int
     *
     * @ORM\Column(name="pseudosales", type="integer", nullable=false)
     */
    private $pseudoSales = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="topseller", type="boolean", nullable=false)
     */
    private $highlight = false;

    /**
     * @var string
     *
     * @ORM\Column(name="keywords", type="string", length=255, nullable=true)
     */
    private $keywords;

    /**
     * @var string
     *
     * @ORM\Column(name="metaTitle", type="string", length=255, nullable=true)
     */
    private $metaTitle;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="changetime", type="datetime", nullable=false)
     */
    private $changed;

    /**
     * @var bool
     *
     * @ORM\Column(name="pricegroupActive", type="boolean", nullable=false)
     */
    private $priceGroupActive = false;

    /**
     * @var bool
     *
     * @deprecated 5.6 will be removed in 5.8
     * @ORM\Column(name="laststock", type="boolean", nullable=true)
     */
    private $lastStock = false;

    /**
     * @var int
     *
     * @ORM\Column(name="crossbundlelook", type="integer", nullable=false)
     */
    private $crossBundleLook = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="notification", type="boolean", nullable=false)
     */
    private $notification = false;

    /**
     * @var string
     *
     * @ORM\Column(name="template", type="string", length=255, nullable=true)
     */
    private $template = '';

    /**
     * @var int
     *
     * @ORM\Column(name="mode", type="integer", nullable=false)
     */
    private $mode = 0;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="available_from", type="datetime", nullable=true)
     */
    private $availableFrom;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="available_to", type="datetime", nullable=true)
     */
    private $availableTo;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->allCategories = new ArrayCollection();
        $this->seoCategories = new ArrayCollection();
        $this->customerGroups = new ArrayCollection();
        $this->propertyValues = new ArrayCollection();
        $this->related = new ArrayCollection();
        $this->similar = new ArrayCollection();
        $this->details = new ArrayCollection();
        $this->links = new ArrayCollection();
        $this->downloads = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->esds = new ArrayCollection();
        $this->added = new \DateTime();
        $this->changed = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return Article
     */
    public function setName($name)
    {
        $this->name = trim($name);

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
     * @param string $description
     *
     * @return Article
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
     * @param string $descriptionLong
     *
     * @return Article
     */
    public function setDescriptionLong($descriptionLong)
    {
        $this->descriptionLong = $descriptionLong;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescriptionLong()
    {
        return $this->descriptionLong;
    }

    /**
     * @param \DateTimeInterface|string $added
     *
     * @return Article
     */
    public function setAdded($added = 'now')
    {
        if (!($added instanceof \DateTimeInterface)) {
            $this->added = new \DateTime($added);
        } else {
            $this->added = $added;
        }

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * @param bool $active
     *
     * @return Article
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param int $pseudoSales
     *
     * @return Article
     */
    public function setPseudoSales($pseudoSales)
    {
        $this->pseudoSales = $pseudoSales;

        return $this;
    }

    /**
     * @return int
     */
    public function getPseudoSales()
    {
        return $this->pseudoSales;
    }

    /**
     * @param bool $highlight
     *
     * @return Article
     */
    public function setHighlight($highlight)
    {
        $this->highlight = $highlight;

        return $this;
    }

    /**
     * @return bool
     */
    public function getHighlight()
    {
        return $this->highlight;
    }

    /**
     * @param string $keywords
     *
     * @return Article
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param string $metaTitle
     *
     * @return Article
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
     * @param \DateTimeInterface|string $changed
     *
     * @return Article
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
     * @param bool $priceGroupActive
     *
     * @return Article
     */
    public function setPriceGroupActive($priceGroupActive)
    {
        $this->priceGroupActive = $priceGroupActive;

        return $this;
    }

    /**
     * @return bool
     */
    public function getPriceGroupActive()
    {
        return $this->priceGroupActive;
    }

    /**
     * @deprecated 5.6 will be removed in 5.7
     *
     * @param bool $lastStock
     *
     * @return Article
     */
    public function setLastStock($lastStock)
    {
        $this->lastStock = $lastStock;

        return $this;
    }

    /**
     * @deprecated 5.6 will be removed in 5.7
     *
     * @return bool
     */
    public function getLastStock()
    {
        return $this->lastStock;
    }

    /**
     * @param bool $notification
     *
     * @return Article
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * @return bool
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @param string $template
     *
     * @return Article
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
     * @param int $mode
     *
     * @return Article
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return ArrayCollection<\Shopware\Models\Category\Category>
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return array
     */
    public function getAllCategories()
    {
        return $this->allCategories->toArray();
    }

    /**
     * @param ArrayCollection<\Shopware\Models\Category\Category> $categories
     *
     * @return Article
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @return Article
     */
    public function addCategory(ArticleCategory $category)
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    /**
     * @return Article
     */
    public function removeCategory(ArticleCategory $category)
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getCustomerGroups()
    {
        return $this->customerGroups;
    }

    /**
     * @param ArrayCollection $customerGroups
     *
     * @return Article
     */
    public function setCustomerGroups($customerGroups)
    {
        $this->customerGroups = $customerGroups;

        return $this;
    }

    /**
     * @return \Shopware\Models\Property\Group|null
     */
    public function getPropertyGroup()
    {
        return $this->propertyGroup;
    }

    /**
     * @param \Shopware\Models\Property\Group $propertyGroup
     *
     * @return Article
     */
    public function setPropertyGroup($propertyGroup)
    {
        $this->propertyGroup = $propertyGroup;

        return $this;
    }

    /**
     * @return ArrayCollection<Article>
     */
    public function getRelated()
    {
        return $this->related;
    }

    /**
     * @return ArrayCollection<Article>
     */
    public function getSimilar()
    {
        return $this->similar;
    }

    /**
     * @param ArrayCollection<Article> $related
     *
     * @return Article
     */
    public function setRelated($related)
    {
        $this->related = $related;

        return $this;
    }

    /**
     * @param ArrayCollection<Article> $similar
     *
     * @return Article
     */
    public function setSimilar($similar)
    {
        $this->similar = $similar;

        return $this;
    }

    /**
     * @return \Shopware\Models\Tax\Tax
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param \Shopware\Models\Tax\Tax $tax
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    }

    /**
     * @return ArrayCollection<Image>
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param Image[]|null $images
     *
     * @return Article
     */
    public function setImages($images)
    {
        $this->setOneToMany($images, Image::class, 'images', 'article');

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getDownloads()
    {
        return $this->downloads;
    }

    /**
     * @param Download[]|null $downloads
     *
     * @return Article
     */
    public function setDownloads($downloads)
    {
        $this->setOneToMany($downloads, Download::class, 'downloads', 'article');

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param Link[]|null $links
     *
     * @return Article
     */
    public function setLinks($links)
    {
        $this->setOneToMany($links, Link::class, 'links', 'article');

        return $this;
    }

    /**
     * OWNING SIDE
     * of the association between articles and supplier
     *
     * @return Supplier
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @param Supplier|array|null $supplier
     *
     * @return \Shopware\Components\Model\ModelEntity
     */
    public function setSupplier($supplier)
    {
        return $this->setManyToOne($supplier, Supplier::class, 'supplier');
    }

    /**
     * @return ArrayCollection
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param Detail[]|null $details
     *
     * @return Article
     */
    public function setDetails($details)
    {
        $this->setOneToMany($details, Detail::class, 'details', 'article');

        return $this;
    }

    /**
     * @return Detail|null
     */
    public function getMainDetail()
    {
        return $this->mainDetail;
    }

    /**
     * @param Detail|null $mainDetail
     *
     * @return Article
     */
    public function setMainDetail($mainDetail)
    {
        $this->setOneToOne($mainDetail, Detail::class, 'mainDetail', 'article');
        if ($this->mainDetail instanceof Detail) {
            $this->mainDetail->setKind(1);
        }

        return $this;
    }

    /**
     * @return \Shopware\Models\Price\Group
     */
    public function getPriceGroup()
    {
        return $this->priceGroup;
    }

    /**
     * @param \Shopware\Models\Price\Group|null $priceGroup
     *
     * @return Article
     */
    public function setPriceGroup($priceGroup)
    {
        $this->priceGroup = $priceGroup;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * @param Vote[]|null $votes
     *
     * @return Article
     */
    public function setVotes($votes)
    {
        $this->setOneToMany($votes, Vote::class, 'votes', 'article');

        return $this;
    }

    /**
     * @return int
     */
    public function getCrossBundleLook()
    {
        return $this->crossBundleLook;
    }

    /**
     * @param int $crossBundleLook
     *
     * @return Article
     */
    public function setCrossBundleLook($crossBundleLook)
    {
        $this->crossBundleLook = $crossBundleLook;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getAvailableFrom()
    {
        return $this->availableFrom;
    }

    /**
     * @param \DateTimeInterface $availableFrom
     *
     * @return Article
     */
    public function setAvailableFrom($availableFrom)
    {
        $this->availableFrom = $availableFrom;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getAvailableTo()
    {
        return $this->availableTo;
    }

    /**
     * @param \DateTimeInterface $availableTo
     *
     * @return Article
     */
    public function setAvailableTo($availableTo)
    {
        $this->availableTo = $availableTo;

        return $this;
    }

    /**
     * @return Configurator\Set|null
     */
    public function getConfiguratorSet()
    {
        return $this->configuratorSet;
    }

    /**
     * @param Configurator\Set $configuratorSet
     *
     * @return Article
     */
    public function setConfiguratorSet($configuratorSet)
    {
        $this->setManyToOne($configuratorSet, Configurator\Set::class, 'configuratorSet');

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPropertyValues()
    {
        return $this->propertyValues;
    }

    /**
     * @param ArrayCollection $propertyValues
     *
     * @return Article
     */
    public function setPropertyValues($propertyValues)
    {
        $this->propertyValues = $propertyValues;

        return $this;
    }

    /**
     * @return Configurator\Template\Template
     */
    public function getConfiguratorTemplate()
    {
        return $this->configuratorTemplate;
    }

    /**
     * @param Configurator\Template\Template $configuratorTemplate
     *
     * @return Article
     */
    public function setConfiguratorTemplate($configuratorTemplate)
    {
        $this->setOneToOne(
            $configuratorTemplate,
            Configurator\Template\Template::class,
            'configuratorTemplate',
            'article'
        );

        return $this;
    }

    /**
     * @return ArrayCollection<\Shopware\Models\Article\SeoCategory>
     */
    public function getSeoCategories()
    {
        return $this->seoCategories;
    }

    /**
     * @param SeoCategory[]|null $seoCategories
     *
     * @return \Shopware\Components\Model\ModelEntity
     */
    public function setSeoCategories($seoCategories)
    {
        return $this->setOneToMany(
            $seoCategories,
            SeoCategory::class,
            'seoCategories',
            'article'
        );
    }

    /**
     * @return ArrayCollection<\Shopware\Models\ProductStream\ProductStream>
     */
    public function getRelatedProductStreams()
    {
        return $this->relatedProductStreams;
    }

    /**
     * @param ArrayCollection<\Shopware\Models\ProductStream\ProductStream> $relatedProductStreams
     */
    public function setRelatedProductStreams($relatedProductStreams)
    {
        $this->relatedProductStreams = $relatedProductStreams;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updateChangedTimestamp()
    {
        $this->changed = new \DateTime();
    }
}
