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

namespace   Shopware\Models\Customer;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Model\LazyFetchModelEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * Shopware customer model represents a single customer.
 *
 * The Shopware customer model represents a row of the s_user table.
 * The customer model data set from the Shopware\Models\Customer\Repository.
 * One customer has the follows associations:
 * <code>
 *   - Billing  =>  Shopware\Models\Customer\Billing    [1:1] [s_user_billingaddress]
 *   - Shipping =>  Shopware\Models\Customer\Shipping   [1:1] [s_user_shippingaddress]
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
 * @ORM\HasLifecycleCallbacks
 */
class Customer extends LazyFetchModelEntity
{
    const ACCOUNT_MODE_CUSTOMER = 0;
    const ACCOUNT_MODE_FAST_LOGIN = 1;

    const CUSTOMER_TYPE_PRIVATE = 'private';
    const CUSTOMER_TYPE_BUSINESS = 'business';

    /**
     * The id property is an identifier property which means
     * doctrine associations can be defined over this field
     *
     * @var integer $id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Contains the id of the customer default payment method.
     * Used for the payment association.
     * @var integer $paymentId
     * @ORM\Column(name="paymentID", type="integer", nullable=false)
     */
    private $paymentId = 0;

    /**
     * Key of the assigned customer group.
     *
     * @var string $groupKey
     * @ORM\Column(name="customergroup", type="string", length=15, nullable=false)
     */
    private $groupKey = '';

    /**
     * Id shop where the customer has registered.
     * @var integer $shopId
     * @ORM\Column(name="subshopID", type="integer", nullable=false)
     */
    private $shopId = 0;

    /**
     * Id of the price group, which the customer is assigned
     * @var integer $priceGroupId
     * @ORM\Column(name="pricegroupID", type="integer", nullable=true)
     */
    private $priceGroupId = null;

    /**
     * If this property is set, set password will be encoded with md5 on save.
     * To check the customer password use the hashPassword field.
     * @var string $password
     */
    private $password = '';

    /**
     * Tells which hash was used for password encryption
     * @var string
     * @ORM\Column(name="encoder", type="string", length=255, nullable=false)
     */
    private $encoderName = 'md5';

    /**
     * If this property is set, the password will not be encoded on save.
     * @var string $rawPassword
     */
    private $rawPassword = null;

    /**
     * Contains the md5 encoded password
     * @var string
     * @ORM\Column(name="password", type="string", length=100, nullable=false)
     */
    private $hashPassword = '';

    /**
     * Flag whether the customer account is activated.
     * @var integer $active
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = 0;

    /**
     * Contains the customer email address which is used to send the order confirmation mail
     * or the newsletter.
     * @var string $email
     * @Assert\Email(strict=false)
     * @Assert\NotBlank
     * @ORM\Column(name="email", type="string", length=70, nullable=false)
     */
    private $email;

    /**
     * Contains the date on which the customer account was created.
     * @var \DateTime $firstLogin
     * @ORM\Column(name="firstlogin", type="date", nullable=false)
     */
    private $firstLogin;

    /**
     * Contains the date on which the customer has logged in recently.
     * @var \DateTime $lastLogin
     * @ORM\Column(name="lastlogin", type="datetime", nullable=false)
     */
    private $lastLogin;

    /**
     * Flag whether the customer checks the "don't create a shop account" checkbox
     * @var integer $accountMode
     * @ORM\Column(name="accountmode", type="integer", nullable=false)
     */
    private $accountMode = 0;

    /**
     *
     * @var string $confirmationKey
     * @ORM\Column(name="confirmationkey", type="string", length=100, nullable=false)
     */
    private $confirmationKey = '';

    /**
     * Contains the session id of the last customer session.
     * @var string $sessionId
     * @ORM\Column(name="sessionID", type="string", length=255, nullable=false)
     */
    private $sessionId= '';

    /**
     * Flag whether the customer wishes to receive the store newsletter
     * @var integer $newsletter
     * @ORM\Column(name="newsletter", type="integer", nullable=false)
     */
    private $newsletter = 0;

    /**
     * @var string $validation
     * @ORM\Column(name="validation", type="string", length=255, nullable=false)
     */
    private $validation = '';

    /**
     * Flag whether the customer is a shop partner.
     * @var integer $affiliate
     * @ORM\Column(name="affiliate", type="integer", nullable=false)
     */
    private $affiliate = 0;

    /**
     * Flag whether a payment default has been filed
     * @var integer $paymentPreset
     * @ORM\Column(name="paymentpreset", type="integer", nullable=false)
     */
    private $paymentPreset = 0;

    /**
     * Id of the language sub shop
     *
     * @var string $languageId
     * @ORM\Column(name="language", type="string", length=10, nullable=false)
     */
    private $languageId = 1;

    /**
     * OWNING SIDE
     *
     * Used for the language subshop association
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="language", referencedColumnName="id")
     */
    private $languageSubShop;

    /**
     * @var string $referer
     * @ORM\Column(name="referer", type="string", length=255, nullable=false)
     */
    private $referer = '';

    /**
     * Contains the internal comment for the customer.
     *
     * @var string $internalComment
     * @ORM\Column(name="internalcomment", type="text", nullable=false)
     */
    private $internalComment = '';

    /**
     * Count of the failed customer logins
     *
     * @var integer $failedLogins
     * @ORM\Column(name="failedlogins", type="integer", nullable=false)
     */
    private $failedLogins = 0;

    /**
     * Contains the time, since the customer is logged into a session.
     * @var \DateTime $lockedUntil
     * @ORM\Column(name="lockedUntil", type="datetime", nullable=true)
     */
    private $lockedUntil = null;

    /**
     * @var string $salutation
     *
     * @Assert\NotBlank
     *
     * @ORM\Column(name="salutation", type="text", nullable=false)
     */
    private $salutation;

    /**
     * @var string $title
     * @ORM\Column(name="title", type="text", nullable=false)
     */
    private $title;

    /**
     * @var string $firstname
     *
     * @Assert\NotBlank
     *
     * @ORM\Column(name="firstname", type="text", nullable=false)
     */
    private $firstname;

    /**
     * Contains the unique customer number
     * @var string $number
     * @ORM\Column(name="customernumber", type="string", length=30, nullable=true)
     */
    protected $number = '';

    /**
     * @var string $lastname
     *
     * @Assert\NotBlank
     *
     * @ORM\Column(name="lastname", type="text", nullable=false)
     */
    private $lastname;

    /**
     * @var \DateTime $birthday
     * @ORM\Column(name="birthday", type="date", nullable=true)
     */
    private $birthday;

    /**
     * INVERSE SIDE
     * The billing property is the inverse side of the association between customer and billing.
     * The association is joined over the billing userID field and the id field of the customer
     *
     * @Assert\Valid
     *
     * @var \Shopware\Models\Customer\Billing
     * @ORM\OneToOne(targetEntity="Shopware\Models\Customer\Billing", mappedBy="customer", orphanRemoval=true, cascade={"persist"})
     */
    protected $billing;

    /**
     * INVERSE SIDE
     * The shipping property is the inverse side of the association between customer and shipping.
     * The association is joined over the shipping userID field and the id field of the customer.
     *
     * @Assert\Valid
     *
     * @var \Shopware\Models\Customer\Shipping
     * @ORM\OneToOne(targetEntity="Shopware\Models\Customer\Shipping", mappedBy="customer", orphanRemoval=true, cascade={"persist"})
     */
    protected $shipping;

    /**
     * OWNING SIDE
     * The group property is the owning side of the association between customer and customer group.
     * The association is joined over the group id field and the groupkey field of the customer.
     *
     * @var \Shopware\Models\Customer\Group $group
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Customer\Group", inversedBy="customers", cascade={"persist"})
     * @ORM\JoinColumn(name="customergroup", referencedColumnName="groupkey")
     */
    protected $group;

    /**
     * INVERSE SIDE
     * The orders property is the inverse side of the association between customer and orders.
     * The association is joined over the customer id field and the userID field of the order.
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Shopware\Models\Order\Order", mappedBy="customer")
     */
    protected $orders;

    /**
     * OWNING SIDE
     * @var \Shopware\Models\Shop\Shop $shop
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="subshopID", referencedColumnName="id")
     */
    protected $shop;

    /**
     * INVERSE SIDE
     * @var \Shopware\Models\Attribute\Customer
     * @Assert\Valid
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Customer", mappedBy="customer", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * OWNING SIDE
     * The price group property represents the owning side for the association between customer and customer price group.
     * The association is joined over the pricegroup id field and the pricegroupID field of the customer.
     *
     * @var \Shopware\Models\Customer\PriceGroup $priceGroup
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Customer\PriceGroup", inversedBy="customers")
     * @ORM\JoinColumn(name="pricegroupID", referencedColumnName="id")
     */
    protected $priceGroup;

    /**
     * INVERSE SIDE
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Notification", mappedBy="customer")
     * @var ArrayCollection
     */
    protected $notifications;

    /**
     * @var ArrayCollection $paymentInstances
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Payment\PaymentInstance", mappedBy="customer")
     */
    protected $paymentInstances;

    /**
     * @var ArrayCollection $paymentData
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Customer\PaymentData", mappedBy="customer", orphanRemoval=true, cascade={"persist"})
     */
    protected $paymentData;

    /**
     * OWNING SIDE
     *
     * @var \Shopware\Models\Customer\Address $defaultBillingAddress
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Customer\Address", inversedBy="customer")
     * @ORM\JoinColumn(name="default_billing_address_id", referencedColumnName="id")
     */
    protected $defaultBillingAddress;

    /**
     * OWNING SIDE
     *
     * @var \Shopware\Models\Customer\Address $defaultShippingAddress
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Customer\Address", inversedBy="customer")
     * @ORM\JoinColumn(name="default_shipping_address_id", referencedColumnName="id")
     */
    protected $defaultShippingAddress;

    /**
     * @var string
     */
    private $customerType;

    /**
     * @var array
     */
    protected $additional;

    /**
     * Class constructor. Initials the orders array and the date fields.
     */
    public function __construct()
    {
        $this->orders     = new ArrayCollection();
        $this->firstLogin = new \DateTime();
        $this->lastLogin  = new \DateTime();
        $this->notifications = new ArrayCollection();
        $this->paymentInstances = new ArrayCollection();
        $this->paymentData = new ArrayCollection();
    }

    /**
     * Returns the unique identifier "id"
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Setter method for the password column property which used for the customer login.
     *
     * @param string $password
     * @return Customer
     */
    public function setPassword($password)
    {
        // Force hashPassword to change with the password
        $this->hashPassword = null;
        $this->password     = $password;
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
     * This propertie will not be hashed!
     *
     * @param string $rawPassword
     */
    public function setRawPassword($rawPassword)
    {
        // Force hashPassword to change with the rawPassword
        $this->hashPassword = null;
        $this->rawPassword  = $rawPassword;
    }

    /**
     * Setter function for the email column property of the customer.
     *
     * @param string $email
     * @return Customer
     */
    public function setEmail($email)
    {
        $this->email = $email;
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
     * @param boolean $active
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
     * @return boolean
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
     * @param integer $accountMode
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
     * @return integer
     */
    public function getAccountMode()
    {
        return $this->accountMode;
    }

    /**
     * Setter function for the confirmationKey column property.
     *
     * @param string $confirmationKey
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
     * @param \DateTime|string $firstLogin
     * @return Customer
     */
    public function setFirstLogin($firstLogin)
    {
        if (!$firstLogin instanceof \DateTime) {
            $firstLogin = new \DateTime($firstLogin);
        }
        $this->firstLogin = $firstLogin;
        return $this;
    }

    /**
     * Getter function for the first login column property of the customer, which contains a DateTime object
     * with the date when the customer creates the account.
     *
     * @return \DateTime
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
     * @param \DateTime|string $lastLogin
     * @return Customer
     */
    public function setLastLogin($lastLogin)
    {
        if (!$lastLogin instanceof \DateTime) {
            $lastLogin = new \DateTime($lastLogin);
        }
        $this->lastLogin = $lastLogin;
        return $this;
    }

    /**
     * Getter function for the last login column property of the customer, which contains a DateTime object
     * with the date when the customer last logged in.
     *
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Setter function of the session id column property. Used to verify the login without the credentials.
     *
     * @param string $sessionId
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
     * @param integer $newsletter
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
     * @return integer
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Setter function of the validation column property.
     *
     * @param string $validation
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
     * @param integer $affiliate
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
     * @return integer
     */
    public function getAffiliate()
    {
        return $this->affiliate;
    }

    /**
     * Setter function for the paymentPreset column property, which is a flag whether a payment default has been filed
     *
     * @param integer $paymentPreset
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
     * @return integer
     */
    public function getPaymentPreset()
    {
        return $this->paymentPreset;
    }


    /**
     * Setter function for the referer column property.
     *
     * @param string $referer
     * @return Customer
     */
    public function setReferer($referer)
    {
        $this->referer = $referer;
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
     * @return Customer
     */
    public function setInternalComment($internalComment)
    {
        $this->internalComment = $internalComment;
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
     * @param integer $failedLogins
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
     * @return integer
     */
    public function getFailedLogins()
    {
        return $this->failedLogins;
    }

    /**
     * Setter function for the lockedUntil column property, which contains the time since the customer is logged into a session.
     * Expects a \DateTime object or a time string which will be converted to a \DateTime object.
     * @param string|\DateTime $lockedUntil
     * @return Customer
     */
    public function setLockedUntil($lockedUntil)
    {
        if (!$lockedUntil instanceof \DateTime) {
            $lockedUntil = new \DateTime($lockedUntil);
        }
        $this->lockedUntil = $lockedUntil;
        return $this;
    }

    /**
     * Getter function for the lockedUntil column property, which contains the time since the customer is logged into a session.
     *
     * @return \DateTime
     */
    public function getLockedUntil()
    {
        return $this->lockedUntil;
    }

    /**
     * Event listener method which fired when the model will be saved.
     * Initials the date time fields if this fields are null.
     * @ORM\PrePersist
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
            $this->encoderName     = Shopware()->PasswordEncoder()->getDefaultPasswordEncoderName();
            $this->hashPassword = Shopware()->PasswordEncoder()->encodePassword($this->password, $this->encoderName);
        }
    }

    /**
     * Event listener method which fired when the model will be updated.
     * @ORM\PreUpdate
     */
    public function onUpdate()
    {
        if (!empty($this->rawPassword)) {
            $this->hashPassword = $this->rawPassword;
        } elseif (!empty($this->password)) {
            $this->encoderName     = Shopware()->PasswordEncoder()->getDefaultPasswordEncoderName();
            $this->hashPassword = Shopware()->PasswordEncoder()->encodePassword($this->password, $this->encoderName);
        }
    }


    /**
     * @return \Shopware\Models\Attribute\Customer
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\Customer|array|null $attribute
     * @return \Shopware\Models\Attribute\Customer
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\Customer', 'attribute', 'customer');
    }

    /**
     * OWNING SIDE
     * Getter function for the shop association property which contains an instance of the Shopware\Models\Shop\Shop model.
     * The shop models contains all data about the shop, on which the customer has registered.
     * The shop association is only used on the customer side.
     *
     * of the association between customers and shop
     * @return \Shopware\Models\Shop\Shop
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
     * @param \Shopware\Models\Shop\Shop|array|null $shop
     * @return \Shopware\Models\Customer\Customer
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
     * @return ArrayCollection
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
     * @param ArrayCollection|array|null $orders
     * @return \Shopware\Models\Customer\Customer
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
     * @return \Shopware\Models\Customer\Group
     */
    public function getGroup()
    {
        return $this->fetchLazy($this->group, array('key' => $this->groupKey));
    }

    /**
     * Setter function for the group association property which contains an instance of the Shopware\Models\Customer\Group model which
     * contains all data about the customer group. The association is defined over
     * the Customer.group property (OWNING SIDE) and the Group.customers (INVERSE SIDE) property.
     * The group data is joined over the s_core_customergroup.id field.
     *
     * @param \Shopware\Models\Customer\Group|array|null $group
     * @return \Shopware\Models\Customer\Customer
     */
    public function setGroup($group)
    {
        return $this->setManyToOne($group, '\Shopware\Models\Customer\Group', 'group');
    }

    /**
     * Returns the instance of the Shopware\Models\Customer\Shipping model which
     * contains all data about the customer shipping address. The association is defined over
     * the Customer.shipping property (INVERSE SIDE) and the Shipping.customer (OWNING SIDE) property.
     * The shipping data is joined over the s_user_shippingaddress.userID field.
     *
     * @return \Shopware\Models\Customer\Shipping
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * Setter function for the shipping association property which contains an instance of the Shopware\Models\Customer\Shipping model which
     * contains all data about the customer shipping address. The association is defined over
     * the Customer.shipping property (INVERSE SIDE) and the Shipping.customer (OWNING SIDE) property.
     * The shipping data is joined over the s_user_shippingaddress.userID field.
     *
     * @param \Shopware\Models\Customer\Shipping|array|null $shipping
     * @return \Shopware\Models\Customer\Shipping
     */
    public function setShipping($shipping)
    {
        return $this->setOneToOne($shipping, '\Shopware\Models\Customer\Shipping', 'shipping', 'customer');
    }

    /**
     * Returns the instance of the Shopware\Models\Customer\Billing model which
     * contains all data about the customer billing address. The association is defined over
     * the Customer.billing property (INVERSE SIDE) and the Billing.customer (OWNING SIDE) property.
     * The billing data is joined over the s_user_billingaddress.userID field.
     *
     * @return \Shopware\Models\Customer\Billing
     */
    public function getBilling()
    {
        return $this->billing;
    }

    /**
     * Setter function for the billing association property which contains an instance of the Shopware\Models\Customer\Billing model which
     * contains all data about the customer billing address. The association is defined over
     * the Customer.billing property (INVERSE SIDE) and the Billing.customer (OWNING SIDE) property.
     * The billing data is joined over the s_user_billingaddress.userID field.
     *
     * @param \Shopware\Models\Customer\Billing|array|null $billing
     * @return \Shopware\Models\Customer\Billing
     */
    public function setBilling($billing)
    {
        return $this->setOneToOne($billing, '\Shopware\Models\Customer\Billing', 'billing', 'customer');
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
     * @return \Shopware\Models\Customer\PriceGroup
     */
    public function getPriceGroup()
    {
        return $this->priceGroup;
    }

    /**
     * @param \Shopware\Models\Customer\PriceGroup $priceGroup
     */
    public function setPriceGroup($priceGroup)
    {
        $this->priceGroup = $priceGroup;
    }

    public function setLanguageSubShop(\Shopware\Models\Shop\Shop $languageSubShop)
    {
        $this->languageSubShop = $languageSubShop;

        $subShop = ($languageSubShop->getMain()) ? $languageSubShop->getMain() : $languageSubShop;
        $this->setShop($subShop);
    }

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
     * @param ArrayCollection $paymentData
     */
    public function setPaymentData($paymentData)
    {
        $this->paymentData = $paymentData;
    }

    /**
     * @return ArrayCollection
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
     * @return Address
     */
    public function getDefaultBillingAddress()
    {
        return $this->defaultBillingAddress;
    }

    /**
     * @param Address $defaultBillingAddress
     */
    public function setDefaultBillingAddress(Address $defaultBillingAddress)
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
    public function setDefaultShippingAddress(Address $defaultShippingAddress)
    {
        $this->defaultShippingAddress = $defaultShippingAddress;
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
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime|string $birthday
     */
    public function setBirthday($birthday = null)
    {
        if ($birthday instanceof \DateTime) {
            $birthday = $birthday->format('Y-m-d');
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
}
