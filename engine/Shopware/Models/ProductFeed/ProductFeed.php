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

namespace Shopware\Models\ProductFeed;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Attribute\ProductFeed as ProductFeedAttribute;

/**
 * Shopware product feed model represents a single feed.
 *
 * The Shopware product feed  model represents a row of the s_export table.
 * The product feed model data set from the Shopware\Models\ProductFeed\Repository.
 *
 * @ORM\Table(name="s_export")
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\HasLifecycleCallbacks()
 */
class ProductFeed extends ModelEntity
{
    /**
     * @var bool
     *
     * @ORM\Column(name="dirty", type="boolean")
     */
    protected $dirty = false;

    /**
     * INVERSE SIDE
     *
     * @var ProductFeedAttribute
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\ProductFeed", mappedBy="productFeed", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="last_export", type="datetime", nullable=false)
     */
    private $lastExport;

    /**
     * @var int
     *
     * @ORM\Column(name="active", type="integer", nullable=false)
     */
    private $active = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="hash", type="string", length=255, nullable=false)
     */
    private $hash;

    /**
     * @var int
     *
     * @ORM\Column(name="`show`", type="integer", nullable=false)
     */
    private $show = 1;

    /**
     * @var int
     *
     * @ORM\Column(name="count_articles", type="integer", nullable=false)
     */
    private $countArticles;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="expiry", type="datetime", nullable=false)
     */
    private $expiry;

    /**
     * @var int
     *
     * @ORM\Column(name="`interval`", type="integer", nullable=false)
     */
    private $interval;

    /**
     * @var int
     *
     * @ORM\Column(name="formatID", type="integer", nullable=false)
     */
    private $formatId = 1;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="last_change", type="datetime", nullable=false)
     */
    private $lastChange;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=255, nullable=false)
     */
    private $fileName;

    /**
     * @var int
     *
     * @ORM\Column(name="encodingID", type="integer", nullable=false)
     */
    private $encodingId = 1;

    /**
     * @var int
     *
     * @ORM\Column(name="categoryID", type="integer", nullable=true)
     */
    private $categoryId;

    /**
     * @var int
     *
     * @ORM\Column(name="currencyID", type="integer", nullable=true)
     */
    private $currencyId;

    /**
     * @var int
     *
     * @ORM\Column(name="customergroupID", type="integer", nullable=true)
     */
    private $customerGroupId;

    /**
     * @var string
     *
     * @ORM\Column(name="partnerID", type="string", length=255, nullable=true)
     */
    private $partnerId;

    /**
     * @var int
     *
     * @ORM\Column(name="languageID", type="integer", nullable=true)
     */
    private $languageId;

    /**
     * @var int
     *
     * @ORM\Column(name="active_filter", type="integer", nullable=false)
     */
    private $activeFilter = 1;

    /**
     * @var int
     *
     * @ORM\Column(name="image_filter", type="integer", nullable=false)
     */
    private $imageFilter = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="stockmin_filter", type="integer", nullable=false)
     */
    private $stockMinFilter = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="instock_filter", type="integer", nullable=false)
     */
    private $instockFilter;

    /**
     * @var float
     *
     * @ORM\Column(name="price_filter", type="float", nullable=false)
     */
    private $priceFilter;

    /**
     * @var string
     *
     * @ORM\Column(name="own_filter", type="text", nullable=false)
     */
    private $ownFilter;

    /**
     * @var string
     *
     * @ORM\Column(name="header", type="text", nullable=false)
     */
    private $header;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text", nullable=false)
     */
    private $body;

    /**
     * @var string
     *
     * @ORM\Column(name="footer", type="text", nullable=false)
     */
    private $footer;

    /**
     * @var int
     *
     * @ORM\Column(name="count_filter", type="integer", nullable=false)
     */
    private $countFilter;

    /**
     * @var int
     *
     * @ORM\Column(name="multishopID", type="integer", nullable=true)
     */
    private $shopId;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="cache_refreshed", type="datetime", nullable=true)
     */
    private $cacheRefreshed;

    /**
     * @var int
     *
     * @ORM\Column(name="variant_export", type="integer", nullable=false)
     */
    private $variantExport = 1;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Article\Supplier>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Supplier")
     * @ORM\JoinTable(name="s_export_suppliers",
     *     joinColumns={@ORM\JoinColumn(name="feedID", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="supplierID", referencedColumnName="id")}
     * )
     */
    private $suppliers;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Article\Article>
     *
     * @ORM\ManyToMany(targetEntity="\Shopware\Models\Article\Article")
     * @ORM\JoinTable(name="s_export_articles",
     *     joinColumns={@ORM\JoinColumn(name="feedID", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="articleID", referencedColumnName="id")}
     * )
     */
    private $articles;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Category\Category>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Category\Category")
     * @ORM\JoinTable(name="s_export_categories",
     *     joinColumns={@ORM\JoinColumn(name="feedID", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="categoryID", referencedColumnName="id")}
     * )
     */
    private $categories;

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
     * @return ProductFeed
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
     * @param \DateTimeInterface|string $lastExport
     *
     * @return ProductFeed
     */
    public function setLastExport($lastExport)
    {
        if (!$lastExport instanceof \DateTimeInterface) {
            $lastExport = new \DateTime($lastExport);
        }
        $this->lastExport = $lastExport;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastExport()
    {
        return $this->lastExport;
    }

    /**
     * @param int $active
     *
     * @return ProductFeed
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param string $hash
     *
     * @return ProductFeed
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param int $show
     *
     * @return ProductFeed
     */
    public function setShow($show)
    {
        $this->show = $show;

        return $this;
    }

    /**
     * @return int
     */
    public function getShow()
    {
        return $this->show;
    }

    /**
     * @param int $countArticles
     *
     * @return ProductFeed
     */
    public function setCountArticles($countArticles)
    {
        $this->countArticles = $countArticles;

        return $this;
    }

    /**
     * @return int
     */
    public function getCountArticles()
    {
        return $this->countArticles;
    }

    /**
     * @param \DateTimeInterface|string $expiry
     *
     * @return ProductFeed
     */
    public function setExpiry($expiry)
    {
        if (!$expiry instanceof \DateTimeInterface) {
            $expiry = new \DateTime($expiry);
        }
        $this->expiry = $expiry;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * @param int $interval
     *
     * @return ProductFeed
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     * @return int
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param int $formatId
     *
     * @return ProductFeed
     */
    public function setFormatId($formatId)
    {
        $this->formatId = $formatId;

        return $this;
    }

    /**
     * @return int
     */
    public function getFormatId()
    {
        return $this->formatId;
    }

    /**
     * @param \DateTimeInterface|string $lastChange
     *
     * @return ProductFeed
     */
    public function setLastChange($lastChange)
    {
        if (!$lastChange instanceof \DateTimeInterface) {
            $lastChange = new \DateTime($lastChange);
        }
        $this->lastChange = $lastChange;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastChange()
    {
        return $this->lastChange;
    }

    /**
     * @param string $fileName
     *
     * @return ProductFeed
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param int $encodingId
     *
     * @return ProductFeed
     */
    public function setEncodingId($encodingId)
    {
        $this->encodingId = $encodingId;

        return $this;
    }

    /**
     * @return int
     */
    public function getEncodingId()
    {
        return $this->encodingId;
    }

    /**
     * @param int $categoryId
     *
     * @return ProductFeed
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param int $currencyId
     *
     * @return ProductFeed
     */
    public function setCurrencyId($currencyId)
    {
        $this->currencyId = $currencyId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrencyId()
    {
        return $this->currencyId;
    }

    /**
     * @param int $customerGroupId
     *
     * @return ProductFeed
     */
    public function setCustomerGroupId($customerGroupId)
    {
        $this->customerGroupId = $customerGroupId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->customerGroupId;
    }

    /**
     * @param string $partnerId
     *
     * @return ProductFeed
     */
    public function setPartnerId($partnerId)
    {
        $this->partnerId = $partnerId;

        return $this;
    }

    /**
     * @return string
     */
    public function getPartnerId()
    {
        return $this->partnerId;
    }

    /**
     * @param int $languageId
     *
     * @return ProductFeed
     */
    public function setLanguageId($languageId)
    {
        $this->languageId = $languageId;

        return $this;
    }

    /**
     * @return int
     */
    public function getLanguageId()
    {
        return $this->languageId;
    }

    /**
     * @param int $activeFilter
     *
     * @return ProductFeed
     */
    public function setActiveFilter($activeFilter)
    {
        $this->activeFilter = $activeFilter;

        return $this;
    }

    /**
     * @return int
     */
    public function getActiveFilter()
    {
        return $this->activeFilter;
    }

    /**
     * @param int $imageFilter
     *
     * @return ProductFeed
     */
    public function setImageFilter($imageFilter)
    {
        $this->imageFilter = $imageFilter;

        return $this;
    }

    /**
     * @return int
     */
    public function getImageFilter()
    {
        return $this->imageFilter;
    }

    /**
     * @param int $stockMinFilter
     *
     * @return ProductFeed
     */
    public function setStockMinFilter($stockMinFilter)
    {
        $this->stockMinFilter = $stockMinFilter;

        return $this;
    }

    /**
     * @return int
     */
    public function getStockMinFilter()
    {
        return $this->stockMinFilter;
    }

    /**
     * @param int $instockFilter
     *
     * @return ProductFeed
     */
    public function setInstockFilter($instockFilter)
    {
        $this->instockFilter = $instockFilter;

        return $this;
    }

    /**
     * @return int
     */
    public function getInstockFilter()
    {
        return $this->instockFilter;
    }

    /**
     * @param float $priceFilter
     *
     * @return ProductFeed
     */
    public function setPriceFilter($priceFilter)
    {
        $this->priceFilter = $priceFilter;

        return $this;
    }

    /**
     * @return float
     */
    public function getPriceFilter()
    {
        return $this->priceFilter;
    }

    /**
     * @param string $ownFilter
     *
     * @return ProductFeed
     */
    public function setOwnFilter($ownFilter)
    {
        $this->ownFilter = $ownFilter;

        return $this;
    }

    /**
     * @return string
     */
    public function getOwnFilter()
    {
        return $this->ownFilter;
    }

    /**
     * @param string $header
     *
     * @return ProductFeed
     */
    public function setHeader($header)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param string $body
     *
     * @return ProductFeed
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $footer
     *
     * @return ProductFeed
     */
    public function setFooter($footer)
    {
        $this->footer = $footer;

        return $this;
    }

    /**
     * @return string
     */
    public function getFooter()
    {
        return $this->footer;
    }

    /**
     * @param int $countFilter
     *
     * @return ProductFeed
     */
    public function setCountFilter($countFilter)
    {
        $this->countFilter = $countFilter;

        return $this;
    }

    /**
     * @return int
     */
    public function getCountFilter()
    {
        return $this->countFilter;
    }

    /**
     * @param int $shopId
     *
     * @return ProductFeed
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;

        return $this;
    }

    /**
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @param int $variantExport
     *
     * @return ProductFeed
     */
    public function setVariantExport($variantExport)
    {
        $this->variantExport = $variantExport;

        return $this;
    }

    /**
     * @return int
     */
    public function getVariantExport()
    {
        return $this->variantExport;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $categories
     *
     * @return ProductFeed
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getSuppliers()
    {
        return $this->suppliers;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $suppliers
     */
    public function setSuppliers($suppliers)
    {
        $this->suppliers = $suppliers;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $articles
     */
    public function setArticles($articles)
    {
        $this->articles = $articles;
    }

    /**
     * @return ProductFeedAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param ProductFeedAttribute|array|null $attribute
     *
     * @return ProductFeed
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, ProductFeedAttribute::class, 'attribute', 'productFeed');
    }

    /**
     * Set cache refreshed datetime
     *
     * @param \DateTimeInterface|string $cacheRefreshed
     *
     * @return ProductFeed
     */
    public function setCacheRefreshed($cacheRefreshed)
    {
        if (!$cacheRefreshed instanceof \DateTimeInterface) {
            $cacheRefreshed = new \DateTime($cacheRefreshed);
        }
        $this->cacheRefreshed = $cacheRefreshed;

        return $this;
    }

    /**
     * Get cache refreshed datetime
     *
     * @return \DateTimeInterface
     */
    public function getCacheRefreshed()
    {
        return $this->cacheRefreshed;
    }

    /**
     * @param bool $dirty
     */
    public function setDirty($dirty)
    {
        $this->dirty = $dirty;
    }

    /**
     * @return bool
     */
    public function isDirty()
    {
        return $this->dirty;
    }

    /**
     * @ORM\PostLoad()
     * @ORM\PreUpdate()
     * @ORM\PrePersist()
     */
    public function sanitizeFilename()
    {
        $this->fileName = basename($this->fileName);
        $extension = strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION));

        if (!empty($extension) && in_array($extension, \Shopware_Controllers_Backend_MediaManager::$fileUploadBlacklist, true)) {
            $this->fileName = str_replace('.' . $extension, '.invalid', strtolower($this->fileName));

            // To prevent PrePersist event
            if ($this->id) {
                Shopware()->Models()->flush($this);
            }
        }
    }
}
