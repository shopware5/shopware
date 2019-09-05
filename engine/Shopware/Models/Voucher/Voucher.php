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

namespace Shopware\Models\Voucher;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Standard Voucher Model Entity
 *
 * @ORM\Table(name="s_emarketing_vouchers")
 * @ORM\Entity(repositoryClass="Repository")
 */
class Voucher extends ModelEntity
{
    /**
     * INVERSE SIDE
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Voucher\Code>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Voucher\Code", mappedBy="voucher", orphanRemoval=true, cascade={"persist"})
     */
    protected $codes;

    /**
     * INVERSE SIDE
     *
     * @var \Shopware\Models\Attribute\Voucher
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Voucher", mappedBy="voucher", orphanRemoval=true, cascade={"persist"})
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
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="vouchercode", type="string", length=100, nullable=false)
     */
    private $voucherCode;

    /**
     * @var int
     *
     * @ORM\Column(name="numberofunits", type="integer", nullable=false)
     */
    private $numberOfUnits;

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="float", nullable=false)
     */
    private $value;

    /**
     * @var float
     *
     * @ORM\Column(name="minimumcharge", type="float", nullable=false)
     */
    private $minimumCharge;

    /**
     * @var int
     *
     * @ORM\Column(name="shippingfree", type="integer", nullable=false)
     */
    private $shippingFree;

    /**
     * @var int
     *
     * @ORM\Column(name="bindtosupplier", type="integer", nullable=true)
     */
    private $bindToSupplier;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="valid_from", type="date", nullable=true)
     */
    private $validFrom;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="valid_to", type="date", nullable=true)
     */
    private $validTo;

    /**
     * @var string
     *
     * @ORM\Column(name="ordercode", type="string", length=100, nullable=false)
     */
    private $orderCode;

    /**
     * @var int
     *
     * @ORM\Column(name="modus", type="integer", nullable=false)
     */
    private $modus;

    /**
     * @var int
     *
     * @ORM\Column(name="percental", type="integer", nullable=false)
     */
    private $percental;

    /**
     * @var int
     *
     * @ORM\Column(name="numorder", type="integer", nullable=false)
     */
    private $numOrder;

    /**
     * @var int
     *
     * @ORM\Column(name="customergroup", type="integer", nullable=true)
     */
    private $customerGroup;

    /**
     * @var string
     *
     * @ORM\Column(name="restrictarticles", type="text", nullable=false)
     */
    private $restrictArticles;

    /**
     * @var int
     *
     * @ORM\Column(name="strict", type="integer", nullable=false)
     */
    private $strict;

    /**
     * @var int
     *
     * @ORM\Column(name="subshopID", type="integer", nullable=true)
     */
    private $shopId;

    /**
     * @var string
     *
     * @ORM\Column(name="taxconfig", type="string", length=15, nullable=false)
     */
    private $taxConfig;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_stream_ids", type="text", nullable=true)
     */
    private $customerStreamIds;

    public function __construct()
    {
        $this->codes = new ArrayCollection();
    }

    /**
     * Getter Method to get the Id field from the Model
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Setter Method to set the description field from the Model
     *
     * @param string $description
     *
     * @return Voucher
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Getter Method to get the description field from the Model
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Setter Method to set the voucherCode field from the Model
     *
     * @param string $voucherCode
     *
     * @return Voucher
     */
    public function setVoucherCode($voucherCode)
    {
        $this->voucherCode = $voucherCode;

        return $this;
    }

    /**
     * Getter Method to get the voucherCode field from the Model
     *
     * @return string
     */
    public function getVoucherCode()
    {
        return $this->voucherCode;
    }

    /**
     * Setter Method to set the numberOfUnits field from the Model
     *
     * @param int $numberOfUnits
     *
     * @return Voucher
     */
    public function setNumberOfUnits($numberOfUnits)
    {
        $this->numberOfUnits = $numberOfUnits;

        return $this;
    }

    /**
     * Getter Method to get the numberOfUnits field from the Model
     *
     * @return int
     */
    public function getNumberOfUnits()
    {
        return $this->numberOfUnits;
    }

    /**
     * Setter Method to set the value field from the Model
     *
     * @param float $value
     *
     * @return Voucher
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Getter Method to get the value field from the Model
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Setter Method to set the minimumCharge field from the Model
     *
     * @param float $minimumCharge
     *
     * @return Voucher
     */
    public function setMinimumCharge($minimumCharge)
    {
        $this->minimumCharge = $minimumCharge;

        return $this;
    }

    /**
     * Getter Method to get the minimumCharge field from the Model
     *
     * @return float
     */
    public function getMinimumCharge()
    {
        return $this->minimumCharge;
    }

    /**
     * Setter Method to set the shippingFree field from the Model
     *
     * @param int $shippingFree
     *
     * @return Voucher
     */
    public function setShippingFree($shippingFree)
    {
        $this->shippingFree = $shippingFree;

        return $this;
    }

    /**
     * Getter Method to get the shippingFreefield from the Model
     *
     * @return int
     */
    public function getShippingFree()
    {
        return $this->shippingFree;
    }

    /**
     * Setter Method to set the bindToSupplier field from the Model
     *
     * @param int $bindToSupplier
     *
     * @return Voucher
     */
    public function setBindToSupplier($bindToSupplier)
    {
        $this->bindToSupplier = $bindToSupplier;

        return $this;
    }

    /**
     * Getter Method to get the bindToSupplier field from the Model
     *
     * @return int
     */
    public function getBindToSupplier()
    {
        return $this->bindToSupplier;
    }

    /**
     * Setter Method to set the validFrom field from the Model
     *
     * @param \DateTimeInterface|string $validFrom
     *
     * @return Voucher
     */
    public function setValidFrom($validFrom)
    {
        if (!$validFrom instanceof \DateTimeInterface && !empty($validFrom)) {
            $validFrom = new \DateTime($validFrom);
        }
        $this->validFrom = $validFrom;

        return $this;
    }

    /**
     * Getter Method to get the ValidFrom field from the Model
     *
     * @return \DateTimeInterface
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * Setter Method to set the validTo field from the Model
     *
     * @param \DateTimeInterface|string $validTo
     *
     * @return Voucher
     */
    public function setValidTo($validTo)
    {
        if (!$validTo instanceof \DateTimeInterface && !empty($validTo)) {
            $validTo = new \DateTime($validTo);
        }
        $this->validTo = $validTo;

        return $this;
    }

    /**
     * Getter Method to get the ValidTo field from the Model
     *
     * @return \DateTimeInterface
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * Setter Method to set the orderCode field from the Model
     *
     * @param string $orderCode
     *
     * @return Voucher
     */
    public function setOrderCode($orderCode)
    {
        $this->orderCode = $orderCode;

        return $this;
    }

    /**
     * Getter Method to get the OrderCode field from the Model
     *
     * @return string
     */
    public function getOrderCode()
    {
        return $this->orderCode;
    }

    /**
     * Setter Method to set the modus field from the Model
     *
     * @param int $modus
     *
     * @return Voucher
     */
    public function setModus($modus)
    {
        $this->modus = $modus;

        return $this;
    }

    /**
     * Getter Method to get the Modus field from the Model
     *
     * @return int
     */
    public function getModus()
    {
        return $this->modus;
    }

    /**
     * Setter Method to set the percental field from the Model
     *
     * @param int $percental
     *
     * @return Voucher
     */
    public function setPercental($percental)
    {
        $this->percental = $percental;

        return $this;
    }

    /**
     * Getter Method to get the Percental field from the Model
     *
     * @return int
     */
    public function getPercental()
    {
        return $this->percental;
    }

    /**
     * Setter Method to set the numOrder field from the Model
     *
     * @param int $numOrder
     *
     * @return Voucher
     */
    public function setNumOrder($numOrder)
    {
        $this->numOrder = $numOrder;

        return $this;
    }

    /**
     * Getter Method to get the numOrder field from the Model
     *
     * @return int
     */
    public function getNumOrder()
    {
        return $this->numOrder;
    }

    /**
     * Setter Method to set the customerGroup field from the Model
     *
     * @param int $customerGroup
     *
     * @return Voucher
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->customerGroup = $customerGroup;

        return $this;
    }

    /**
     * Getter Method to get the customerGroup field from the Model
     *
     * @return int
     */
    public function getCustomerGroup()
    {
        return $this->customerGroup;
    }

    /**
     * Setter Method to set the restrictArticles field from the Model
     *
     * @param string $restrictArticles
     *
     * @return Voucher
     */
    public function setRestrictArticles($restrictArticles)
    {
        $this->restrictArticles = $restrictArticles;

        return $this;
    }

    /**
     * Getter Method to get the restrictArticles field from the Model
     *
     * @return string
     */
    public function getRestrictArticles()
    {
        return $this->restrictArticles;
    }

    /**
     * Setter Method to set the strict field from the Model
     *
     * @param int $strict
     *
     * @return Voucher
     */
    public function setStrict($strict)
    {
        $this->strict = $strict;

        return $this;
    }

    /**
     * Getter Method to get the strict field from the Model
     *
     * @return int
     */
    public function getStrict()
    {
        return $this->strict;
    }

    /**
     * Setter Method to set the shopId field from the Model
     *
     * @param int $shopId
     *
     * @return Voucher
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;

        return $this;
    }

    /**
     * Getter Method to get the shopId field from the Model
     *
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * Setter Method to set the taxConfig field from the Model
     *
     * @param string $taxConfig
     *
     * @return Voucher
     */
    public function setTaxConfig($taxConfig)
    {
        $this->taxConfig = $taxConfig;

        return $this;
    }

    /**
     * Getter Method to get the taxConfig field from the Model
     *
     * @return string
     */
    public function getTaxConfig()
    {
        return $this->taxConfig;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Voucher\Code>
     */
    public function getCodes()
    {
        return $this->codes;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Voucher\Code>|array|null $codes
     *
     * @return Voucher
     */
    public function setCodes($codes)
    {
        return $this->setOneToMany($codes, Code::class, 'codes', 'voucher');
    }

    /**
     * @return \Shopware\Models\Attribute\Voucher
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\Voucher|null $attribute
     *
     * @return Voucher
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, \Shopware\Models\Attribute\Voucher::class, 'attribute', 'voucher');
    }

    /**
     * @return string
     */
    public function getCustomerStreamIds()
    {
        return $this->customerStreamIds;
    }

    /**
     * @param string $customerStreamIds
     */
    public function setCustomerStreamIds($customerStreamIds)
    {
        $this->customerStreamIds = $customerStreamIds;
    }
}
