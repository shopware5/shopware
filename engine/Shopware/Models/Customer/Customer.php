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

namespace Shopware\Models\Customer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\LazyFetchModelEntity;
use Shopware\Components\Model\ModelEntity;
use Shopware\Components\Security\AttributeCleanerTrait;
use Shopware\Models\Attribute\Customer as CustomerAttribute;
use Shopware\Models\Shop\Shop;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Shopware customer model represents a single customer.
 *
 * The Shopware customer model represents a row of the s_user table.
 * The customer model data set from the Shopware\Models\Customer\Repository.
 * One customer has the follows associations:
 * <code>
 *   - Address  =>  Shopware\Models\Customer\Address    [1:1] [s_user_addresses]
 *   - Group    =>  Shopware\Models\Customer\Group      [n:1] [s_core_customergroups]
 *   - Shop     =>  Shopware\Models\Shop\Shop           [n:1] [s_core_shops]
 *   - Orders   =>  Shopware\Models\Order\Order         [1:n] [s_order]
 * </code>
 * The s_user table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 *   - KEY `email` (`email`)
 *   - KEY `sessionID` (`sessionID`)
 *   - KEY `firstlogin` (`firstlogin`)
 *   - KEY `lastlogin` (`lastlogin`)
 *   - KEY `pricegroupID` (`pricegroupID`)
 * </code>
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_user")
 * @ORM\HasLifecycleCallbacks()
 */
class Customer extends LazyFetchModelEntity
{
    /*
     * HTML Cleansing trait (Used to cleanup different properties in setters)
     * @see \Shopware\Components\Security\AttributeCleanerTrait
     */
    use AttributeCleanerTrait;

    const ACCOUNT_MODE_CUSTOMER = 0;
    const ACCOUNT_MODE_FAST_LOGIN = 1;

    const CUSTOMER_TYPE_PRIVATE = 'private';
    const CUSTOMER_TYPE_BUSINESS = 'business';

    /**
     * Contains the unique customer number
     *
     * @var string
     *
     * @ORM\Column(name="customernumber", type="string", length=30, nullable=true)
     */
    protected $number = '';

    /**
     * OWNING SIDE
     * The group property is the owning side of the association between customer and customer group.
     * The association is joined over the group id field and the groupkey field of the customer.
     *
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Customer\Group", inversedBy="customers", cascade={"persist"})
     * @ORM\JoinColumn(name="customergroup", referencedColumnName="groupkey")
     */
    protected $group;

    /**
     * INVERSE SIDE
     * The orders property is the inverse side of the association between customer and orders.
     * The association is joined over the customer id field and the userID field of the order.
     *
     * @var ArrayCollection<\Shopware\Models\Order\Order>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Order\Order", mappedBy="customer")
     */
    protected $orders;

    /**
     * OWNING SIDE
     *
     * @var Shop
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="subshopID", referencedColumnName="id")
     */
    protected $shop;

    /**
     * INVERSE SIDE
     *
     * @var CustomerAttribute
     *
     * @Assert\Valid()
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Customer", mappedBy="customer", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * OWNING SIDE
     * The price group property represents the owning side for the association between customer and customer price group.
     * The association is joined over the pricegroup id field and the pricegroupID field of the customer.
     *
     * @var PriceGroup
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Customer\PriceGroup", inversedBy="customers")
     * @ORM\JoinColumn(name="pricegroupID", referencedColumnName="id")
     */
    protected $priceGroup;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<\Shopware\Models\Article\Notification>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Notification", mappedBy="customer")
     */
    protected $notifications;

    /**
     * @var ArrayCollection<\Shopware\Models\Payment\PaymentInstance>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Payment\PaymentInstance", mappedBy="customer")
     */
    protected $paymentInstances;

    /**
     * @var ArrayCollection<\Shopware\Models\Customer\PaymentData>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Customer\PaymentData", mappedBy="customer", orphanRemoval=true, cascade={"persist"})
     */
    protected $paymentData;

    /**
     * OWNING SIDE
     *
     * @var \Shopware\Models\Customer\Address
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Customer\Address", inversedBy="customer")
     * @ORM\JoinColumn(name="default_billing_address_id", referencedColumnName="id")
     */
    protected $defaultBillingAddress;

    /**
     * OWNING SIDE
     *
     * @var \Shopware\Models\Customer\Address
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Customer\Address", inversedBy="customer")
     * @ORM\JoinColumn(name="default_shipping_address_id", referencedColumnName="id")
     */
    protected $defaultShippingAddress;

    /**
     * @var array
     */
    protected $additional;

    /**
     * The id property is an identifier property which means
     * doctrine associations can be defined over this field
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Time of the last modification of the customer
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="changed", type="datetime", nullable=false)
     */
    private $changed;

    /**
     * Contains the id of the customer default payment method.
     * Used for the payment association.
     *
     * @var int
     *
     * @ORM\Column(name="paymentID", type="integer", nullable=false)
     */
    private $paymentId = 0;

    /**
     * Key of the assigned customer group.
     *
     * @var string
     *
     * @ORM\Column(name="customergroup", type="string", length=15, nullable=false)
     */
    private $groupKey = '';

    /**
     * Id shop where the customer has registered.
     *
     * @var int
     *
     * @ORM\Column(name="subshopID", type="integer", nullable=false)
     */
    private $shopId = 0;

    /**
     * Id of the price group, which the customer is assigned
     *
     * @var int
     *
     * @ORM\Column(name="pricegroupID", type="integer", nullable=true)
     */
    private $priceGroupId;

    /**
     * If this property is set, set password will be encoded with md5 on save.
     * To check the customer password use the hashPassword field.
     *
     * @var string
     */
    private $password = '';

    /**
     * Tells which hash was used for password encryption
     *
     * @var string
     *
     * @ORM\Column(name="encoder", type="string", length=255, nullable=false)
     */
    private $encoderName = 'md5';

    /**
     * If this property is set, the password will not be encoded on save.
     *
     * @var string
     */
    private $rawPassword;

    /**
     * Contains the md5 encoded password
     *
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=1024, nullable=false)
     */
    private $hashPassword = '';

    /**
     * Flag whether the customer account is activated.
     *
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = 0;

    /**
     * Contains the customer email address which is used to send the order confirmation mail
     * or the newsletter.
     *
     * @var string
     *
     * @Assert\Email(strict=false)
     * @Assert\NotBlank()
     * @ORM\Column(name="email", type="string", length=70, nullable=false)
     */
    private $email;

    /**
     * Contains the date on which the customer account was created.
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="firstlogin", type="date", nullable=false)
     */
    private $firstLogin;

    /**
     * Contains the date on which the customer has logged in recently.
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="lastlogin", type="datetime", nullable=false)
     */
    private $lastLogin;

    /**
     * Flag whether the customer checks the "don't create a shop account" checkbox
     *
     * @var int
     *
     * @ORM\Column(name="accountmode", type="integer", nullable=false)
     */
    private $accountMode = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="confirmationkey", type="string", length=100, nullable=false)
     */
    private $confirmationKey = '';

    /**
     * Contains the session id of the last customer session.
     *
     * @var string
     *
     * @ORM\Column(name="sessionID", type="string", length=255, nullable=false)
     */
    private $sessionId = '';

    /**
     * Flag whether the customer wishes to receive the store newsletter
     *
     * @var int
     *
     * @ORM\Column(name="newsletter", type="integer", nullable=false)
     */
    private $newsletter = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="validation", type="string", length=255, nullable=false)
     */
    private $validation = '';

    /**
     * Flag whether the customer is a shop partner.
     *
     * @var int
     *
     * @ORM\Column(name="affiliate", type="integer", nullable=false)
     */
    private $affiliate = 0;

    /**
     * Flag whether a payment default has been filed
     *
     * @var int
     *
     * @ORM\Column(name="paymentpreset", type="integer", nullable=false)
     */
    private $paymentPreset = 0;

    /**
     * Id of the language sub shop
     *
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=10, nullable=false)
     */
    private $languageId = 1;

    /**
     * OWNING SIDE
     *
     * Used for the language subshop association
     *
     * @var Shop
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="language", referencedColumnName="id")
     */
    private $languageSubShop;

    /**
     * @var string
     *
     * @ORM\Column(name="referer", type="string", length=255, nullable=false)
     */
    private $referer = '';

    /**
     * Contains the internal comment for the customer.
     *
     * @var string
     *
     * @ORM\Column(name="internalcomment", type="text", nullable=false)
     */
    private $internalComment = '';

    /**
     * Count of the failed customer logins
     *
     * @var int
     *
     * @ORM\Column(name="failedlogins", type="integer", nullable=false)
     */
    private $failedLogins = 0;

    /**
     * Contains the time, since the customer is logged into a session.
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="lockedUntil", type="datetime", nullable=true)
     */
    private $lockedUntil;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="salutation", type="text", nullable=false)
     */
    private $salutation;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="text", nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="firstname", type="text", nullable=false)
     */
    private $firstname;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="lastname", type="text", nullable=false)
     */
    private $lastname;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="birthday", type="date", nullable=true)
     */
    private $birthday;

    /**
     * @var bool
     *
     * @ORM\Column(name="doubleOptinRegister", type="boolean", nullable=false)
     */
    private $doubleOptinRegister;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="doubleOptinEmailSentDate", type="datetime", nullable=true)
     */
    private $doubleOptinEmailSentDate;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="doubleOptinConfirmDate", type="datetime", nullable=true)
     */
    private $doubleOptinConfirmDate;

    /**
     * @var string
     */
    private $customerType;

    /**
     * Contains the date on which the customer account last changed the password
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="password_change_date", type="datetime", nullable=false)
     */
    private $passwordChangeDate;

    /**
     * Contains the ID of the opt-in entry, if any available
     *
     * @var int
     *
     * @ORM\Column(name="register_opt_in_id", type="integer", nullable=true)
     */
    private $registerOptInId;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->firstLogin = new \DateTime();
        $this->lastLogin = new \DateTime();
        $this->passwordChangeDate = new \DateTime();
        $this->notifications = new ArrayCollection();
        $this->paymentInstances = new ArrayCollection();
        $this->paymentData = new ArrayCollection();
    }

    /**
     * Returns the unique identifier "id"
     *
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
     * Setter method for the password column property which used for the customer login.
     *
     * @param string $password
     *
     * @return Customer
     */
    public function setPassword($password)
    {
        // Force hashPassword to change with the password
        $this->hashPassword = null;
        $this->password = $password;

        return $this;
    }

    /**
     * Getter method for the password column property which used for the customer login.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->hashPassword;
    }

    /**
     * Setter method for the rawPassword column property which used for the customer login.
     * This property will not be hashed!
     *
     * @param string $rawPassword
     */
    public function setRawPassword($rawPassword)
    {
        // Force hashPassword to change with the rawPassword
        $this->hashPassword = null;
        $this->rawPassword = $rawPassword;
    }

    /**
     * Setter function for the email column property of the customer.
     *
     * @param string $email
     *
     * @return Customer
     */
    public function setEmail($email)
    {
        $this->email = $this->cleanup($email);

        return $this;
    }

    /**
     * Getter function for the email column property of the customer.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Setter function for the active column property which is a flag whether the customer account is activated.
     *
     * @param bool $active
     *
     * @return Customer
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Getter function for the active column property which is a flag whether the customer account is activated.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Setter function for the accountMode column property which is a flag whether the customer has activated
     * the checkbox "don't create customer account".<br>
     * 0 => normal account ("don't create customer account" wasn't checked)<br>
     * 1 => hidden account ("don't create customer account" was checked)
     *
     * @param int $accountMode
     *
     * @return Customer
     */
    public function setAccountMode($accountMode)
    {
        $this->accountMode = $accountMode;

        return $this;
    }

    /**
     * Getter function for the accountMode column property which is a flag whether the customer has activated
     * the checkbox "don't create customer account".<br>
     * 0 => normal account ("don't create customer account" wasn't checked)<br>
     * 1 => hidden account ("don't create customer account" was checked)
     *
     * @return int
     */
    public function getAccountMode()
    {
        return $this->accountMode;
    }

    /**
     * Setter function for the confirmationKey column property.
     *
     * @param string $confirmationKey
     *
     * @return Customer
     */
    public function setConfirmationKey($confirmationKey)
    {
        $this->confirmationKey = $confirmationKey;

        return $this;
    }

    /**
     * Getter function for the confirmationKey column property.
     *
     * @return string
     */
    public function getConfirmationKey()
    {
        return $this->confirmationKey;
    }

    /**
     * Setter function for the first login column property of the customer, which contains a DateTime object
     * with the date when the customer creates the account. The parameter can be a DateTime object
     * or a string with the date. If a string is passed, the string converts to an DateTime object.
     *
     * @param \DateTimeInterface|string $firstLogin
     *
     * @return Customer
     */
    public function setFirstLogin($firstLogin)
    {
        if (!$firstLogin instanceof \DateTimeInterface) {
            $firstLogin = new \DateTime($firstLogin);
        }
        $this->firstLogin = $firstLogin;

        return $this;
    }

    /**
     * Getter function for the first login column property of the customer, which contains a DateTime object
     * with the date when the customer creates the account.
     *
     * @return \DateTimeInterface
     */
    public function getFirstLogin()
    {
        return $this->firstLogin;
    }

    /**
     * Setter function for the last login column property of the customer, which contains a DateTime object
     * with the date when the customer last logged in. The parameter can be a DateTime object
     * or a string with the date. If a string is passed, the string converts to an DateTime object.
     *
     * @param \DateTimeInterface|string $lastLogin
     *
     * @return Customer
     */
    public function setLastLogin($lastLogin)
    {
        if (!$lastLogin instanceof \DateTimeInterface) {
            $lastLogin = new \DateTime($lastLogin);
        }
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * Getter function for the last login column property of the customer, which contains a DateTime object
     * with the date when the customer last logged in.
     *
     * @return \DateTimeInterface
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Setter function of the session id column property. Used to verify the login without the credentials.
     *
     * @param string $sessionId
     *
     * @return Customer
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * Getter function of the session id column property. Used to verify the login without the credentials. <br>
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Setter function of the newsletter column property, which is a flag whether the customer wants to receive the newsletter.
     * 0 => Customer don't want to receive the newsletter
     * 1 => Customer want to receive the newsletter
     *
     * @param int $newsletter
     *
     * @return Customer
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /**
     * Getter function of the newsletter column property, which is a flag whether the customer wants to receive the newsletter.
     * 0 => Customer doesn't want to receive the newsletter
     * 1 => Customer wants to receive the newsletter
     *
     * @return int
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Setter function of the validation column property.
     *
     * @param string $validation
     *
     * @return Customer
     */
    public function setValidation($validation)
    {
        $this->validation = $validation;

        return $this;
    }

    /**
     * Getter function of the validation column property.
     *
     * @return string
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * Setter function for the affiliate column property, which is a flag whether the customer is a shop partner.
     * 0 => Customer isn't a shop partner
     * 1 => Customer is a shop partner
     *
     * @param int $affiliate
     *
     * @return Customer
     */
    public function setAffiliate($affiliate)
    {
        $this->affiliate = $affiliate;

        return $this;
    }

    /**
     * Getter function for the affiliate column property, which is a flag whether the customer is a shop partner.
     * 0 => Customer isn't a shop partner
     * 1 => Customer is a shop partner
     *
     * @return int
     */
    public function getAffiliate()
    {
        return $this->affiliate;
    }

    /**
     * Setter function for the paymentPreset column property, which is a flag whether a payment default has been filed
     *
     * @param int $paymentPreset
     *
     * @return Customer
     */
    public function setPaymentPreset($paymentPreset)
    {
        $this->paymentPreset = $paymentPreset;

        return $this;
    }

    /**
     * Getter function for the paymentPreset column property, which is a flag whether a payment default has been filed
     *
     * @return int
     */
    public function getPaymentPreset()
    {
        return $this->paymentPreset;
    }

    /**
     * Setter function for the referer column property.
     *
     * @param string $referer
     *
     * @return Customer
     */
    public function setReferer($referer)
    {
        $this->referer = $this->cleanup($referer);

        return $this;
    }

    /**
     * Getter function for the referer column property.
     *
     * @return string
     */
    public function getReferer()
    {
        return $this->referer;
    }

    /**
     * Setter function for the internalComment column property.
     *
     * @param string $internalComment
     *
     * @return Customer
     */
    public function setInternalComment($internalComment)
    {
        $this->internalComment = $this->cleanup($internalComment);

        return $this;
    }

    /**
     * Getter function for the internalComment column property.
     *
     * @return string
     */
    public function getInternalComment()
    {
        return $this->internalComment;
    }

    /**
     * Setter function for the failedLogins column property.
     *
     * @param int $failedLogins
     *
     * @return Customer
     */
    public function setFailedLogins($failedLogins)
    {
        $this->failedLogins = $failedLogins;

        return $this;
    }

    /**
     * Getter function for the failedLogins column property.
     *
     * @return int
     */
    public function getFailedLogins()
    {
        return $this->failedLogins;
    }

    /**
     * Setter function for the lockedUntil column property, which contains the time since the customer is logged into a session.
     * Expects a \DateTimeInterface object or a time string which will be converted to a \DateTime object.
     *
     * @param string|\DateTimeInterface $lockedUntil
     *
     * @return Customer
     */
    public function setLockedUntil($lockedUntil)
    {
        if (!$lockedUntil instanceof \DateTimeInterface) {
            $lockedUntil = new \DateTime($lockedUntil);
        }
        $this->lockedUntil = $lockedUntil;

        return $this;
    }

    /**
     * Getter function for the lockedUntil column property, which contains the time since the customer is logged into a session.
     *
     * @return \DateTimeInterface
     */
    public function getLockedUntil()
    {
        return $this->lockedUntil;
    }

    /**
     * Event listener method which is fired when the model is saved.
     * This method will also initialize the date time fields if these fields are null.
     *
     * @ORM\PrePersist()
     *
     * @throws \LogicException (See AttributeCleanerTrait)
     */
    public function onSave()
    {
        if ($this->firstLogin === null) {
            $this->firstLogin = new \DateTime();
        }
        if ($this->lastLogin === null) {
            $this->lastLogin = new \DateTime();
        }

        if (!empty($this->rawPassword)) {
            $this->hashPassword = $this->rawPassword;
        } elseif (!empty($this->password)) {
            $this->encoderName = Shopware()->PasswordEncoder()->getDefaultPasswordEncoderName();
            $this->hashPassword = Shopware()->PasswordEncoder()->encodePassword($this->password, $this->encoderName);
        }
    }

    /**
     * Event listener method which is fired when the model is updated.
     *
     * @ORM\PreUpdate()
     *
     * @throws \LogicException (See AttributeCleanerTrait)
     */
    public function onUpdate()
    {
        if (!empty($this->rawPassword)) {
            $this->hashPassword = $this->rawPassword;
        } elseif (!empty($this->password)) {
            $this->encoderName = Shopware()->PasswordEncoder()->getDefaultPasswordEncoderName();
            $this->hashPassword = Shopware()->PasswordEncoder()->encodePassword($this->password, $this->encoderName);
        }

        $changeSet = Shopware()->Models()->getUnitOfWork()->getEntityChangeSet($this);

        $passwordChanged = isset($changeSet['hashPassword']) && $changeSet['hashPassword'][0] !== $changeSet['hashPassword'][1];

        if ($passwordChanged) {
            $this->passwordChangeDate = new \DateTime();
        }
    }

    /**
     * @return CustomerAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param CustomerAttribute|array|null $attribute
     *
     * @return Customer
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, CustomerAttribute::class, 'attribute', 'customer');
    }

    /**
     * OWNING SIDE
     * Getter function for the shop association property which contains an instance of the Shopware\Models\Shop\Shop model.
     * The shop models contains all data about the shop, on which the customer has registered.
     * The shop association is only used on the customer side.
     *
     * of the association between customers and shop
     *
     * @return Shop
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * Setter function for the shop association property which contains an instance of the Shopware\Models\Shop\Shop model.
     * The shop models contains all data about the shop, on which the customer has registered.
     * The shop association is only used on the customer side.
     *
     * @param Shop|array|null $shop
     *
     * @return Customer
     */
    public function setShop($shop)
    {
        $this->shop = $shop;

        return $this;
    }

    /**
     * Returns an array collection of Shopware\Models\Order\Order model instances, which
     * contains all data about the a single customer order. The association is defined over
     * the Customer.orders property (INVERSE SIDE) and the Order.customer (OWNING SIDE) property.
     * The order data is joined over the s_order.userID field.
     *
     * @return ArrayCollection<\Shopware\Models\Order\Order>
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * Setter function for the orders association property which contains many instances of the Shopware\Models\Order\Order model which
     * contains all data about the a single customer order. The association is defined over
     * the Customer.orders property (INVERSE SIDE) and the Order.customer (OWNING SIDE) property.
     * The order data is joined over the s_order.userID field.
     *
     * @param ArrayCollection<\Shopware\Models\Order\Order>|null $orders
     *
     * @return Customer
     */
    public function setOrders($orders)
    {
        $this->orders = $orders;

        return $this;
    }

    /**
     * OWNING SIDE
     * Returns the instance of the Shopware\Models\Customer\Group model which
     * contains all data about the customer group. The association is defined over
     * the Customer.group property (OWNING SIDE) and the Group.customers (INVERSE SIDE) property.
     * The group data is joined over the s_core_customergroup.id field.
     *
     * of the association between customers and group
     *
     * @return Group|null
     */
    public function getGroup()
    {
        /** @var Group|null $return */
        $return = $this->fetchLazy($this->group, ['key' => $this->groupKey]);

        return $return;
    }

    /**
     * Setter function for the group association property which contains an instance of the Shopware\Models\Customer\Group model which
     * contains all data about the customer group. The association is defined over
     * the Customer.group property (OWNING SIDE) and the Group.customers (INVERSE SIDE) property.
     * The group data is joined over the s_core_customergroup.id field.
     *
     * @param Group|array|null $group
     *
     * @return Customer
     */
    public function setGroup($group)
    {
        return $this->setManyToOne($group, Group::class, 'group');
    }

    /**
     * @return int
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * @param int $paymentId
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;
    }

    /**
     * @return PriceGroup
     */
    public function getPriceGroup()
    {
        return $this->priceGroup;
    }

    /**
     * @param PriceGroup $priceGroup
     */
    public function setPriceGroup($priceGroup)
    {
        $this->priceGroup = $priceGroup;
    }

    public function setLanguageSubShop(Shop $languageSubShop)
    {
        $this->languageSubShop = $languageSubShop;

        $subShop = $languageSubShop->getMain() ?: $languageSubShop;
        $this->setShop($subShop);
    }

    /**
     * @return Shop
     */
    public function getLanguageSubShop()
    {
        return $this->languageSubShop;
    }

    /**
     * @return string
     */
    public function getLanguageId()
    {
        return $this->languageId;
    }

    /**
     * @param ArrayCollection<\Shopware\Models\Payment\PaymentInstance> $paymentInstances
     */
    public function setPaymentInstances($paymentInstances)
    {
        $this->paymentInstances = $paymentInstances;
    }

    /**
     * @return ArrayCollection<\Shopware\Models\Payment\PaymentInstance>
     */
    public function getPaymentInstances()
    {
        return $this->paymentInstances;
    }

    /**
     * @param ArrayCollection<\Shopware\Models\Customer\PaymentData> $paymentData
     */
    public function setPaymentData($paymentData)
    {
        $this->paymentData = $paymentData;
    }

    /**
     * @return ArrayCollection<\Shopware\Models\Customer\PaymentData>
     */
    public function getPaymentData()
    {
        return $this->paymentData;
    }

    /**
     * @param \Shopware\Models\Customer\PaymentData $paymentData
     */
    public function addPaymentData(PaymentData $paymentData)
    {
        $paymentData->setCustomer($this);

        $this->paymentData[] = $paymentData;
    }

    /**
     * @return string
     */
    public function getGroupKey()
    {
        return $this->groupKey;
    }

    /**
     * @return Address|null
     */
    public function getDefaultBillingAddress()
    {
        return $this->defaultBillingAddress;
    }

    /**
     * @param Address $defaultBillingAddress
     *
     * @return ModelEntity
     */
    public function setDefaultBillingAddress($defaultBillingAddress)
    {
        return $this->setOneToOne($defaultBillingAddress, Address::class, 'defaultBillingAddress', 'customer');
    }

    /**
     * @return Address|null
     */
    public function getDefaultShippingAddress()
    {
        return $this->defaultShippingAddress;
    }

    /**
     * @param Address $defaultShippingAddress
     *
     * @return ModelEntity
     */
    public function setDefaultShippingAddress($defaultShippingAddress)
    {
        return $this->setOneToOne($defaultShippingAddress, Address::class, 'defaultShippingAddress', 'customer');
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
        $this->salutation = $this->cleanup($salutation);
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
        $this->title = $this->cleanup($title);
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
        $this->firstname = $this->cleanup($firstname);
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
        $this->lastname = $this->cleanup($lastname);
    }

    /**
     * @return \DateTimeInterface
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param \DateTimeInterface|string|null $birthday
     */
    public function setBirthday($birthday = null)
    {
        if (!$birthday instanceof \DateTimeInterface && $birthday !== null) {
            $birthday = new \DateTime($birthday);
        }

        $this->birthday = $birthday;
    }

    /**
     * @return string
     */
    public function getEncoderName()
    {
        return $this->encoderName;
    }

    /**
     * @param string $encoderName
     */
    public function setEncoderName($encoderName)
    {
        $this->encoderName = $encoderName;
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
     * @return array
     */
    public function getAdditional()
    {
        return $this->additional;
    }

    /**
     * @param array $additional
     */
    public function setAdditional($additional)
    {
        $this->additional = $additional;
    }

    /**
     * @return bool
     */
    public function getDoubleOptinRegister()
    {
        return $this->doubleOptinRegister;
    }

    /**
     * @param bool $doubleOptinRegister
     */
    public function setDoubleOptinRegister($doubleOptinRegister)
    {
        $this->doubleOptinRegister = $doubleOptinRegister;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDoubleOptinEmailSentDate()
    {
        return $this->doubleOptinEmailSentDate;
    }

    /**
     * @param \DateTimeInterface $doubleOptinEmailSentDate
     */
    public function setDoubleOptinEmailSentDate($doubleOptinEmailSentDate)
    {
        $this->doubleOptinEmailSentDate = $doubleOptinEmailSentDate;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDoubleOptinConfirmDate()
    {
        return $this->doubleOptinConfirmDate;
    }

    /**
     * @param \DateTimeInterface|null $doubleOptinConfirmDate
     */
    public function setDoubleOptinConfirmDate($doubleOptinConfirmDate)
    {
        $this->doubleOptinConfirmDate = $doubleOptinConfirmDate;
    }

    public function getPasswordChangeDate(): \DateTimeInterface
    {
        return $this->passwordChangeDate;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updateChangedTimestamp()
    {
        $this->changed = new \DateTime();
    }

    public function getRegisterOptInId(): int
    {
        return $this->registerOptInId;
    }

    /**
     * @param int $registerOptInId
     */
    public function setRegisterOptInId(int $registerOptInId = null): void
    {
        $this->registerOptInId = $registerOptInId;
    }
}
