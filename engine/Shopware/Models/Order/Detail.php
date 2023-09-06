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

namespace Shopware\Models\Order;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Article\Detail as ArticleDetail;
use Shopware\Models\Attribute\OrderDetail;
use Shopware\Models\Tax\Tax;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Shopware order detail model represents a single detail data of an order .
 * <br>
 * The Shopware order detail model represents a row of the order_details table.
 * The s_order_details table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 *   - KEY `email` (`email`)
 *   - KEY `orderID` (`orderID`)
 *   - KEY `articleID` (`articleID`)
 *   - KEY `ordernumber` (`ordernumber`)
 *   - KEY `articleordernumber` (`articleordernumber`)
 * </code>
 *
 * @ORM\Table(name="s_order_details")
 * @ORM\Entity()
 */
class Detail extends ModelEntity
{
    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Order\Order", inversedBy="details")
     * @ORM\JoinColumn(name="orderID", referencedColumnName="id", nullable=false)
     */
    protected $order;

    /**
     * @var Status
     *
     * @Assert\NotBlank()
     *
     * @var Status
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Order\DetailStatus")
     * @ORM\JoinColumn(name="status", referencedColumnName="id", nullable=false)
     */
    protected $status;

    /**
     * @var Tax|null
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Tax\Tax")
     * @ORM\JoinColumn(name="taxID", referencedColumnName="id", nullable=true)
     */
    protected $tax;

    /**
     * INVERSE SIDE
     *
     * @var OrderDetail|null
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\OrderDetail", mappedBy="orderDetail", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * INVERSE SIDE
     *
     * @var Esd|null
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Order\Esd", mappedBy="orderDetail")
     */
    protected $esd;

    /**
     * @var ArticleDetail|null
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Detail")
     * @ORM\JoinColumn(name="articleDetailID", referencedColumnName="id")
     */
    protected $articleDetail;

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
     * @ORM\Column(name="orderID", type="integer", nullable=false)
     */
    private $orderId;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="articleID", type="integer", nullable=false)
     */
    private $articleId;

    /**
     * @var int
     *
     * @ORM\Column(name="taxID", type="integer", nullable=false)
     */
    private $taxId;

    /**
     * @var float
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="tax_rate", type="float", nullable=false)
     */
    private $taxRate;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $statusId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="articleDetailID", type="integer", nullable=true)
     */
    private $articleDetailID;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ordernumber", type="string", length=255, nullable=true)
     */
    private $number;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="articleordernumber", type="string", length=255, nullable=false)
     */
    private $articleNumber;

    /**
     * @var float
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="price", type="float", nullable=false)
     */
    private $price;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="quantity", type="integer", nullable=false)
     */
    private $quantity;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $articleName;

    /**
     * @var int
     *
     * @ORM\Column(name="shipped", type="integer", nullable=false)
     */
    private $shipped = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="shippedgroup", type="integer", nullable=false)
     */
    private $shippedGroup = 0;

    /**
     * @var DateTimeInterface|null
     *
     * @ORM\Column(name="releasedate", type="date", nullable=true)
     */
    private $releaseDate;

    /**
     * @var int
     *
     * @ORM\Column(name="modus", type="integer", nullable=false)
     */
    private $mode = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="esdarticle", type="integer", nullable=false)
     */
    private $esdArticle = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="config", type="text", nullable=false)
     */
    private $config = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="ean", type="string", length=255, nullable=true)
     */
    private $ean;

    /**
     * @var string|null
     *
     * @ORM\Column(name="unit", type="string", length=255, nullable=true)
     */
    private $unit;

    /**
     * @var string|null
     *
     * @ORM\Column(name="pack_unit", type="string", length=255, nullable=true)
     */
    private $packUnit;

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
     * @param int $articleId
     *
     * @return Detail
     */
    public function setArticleId($articleId)
    {
        $this->articleId = $articleId;

        return $this;
    }

    /**
     * @return int
     */
    public function getArticleId()
    {
        return $this->articleId;
    }

    /**
     * @param float $price
     *
     * @return Detail
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param int $quantity
     *
     * @return Detail
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param string $articleName
     *
     * @return Detail
     */
    public function setArticleName($articleName)
    {
        $this->articleName = $articleName;

        return $this;
    }

    /**
     * @return string
     */
    public function getArticleName()
    {
        return $this->articleName;
    }

    /**
     * @return ArticleDetail|null
     */
    public function getArticleDetail()
    {
        return $this->articleDetail;
    }

    public function setArticleDetail(?ArticleDetail $articleDetail = null)
    {
        $this->articleDetail = $articleDetail;
    }

    /**
     * @param int $shipped
     *
     * @return Detail
     */
    public function setShipped($shipped)
    {
        $this->shipped = $shipped;

        return $this;
    }

    /**
     * @return int
     */
    public function getShipped()
    {
        return $this->shipped;
    }

    /**
     * @param int $shippedGroup
     *
     * @return Detail
     */
    public function setShippedGroup($shippedGroup)
    {
        $this->shippedGroup = $shippedGroup;

        return $this;
    }

    /**
     * @return int
     */
    public function getShippedGroup()
    {
        return $this->shippedGroup;
    }

    /**
     * @param DateTimeInterface|null $releaseDate
     *
     * @return Detail
     */
    public function setReleaseDate($releaseDate)
    {
        $this->releaseDate = $releaseDate;

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
     * @param int $mode
     *
     * @return Detail
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
     * @param int $esdArticle
     *
     * @return Detail
     */
    public function setEsdArticle($esdArticle)
    {
        $this->esdArticle = $esdArticle;

        return $this;
    }

    /**
     * @return int
     */
    public function getEsdArticle()
    {
        return $this->esdArticle;
    }

    /**
     * @param string $config
     *
     * @return Detail
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return string
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return Order|null
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param DetailStatus $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return Tax|null
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param Tax|null $tax
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    }

    /**
     * @return OrderDetail|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param OrderDetail|array|null $attribute
     *
     * @return Detail
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, OrderDetail::class, 'attribute', 'orderDetail');
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
     * @param string $articleNumber
     */
    public function setArticleNumber($articleNumber)
    {
        $this->articleNumber = $articleNumber;
    }

    /**
     * @return string
     */
    public function getArticleNumber()
    {
        return $this->articleNumber;
    }

    /**
     * @param float $taxRate
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;
    }

    /**
     * @return float
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * @param string|null $ean
     */
    public function setEan($ean)
    {
        $this->ean = $ean;
    }

    /**
     * @return string|null
     */
    public function getEan()
    {
        return $this->ean;
    }

    /**
     * @param string|null $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return string|null
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param string|null $packUnit
     */
    public function setPackUnit($packUnit)
    {
        $this->packUnit = $packUnit;
    }

    /**
     * @return string|null
     */
    public function getPackUnit()
    {
        return $this->packUnit;
    }
}
