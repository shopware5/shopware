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

use Shopware\Components\Model\ModelEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Models\Category\Category as ArticleCategory;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Shopware Article Model
 *
 * @category  Shopware
 * @package   Shopware\Models
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_articles")
 */
class Article extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="main_detail_id", type="integer", nullable=true)
     */
    private $mainDetailId = null;

    /**
     * @var integer $supplierId
     *
     * @ORM\Column(name="supplierID", type="integer", nullable=true)
     */
    private $supplierId = null;

    /**
     * @var integer $taxId
     *
     * @ORM\Column(name="taxID", type="integer", nullable=true)
     */
    private $taxId = null;

    /**
     * @var integer $priceGroupId
     *
     * @ORM\Column(name="pricegroupID", type="integer", nullable=true)
     */
    private $priceGroupId = null;

    /**
     * @var integer $filterGroupId
     *
     * @ORM\Column(name="filtergroupID", type="integer", nullable=true)
     */
    private $filterGroupId = null;

    /**
     * @var integer $filterGroupId
     *
     * @ORM\Column(name="configurator_set_id", type="integer", nullable=true)
     */
    private $configuratorSetId = null;

    /**
     * @var string $name
     *
     * @Assert\NotBlank
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description = null;

    /**
     * @var string $descriptionLong
     *
     * @ORM\Column(name="description_long", type="text", nullable=true)
     */
    private $descriptionLong = null;

    /**
     * @var \DateTime $added
     *
     * @Assert\DateTime()
     *
     * @ORM\Column(name="datum", type="date", nullable=true)
     */
    private $added = null;

    /**
     * @var integer $active
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = false;


    /**
     * @var integer $pseudoSales
     *
     * @ORM\Column(name="pseudosales", type="integer", nullable=false)
     */
    private $pseudoSales = 0;

    /**
     * @var integer $highlight
     *
     * @ORM\Column(name="topseller", type="boolean", nullable=false)
     */
    private $highlight = false;

    /**
     * @var string $keywords
     *
     * @ORM\Column(name="keywords", type="string", length=255, nullable=true)
     */
    private $keywords = null;

    /**
     * @var string $metaTitle
     *
     * @ORM\Column(name="metaTitle", type="string", length=255, nullable=true)
     */
    private $metaTitle = null;

    /**
     * @var \DateTime $changed
     *
     * @ORM\Column(name="changetime", type="datetime", nullable=false)
     */
    private $changed = 'now';

    /**
     * @var integer $priceGroupActive
     *
     * @ORM\Column(name="pricegroupActive", type="boolean", nullable=false)
     */
    private $priceGroupActive = false;

    /**
     * @var integer $lastStock
     *
     * @ORM\Column(name="laststock", type="boolean", nullable=false)
     */
    private $lastStock = false;

    /**
     * @var integer $crossBundleLook
     *
     * @ORM\Column(name="crossbundlelook", type="integer", nullable=false)
     */
    private $crossBundleLook = false;

    /**
     * @var integer $notification
     *
     * @ORM\Column(name="notification", type="boolean", nullable=false)
     */
    private $notification = false;

    /**
     * @var string $template
     *
     * @ORM\Column(name="template", type="string", length=255, nullable=true)
     */
    private $template = '';

    /**
     * @var integer $mode
     *
     * @ORM\Column(name="mode", type="integer", nullable=false)
     */
    private $mode = 0;

    /**
     * @var \DateTime $availableFrom
     *
     * @ORM\Column(name="available_from", type="datetime", nullable=true)
     */
    private $availableFrom = null;

    /**
     * @var \DateTime $availableTo
     *
     * @ORM\Column(name="available_to", type="datetime", nullable=true)
     */
    private $availableTo = null;

    /**
     * OWNING SIDE
     *
     * @Assert\NotBlank
     * @Assert\Valid
     *
     * @var \Shopware\Models\Tax\Tax $tax
     * @ORM\OneToOne(targetEntity="Shopware\Models\Tax\Tax")
     * @ORM\JoinColumn(name="taxID", referencedColumnName="id")
     */
    protected $tax;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Category\Category")
     * @ORM\JoinTable(name="s_articles_categories",
     *      joinColumns={
     *          @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="categoryID", referencedColumnName="id")
     *      }
     * )
     */
    protected $categories;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Category\Category")
     * @ORM\JoinTable(name="s_articles_categories_ro",
     *      joinColumns={
     *          @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="categoryID", referencedColumnName="id")
     *      }
     * )
     */
    protected $allCategories;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(
     *      targetEntity="Shopware\Models\Article\SeoCategory",
     *      mappedBy="article",
     *      orphanRemoval=true,
     *      cascade={"persist"}
     * )
     */
    protected $seoCategories;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Customer\Group")
     * @ORM\JoinTable(name="s_articles_avoid_customergroups",
     *      joinColumns={
     *          @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="customergroupID", referencedColumnName="id", unique=true)
     *      }
     * )
     */
    protected $customerGroups;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\ProductStream\ProductStream")
     * @ORM\JoinTable(name="s_product_streams_articles",
     *      joinColumns={
     *          @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="stream_id", referencedColumnName="id")
     *      }
     * )
     */
    protected $relatedProductStreams;

    /**
     * OWNING SIDE
     *
     * @var ArrayCollection
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Property\Group", inversedBy="articles")
     * @ORM\JoinColumn(name="filtergroupID", referencedColumnName="id")
     */
    protected $propertyGroup;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Article")
     * @ORM\JoinTable(name="s_articles_relationships",
     *      joinColumns={
     *          @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="relatedarticle", referencedColumnName="id")
     *      }
     * )
     */
    protected $related;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Article")
     * @ORM\JoinTable(name="s_articles_similar",
     *      joinColumns={
     *          @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="relatedarticle", referencedColumnName="id")
     *      }
     * )
     */
    protected $similar;

    /**
     * OWNING SIDE
     *
     * @var \Shopware\Models\Article\Supplier $supplier
     *
     * @Assert\Valid
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Supplier", inversedBy="articles", cascade={"persist"})
     * @ORM\JoinColumn(name="supplierID", referencedColumnName="id")
     */
    protected $supplier;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection
     *
     * @Assert\Valid
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Detail", mappedBy="article", cascade={"persist"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $details;

    /**
     * OWNING SIDE
     *
     * @var \Shopware\Models\Article\Detail
     *
     * @Assert\NotBlank
     * @Assert\Valid
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Detail", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="main_detail_id", referencedColumnName="id")
     */
    protected $mainDetail;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection
     *
     * @Assert\Valid
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Link", mappedBy="article", orphanRemoval=true, cascade={"persist"})
     */
    protected $links;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection
     *
     * @Assert\Valid
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Download", mappedBy="article", orphanRemoval=true, cascade={"persist"})
     */
    protected $downloads;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection
     *
     * @Assert\Valid
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Image", mappedBy="article", orphanRemoval=true, cascade={"persist"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $images;

    /**
     * OWNING SIDE
     *
     * @var \Shopware\Models\Price\Group $priceGroup
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Price\Group")
     * @ORM\JoinColumn(name="pricegroupID", referencedColumnName="id")
     */
    protected $priceGroup;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Vote", mappedBy="article", orphanRemoval=true, cascade={"persist"})
     */
    protected $votes;

    /**
     * INVERSE SIDE
     *
     * @var \Shopware\Models\Attribute\Article
     *
     * @Assert\Valid
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Article", mappedBy="article", cascade={"persist"})
     */
    protected $attribute;

    /**
     * OWNING SIDE
     *
     * @var \Shopware\Models\Article\Configurator\Set
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Configurator\Set", inversedBy="articles", cascade={"persist"})
     * @ORM\JoinColumn(name="configurator_set_id", referencedColumnName="id")
     */
    protected $configuratorSet;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Property\Value", inversedBy="articles", cascade={"persist"})
     * @ORM\JoinTable(name="s_filter_articles",
     *      joinColumns={
     *          @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="valueID", referencedColumnName="id")
     *      }
     * )
     */
    protected $propertyValues;

    /**
     * INVERSE SIDE
     *
     * @var \Shopware\Models\Article\Configurator\Template\Template
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Configurator\Template\Template", mappedBy="article", orphanRemoval=true, cascade={"persist"})
     */
    protected $configuratorTemplate;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Esd", mappedBy="article", orphanRemoval=true, cascade={"persist"})
     */
    protected $esds;

    /**
     * Class constructor.
     */
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Article
     */
    public function setName($name)
    {
        $this->name = trim($name);
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Article
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set descriptionLong
     *
     * @param string $descriptionLong
     * @return Article
     */
    public function setDescriptionLong($descriptionLong)
    {
        $this->descriptionLong = $descriptionLong;
        return $this;
    }

    /**
     * Get descriptionLong
     *
     * @return string
     */
    public function getDescriptionLong()
    {
        return $this->descriptionLong;
    }

    /**
     * Set date
     *
     * @param \DateTime|string $added
     * @return Article
     */
    public function setAdded($added = 'now')
    {
        if (!($added instanceof \DateTime)) {
            $this->added = new \DateTime($added);
        } else {
            $this->added = $added;
        }
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * Set active
     *
     * @param bool $active
     * @return Article
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Get active
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set pseudoSales
     *
     * @param integer $pseudoSales
     * @return Article
     */
    public function setPseudoSales($pseudoSales)
    {
        $this->pseudoSales = $pseudoSales;
        return $this;
    }

    /**
     * Get pseudoSales
     *
     * @return integer
     */
    public function getPseudoSales()
    {
        return $this->pseudoSales;
    }

    /**
     * Set highlight
     *
     * @param integer $highlight
     * @return Article
     */
    public function setHighlight($highlight)
    {
        $this->highlight = $highlight;
        return $this;
    }

    /**
     * Get highlight
     *
     * @return integer
     */
    public function getHighlight()
    {
        return $this->highlight;
    }

    /**
     * Set keywords
     *
     * @param string $keywords
     * @return Article
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
        return $this;
    }

    /**
     * Get keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set metaTitle
     *
     * @param string $metaTitle
     * @return Article
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;
        return $this;
    }

    /**
     * Get metaTitle
     *
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * Set changed
     *
     * @param \DateTime|string $changed
     * @return Article
     */
    public function setChanged($changed = 'now')
    {
        if (!$changed instanceof \DateTime) {
            $this->changed = new \DateTime($changed);
        } else {
            $this->changed = $changed;
        }
        return $this;
    }

    /**
     * Get changed
     *
     * @return \DateTime
     */
    public function getChanged()
    {
        return $this->changed;
    }

    /**
     * Set priceGroupActive
     *
     * @param integer $priceGroupActive
     * @return Article
     */
    public function setPriceGroupActive($priceGroupActive)
    {
        $this->priceGroupActive = $priceGroupActive;
        return $this;
    }

    /**
     * Get priceGroupActive
     *
     * @return integer
     */
    public function getPriceGroupActive()
    {
        return $this->priceGroupActive;
    }

    /**
     * Set lastStock
     *
     * @param integer $lastStock
     * @return Article
     */
    public function setLastStock($lastStock)
    {
        $this->lastStock = $lastStock;
        return $this;
    }

    /**
     * Get lastStock
     *
     * @return integer
     */
    public function getLastStock()
    {
        return $this->lastStock;
    }

    /**
     * Set notification
     *
     * @param integer $notification
     * @return Article
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;
        return $this;
    }

    /**
     * Get notification
     *
     * @return integer
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Set template
     *
     * @param string $template
     * @return Article
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set mode
     *
     * @param integer $mode
     * @return Article
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * Get mode
     *
     * @return integer
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return ArrayCollection
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
     * @param ArrayCollection $categories
     * @return Article
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @param ArticleCategory $category
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
     * @param ArticleCategory $category
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
     * @return Article
     */
    public function setCustomerGroups($customerGroups)
    {
        $this->customerGroups = $customerGroups;
        return $this;
    }

    /**
     * @return \Shopware\Models\Property\Group
     */
    public function getPropertyGroup()
    {
        return $this->propertyGroup;
    }

    /**
     * @param \Shopware\Models\Property\Group $propertyGroup
     * @return Article
     */
    public function setPropertyGroup($propertyGroup)
    {
        $this->propertyGroup = $propertyGroup;
        return $this;
    }



    /**
     * @return ArrayCollection
     */
    public function getRelated()
    {
        return $this->related;
    }

    /**
     * @return ArrayCollection
     */
    public function getSimilar()
    {
        return $this->similar;
    }

    /**
     * @param $related ArrayCollection
     * @return Article
     */
    public function setRelated($related)
    {
        $this->related = $related;
        return $this;
    }

    /**
     * @param $similar ArrayCollection
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
     * @return ArrayCollection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param ArrayCollection|array|null $images
     * @return Article
     */
    public function setImages($images)
    {
        return $this->setOneToMany($images, '\Shopware\Models\Article\Image', 'images', 'article');
    }

    /**
     * @return ArrayCollection
     */
    public function getDownloads()
    {
        return $this->downloads;
    }

    /**
     * @param ArrayCollection|array|null $downloads
     * @return Article
     */
    public function setDownloads($downloads)
    {
        return $this->setOneToMany($downloads, '\Shopware\Models\Article\Download', 'downloads', 'article');
    }

    /**
     * @return ArrayCollection
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param ArrayCollection|array|null $links
     * @return Article
     */
    public function setLinks($links)
    {
        return $this->setOneToMany($links, '\Shopware\Models\Article\Link', 'links', 'article');
    }

    /**
     * OWNING SIDE
     * of the association between articles and supplier
     * @return \Shopware\Models\Article\Supplier
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @param \Shopware\Models\Article\Supplier|array|null $supplier
     * @return \Shopware\Components\Model\ModelEntity
     */
    public function setSupplier($supplier)
    {
        return $this->setManyToOne($supplier, '\Shopware\Models\Article\Supplier', 'supplier');
    }

    /**
     * @return ArrayCollection
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param ArrayCollection|array|null $details
     * @return Article
     */
    public function setDetails($details)
    {
        return $this->setOneToMany($details, '\Shopware\Models\Article\Detail', 'details', 'article');
    }

    /**
     * @return \Shopware\Models\Article\Detail
     */
    public function getMainDetail()
    {
        return $this->mainDetail;
    }

    /**
     * @param \Shopware\Models\Article\Detail|array|null $mainDetail
     * @return \Shopware\Models\Article\Detail
     */
    public function setMainDetail($mainDetail)
    {
        $return = $this->setOneToOne($mainDetail, '\Shopware\Models\Article\Detail', 'mainDetail', 'article');
        if ($this->mainDetail instanceof \Shopware\Models\Article\Detail) {
            $this->mainDetail->setKind(1);
        }
        return $return;
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
     * @param ArrayCollection|array|null $votes
     * @return Article
     */
    public function setVotes($votes)
    {
        return $this->setOneToMany($votes, '\Shopware\Models\Article\Vote', 'votes', 'article');
    }

    /**
     * @return \Shopware\Models\Attribute\Article
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\Article|array|null $attribute
     * @return Article
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\Article', 'attribute', 'article');
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
     * @return Article
     */
    public function setCrossBundleLook($crossBundleLook)
    {
        $this->crossBundleLook = $crossBundleLook;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getAvailableFrom()
    {
        return $this->availableFrom;
    }

    /**
     * @param \DateTime $availableFrom
     * @return Article
     */
    public function setAvailableFrom($availableFrom)
    {
        $this->availableFrom = $availableFrom;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getAvailableTo()
    {
        return $this->availableTo;
    }

    /**
     * @param \DateTime $availableTo
     * @return Article
     */
    public function setAvailableTo($availableTo)
    {
        $this->availableTo = $availableTo;

        return $this;
    }

    /**
     * @return \Shopware\Models\Article\Configurator\Set
     */
    public function getConfiguratorSet()
    {
        return $this->configuratorSet;
    }

    /**
     * @param \Shopware\Models\Article\Configurator\Set $configuratorSet
     * @return Article
     */
    public function setConfiguratorSet($configuratorSet)
    {
        $this->setManyToOne($configuratorSet, '\Shopware\Models\Article\Configurator\Set', 'configuratorSet');

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
     * @return Article
     */
    public function setPropertyValues($propertyValues)
    {
        $this->propertyValues = $propertyValues;

        return $this;
    }

    /**
     * @return \Shopware\Models\Article\Configurator\Template\Template
     */
    public function getConfiguratorTemplate()
    {
        return $this->configuratorTemplate;
    }

    /**
     * @param \Shopware\Models\Article\Configurator\Template\Template $configuratorTemplate
     * @return Article
     */
    public function setConfiguratorTemplate($configuratorTemplate)
    {
        $this->setOneToOne($configuratorTemplate, '\Shopware\Models\Article\Configurator\Template\Template', 'configuratorTemplate', 'article');

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getSeoCategories()
    {
        return $this->seoCategories;
    }

    /**
     * @param ArrayCollection $seoCategories
     * @return \Shopware\Components\Model\ModelEntity
     */
    public function setSeoCategories($seoCategories)
    {
        return $this->setOneToMany(
            $seoCategories,
            '\Shopware\Models\Article\SeoCategory',
            'seoCategories',
            'article'
        );
    }

    /**
     * @return ArrayCollection
     */
    public function getRelatedProductStreams()
    {
        return $this->relatedProductStreams;
    }

    /**
     * @param ArrayCollection $relatedProductStreams
     */
    public function setRelatedProductStreams($relatedProductStreams)
    {
        $this->relatedProductStreams = $relatedProductStreams;
    }
}
