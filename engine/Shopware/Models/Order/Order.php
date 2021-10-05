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
use Shopware\Bundle\OrderBundle\Service\CalculationServiceInterface;
use Shopware\Components\Model\ModelEntity;
use Shopware\Components\Security\AttributeCleanerTrait;
use Shopware\Models\Attribute\Order as OrderAttribute;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Order\Document\Document;
use Shopware\Models\Partner\Partner;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Payment\PaymentInstance;
use Shopware\Models\Shop\Shop;
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
     * @var Customer|null
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Customer\Customer", inversedBy="orders")
     * @ORM\JoinColumn(name="userID", referencedColumnName="id")
     */
    protected $customer;

    /**
     * @var Payment
     *
     * @Assert\NotBlank()
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Payment\Payment")
     * @ORM\JoinColumn(name="paymentID", referencedColumnName="id", nullable=false)
     */
    protected $payment;

    /**
     * @var Dispatch
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Dispatch\Dispatch")
     * @ORM\JoinColumn(name="dispatchID", referencedColumnName="id", nullable=false)
     *
     * @Assert\NotBlank()
     */
    protected $dispatch;

    /**
     * The shop property is the owning side of the association between order and shop.
     * The association is joined over the order userID field and the id field of the shop.
     *
     * @var Shop
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="subshopID", referencedColumnName="id", nullable=false)
     *
     * @Assert\NotBlank()
     */
    protected $shop;

    /**
     * @var Partner|null
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Partner\Partner", inversedBy="orders")
     * @ORM\JoinColumn(name="partnerID", referencedColumnName="idcode")
     */
    protected $partner;

    /**
     * INVERSE SIDE
     *
     * @var OrderAttribute|null
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Order", mappedBy="order", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * @var Status
     *
     * @Assert\NotBlank()
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Order\Status")
     * @ORM\JoinColumn(name="cleared", referencedColumnName="id", nullable=false)
     */
    protected $paymentStatus;

    /**
     * @Assert\NotBlank()
     *
     * @var Status
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Order\Status")
     * @ORM\JoinColumn(name="status", referencedColumnName="id", nullable=false)
     */
    protected $orderStatus;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<Detail>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Order\Detail", mappedBy="order", orphanRemoval=true, cascade={"persist"})
     */
    protected $details;

    /**
     * INVERSE SIDE
     * The billing property is the inverse side of the association between order and billing.
     * The association is joined over the billing orderID field and the id field of the order
     *
     * @var Billing|null
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Order\Billing", mappedBy="order", orphanRemoval=true, cascade={"persist"})
     */
    protected $billing;

    /**
     * INVERSE SIDE
     * The shipping property is the inverse side of the association between order and shipping.
     * The association is joined over the shipping orderID field and the id field of the order
     *
     * @var Shipping|null
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Order\Shipping", mappedBy="order", orphanRemoval=true, cascade={"persist"})
     */
    protected $shipping;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<Document>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Order\Document\Document", mappedBy="order", orphanRemoval=true, cascade={"persist"})
     */
    protected $documents;

    /**
     * @var ArrayCollection<History>
     *
     * @ORM\OneToMany(targetEntity="\Shopware\Models\Order\History", mappedBy="order", orphanRemoval=true)
     * @ORM\JoinColumn(name="id", referencedColumnName="orderID")
     */
    protected $history;

    /**
     * INVERSE SIDE
     *
     * @var Esd|null
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Order\Esd", mappedBy="order")
     */
    protected $esd;

    /**
     * @var ArrayCollection<PaymentInstance>
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
     * @var string|null
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
     * @var int
     *
     * @ORM\Column(name="dispatchID", type="integer", nullable=false)
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
     * @var float|null
     *
     * @ORM\Column(name="invoice_shipping_tax_rate", type="float", nullable=true)
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
     * @var \DateTimeInterface|null
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
     * @var Shop
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="language", referencedColumnName="id", nullable=false)
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
     * @var string|null
     *
     * @ORM\Column(name="remote_addr", type="string", length=255, nullable=true)
     */
    private $remoteAddress;

    /**
     * @var string|null
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
     * @param string|null $number
     *
     * @return Order
     */
    public function setNumber($number)
    {
        if (\is_string($number)) {
            $number = $this->cleanup($number);
        }
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
     * @return float|null
     */
    public function getInvoiceShippingTaxRate()
    {
        return $this->invoiceShippingTaxRate;
    }

    /**
     * @param float|null $invoiceShippingTaxRate
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
        if (!$orderTime instanceof \DateTimeInterface && \is_string($orderTime)) {
            $orderTime = new DateTime($orderTime);
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
        if (!$clearedDate instanceof \DateTimeInterface && \is_string($clearedDate)) {
            $clearedDate = new DateTime($clearedDate);
        }
        $this->clearedDate = $clearedDate;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
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
     * @return string|null
     */
    public function getRemoteAddress()
    {
        return $this->remoteAddress;
    }

    /**
     * @return Customer|null
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer|null $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param Payment $payment
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
    }

    /**
     * @return Dispatch
     */
    public function getDispatch()
    {
        return $this->dispatch;
    }

    /**
     * @param Dispatch $dispatch
     */
    public function setDispatch($dispatch)
    {
        $this->dispatch = $dispatch;
    }

    /**
     * @return Status
     */
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * @param Status $paymentStatus
     */
    public function setPaymentStatus($paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;
    }

    /**
     * @return Status
     */
    public function getOrderStatus()
    {
        return $this->orderStatus;
    }

    /**
     * @param Status $orderStatus
     */
    public function setOrderStatus($orderStatus)
    {
        $this->orderStatus = $orderStatus;
    }

    /**
     * @return Shop
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @param Shop $shop
     */
    public function setShop($shop)
    {
        $this->shop = $shop;
    }

    /**
     * @return Shipping|null
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param Shipping|null $shipping
     *
     * @return Order
     */
    public function setShipping($shipping)
    {
        return $this->setOneToOne($shipping, Shipping::class, 'shipping', 'order');
    }

    /**
     * @return Billing|null
     */
    public function getBilling()
    {
        return $this->billing;
    }

    /**
     * @param Billing|null $billing
     *
     * @return Order
     */
    public function setBilling($billing)
    {
        return $this->setOneToOne($billing, Billing::class, 'billing', 'order');
    }

    /**
     * @return ArrayCollection<array-key, Detail>
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param ArrayCollection<array-key, Detail>|array<array-key, Detail> $details
     *
     * @return Order
     */
    public function setDetails($details)
    {
        return $this->setOneToMany($details, Detail::class, 'details', 'order');
    }

    /**
     * @return ArrayCollection<History>
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @param ArrayCollection<History> $history
     */
    public function setHistory($history)
    {
        $this->history = $history;
    }

    /**
     * The calculateInvoiceAmount function recalculated the net and gross amount based on the
     * order positions.
     *
     * @deprecated since 5.7 will be removed in version 5.8 - Please use the service \Shopware\Bundle\OrderBundle\Service\CalculationServiceInterface::class.
     */
    public function calculateInvoiceAmount()
    {
        trigger_error(sprintf(
            '%s:%s is deprecated since Shopware 5.7 and will be removed with 5.8. Please use the service with id `%s` instead',
            __CLASS__,
            __METHOD__,
            CalculationServiceInterface::class
        ), E_USER_DEPRECATED);

        /** @var CalculationServiceInterface $service */
        $service = Shopware()->Container()->get(CalculationServiceInterface::class);
        $service->recalculateOrderTotals($this);
    }

    /**
     * @return OrderAttribute|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param OrderAttribute|array|null $attribute
     *
     * @return Order
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, OrderAttribute::class, 'attribute', 'order');
    }

    /**
     * @return Partner|null
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * @param Partner|null $partner
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
    }

    /**
     * @return ArrayCollection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param Document[]|null $documents
     *
     * @return Order
     */
    public function setDocuments($documents)
    {
        return $this->setOneToMany($documents, Document::class, 'documents', 'order');
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
     * @param Shop $languageSubShop
     */
    public function setLanguageSubShop($languageSubShop)
    {
        $this->languageSubShop = $languageSubShop;
    }

    /**
     * @return Shop
     */
    public function getLanguageSubShop()
    {
        return $this->languageSubShop;
    }

    /**
     * @param ArrayCollection $paymentInstances
     */
    public function setPaymentInstances($paymentInstances)
    {
        $this->paymentInstances = $paymentInstances;
    }

    /**
     * @return ArrayCollection
     */
    public function getPaymentInstances()
    {
        return $this->paymentInstances;
    }

    /**
     * @param string|null $deviceType
     */
    public function setDeviceType($deviceType)
    {
        if (\is_string($deviceType)) {
            $deviceType = $this->cleanup($deviceType);
        }
        $this->deviceType = $deviceType;
    }

    /**
     * @return string|null
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
