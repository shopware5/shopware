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

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Components\Security\AttributeCleanerTrait;
use Symfony\Component\Validator\Constraints as Assert;

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
 * @ORM\HasLifecycleCallbacks()
 */
class Order extends ModelEntity
{
    /*
     * HTML Cleansing trait for different attributes in a class (implemented in setters)
     * @see \Shopware\Components\Security\AttributeCleanerTrait
     */
    use AttributeCleanerTrait;

    /**
     * @var \Shopware\Models\Customer\Customer
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Customer\Customer", inversedBy="orders")
     * @ORM\JoinColumn(name="userID", referencedColumnName="id")
     */
    protected $customer;

    /**
     * @var \Shopware\Models\Payment\Payment
     *
     * @Assert\NotBlank()
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Payment\Payment")
     * @ORM\JoinColumn(name="paymentID", referencedColumnName="id")
     */
    protected $payment;

    /**
     * @var \Shopware\Models\Dispatch\Dispatch
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Dispatch\Dispatch")
     * @ORM\JoinColumn(name="dispatchID", referencedColumnName="id")
     *
     * @Assert\NotBlank()
     */
    protected $dispatch;

    /**
     * The shop property is the owning side of the association between order and shop.
     * The association is joined over the order userID field and the id field of the shop.
     *
     * @var \Shopware\Models\Shop\Shop
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="subshopID", referencedColumnName="id")
     *
     * @Assert\NotBlank()
     */
    protected $shop;

    /**
     * @var \Shopware\Models\Partner\Partner
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Partner\Partner", inversedBy="orders")
     * @ORM\JoinColumn(name="partnerID", referencedColumnName="idcode")
     */
    protected $partner;

    /**
     * INVERSE SIDE
     *
     * @var \Shopware\Models\Attribute\Order
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Order", mappedBy="order", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * @var \Shopware\Models\Order\Status
     *
     * @Assert\NotBlank()
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Order\Status")
     * @ORM\JoinColumn(name="cleared", referencedColumnName="id")
     */
    protected $paymentStatus;

    /**
     * @Assert\NotBlank()
     *
     * @var \Shopware\Models\Order\Status
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Order\Status")
     * @ORM\JoinColumn(name="status", referencedColumnName="id")
     */
    protected $orderStatus;

    /**
     * INVERSE SIDE
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Order\Detail>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Order\Detail", mappedBy="order", orphanRemoval=true, cascade={"persist"})
     */
    protected $details;

    /**
     * INVERSE SIDE
     * The billing property is the inverse side of the association between order and billing.
     * The association is joined over the billing orderID field and the id field of the order
     *
     * @var \Shopware\Models\Order\Billing
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Order\Billing", mappedBy="order", orphanRemoval=true, cascade={"persist"})
     */
    protected $billing;

    /**
     * INVERSE SIDE
     * The shipping property is the inverse side of the association between order and shipping.
     * The association is joined over the shipping orderID field and the id field of the order
     *
     * @var \Shopware\Models\Order\Shipping
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Order\Shipping", mappedBy="order", orphanRemoval=true, cascade={"persist"})
     */
    protected $shipping;

    /**
     * INVERSE SIDE
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Order\Document\Document>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Order\Document\Document", mappedBy="order", orphanRemoval=true, cascade={"persist"})
     */
    protected $documents;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Order\History>
     *
     * @ORM\OneToMany(targetEntity="\Shopware\Models\Order\History", mappedBy="order", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="orderID")
     */
    protected $history;

    /**
     * INVERSE SIDE
     *
     * @var \Shopware\Models\Order\Esd
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Order\Esd", mappedBy="order")
     */
    protected $esd;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Payment\PaymentInstance>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Payment\PaymentInstance", mappedBy="order")
     */
    protected $paymentInstances;

    /**
     * Unique identifier field.
     *
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private $id;

    /**
     * Time of the last modification of the order
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="changed", type="datetime", nullable=false)
     */
    private $changed;

    /**
     * Contains the alphanumeric order number. If the
     *
     * @var string
     *
     * @ORM\Column(name="ordernumber", type="string", length=255, nullable=true)
     */
    private $number;

    /**
     * @var int
     *
     * @ORM\Column(name="userID", type="integer", nullable=false)
     */
    private $customerId;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status;

    /**
     * @var int
     *
     * @ORM\Column(name="cleared", type="integer", nullable=false)
     */
    private $cleared;

    /**
     * @var int
     *
     * @ORM\Column(name="paymentID", type="integer", nullable=false)
     */
    private $paymentId;

    /**
     * @var string
     *
     * @ORM\Column(name="dispatchID", type="integer", nullable=true)
     */
    private $dispatchId;

    /**
     * @var string
     *
     * @ORM\Column(name="partnerID", type="string", length=255, nullable=false)
     */
    private $partnerId;

    /**
     * @var int
     *
     * @ORM\Column(name="subshopID", type="integer", nullable=false)
     */
    private $shopId;

    /**
     * @var float
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="invoice_amount", type="float", nullable=false)
     */
    private $invoiceAmount;

    /**
     * @var float
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="invoice_amount_net", type="float", nullable=false)
     */
    private $invoiceAmountNet;

    /**
     * @var float
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="invoice_shipping", type="float", nullable=false)
     */
    private $invoiceShipping;

    /**
     * @var float
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="invoice_shipping_net", type="float", nullable=false)
     */
    private $invoiceShippingNet;

    /**
     * @var float
     *
     * @ORM\Column(name="invoice_shipping_tax_rate", type="decimal", nullable=true)
     */
    private $invoiceShippingTaxRate;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="ordertime", type="datetime", nullable=false)
     */
    private $orderTime = null;

    /**
     * @var string
     *
     * @ORM\Column(name="transactionID", type="string", length=255, nullable=false)
     */
    private $transactionId;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=false)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="customercomment", type="text", nullable=false)
     */
    private $customerComment;

    /**
     * @var string
     *
     * @ORM\Column(name="internalcomment", type="text", nullable=false)
     */
    private $internalComment;

    /**
     * @Assert\NotBlank()
     *
     * @var int
     *
     * @ORM\Column(name="net", type="integer", nullable=false)
     */
    private $net;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="taxfree", type="integer", nullable=false)
     */
    private $taxFree;

    /**
     * @var string
     *
     * @ORM\Column(name="temporaryID", type="string", length=255, nullable=false)
     */
    private $temporaryId;

    /**
     * @var string
     *
     * @ORM\Column(name="referer", type="text", nullable=false)
     */
    private $referer;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="cleareddate", type="datetime", nullable=true)
     */
    private $clearedDate = null;

    /**
     * @var string
     *
     * @ORM\Column(name="trackingcode", type="text", nullable=false)
     */
    private $trackingCode;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="language", type="string", length=10, nullable=false)
     */
    private $languageIso;

    /**
     * OWNING SIDE
     *
     * Used for the language subshop association
     *
     * @var \Shopware\Models\Shop\Shop
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="language", referencedColumnName="id")
     */
    private $languageSubShop;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=5, nullable=false)
     * @Assert\NotBlank()
     */
    private $currency;

    /**
     * @var float
     *
     * @ORM\Column(name="currencyfactor", type="float", nullable=false)
     * @Assert\NotBlank()
     */
    private $currencyFactor;

    /**
     * @var string
     *
     * @ORM\Column(name="remote_addr", type="string", length=255, nullable=true)
     */
    private $remoteAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="deviceType", type="string", length=50, nullable=true)
     */
    private $deviceType = 'desktop';

    /**
     * @var bool
     *
     * @ORM\Column(name="is_proportional_calculation", type="boolean", nullable=false)
     */
    private $isProportionalCalculation = false;

    public function __construct()
    {
        $this->details = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->history = new ArrayCollection();
        $this->paymentInstances = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getChanged()
    {
        return $this->changed;
    }

    /**
     * @param string $number
     *
     * @return Order
     */
    public function setNumber($number)
    {
        $this->number = $this->cleanup($number);

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
     * @param float $invoiceAmount
     *
     * @return Order
     */
    public function setInvoiceAmount($invoiceAmount)
    {
        $this->invoiceAmount = $invoiceAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getInvoiceAmount()
    {
        return $this->invoiceAmount;
    }

    /**
     * @param float $invoiceAmountNet
     *
     * @return Order
     */
    public function setInvoiceAmountNet($invoiceAmountNet)
    {
        $this->invoiceAmountNet = $invoiceAmountNet;

        return $this;
    }

    /**
     * @return float
     */
    public function getInvoiceAmountNet()
    {
        return $this->invoiceAmountNet;
    }

    /**
     * @param float $invoiceShipping
     *
     * @return Order
     */
    public function setInvoiceShipping($invoiceShipping)
    {
        $this->invoiceShipping = $invoiceShipping;

        return $this;
    }

    /**
     * @return float
     */
    public function getInvoiceShipping()
    {
        return $this->invoiceShipping;
    }

    /**
     * @param float $invoiceShippingNet
     *
     * @return Order
     */
    public function setInvoiceShippingNet($invoiceShippingNet)
    {
        $this->invoiceShippingNet = $invoiceShippingNet;

        return $this;
    }

    /**
     * @return float
     */
    public function getInvoiceShippingNet()
    {
        return $this->invoiceShippingNet;
    }

    /**
     * @return float
     */
    public function getInvoiceShippingTaxRate()
    {
        return $this->invoiceShippingTaxRate;
    }

    /**
     * @param float $invoiceShippingTaxRate
     */
    public function setInvoiceShippingTaxRate($invoiceShippingTaxRate)
    {
        $this->invoiceShippingTaxRate = $invoiceShippingTaxRate;
    }

    /**
     * @param \DateTimeInterface|string $orderTime
     *
     * @return Order
     */
    public function setOrderTime($orderTime)
    {
        if (!$orderTime instanceof \DateTimeInterface && is_string($orderTime)) {
            $orderTime = new \DateTime($orderTime);
        }
        $this->orderTime = $orderTime;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getOrderTime()
    {
        return $this->orderTime;
    }

    /**
     * @param string $transactionId
     *
     * @return Order
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $this->cleanup($transactionId);

        return $this;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $comment
     *
     * @return Order
     */
    public function setComment($comment)
    {
        $this->comment = $this->cleanup($comment);

        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $customerComment
     *
     * @return Order
     */
    public function setCustomerComment($customerComment)
    {
        $this->customerComment = $this->cleanup($customerComment);

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerComment()
    {
        return $this->customerComment;
    }

    /**
     * @param string $internalComment
     *
     * @return Order
     */
    public function setInternalComment($internalComment)
    {
        $this->internalComment = $this->cleanup($internalComment);

        return $this;
    }

    /**
     * @return string
     */
    public function getInternalComment()
    {
        return $this->internalComment;
    }

    /**
     * @param int $net
     *
     * @return Order
     */
    public function setNet($net)
    {
        $this->net = $net;

        return $this;
    }

    /**
     * @return int
     */
    public function getNet()
    {
        return $this->net;
    }

    /**
     * @param int $taxFree
     *
     * @return Order
     */
    public function setTaxFree($taxFree)
    {
        $this->taxFree = $taxFree;

        return $this;
    }

    /**
     * @return int
     */
    public function getTaxFree()
    {
        return $this->taxFree;
    }

    /**
     * @param string $temporaryId
     *
     * @return Order
     */
    public function setTemporaryId($temporaryId)
    {
        $this->temporaryId = $this->cleanup($temporaryId);

        return $this;
    }

    /**
     * @return string
     */
    public function getTemporaryId()
    {
        return $this->temporaryId;
    }

    /**
     * @param string $referer
     *
     * @return Order
     */
    public function setReferer($referer)
    {
        $this->referer = $referer;

        return $this;
    }

    /**
     * @return string
     */
    public function getReferer()
    {
        return $this->referer;
    }

    /**
     * @param \DateTimeInterface|string $clearedDate
     *
     * @return Order
     */
    public function setClearedDate($clearedDate)
    {
        if (!$clearedDate instanceof \DateTimeInterface && is_string($clearedDate)) {
            $clearedDate = new \DateTime($clearedDate);
        }
        $this->clearedDate = $clearedDate;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getClearedDate()
    {
        return $this->clearedDate;
    }

    /**
     * @param string $trackingCode
     *
     * @return Order
     */
    public function setTrackingCode($trackingCode)
    {
        $this->trackingCode = $trackingCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getTrackingCode()
    {
        return $this->trackingCode;
    }

    /**
     * @param string $languageIso
     *
     * @return Order
     */
    public function setLanguageIso($languageIso)
    {
        $this->languageIso = $this->cleanup($languageIso);

        return $this;
    }

    /**
     * @return string
     */
    public function getLanguageIso()
    {
        return $this->languageIso;
    }

    /**
     * @param string $currency
     *
     * @return Order
     */
    public function setCurrency($currency)
    {
        $this->currency = $this->cleanup($currency);

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param float $currencyFactor
     *
     * @return Order
     */
    public function setCurrencyFactor($currencyFactor)
    {
        $this->currencyFactor = $currencyFactor;

        return $this;
    }

    /**
     * @return float
     */
    public function getCurrencyFactor()
    {
        return $this->currencyFactor;
    }

    /**
     * @param string $remoteAddress
     *
     * @return Order
     */
    public function setRemoteAddress($remoteAddress)
    {
        $this->remoteAddress = $this->cleanup($remoteAddress);

        return $this;
    }

    /**
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
     * @param \Shopware\Models\Customer\Customer $customer
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
     * @param \Shopware\Models\Payment\Payment $payment
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
     * @param \Shopware\Models\Dispatch\Dispatch $dispatch
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
     * @param \Shopware\Models\Order\Status $paymentStatus
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
     * @param \Shopware\Models\Order\Status $orderStatus
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
     * @param \Shopware\Models\Order\Shipping|null $shipping
     *
     * @return Order
     */
    public function setShipping($shipping)
    {
        return $this->setOneToOne($shipping, \Shopware\Models\Order\Shipping::class, 'shipping', 'order');
    }

    /**
     * @return \Shopware\Models\Order\Billing
     */
    public function getBilling()
    {
        return $this->billing;
    }

    /**
     * @param \Shopware\Models\Order\Billing|null $billing
     *
     * @return Order
     */
    public function setBilling($billing)
    {
        return $this->setOneToOne($billing, \Shopware\Models\Order\Billing::class, 'billing', 'order');
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Order\Detail>
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param \Shopware\Models\Order\Detail[]|null $details
     *
     * @return Order
     */
    public function setDetails($details)
    {
        return $this->setOneToMany($details, \Shopware\Models\Order\Detail::class, 'details', 'order');
    }

    /**
     * @return ArrayCollection<\Shopware\Models\Order\History>
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @param ArrayCollection<\Shopware\Models\Order\History> $history
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

        // Iterate order details to recalculate the amount.
        /** @var Detail $detail */
        foreach ($this->getDetails() as $detail) {
            $price = round($detail->getPrice(), 2);

            $invoiceAmount += $price * $detail->getQuantity();

            $tax = $detail->getTax();

            $taxValue = $detail->getTaxRate();

            // Additional tax checks required for sw-2238, sw-2903 and sw-3164
            if ($tax && $tax->getId() !== 0 && $tax->getId() !== null && $tax->getTax() !== null) {
                $taxValue = $tax->getTax();
            }

            if ($this->net) {
                $invoiceAmountNet += Shopware()->Container()->get('shopware.cart.net_rounding')->round($price, $taxValue, $detail->getQuantity());
            } else {
                $invoiceAmountNet += round(($price * $detail->getQuantity()) / (100 + $taxValue) * 100, 2);
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
     *
     * @return \Shopware\Models\Order\Order
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, \Shopware\Models\Attribute\Order::class, 'attribute', 'order');
    }

    /**
     * @return \Shopware\Models\Partner\Partner
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * @param \Shopware\Models\Partner\Partner $partner
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Order\Document\Document>
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param \Shopware\Models\Order\Document\Document[]|null $documents
     *
     * @return Order
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
     * @param \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Payment\PaymentInstance> $paymentInstances
     */
    public function setPaymentInstances($paymentInstances)
    {
        $this->paymentInstances = $paymentInstances;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Payment\PaymentInstance>
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
        $this->deviceType = $this->cleanup($deviceType);
    }

    /**
     * @return string
     */
    public function getDeviceType()
    {
        return $this->deviceType;
    }

    /**
     * @return bool
     */
    public function isProportionalCalculation()
    {
        return $this->isProportionalCalculation;
    }

    /**
     * @param bool $proportionalCalculation
     */
    public function setIsProportionalCalculation($proportionalCalculation)
    {
        $this->isProportionalCalculation = $proportionalCalculation;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updateChangedTimestamp()
    {
        $this->changed = new DateTime();
    }
}
