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

namespace Shopware\Models\Article\Configurator\Template;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Article\Article as Product;
use Shopware\Models\Article\Unit;
use Shopware\Models\Attribute\Template as TemplateAttribute;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="s_article_configurator_templates")
 */
class Template extends ModelEntity
{
    /**
     * OWNING SIDE
     *
     * @var Product
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Article", inversedBy="configuratorTemplate")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id", nullable=false)
     */
    protected $article;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<\Shopware\Models\Article\Configurator\Template\Price>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Configurator\Template\Price", mappedBy="template", orphanRemoval=true, cascade={"persist"})
     */
    protected $prices;

    /**
     * INVERSE SIDE
     *
     * @var TemplateAttribute|null
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Template", mappedBy="template", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * OWNING SIDE
     *
     * @var Unit|null
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Unit", inversedBy="articles", cascade={"persist"})
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id")
     */
    protected $unit;

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
     * @ORM\Column(name="article_id", type="integer", nullable=false)
     */
    private $articleId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="unit_id", type="integer", nullable=true)
     */
    private $unitId;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Regex("/^[a-zA-Z0-9-_. ]+$/")
     * @ORM\Column(name="order_number", type="string", nullable=false, unique=true)
     */
    private $number = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="suppliernumber", type="string", nullable=true)
     */
    private $supplierNumber;

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
     * @var int|null
     *
     * @ORM\Column(name="instock", type="integer", nullable=true)
     */
    private $inStock;

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
    private $lastStock;

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
     * @var int|null
     *
     * @ORM\Column(name="minpurchase", type="integer", nullable=true)
     */
    private $minPurchase;

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
     * @return Template
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $supplierNumber
     *
     * @return Template
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
     * @param string|null $additionalText
     *
     * @return Template
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
     * @return Template
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
     * @return Template
     */
    public function setInStock($inStock)
    {
        $this->inStock = $inStock;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getInStock()
    {
        return $this->inStock;
    }

    /**
     * @param int $stockMin
     *
     * @return Template
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
     */
    public function setLastStock($lastStock)
    {
        $this->lastStock = $lastStock;
    }

    /**
     * Get last stock
     *
     * @return bool
     */
    public function getLastStock()
    {
        return $this->lastStock;
    }

    /**
     * @param string|null $weight
     *
     * @return Template
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
     * @return Template
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
     * @return Product|null
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @param Product $article
     *
     * @return Template
     */
    public function setArticle($article)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * @return TemplateAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param TemplateAttribute|array|null $attribute
     *
     * @return Template
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, TemplateAttribute::class, 'attribute', 'template');
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @param Price[]|null $prices
     *
     * @return Template
     */
    public function setPrices($prices)
    {
        return $this->setOneToMany($prices, Price::class, 'prices', 'template');
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
     * @return Template
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
     * @return Template
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
     * @return Template
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
     * @return Template
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
     * @return Template
     */
    public function setMinPurchase($minPurchase)
    {
        $this->minPurchase = $minPurchase;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMinPurchase()
    {
        return $this->minPurchase;
    }

    /**
     * @param int $purchaseSteps
     *
     * @return Template
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
     * @return Template
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
     * @return Template
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
     * @return Template
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
     * @return Template
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
     * @param Unit|array $unit
     *
     * @return Template
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
    }
}
