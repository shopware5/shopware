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

namespace Shopware\Models\Article\Configurator\Template;

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
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
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
     * @var TemplateAttribute
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Template", mappedBy="template", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * OWNING SIDE
     *
     * @var Unit
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
     * @var int
     *
     * @ORM\Column(name="unit_id", type="integer", nullable=true)
     */
    private $unitId;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Regex("/^[a-zA-Z0-9-_. ]+$/")
     *
     * @ORM\Column(name="order_number", type="string", nullable=false, unique=true)
     */
    private $number = '';

    /**
     * @var string
     *
     * @ORM\Column(name="suppliernumber", type="string", nullable=true)
     */
    private $supplierNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="additionaltext", type="string", nullable=true)
     */
    private $additionalText;

    /**
     * @var int
     *
     * @ORM\Column(name="active", type="integer", nullable=false)
     */
    private $active = false;

    /**
     * @var int
     *
     * @ORM\Column(name="instock", type="integer", nullable=true)
     */
    private $inStock;

    /**
     * @var int
     *
     * @ORM\Column(name="stockmin", type="integer", nullable=true)
     */
    private $stockMin;

    /**
     * @var int
     *
     * @ORM\Column(name="laststock", type="boolean", nullable=false)
     */
    private $lastStock;

    /**
     * @var float
     *
     * @ORM\Column(name="weight", type="decimal", nullable=true, precision=3)
     */
    private $weight;

    /**
     * @var float
     *
     * @ORM\Column(name="width", type="decimal", nullable=true, precision=3)
     */
    private $width;

    /**
     * @var float
     *
     * @ORM\Column(name="length", type="decimal", nullable=true, precision=3)
     */
    private $len;

    /**
     * @var float
     *
     * @ORM\Column(name="height", type="decimal", nullable=true, precision=3)
     */
    private $height;

    /**
     * @var string
     *
     * @ORM\Column(name="ean", type="string", nullable=true)
     */
    private $ean;

    /**
     * @var float
     *
     * @ORM\Column(name="purchaseprice", type="decimal", nullable=false)
     */
    private $purchasePrice = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="minpurchase", type="integer", nullable=true)
     */
    private $minPurchase;

    /**
     * @var int
     *
     * @ORM\Column(name="purchasesteps", type="integer", nullable=true)
     */
    private $purchaseSteps;

    /**
     * @var int
     *
     * @ORM\Column(name="maxpurchase", type="integer", nullable=true)
     */
    private $maxPurchase;

    /**
     * @var float
     *
     * @ORM\Column(name="purchaseunit", type="decimal", nullable=true)
     */
    private $purchaseUnit;

    /**
     * @var float
     *
     * @ORM\Column(name="referenceunit", type="decimal", nullable=true)
     */
    private $referenceUnit;

    /**
     * @var string
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
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="releasedate", type="date", nullable=true)
     */
    private $releaseDate;

    /**
     * @var string
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
     * @return string
     */
    public function getSupplierNumber()
    {
        return $this->supplierNumber;
    }

    /**
     * @param string $additionalText
     *
     * @return Template
     */
    public function setAdditionalText($additionalText)
    {
        $this->additionalText = $additionalText;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdditionalText()
    {
        return $this->additionalText;
    }

    /**
     * @param int $active
     *
     * @return Template
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
     * @return int
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
     * @return int
     */
    public function getStockMin()
    {
        return $this->stockMin;
    }

    /**
     * @param int $lastStock
     */
    public function setLastStock($lastStock)
    {
        $this->lastStock = (int) $lastStock;
    }

    /**
     * Get last stock
     *
     * @return int
     */
    public function getLastStock()
    {
        return $this->lastStock;
    }

    /**
     * @param float $weight
     *
     * @return Template
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return float
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
     * @return Product
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
     * @return float
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param float $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return float
     */
    public function getLen()
    {
        return $this->len;
    }

    /**
     * @param float $length
     */
    public function setLen($length)
    {
        $this->len = $length;
    }

    /**
     * @return float
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param float $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return string
     */
    public function getEan()
    {
        return $this->ean;
    }

    /**
     * @param string $ean
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
     * @param string $shippingTime
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
     * @return string
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
     * @param \DateTimeInterface|string|null $releaseDate
     *
     * @return Template
     */
    public function setReleaseDate($releaseDate = null)
    {
        if ($releaseDate !== null && !($releaseDate instanceof \DateTimeInterface)) {
            $this->releaseDate = new \DateTime($releaseDate);
        } else {
            $this->releaseDate = $releaseDate;
        }

        return $this;
    }

    /**
     * @return \DateTimeInterface
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
     * @return int
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
     * @return int
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
     * @return int
     */
    public function getMaxPurchase()
    {
        return $this->maxPurchase;
    }

    /**
     * @param float $purchaseUnit
     *
     * @return Template
     */
    public function setPurchaseUnit($purchaseUnit)
    {
        $this->purchaseUnit = $purchaseUnit;

        return $this;
    }

    /**
     * @return float
     */
    public function getPurchaseUnit()
    {
        return $this->purchaseUnit;
    }

    /**
     * @param float $referenceUnit
     *
     * @return Template
     */
    public function setReferenceUnit($referenceUnit)
    {
        $this->referenceUnit = $referenceUnit;

        return $this;
    }

    /**
     * @return float
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
     * @return string
     */
    public function getPackUnit()
    {
        return $this->packUnit;
    }

    /**
     * OWNING SIDE
     * of the association between articles and unit
     *
     * @return Unit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param Unit|array|null $unit
     *
     * @return Template
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
    }
}
