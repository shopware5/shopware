<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Models\Article;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\DBAL\Constraints as ShopwareAssert;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Article\Configurator\Option;
use Shopware\Models\Attribute\Article as ProductAttribute;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_articles_details")
 */
class Detail extends ModelEntity
{
    /**
     * OWNING SIDE
     *
     * @var Article
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Article", inversedBy="details")
     * @ORM\JoinColumn(name="articleID", referencedColumnName="id", nullable=false)
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $article;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<array-key, Price>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Price", mappedBy="detail", orphanRemoval=true, cascade={"persist"})
     */
    protected $prices;

    /**
     * INVERSE SIDE
     *
     * @var ProductAttribute|null
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Article", mappedBy="articleDetail", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * OWNING SIDE
     *
     * @var Unit|null
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Unit", inversedBy="articles", cascade={"persist"})
     * @ORM\JoinColumn(name="unitID", referencedColumnName="id")
     */
    protected $unit;

    /**
     * OWNING SIDE
     *
     * @var ArrayCollection<array-key, Option>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Configurator\Option", inversedBy="articles")
     * @ORM\JoinTable(name="s_article_configurator_option_relations",
     *     joinColumns={
     *         @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="option_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $configuratorOptions;

    /**
     * INVERSE SIDE
     *
     * @var Esd|null
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Esd", mappedBy="articleDetail", orphanRemoval=true, cascade={"persist"})
     */
    protected $esd;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<Notification>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Notification", mappedBy="articleDetail")
     */
    protected $notifications;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<array-key, Image>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Image", mappedBy="articleDetail", orphanRemoval=true, cascade={"persist"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $images;

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
     * @ORM\Column(name="articleID", type="integer", nullable=false)
     */
    private $articleId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="unitID", type="integer", nullable=true)
     */
    private $unitId;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ShopwareAssert\OrderNumber
     * @ORM\Column(name="ordernumber", type="string", nullable=false, unique=true)
     */
    private $number = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="suppliernumber", type="string", nullable=true)
     */
    private $supplierNumber;

    /**
     * @var int
     *
     * @ORM\Column(name="kind", type="integer", nullable=false)
     */
    private $kind = 2;

    /**
     * @var string|null
     *
     * @ORM\Column(name="additionaltext", type="string", nullable=true)
     */
    private $additionalText;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = false;

    /**
     * @var int
     *
     * @ORM\Column(name="instock", type="integer", nullable=false)
     */
    private $inStock = 0;

    /**
     * @var int|null
     *
     * @ORM\Column(name="stockmin", type="integer", nullable=true)
     */
    private $stockMin;

    /**
     * @var bool
     *
     * @ORM\Column(name="laststock", type="boolean", nullable=false)
     */
    private $lastStock = false;

    /**
     * @var string|null
     *
     * @ORM\Column(name="weight", type="decimal", precision=10, scale=3, nullable=true)
     */
    private $weight;

    /**
     * @var string|null
     *
     * @ORM\Column(name="width", type="decimal", precision=10, scale=3, nullable=true)
     */
    private $width;

    /**
     * @var string|null
     *
     * @ORM\Column(name="length", type="decimal", precision=10, scale=3, nullable=true)
     */
    private $len;

    /**
     * @var string|null
     *
     * @ORM\Column(name="height", type="decimal", precision=10, scale=3, nullable=true)
     */
    private $height;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ean", type="string", nullable=true)
     */
    private $ean;

    /**
     * @var float
     *
     * @ORM\Column(name="purchaseprice", type="float", nullable=false)
     */
    private $purchasePrice = 0.0;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="minpurchase", type="integer", nullable=false)
     */
    private $minPurchase = 1;

    /**
     * @var int|null
     *
     * @ORM\Column(name="purchasesteps", type="integer", nullable=true)
     */
    private $purchaseSteps;

    /**
     * @var int|null
     *
     * @ORM\Column(name="maxpurchase", type="integer", nullable=true)
     */
    private $maxPurchase;

    /**
     * @var string|null
     *
     * @ORM\Column(name="purchaseunit", type="decimal", precision=11, scale=4, nullable=true)
     */
    private $purchaseUnit;

    /**
     * @var string|null
     *
     * @ORM\Column(name="referenceunit", type="decimal", precision=10, scale=3, nullable=true)
     */
    private $referenceUnit;

    /**
     * @var string|null
     *
     * @ORM\Column(name="packunit", type="text", nullable=true)
     */
    private $packUnit;

    /**
     * @var bool
     *
     * @ORM\Column(name="shippingfree", type="boolean", nullable=false)
     */
    private $shippingFree = false;

    /**
     * @var DateTimeInterface|null
     *
     * @ORM\Column(name="releasedate", type="date", nullable=true)
     */
    private $releaseDate;

    /**
     * @var string|null
     *
     * @ORM\Column(name="shippingtime", type="string", length=11, nullable=true)
     */
    private $shippingTime;

    public function __construct()
    {
        $this->prices = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->configuratorOptions = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->attribute = new ProductAttribute();
        $this->attribute->setArticleDetail($this);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $number
     *
     * @return Detail
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $supplierNumber
     *
     * @return Detail
     */
    public function setSupplierNumber($supplierNumber)
    {
        $this->supplierNumber = $supplierNumber;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSupplierNumber()
    {
        return $this->supplierNumber;
    }

    /**
     * @param int $kind
     *
     * @return Detail
     */
    public function setKind($kind)
    {
        $this->kind = $kind;

        return $this;
    }

    /**
     * @return int
     */
    public function getKind()
    {
        return $this->kind;
    }

    /**
     * @param string $additionalText
     *
     * @return Detail
     */
    public function setAdditionalText($additionalText)
    {
        $this->additionalText = $additionalText;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAdditionalText()
    {
        return $this->additionalText;
    }

    /**
     * @param bool $active
     *
     * @return Detail
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
     * @param int $inStock
     *
     * @return Detail
     */
    public function setInStock($inStock)
    {
        $this->inStock = (int) $inStock;

        return $this;
    }

    /**
     * @return int
     */
    public function getInStock()
    {
        return $this->inStock;
    }

    /**
     * @param int $stockMin
     *
     * @return Detail
     */
    public function setStockMin($stockMin)
    {
        $this->stockMin = $stockMin;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getStockMin()
    {
        return $this->stockMin;
    }

    /**
     * @param bool $lastStock
     *
     * @return Detail
     */
    public function setLastStock($lastStock)
    {
        $this->lastStock = $lastStock;

        return $this;
    }

    /**
     * @return bool
     */
    public function getLastStock()
    {
        return $this->lastStock;
    }

    /**
     * @param string|null $weight
     *
     * @return Detail
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param int $position
     *
     * @return Detail
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getArticleId()
    {
        return $this->articleId;
    }

    /**
     * @return Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @return Detail
     */
    public function setArticle(Article $article)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * @return ProductAttribute|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param ProductAttribute|array|null $attribute
     *
     * @return Detail
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, ProductAttribute::class, 'attribute', 'articleDetail');
    }

    /**
     * @return ArrayCollection<array-key, Price>
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @param ArrayCollection<array-key, Price>|array<Price>|null $prices
     *
     * @return Detail
     */
    public function setPrices($prices)
    {
        return $this->setOneToMany($prices, Price::class, 'prices', 'detail');
    }

    /**
     * @return string|null
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param string|null $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return string|null
     */
    public function getLen()
    {
        return $this->len;
    }

    /**
     * @param string|null $length
     */
    public function setLen($length)
    {
        $this->len = $length;
    }

    /**
     * @return string|null
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param string|null $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return string|null
     */
    public function getEan()
    {
        return $this->ean;
    }

    /**
     * @param string|null $ean
     */
    public function setEan($ean)
    {
        $this->ean = $ean;
    }

    /**
     * Set purchase price
     *
     * @param float $purchasePrice
     *
     * @return Detail
     */
    public function setPurchasePrice($purchasePrice)
    {
        $this->purchasePrice = $purchasePrice;

        return $this;
    }

    /**
     * Get purchase price
     *
     * @return float
     */
    public function getPurchasePrice()
    {
        return $this->purchasePrice;
    }

    /**
     * Set shipping time
     *
     * @param string|null $shippingTime
     *
     * @return Detail
     */
    public function setShippingTime($shippingTime)
    {
        $this->shippingTime = $shippingTime;

        return $this;
    }

    /**
     * Get shipping time
     *
     * @return string|null
     */
    public function getShippingTime()
    {
        return $this->shippingTime;
    }

    /**
     * @param bool $shippingFree
     *
     * @return Detail
     */
    public function setShippingFree($shippingFree)
    {
        $this->shippingFree = $shippingFree;

        return $this;
    }

    /**
     * @return bool
     */
    public function getShippingFree()
    {
        return $this->shippingFree;
    }

    /**
     * @param DateTimeInterface|string|null $releaseDate
     *
     * @return Detail
     */
    public function setReleaseDate($releaseDate = null)
    {
        if ($releaseDate !== null && !($releaseDate instanceof DateTimeInterface)) {
            $this->releaseDate = new DateTime($releaseDate);
        } else {
            $this->releaseDate = $releaseDate;
        }

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getReleaseDate()
    {
        return $this->releaseDate;
    }

    /**
     * @param int $minPurchase
     *
     * @return Detail
     */
    public function setMinPurchase($minPurchase)
    {
        if ($minPurchase <= 0) {
            $minPurchase = 1;
        }

        $this->minPurchase = $minPurchase;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinPurchase()
    {
        return $this->minPurchase;
    }

    /**
     * @param int $purchaseSteps
     *
     * @return Detail
     */
    public function setPurchaseSteps($purchaseSteps)
    {
        $this->purchaseSteps = $purchaseSteps;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPurchaseSteps()
    {
        return $this->purchaseSteps;
    }

    /**
     * @param int $maxPurchase
     *
     * @return Detail
     */
    public function setMaxPurchase($maxPurchase)
    {
        $this->maxPurchase = $maxPurchase;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxPurchase()
    {
        return $this->maxPurchase;
    }

    /**
     * @param string|null $purchaseUnit
     *
     * @return Detail
     */
    public function setPurchaseUnit($purchaseUnit)
    {
        $this->purchaseUnit = $purchaseUnit;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPurchaseUnit()
    {
        return $this->purchaseUnit;
    }

    /**
     * @param string|null $referenceUnit
     *
     * @return Detail
     */
    public function setReferenceUnit($referenceUnit)
    {
        $this->referenceUnit = $referenceUnit;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getReferenceUnit()
    {
        return $this->referenceUnit;
    }

    /**
     * @param string $packUnit
     *
     * @return Detail
     */
    public function setPackUnit($packUnit)
    {
        $this->packUnit = $packUnit;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPackUnit()
    {
        return $this->packUnit;
    }

    /**
     * OWNING SIDE
     * of the association between articles and unit
     *
     * @return Unit|null
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param Unit|array|null $unit
     *
     * @return Detail
     */
    public function setUnit($unit)
    {
        return $this->setManyToOne($unit, Unit::class, 'unit');
    }

    /**
     * @return ArrayCollection<array-key, Option>
     */
    public function getConfiguratorOptions()
    {
        return $this->configuratorOptions;
    }

    /**
     * @param ArrayCollection<array-key, Option> $configuratorOptions
     */
    public function setConfiguratorOptions($configuratorOptions)
    {
        $this->configuratorOptions = $configuratorOptions;
    }

    /**
     * @param Esd|null $esd
     */
    public function setEsd($esd)
    {
        $this->esd = $esd;
    }

    /**
     * @return Esd|null
     */
    public function getEsd()
    {
        return $this->esd;
    }

    /**
     * @return ArrayCollection<array-key, Image>
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param ArrayCollection<array-key, Image>|array<Image>|null $images
     *
     * @return Detail
     */
    public function setImages($images)
    {
        return $this->setOneToMany($images, Image::class, 'images', 'articleDetail');
    }
}
