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

namespace Shopware\Models\Order;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Article\Detail as ArticleDetail;
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
 * @ORM\HasLifecycleCallbacks()
 */
class Detail extends ModelEntity
{
    /**
     * @var \Shopware\Models\Order\Order
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Order\Order", inversedBy="details")
     * @ORM\JoinColumn(name="orderID", referencedColumnName="id")
     */
    protected $order;

    /**
     * @var \Shopware\Models\Order\Status
     *
     * @Assert\NotBlank()
     *
     * @var \Shopware\Models\Order\Status
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Order\DetailStatus")
     * @ORM\JoinColumn(name="status", referencedColumnName="id")
     */
    protected $status;

    /**
     * @var \Shopware\Models\Tax\Tax
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Tax\Tax")
     * @ORM\JoinColumn(name="taxID", referencedColumnName="id")
     */
    protected $tax;

    /**
     * INVERSE SIDE
     *
     * @var \Shopware\Models\Attribute\OrderDetail
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\OrderDetail", mappedBy="orderDetail", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * INVERSE SIDE
     *
     * @var \Shopware\Models\Order\Esd
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
     *
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
     *
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
     * @var string
     *
     * @ORM\Column(name="ordernumber", type="string", length=255, nullable=true)
     */
    private $number;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="articleordernumber", type="string", length=255, nullable=false)
     */
    private $articleNumber;

    /**
     * @var float
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="price", type="float", nullable=false)
     */
    private $price;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="quantity", type="integer", nullable=false)
     */
    private $quantity;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
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
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="releasedate", type="date", nullable=true)
     */
    private $releaseDate = null;

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
     * @var string
     *
     * @ORM\Column(name="ean", type="string", length=255, nullable=true)
     */
    private $ean;

    /**
     * @var string
     *
     * @ORM\Column(name="unit", type="string", length=255, nullable=true)
     */
    private $unit;

    /**
     * @var string
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
     * @return string
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

    public function setArticleDetail(ArticleDetail $articleDetail = null)
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
     * @param \DateTimeInterface $releaseDate
     *
     * @return Detail
     */
    public function setReleaseDate($releaseDate)
    {
        $this->releaseDate = $releaseDate;

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
     * @param \Shopware\Models\Order\DetailStatus $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return \Shopware\Models\Tax\Tax|null
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
     * If the position is deleted, the article stock must be increased based on the ordering quantity.
     * The prePersist and preUpdate function call the calculateOrderAmount function to recalculate the
     * order invoice amount, the after remove function can't handle this logic,
     *
     * @ORM\PreRemove()
     */
    public function afterRemove()
    {
        $repository = Shopware()->Models()->getRepository(\Shopware\Models\Article\Detail::class);
        $article = $repository->findOneBy(['number' => $this->articleNumber]);

        // Do not increase instock for canceled orders
        if ($this->getOrder() && $this->getOrder()->getOrderStatus()->getId() === -1) {
            return;
        }

        /*
         * before try to get the article, check if the association field (articleNumber) is not empty
         */
        if (!empty($this->articleNumber) && $article instanceof \Shopware\Models\Article\Detail) {
            $article->setInStock($article->getInStock() + $this->quantity);
            Shopware()->Models()->persist($article);
        }
    }

    /**
     * If an position is added, the order amount has to be recalculated
     *
     * @ORM\PrePersist()
     */
    public function beforeInsert()
    {
    }

    /**
     * If an position is added, the stock of the article will be reduced by the ordered quantity.
     *
     * @ORM\PostPersist()
     */
    public function afterInsert()
    {
        $repository = Shopware()->Models()->getRepository(\Shopware\Models\Article\Detail::class);
        $article = $repository->findOneBy(['number' => $this->articleNumber]);

        /*
         * before try to get the article, check if the association field (articleNumber) is not empty
         */
        if (!empty($this->articleNumber) && $article instanceof \Shopware\Models\Article\Detail) {
            $article->setInStock($article->getInStock() - $this->quantity);
            Shopware()->Models()->persist($article);
            Shopware()->Models()->flush();
        }
        $this->calculateOrderAmount();
    }

    /**
     * If the position article has been changed, the old article stock must be increased based on the (old) ordering quantity.
     * The stock of the new article will be reduced by the (new) ordered quantity.
     *
     * @ORM\PreUpdate()
     */
    public function beforeUpdate()
    {
        //returns a change set for the model, which contains all changed properties with the old and new value.
        $changeSet = Shopware()->Models()->getUnitOfWork()->getEntityChangeSet($this);

        $articleChange = $changeSet['articleNumber'];
        $quantityChange = $changeSet['quantity'];

        //init the articles
        $newArticle = null;
        $oldArticle = null;

        //calculate the difference of the position quantity
        $oldQuantity = empty($quantityChange) ? $this->quantity : $quantityChange[0];
        $newQuantity = empty($quantityChange) ? $this->quantity : $quantityChange[1];
        $quantityDiff = $oldQuantity - $newQuantity;

        $repository = Shopware()->Models()->getRepository(\Shopware\Models\Article\Detail::class);
        $article = $repository->findOneBy(['number' => $this->articleNumber]);

        // If the position article has been changed, the old article stock must be increased based on the (old) ordering quantity.
        // The stock of the new article will be reduced by the (new) ordered quantity.
        if (!empty($articleChange)) {
            /*
             * before try to get the article, check if the association field (articleNumber) is not empty,
             * otherwise the find function will throw an exception
             */
            if (!empty($articleChange[0])) {
                /** @var \Shopware\Models\Article\Detail $oldArticle */
                $oldArticle = $repository->findOneBy(['number' => $articleChange[0]]);
            }

            /*
             * before try to get the article, check if the association field (articleNumber) is not empty,
             * otherwise the find function will throw an exception
             */
            if (!empty($articleChange[1])) {
                /** @var \Shopware\Models\Article\Detail $newArticle */
                $newArticle = $repository->findOneBy(['number' => $articleChange[1]]);
            }

            //is the new articleNumber and valid model identifier?
            if ($newArticle instanceof \Shopware\Models\Article\Detail) {
                $newArticle->setInStock($newArticle->getInStock() - $newQuantity);
                Shopware()->Models()->persist($newArticle);
            }

            //was the old articleNumber and valid model identifier?
            if ($oldArticle instanceof \Shopware\Models\Article\Detail) {
                $oldArticle->setInStock($oldArticle->getInStock() + $oldQuantity);
                Shopware()->Models()->persist($oldArticle);
            }
        } elseif ($article instanceof \Shopware\Models\Article\Detail) {
            $article->setInStock($article->getInStock() + $quantityDiff);
            Shopware()->Models()->persist($article);
        }

        $articleChange = (bool) ($changeSet['articleNumber'][0] != $changeSet['articleNumber'][1]);
        $quantityChange = (bool) ($changeSet['quantity'][0] != $changeSet['quantity'][1]);
        $priceChanged = (bool) ($changeSet['price'][0] != $changeSet['price'][1]);
        $taxChanged = (bool) ($changeSet['taxRate'][0] != $changeSet['taxRate'][1]);

        if ($quantityChange || $articleChange || $priceChanged || $taxChanged) {
            $this->calculateOrderAmount();
        }
    }

    /**
     * @return \Shopware\Models\Attribute\OrderDetail
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\OrderDetail|array|null $attribute
     *
     * @return \Shopware\Models\Order\Detail
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, \Shopware\Models\Attribute\OrderDetail::class, 'attribute', 'orderDetail');
    }

    /**
     * @param \Shopware\Models\Order\Esd $esd
     */
    public function setEsd($esd)
    {
        $this->esd = $esd;
    }

    /**
     * @return \Shopware\Models\Order\Esd
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
     * @param string $ean
     */
    public function setEan($ean)
    {
        $this->ean = $ean;
    }

    /**
     * @return string
     */
    public function getEan()
    {
        return $this->ean;
    }

    /**
     * @param string $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param string $packUnit
     */
    public function setPackUnit($packUnit)
    {
        $this->packUnit = $packUnit;
    }

    /**
     * @return string
     */
    public function getPackUnit()
    {
        return $this->packUnit;
    }

    /**
     * Internal helper function which check if the associated order exist
     * and recalculate the order amount by using the
     * Shopware\Models\Order\Order::calculateInvoiceAmount function.
     */
    private function calculateOrderAmount()
    {
        if ($this->getOrder() instanceof Order) {
            // Recalculates the new amount
            $this->getOrder()->calculateInvoiceAmount();
            Shopware()->Models()->persist($this->getOrder());
        }
    }
}
