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

namespace Shopware\Models\Payment;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Shopware payment model represents a single payment transaction.
 * <br>
 * The Shopware Payment Instance model represents a row of the s_core_payment_instance.
 * It's used to store actual payment instances
 * For now it will only be used for SEPA payments, but it's meant to be
 * extendable so that it can accommodate future scenarios
 *
 * @ORM\Entity(repositoryClass="PaymentInstanceRepository")
 * @ORM\Table(name="s_core_payment_instance")
 * @ORM\HasLifecycleCallbacks()
 */
class PaymentInstance extends ModelEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="order_id", type="integer")
     */
    protected $orderId;

    /**
     * @var \Shopware\Models\Payment\Payment
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Payment\Payment", inversedBy="paymentInstances")
     * @ORM\JoinColumn(name="payment_mean_id", referencedColumnName="id")
     */
    protected $paymentMean;

    /**
     * @var \Shopware\Models\Order\Order
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Order\Order", inversedBy="paymentInstances")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    protected $order;

    /**
     * @var \Shopware\Models\Customer\Customer
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Customer\Customer", inversedBy="paymentInstances")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $customer;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255, nullable=true)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255, nullable=true)
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    protected $address;

    /**
     * @var string
     *
     * @ORM\Column(name="zipcode", type="string", length=15, nullable=true)
     */
    protected $zipCode;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=50, nullable=true)
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_name", type="string", length=255, nullable=true)
     */
    protected $bankName;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_code", type="string", length=255, nullable=true)
     */
    protected $bankCode;

    /**
     * @var string
     *
     * @ORM\Column(name="account_number", type="string", length=50, nullable=true)
     */
    protected $accountNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="account_holder", type="string", length=255, nullable=true)
     */
    protected $accountHolder;

    /**
     * @var string
     *
     * @ORM\Column(name="bic", type="string", length=50, nullable=true)
     */
    protected $bic;

    /**
     * @var string
     *
     * @ORM\Column(name="iban", type="string", length=50, nullable=true)
     */
    protected $iban;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="decimal", precision=20, scale=4)
     */
    protected $amount;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="created_at", type="date", nullable=false)
     */
    protected $createdAt;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Gets the id of the payment
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $bankName
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;
    }

    /**
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * @param string $bic
     */
    public function setBic($bic)
    {
        $this->bic = $bic;
    }

    /**
     * @return string
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param \DateTimeInterface $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \Shopware\Models\Customer\Customer $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return \Shopware\Models\Customer\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $iban
     */
    public function setIban($iban)
    {
        $this->iban = $iban;
    }

    /**
     * @return string
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param \Shopware\Models\Order\Order $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return \Shopware\Models\Order\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param \Shopware\Models\Payment\Payment $paymentMean
     */
    public function setPaymentMean($paymentMean)
    {
        $this->paymentMean = $paymentMean;
    }

    /**
     * @return \Shopware\Models\Payment\Payment
     */
    public function getPaymentMean()
    {
        return $this->paymentMean;
    }

    /**
     * @param string $zipCode
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;
    }

    /**
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $accountHolder
     */
    public function setAccountHolder($accountHolder)
    {
        $this->accountHolder = $accountHolder;
    }

    /**
     * @return string
     */
    public function getAccountHolder()
    {
        return $this->accountHolder;
    }

    /**
     * @param string $accountNumber
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
    }

    /**
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @param string $bankCode
     */
    public function setBankCode($bankCode)
    {
        $this->bankCode = $bankCode;
    }

    /**
     * @return string
     */
    public function getBankCode()
    {
        return $this->bankCode;
    }
}
