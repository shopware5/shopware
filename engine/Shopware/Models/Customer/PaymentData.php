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

namespace Shopware\Models\Customer;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Payment\Payment;

/**
 * Shopware payment model represents a payment method information.
 * <br>
 * The Shopware Payment Data model represents a row of the s_core_payment_data.
 * It's used to store payment data
 * For now it will only be used for SEPA payments, but it's meant to be
 * extendable so that it can accommodate future scenarios
 *
 * @ORM\Entity(repositoryClass="PaymentDataRepository")
 * @ORM\Table(name="s_core_payment_data")
 * @ORM\HasLifecycleCallbacks()
 */
class PaymentData extends ModelEntity
{
    /**
     * @var Payment
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Payment\Payment", inversedBy="paymentData")
     * @ORM\JoinColumn(name="payment_mean_id", referencedColumnName="id", nullable=false)
     */
    protected $paymentMean;

    /**
     * @var int
     *
     * @ORM\Column(name="payment_mean_id", type="integer", nullable=false)
     */
    protected $paymentMeanId;

    /**
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Customer\Customer", inversedBy="paymentData")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $customer;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="use_billing_data", type="boolean", nullable=true)
     */
    protected $useBillingData;

    /**
     * @var string|null
     *
     * @ORM\Column(name="bankname", type="string", length=255, nullable=true)
     */
    protected $bankName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="bic", type="string", length=50, nullable=true)
     */
    protected $bic;

    /**
     * @var string|null
     *
     * @ORM\Column(name="iban", type="string", length=50, nullable=true)
     */
    protected $iban;

    /**
     * @var string|null
     *
     * @ORM\Column(name="account_number", type="string", length=50, nullable=true)
     */
    protected $accountNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="bank_code", type="string", length=50, nullable=true)
     */
    protected $bankCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="account_holder", type="string", length=50, nullable=true)
     */
    protected $accountHolder;

    /**
     * @var DateTimeInterface
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
        $this->createdAt = new DateTime();
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
     * @param string $bankName
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;
    }

    /**
     * @return string|null
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
     * @return string|null
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * @param DateTimeInterface $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param string $iban
     */
    public function setIban($iban)
    {
        $this->iban = $iban;
    }

    /**
     * @return string|null
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * @param Payment $paymentMean
     */
    public function setPaymentMean($paymentMean)
    {
        $this->paymentMean = $paymentMean;
    }

    /**
     * @return Payment
     */
    public function getPaymentMean()
    {
        return $this->paymentMean;
    }

    /**
     * @param bool $useBillingData
     */
    public function setUseBillingData($useBillingData)
    {
        $this->useBillingData = $useBillingData;
    }

    /**
     * @return bool|null
     */
    public function getUseBillingData()
    {
        return $this->useBillingData;
    }

    /**
     * @param string $accountHolder
     */
    public function setAccountHolder($accountHolder)
    {
        $this->accountHolder = $accountHolder;
    }

    /**
     * @return string|null
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
     * @return string|null
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
     * @return string|null
     */
    public function getBankCode()
    {
        return $this->bankCode;
    }

    /**
     * @return int
     */
    public function getPaymentMeanId()
    {
        return $this->paymentMeanId;
    }
}
