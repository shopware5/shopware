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

namespace   Shopware\Models\Order;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Shopware order model represents a single order in your shop.
 * It contains all data about the order head data and has association to the
 * assigned user, order status, dispatch method, ... . The order
 * will be created over the store front and will displayed in the order backend module.
 *
 * The order model has the following associations:
 * <code>
 *   - Details      =>  Shopware\Models\Order\Detail           [s_order_details]       bi-directional
 *   - Customer     =>  Shopware\Models\Customer\Customer      [s_user]                bi-directional
 *   - History      =>  Shopware\Models\Order\History          [s_order_history]       bi-directional
 *   - Payment      =>  Shopware\Models\Payment\Payment        [core_paymentmeans]     bi-directional
 *   - Dispatch     =>  Shopware\Models\Dispatch\Dispatch      [premium_dispatch]      uni-directional
 *   - Payment Status =>  Shopware\Models\Order\Status         [core_states]           uni-directional
 *   - Order Status  =>  Shopware\Models\Order\Status          [core_states]           uni-directional
 *   - Shop         => Shopware\Models\Shop\Shop               [core_multilanguage]    uni-directional
 *   - Shipping    => Shopware\Models\Order\Shipping           [order_shippingaddress] bi-directional
 *   - Billing     => Shopware\Models\Order\Billing            [order_billingaddress]  bi-directional
 *   - Documents   => Shopware\Models\Order\Document\Document  [order_documents]       bi-directional
 * </code>
 * The s_order table has the follows indices:
 * <code>
 *    - PRIMARY KEY (`id`),
 *    - KEY `partnerID` (`partnerID`),
 *    - KEY `userID` (`userID`),
 *    - KEY `ordertime` (`ordertime`),
 *    - KEY `cleared` (`cleared`),
 *    - KEY `status` (`status`),
 *    - KEY `paymentID` (`paymentID`),
 *    - KEY `temporaryID` (`temporaryID`),
 *    - KEY `ordernumber` (`ordernumber`),
 *    - KEY `transactionID` (`transactionID`)
 * </code>
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_order")
 * @ORM\HasLifecycleCallbacks
 */
class Order extends ModelEntity
{
    /**
     * Unique identifier field.
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Contains the alphanumeric order number. If the
     * @var string $number
     * @ORM\Column(name="ordernumber", type="string", length=255, nullable=true)
     */
    private $number;

    /**
     * @var integer $customerId
     *
     * @ORM\Column(name="userID", type="integer", nullable=false)
     */
    private $customerId;

    /**
     * @var integer $status
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status;

    /**
     * @var integer $cleared
     *
     * @ORM\Column(name="cleared", type="integer", nullable=false)
     */
    private $cleared;

    /**
     * @var integer $paymentId
     *
     * @ORM\Column(name="paymentID", type="integer", nullable=false)
     */
    private $paymentId;

    /**
     * @var string $dispatchId
     *
     * @ORM\Column(name="dispatchID", type="integer", nullable=true)
     */
    private $dispatchId;

    /**
     * @var string $partnerId
     *
     * @ORM\Column(name="partnerID", type="string", length=255, nullable=false)
     */
    private $partnerId;

    /**
     * @var integer $shopId
     *
     * @ORM\Column(name="subshopID", type="integer", nullable=false)
     */
    private $shopId;

    /**
     * @Assert\NotBlank
     *
     * @var float $invoiceAmount
     *
     * @ORM\Column(name="invoice_amount", type="float", nullable=false)
     */
    private $invoiceAmount;

    /**
     * @Assert\NotBlank
     *
     * @var float $invoiceAmountNet
     *
     * @ORM\Column(name="invoice_amount_net", type="float", nullable=false)
     */
    private $invoiceAmountNet;

    /**
     * @Assert\NotBlank
     *
     * @var float $invoiceShipping
     *
     * @ORM\Column(name="invoice_shipping", type="float", nullable=false)
     */
    private $invoiceShipping;

    /**
     * @Assert\NotBlank
     *
     * @var float $invoiceShippingNet
     *
     * @ORM\Column(name="invoice_shipping_net", type="float", nullable=false)
     */
    private $invoiceShippingNet;

    /**
     * @var \DateTime $orderTime
     *
     * @ORM\Column(name="ordertime", type="datetime", nullable=false)
     */
    private $orderTime = null;

    /**
     * @var string $transactionId
     *
     * @ORM\Column(name="transactionID", type="string", length=255, nullable=false)
     */
    private $transactionId;

    /**
     * @var string $comment
     *
     * @ORM\Column(name="comment", type="text", nullable=false)
     */
    private $comment;

    /**
     * @var string $customerComment
     *
     * @ORM\Column(name="customercomment", type="text", nullable=false)
     */
    private $customerComment;

    /**
     * @var string $internalComment
     *
     * @ORM\Column(name="internalcomment", type="text", nullable=false)
     */
    private $internalComment;

    /**
     * @Assert\NotBlank
     *
     * @var integer $net
     *
     * @ORM\Column(name="net", type="integer", nullable=false)
     */
    private $net;

    /**
     * @Assert\NotBlank
     *
     * @var integer $taxFree
     *
     * @ORM\Column(name="taxfree", type="integer", nullable=false)
     */
    private $taxFree;

    /**
     * @var string $temporaryId
     *
     * @ORM\Column(name="temporaryID", type="string", length=255, nullable=false)
     */
    private $temporaryId;

    /**
     * @var string $referer
     *
     * @ORM\Column(name="referer", type="text", nullable=false)
     */
    private $referer;

    /**
     * @var \DateTime $clearedDate
     *
     * @ORM\Column(name="cleareddate", type="datetime", nullable=true)
     */
    private $clearedDate = null;

    /**
     * @var string $trackingCode
     *
     * @ORM\Column(name="trackingcode", type="string", length=255, nullable=false)
     */
    private $trackingCode;

    /**
     * @Assert\NotBlank
     *
     * @var string $languageIso
     * @ORM\Column(name="language", type="string", length=10, nullable=false)
     */
    private $languageIso;

    /**
     * OWNING SIDE
     *
     * Used for the language subshop association
     * @var \Shopware\Models\Shop\Shop
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="language", referencedColumnName="id")
     */
    private $languageSubShop;

    /**
     * @Assert\NotBlank
     *
     * @var string $currency
     *
     * @ORM\Column(name="currency", type="string", length=5, nullable=false)
     */
    private $currency;

    /**
     * @Assert\NotBlank
     *
     * @var float $currencyFactor
     *
     * @ORM\Column(name="currencyfactor", type="float", nullable=false)
     */
    private $currencyFactor;

    /**
     * @Assert\NotBlank
     *
     * @var string $remoteAddress
     *
     * @ORM\Column(name="remote_addr", type="string", length=255, nullable=true)
     */
    private $remoteAddress;

    /**
     * @var string $deviceType
     *
     * @ORM\Column(name="deviceType", type="string", length=50, nullable=true)
     */
    private $deviceType = 'desktop';

    /**
     * @var \Shopware\Models\Customer\Customer
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Customer\Customer", inversedBy="orders")
     * @ORM\JoinColumn(name="userID", referencedColumnName="id")
     */
    protected $customer;

    /**
     * @Assert\NotBlank
     *
     * @var \Shopware\Models\Payment\Payment
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Payment\Payment")
     * @ORM\JoinColumn(name="paymentID", referencedColumnName="id")
     */
    protected $payment;

    /**
     * @Assert\NotBlank
     *
     * @var \Shopware\Models\Dispatch\Dispatch
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Dispatch\Dispatch")
     * @ORM\JoinColumn(name="dispatchID", referencedColumnName="id")
     */
    protected $dispatch;

    /**
     * The shop property is the owning side of the association between order and shop.
     * The association is joined over the order userID field and the id field of the shop.
     *
     * @Assert\NotBlank
     *
     * @var \Shopware\Models\Shop\Shop
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="subshopID", referencedColumnName="id")
     */
    protected $shop;

    /**
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Partner\Partner", inversedBy="orders")
     * @ORM\JoinColumn(name="partnerID", referencedColumnName="idcode")
     * @var \Shopware\Models\Partner\Partner
     */
    protected $partner;

    /**
     * INVERSE SIDE
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Order", mappedBy="order", orphanRemoval=true, cascade={"persist"})
     * @var \Shopware\Models\Attribute\Order
     */
    protected $attribute;

    /**
     * @Assert\NotBlank
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Order\Status")
     * @ORM\JoinColumn(name="cleared", referencedColumnName="id")
     * @var \Shopware\Models\Order\Status
     */
    protected $paymentStatus;

    /**
     * @Assert\NotBlank
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Order\Status")
     * @ORM\JoinColumn(name="status", referencedColumnName="id")
     * @var \Shopware\Models\Order\Status
     */
    protected $orderStatus;

    /**
     * INVERSE SIDE
     * @ORM\OneToMany(targetEntity="Shopware\Models\Order\Detail", mappedBy="order", orphanRemoval=true, cascade={"persist"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $details;

    /**
     * INVERSE SIDE
     * The billing property is the inverse side of the association between order and billing.
     * The association is joined over the billing orderID field and the id field of the order
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Order\Billing", mappedBy="order", orphanRemoval=true, cascade={"persist"})
     * @var \Shopware\Models\Order\Billing
     */
    protected $billing;

    /**
     * INVERSE SIDE
     * The shipping property is the inverse side of the association between order and shipping.
     * The association is joined over the shipping orderID field and the id field of the order
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Order\Shipping", mappedBy="order", orphanRemoval=true, cascade={"persist"})
     * @var \Shopware\Models\Order\Shipping
     */
    protected $shipping;

    /**
     * INVERSE SIDE
     * @ORM\OneToMany(targetEntity="Shopware\Models\Order\Document\Document", mappedBy="order", orphanRemoval=true, cascade={"persist"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $documents;

    /**
     * @ORM\OneToMany(targetEntity="\Shopware\Models\Order\History", mappedBy="order", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="orderID")
     * @var \Doctrine\Common\Collections\ArrayCollection $history
     */
    protected $history;

    /**
     * INVERSE SIDE
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Order\Esd", mappedBy="order")
     * @var \Shopware\Models\Order\Esd
     */
    protected $esd;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $paymentInstances
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Payment\PaymentInstance", mappedBy="order")
     */
    protected $paymentInstances;


    public function __construct()
    {
        $this->details = new ArrayCollection();
        $this->paymentInstances = new ArrayCollection();
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
     * Set number
     *
     * @param string $number
     * @return Order
     */
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set invoiceAmount
     *
     * @param float $invoiceAmount
     * @return Order
     */
    public function setInvoiceAmount($invoiceAmount)
    {
        $this->invoiceAmount = $invoiceAmount;
        return $this;
    }

    /**
     * Get invoiceAmount
     *
     * @return float
     */
    public function getInvoiceAmount()
    {
        return $this->invoiceAmount;
    }

    /**
     * Set invoiceAmountNet
     *
     * @param float $invoiceAmountNet
     * @return Order
     */
    public function setInvoiceAmountNet($invoiceAmountNet)
    {
        $this->invoiceAmountNet = $invoiceAmountNet;
        return $this;
    }

    /**
     * Get invoiceAmountNet
     *
     * @return float
     */
    public function getInvoiceAmountNet()
    {
        return $this->invoiceAmountNet;
    }

    /**
     * Set invoiceShipping
     *
     * @param float $invoiceShipping
     * @return Order
     */
    public function setInvoiceShipping($invoiceShipping)
    {
        $this->invoiceShipping = $invoiceShipping;
        return $this;
    }

    /**
     * Get invoiceShipping
     *
     * @return float
     */
    public function getInvoiceShipping()
    {
        return $this->invoiceShipping;
    }

    /**
     * Set invoiceShippingNet
     *
     * @param float $invoiceShippingNet
     * @return Order
     */
    public function setInvoiceShippingNet($invoiceShippingNet)
    {
        $this->invoiceShippingNet = $invoiceShippingNet;
        return $this;
    }

    /**
     * Get invoiceShippingNet
     *
     * @return float
     */
    public function getInvoiceShippingNet()
    {
        return $this->invoiceShippingNet;
    }

    /**
     * Set orderTime
     *
     * @param \DateTime|string $orderTime
     * @return Order
     */
    public function setOrderTime($orderTime)
    {
        if (!$orderTime instanceof \DateTime && is_string($orderTime)) {
            $orderTime = new \DateTime($orderTime);
        }
        $this->orderTime = $orderTime;
        return $this;
    }

    /**
     * Get orderTime
     *
     * @return \DateTime
     */
    public function getOrderTime()
    {
        return $this->orderTime;
    }

    /**
     * Set transactionId
     *
     * @param string $transactionId
     * @return Order
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * Get transactionId
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return Order
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set customerComment
     *
     * @param string $customerComment
     * @return Order
     */
    public function setCustomerComment($customerComment)
    {
        $this->customerComment = $customerComment;
        return $this;
    }

    /**
     * Get customerComment
     *
     * @return string
     */
    public function getCustomerComment()
    {
        return $this->customerComment;
    }

    /**
     * Set internalComment
     *
     * @param string $internalComment
     * @return Order
     */
    public function setInternalComment($internalComment)
    {
        $this->internalComment = $internalComment;
        return $this;
    }

    /**
     * Get internalComment
     *
     * @return string
     */
    public function getInternalComment()
    {
        return $this->internalComment;
    }

    /**
     * Set net
     *
     * @param integer $net
     * @return Order
     */
    public function setNet($net)
    {
        $this->net = $net;
        return $this;
    }

    /**
     * Get net
     *
     * @return integer
     */
    public function getNet()
    {
        return $this->net;
    }

    /**
     * Set taxFree
     *
     * @param integer $taxFree
     * @return Order
     */
    public function setTaxFree($taxFree)
    {
        $this->taxFree = $taxFree;
        return $this;
    }

    /**
     * Get taxFree
     *
     * @return integer
     */
    public function getTaxFree()
    {
        return $this->taxFree;
    }

    /**
     * Set temporaryId
     *
     * @param string $temporaryId
     * @return Order
     */
    public function setTemporaryId($temporaryId)
    {
        $this->temporaryId = $temporaryId;
        return $this;
    }

    /**
     * Get temporaryId
     *
     * @return string
     */
    public function getTemporaryId()
    {
        return $this->temporaryId;
    }

    /**
     * Set referer
     *
     * @param string $referer
     * @return Order
     */
    public function setReferer($referer)
    {
        $this->referer = $referer;
        return $this;
    }

    /**
     * Get referer
     *
     * @return string
     */
    public function getReferer()
    {
        return $this->referer;
    }

    /**
     * Set clearedDate
     *
     * @param \DateTime|string $clearedDate
     * @return Order
     */
    public function setClearedDate($clearedDate)
    {
        if (!$clearedDate instanceof \DateTime && is_string($clearedDate)) {
            $clearedDate = new \DateTime($clearedDate);
        }
        $this->clearedDate = $clearedDate;
        return $this;
    }

    /**
     * Get clearedDate
     *
     * @return \DateTime
     */
    public function getClearedDate()
    {
        return $this->clearedDate;
    }

    /**
     * Set trackingCode
     *
     * @param string $trackingCode
     * @return Order
     */
    public function setTrackingCode($trackingCode)
    {
        $this->trackingCode = $trackingCode;
        return $this;
    }

    /**
     * Get trackingCode
     *
     * @return string
     */
    public function getTrackingCode()
    {
        return $this->trackingCode;
    }

    /**
     * Set languageIso
     *
     * @param string $languageIso
     * @return Order
     */
    public function setLanguageIso($languageIso)
    {
        $this->languageIso = $languageIso;
        return $this;
    }

    /**
     * Get languageIso
     *
     * @return string
     */
    public function getLanguageIso()
    {
        return $this->languageIso;
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return Order
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set currencyFactor
     *
     * @param float $currencyFactor
     * @return Order
     */
    public function setCurrencyFactor($currencyFactor)
    {
        $this->currencyFactor = $currencyFactor;
        return $this;
    }

    /**
     * Get currencyFactor
     *
     * @return float
     */
    public function getCurrencyFactor()
    {
        return $this->currencyFactor;
    }

    /**
     * Set remoteAddress
     *
     * @param string $remoteAddress
     * @return Order
     */
    public function setRemoteAddress($remoteAddress)
    {
        $this->remoteAddress = $remoteAddress;
        return $this;
    }

    /**
     * Get remoteAddress
     *
     * @return string
     */
    public function getRemoteAddress()
    {
        return $this->remoteAddress;
    }

    /**
     * @return \Shopware\Models\Customer\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param  $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return \Shopware\Models\Payment\Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param  $payment
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
    }

    /**
     * @return \Shopware\Models\Dispatch\Dispatch
     */
    public function getDispatch()
    {
        return $this->dispatch;
    }

    /**
     * @param  $dispatch
     */
    public function setDispatch($dispatch)
    {
        $this->dispatch = $dispatch;
    }

    /**
     * @return \Shopware\Models\Order\Status
     */
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * @param  $paymentStatus
     */
    public function setPaymentStatus($paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;
    }

    /**
     * @return \Shopware\Models\Order\Status
     */
    public function getOrderStatus()
    {
        return $this->orderStatus;
    }

    /**
     * @param $orderStatus
     * @return void
     */
    public function setOrderStatus($orderStatus)
    {
        $this->orderStatus = $orderStatus;
    }

    /**
     * @return \Shopware\Models\Shop\Shop
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @param \Shopware\Models\Shop\Shop $shop
     */
    public function setShop($shop)
    {
        $this->shop = $shop;
    }

    /**
     * @return \Shopware\Models\Order\Shipping
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param \Shopware\Models\Order\Shipping|array|null $shipping
     * @return \Shopware\Models\Order\Shipping
     */
    public function setShipping($shipping)
    {
        return $this->setOneToOne($shipping, '\Shopware\Models\Order\Shipping', 'shipping', 'order');
    }

    /**
     * @return \Shopware\Models\Order\Billing
     */
    public function getBilling()
    {
        return $this->billing;
    }

    /**
     * @param \Shopware\Models\Order\Billing|array|null $billing
     * @return \Shopware\Models\Order\Billing
     */
    public function setBilling($billing)
    {
        return $this->setOneToOne($billing, '\Shopware\Models\Order\Billing', 'billing', 'order');
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection|array|null $details
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function setDetails($details)
    {
        return $this->setOneToMany($details, '\Shopware\Models\Order\Detail', 'details', 'order');
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $history
     */
    public function setHistory($history)
    {
        $this->history = $history;
    }

    /**
     * The calculateInvoiceAmount function recalculated the net and gross amount based on the
     * order positions.
     */
    public function calculateInvoiceAmount()
    {
        $invoiceAmount = 0;
        $invoiceAmountNet = 0;

        //iterate order details to recalculate the amount.
        /**@var $detail Detail*/
        foreach ($this->getDetails() as $detail) {
            $invoiceAmount += $detail->getPrice() * $detail->getQuantity();

            $tax = $detail->getTax();

            $taxValue = $detail->getTaxRate();

            // additional tax checks required for sw-2238, sw-2903 and sw-3164
            if ($tax && $tax->getId() !== 0 && $tax->getId() !== null && $tax->getTax() !== null) {
                $taxValue = $tax->getTax();
            }

            if ($this->net) {
                $invoiceAmountNet += round(($detail->getPrice() * $detail->getQuantity()) / 100 * (100 + $taxValue), 2);
            } else {
                $invoiceAmountNet += ($detail->getPrice() * $detail->getQuantity()) / (100 + $taxValue) * 100;
            }
        }

        if ($this->taxFree) {
            $this->invoiceAmountNet = $invoiceAmount + $this->invoiceShippingNet;
            $this->invoiceAmount = $this->invoiceAmountNet;
        } elseif ($this->net) {
            $this->invoiceAmountNet = $invoiceAmount + $this->invoiceShippingNet;
            $this->invoiceAmount = $invoiceAmountNet + $this->invoiceShipping;
        } else {
            $this->invoiceAmount = $invoiceAmount + $this->invoiceShipping;
            $this->invoiceAmountNet = $invoiceAmountNet + $this->invoiceShippingNet;
        }
    }

    /**
     * @return \Shopware\Models\Attribute\Order
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\Order|array|null $attribute
     * @return \Shopware\Models\Order\Order
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\Order', 'attribute', 'order');
    }

    /**
     * Get Partner
     *
     * @return \Shopware\Models\Partner\Partner
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * Set Partner
     *
     * @param \Shopware\Models\Partner\Partner $partner
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection|array|null $documents
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function setDocuments($documents)
    {
        return $this->setOneToMany($documents, '\Shopware\Models\Order\Document\Document', 'documents', 'order');
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
     * @param \Shopware\Models\Shop\Shop $languageSubShop
     */
    public function setLanguageSubShop($languageSubShop)
    {
        $this->languageSubShop = $languageSubShop;
    }

    /**
     * @return \Shopware\Models\Shop\Shop
     */
    public function getLanguageSubShop()
    {
        return $this->languageSubShop;
    }

    /**
     * @param mixed $paymentInstances
     */
    public function setPaymentInstances($paymentInstances)
    {
        $this->paymentInstances = $paymentInstances;
    }

    /**
     * @return mixed
     */
    public function getPaymentInstances()
    {
        return $this->paymentInstances;
    }

    /**
     * @param string $deviceType
     */
    public function setDeviceType($deviceType)
    {
        $this->deviceType = $deviceType;
    }

    /**
     * @return string
     */
    public function getDeviceType()
    {
        return $this->deviceType;
    }
}
