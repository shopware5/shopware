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
use Shopware\Models\Attribute\OrderBasket as OrderBasketAttribute;

/**
 * @ORM\Table(name="s_order_basket")
 * @ORM\Entity()
 */
class Basket extends ModelEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="userID", type="integer", nullable=true)
     */
    protected $customerId;

    /**
     * @var int
     *
     * @ORM\Column(name="articleID", type="integer", nullable=true)
     */
    protected $articleId;

    /**
     * @var string
     *
     * @ORM\Column(name="ordernumber", type="string", length=255, nullable=true)
     */
    protected $orderNumber;

    /**
     * @var float
     *
     * @ORM\Column(name="tax_rate", type="float", nullable=false)
     */
    protected $taxRate = 0;

    /**
     * INVERSE SIDE
     *
     * @var OrderBasketAttribute
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\OrderBasket", mappedBy="orderBasket", orphanRemoval=true, cascade={"persist"})
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
     * @ORM\Column(name="sessionID", type="string", length=70, nullable=false)
     */
    private $sessionId;

    /**
     * @var string
     *
     * @ORM\Column(name="partnerID", type="string", length=45, nullable=true)
     */
    private $partnerId;

    /**
     * @var string
     *
     * @ORM\Column(name="articlename", type="string", length=255, nullable=false)
     */
    private $articleName = '';

    /**
     * @var int
     *
     * @ORM\Column(name="shippingfree", type="integer", nullable=false)
     */
    private $shippingFree = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer", nullable=false)
     */
    private $quantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=false)
     */
    private $price = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="netprice", type="float", nullable=false)
     */
    private $netPrice = 0;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="datum", type="datetime", nullable=false)
     */
    private $date;

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
     * @ORM\Column(name="lastviewport", type="string", length=255, nullable=false)
     */
    private $lastViewPort = '';

    /**
     * @var string
     *
     * @ORM\Column(name="useragent", type="string", length=255, nullable=false)
     */
    private $userAgent = '';

    /**
     * @var string
     *
     * @ORM\Column(name="config", type="text", nullable=false)
     */
    private $config = '';

    /**
     * @var float
     *
     * @ORM\Column(name="currencyFactor", type="float", nullable=false)
     */
    private $currencyFactor = 1;

    /**
     * @return OrderBasketAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param OrderBasketAttribute|array|null $attribute
     *
     * @return Basket
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, OrderBasketAttribute::class, 'attribute', 'orderBasket');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $articleName
     */
    public function setArticleName($articleName)
    {
        $this->articleName = $articleName;
    }

    /**
     * @return string
     */
    public function getArticleName()
    {
        return $this->articleName;
    }

    /**
     * @param string $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param float $currencyFactor
     */
    public function setCurrencyFactor($currencyFactor)
    {
        $this->currencyFactor = $currencyFactor;
    }

    /**
     * @return float
     */
    public function getCurrencyFactor()
    {
        return $this->currencyFactor;
    }

    /**
     * @param int $esdArticle
     */
    public function setEsdArticle($esdArticle)
    {
        $this->esdArticle = $esdArticle;
    }

    /**
     * @return int
     */
    public function getEsdArticle()
    {
        return $this->esdArticle;
    }

    /**
     * @param string $lastViewPort
     */
    public function setLastViewPort($lastViewPort)
    {
        $this->lastViewPort = $lastViewPort;
    }

    /**
     * @return string
     */
    public function getLastViewPort()
    {
        return $this->lastViewPort;
    }

    /**
     * @param int $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $partnerId
     */
    public function setPartnerId($partnerId)
    {
        $this->partnerId = $partnerId;
    }

    /**
     * @return string
     */
    public function getPartnerId()
    {
        return $this->partnerId;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
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
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param string $sessionId
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param int $shippingFree
     */
    public function setShippingFree($shippingFree)
    {
        $this->shippingFree = $shippingFree;
    }

    /**
     * @return int
     */
    public function getShippingFree()
    {
        return $this->shippingFree;
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTimeInterface $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return float
     */
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * @param float $netPrice
     */
    public function setNetPrice($netPrice)
    {
        $this->netPrice = $netPrice;
    }

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @param int $articleId
     */
    public function setArticleId($articleId)
    {
        $this->articleId = $articleId;
    }

    /**
     * @return int
     */
    public function getArticleId()
    {
        return $this->articleId;
    }

    /**
     * @param int $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @return float
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * @param float $taxRate
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;
    }
}
