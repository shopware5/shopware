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

namespace Shopware\Bundle\CartBundle\Domain\Customer;

use Shopware\Bundle\CartBundle\Domain\Payment\PaymentInformation;
use Shopware\Bundle\CartBundle\Domain\Payment\PaymentService;
use Shopware\Bundle\CartBundle\Domain\CloneTrait;
use Shopware\Bundle\CartBundle\Domain\JsonSerializableTrait;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

class Customer extends Extendable
{
    use CloneTrait, JsonSerializableTrait;

    const ACCOUNT_MODE_CUSTOMER = 0;
    const ACCOUNT_MODE_FAST_LOGIN = 1;

    const CUSTOMER_TYPE_PRIVATE = 'private';
    const CUSTOMER_TYPE_BUSINESS = 'business';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $number;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var boolean
     */
    protected $active;

    /**
     * @var string
     */
    protected $salutation;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $firstname;

    /**
     * @var string
     */
    protected $lastname;

    /**
     * @var Shop
     */
    protected $assignedShop;

    /**
     * @var Shop
     */
    protected $assignedLanguageShop;

    /**
     * @var int|null
     */
    protected $assignedShopId;

    /**
     * @var int|null
     */
    protected $assignedLanguageShopId;

    /**
     * @var \DateTime
     */
    protected $firstLogin;

    /**
     * @var \DateTime
     */
    protected $lastLogin;

    /**
     * @var \DateTime|null
     */
    protected $lockedUntil;

    /**
     * @var int
     */
    protected $failedLogins;

    /**
     * @var \DateTime|null
     */
    protected $birthday;

    /**
     * @var int
     */
    protected $accountMode = self::ACCOUNT_MODE_CUSTOMER;

    /**
     * @var string
     */
    protected $customerType = self::CUSTOMER_TYPE_PRIVATE;

    /**
     * @var string|null
     */
    protected $validation;

    /**
     * @var string|null
     */
    protected $confirmationKey;

    /**
     * @var bool
     */
    protected $orderedNewsletter = false;

    /**
     * @var bool
     */
    protected $isPartner = false;

    /**
     * @var string|null
     */
    protected $referer;

    /**
     * @var string
     */
    protected $internalComment;

    /**
     * @var Group
     */
    protected $customerGroup;

    /**
     * @var boolean
     */
    protected $hasNotifications;

    /**
     * @var PaymentInformation[]
     */
    protected $paymentInformation = [];

    /**
     * @var int
     */
    protected $defaultBillingAddressId;

    /**
     * @var int
     */
    protected $defaultShippingAddressId;

    /**
     * @var Address
     */
    protected $defaultBillingAddress;

    /**
     * @var Address
     */
    protected $defaultShippingAddress;

    /**
     * @var int
     */
    protected $presetPaymentServiceId;

    /**
     * @var int
     */
    protected $lastPaymentServiceId;

    /**
     * @var PaymentService
     */
    protected $presetPaymentService;

    /**
     * @var PaymentService
     */
    protected $lastPaymentService;
    #
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * @param string $salutation
     */
    public function setSalutation($salutation)
    {
        $this->salutation = $salutation;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return Shop
     */
    public function getAssignedShop()
    {
        return $this->assignedShop;
    }

    /**
     * @param Shop $assignedShop
     */
    public function setAssignedShop($assignedShop)
    {
        $this->assignedShop = $assignedShop;
    }

    /**
     * @return Shop
     */
    public function getAssignedLanguageShop()
    {
        return $this->assignedLanguageShop;
    }

    /**
     * @param Shop $assignedLanguageShop
     */
    public function setAssignedLanguageShop($assignedLanguageShop)
    {
        $this->assignedLanguageShop = $assignedLanguageShop;
    }

    /**
     * @return \DateTime
     */
    public function getFirstLogin()
    {
        return $this->firstLogin;
    }

    /**
     * @param \DateTime $firstLogin
     */
    public function setFirstLogin($firstLogin)
    {
        $this->firstLogin = $firstLogin;
    }

    /**
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * @param \DateTime $lastLogin
     */
    public function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;
    }

    /**
     * @return \DateTime|null
     */
    public function getLockedUntil()
    {
        return $this->lockedUntil;
    }

    /**
     * @param \DateTime|null $lockedUntil
     */
    public function setLockedUntil($lockedUntil)
    {
        $this->lockedUntil = $lockedUntil;
    }

    /**
     * @return int
     */
    public function getFailedLogins()
    {
        return $this->failedLogins;
    }

    /**
     * @param int $failedLogins
     */
    public function setFailedLogins($failedLogins)
    {
        $this->failedLogins = $failedLogins;
    }

    /**
     * @return \DateTime|null
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime|null $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * @return int
     */
    public function getAccountMode()
    {
        return $this->accountMode;
    }

    /**
     * @param int $accountMode
     */
    public function setAccountMode($accountMode)
    {
        $this->accountMode = $accountMode;
    }

    /**
     * @return string
     */
    public function getCustomerType()
    {
        return $this->customerType;
    }

    /**
     * @param string $customerType
     */
    public function setCustomerType($customerType)
    {
        $this->customerType = $customerType;
    }

    /**
     * @return null|string
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * @param null|string $validation
     */
    public function setValidation($validation)
    {
        $this->validation = $validation;
    }

    /**
     * @return null|string
     */
    public function getConfirmationKey()
    {
        return $this->confirmationKey;
    }

    /**
     * @param null|string $confirmationKey
     */
    public function setConfirmationKey($confirmationKey)
    {
        $this->confirmationKey = $confirmationKey;
    }

    /**
     * @return boolean
     */
    public function isOrderedNewsletter()
    {
        return $this->orderedNewsletter;
    }

    /**
     * @param boolean $orderedNewsletter
     */
    public function setOrderedNewsletter($orderedNewsletter)
    {
        $this->orderedNewsletter = $orderedNewsletter;
    }

    /**
     * @return boolean
     */
    public function isIsPartner()
    {
        return $this->isPartner;
    }

    /**
     * @param boolean $isPartner
     */
    public function setIsPartner($isPartner)
    {
        $this->isPartner = $isPartner;
    }

    /**
     * @return null|string
     */
    public function getReferer()
    {
        return $this->referer;
    }

    /**
     * @param null|string $referer
     */
    public function setReferer($referer)
    {
        $this->referer = $referer;
    }

    /**
     * @return string
     */
    public function getInternalComment()
    {
        return $this->internalComment;
    }

    /**
     * @param string $internalComment
     */
    public function setInternalComment($internalComment)
    {
        $this->internalComment = $internalComment;
    }

    /**
     * @return Group
     */
    public function getCustomerGroup()
    {
        return $this->customerGroup;
    }

    /**
     * @param Group $customerGroup
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->customerGroup = $customerGroup;
    }

    /**
     * @return boolean
     */
    public function isHasNotifications()
    {
        return $this->hasNotifications;
    }

    /**
     * @param boolean $hasNotifications
     */
    public function setHasNotifications($hasNotifications)
    {
        $this->hasNotifications = $hasNotifications;
    }

    /**
     * @return PaymentInformation[]
     */
    public function getPaymentInformation()
    {
        return $this->paymentInformation;
    }

    /**
     * @param PaymentInformation[] $paymentInformation
     */
    public function setPaymentInformation($paymentInformation)
    {
        $this->paymentInformation = $paymentInformation;
    }

    /**
     * @return int
     */
    public function getDefaultBillingAddressId()
    {
        return $this->defaultBillingAddressId;
    }

    /**
     * @param int $defaultBillingAddressId
     */
    public function setDefaultBillingAddressId($defaultBillingAddressId)
    {
        $this->defaultBillingAddressId = $defaultBillingAddressId;
    }

    /**
     * @return int
     */
    public function getDefaultShippingAddressId()
    {
        return $this->defaultShippingAddressId;
    }

    /**
     * @param int $defaultShippingAddressId
     */
    public function setDefaultShippingAddressId($defaultShippingAddressId)
    {
        $this->defaultShippingAddressId = $defaultShippingAddressId;
    }

    /**
     * @return Address
     */
    public function getDefaultBillingAddress()
    {
        return $this->defaultBillingAddress;
    }

    /**
     * @param Address $defaultBillingAddress
     */
    public function setDefaultBillingAddress($defaultBillingAddress)
    {
        $this->defaultBillingAddress = $defaultBillingAddress;
    }

    /**
     * @return Address
     */
    public function getDefaultShippingAddress()
    {
        return $this->defaultShippingAddress;
    }

    /**
     * @param Address $defaultShippingAddress
     */
    public function setDefaultShippingAddress($defaultShippingAddress)
    {
        $this->defaultShippingAddress = $defaultShippingAddress;
    }

    /**
     * @return PaymentService
     */
    public function getPresetPaymentService()
    {
        return $this->presetPaymentService;
    }

    /**
     * @param PaymentService $presetPaymentService
     */
    public function setPresetPaymentService($presetPaymentService)
    {
        $this->presetPaymentService = $presetPaymentService;
    }

    /**
     * @return PaymentService
     */
    public function getLastPaymentService()
    {
        return $this->lastPaymentService;
    }

    /**
     * @param PaymentService $lastPaymentService
     */
    public function setLastPaymentService($lastPaymentService)
    {
        $this->lastPaymentService = $lastPaymentService;
    }

    /**
     * @return int|null
     */
    public function getAssignedShopId()
    {
        return $this->assignedShopId;
    }

    /**
     * @param int|null $assignedShopId
     */
    public function setAssignedShopId($assignedShopId)
    {
        $this->assignedShopId = $assignedShopId;
    }

    /**
     * @return int|null
     */
    public function getAssignedLanguageShopId()
    {
        return $this->assignedLanguageShopId;
    }

    /**
     * @param int|null $assignedLanguageShopId
     */
    public function setAssignedLanguageShopId($assignedLanguageShopId)
    {
        $this->assignedLanguageShopId = $assignedLanguageShopId;
    }

    /**
     * @return int
     */
    public function getPresetPaymentServiceId()
    {
        return $this->presetPaymentServiceId;
    }

    /**
     * @param int $presetPaymentServiceId
     */
    public function setPresetPaymentServiceId($presetPaymentServiceId)
    {
        $this->presetPaymentServiceId = $presetPaymentServiceId;
    }

    /**
     * @return int
     */
    public function getLastPaymentServiceId()
    {
        return $this->lastPaymentServiceId;
    }

    /**
     * @param int $lastPaymentServiceId
     */
    public function setLastPaymentServiceId($lastPaymentServiceId)
    {
        $this->lastPaymentServiceId = $lastPaymentServiceId;
    }
}
