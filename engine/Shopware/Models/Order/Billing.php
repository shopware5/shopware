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
use Shopware\Components\Security\AttributeCleanerTrait;
use Shopware\Models\Attribute\OrderBilling as OrderBillingAttribute;
use Shopware\Models\Customer\Address;

/**
 * Shopware order billing model represents a single billing address of an order.
 *
 * The Shopware order billing model represents a row of the s_order_billingaddress table.
 * The billing model data set from the Shopware\Models\Order\Repository.
 * One billing address has the follows associations:
 * <code>
 *   - Order    =>  Shopware\Models\Order\Order [1:1] [s_order]
 *   - Customer =>  Shopware\Models\Customer\Customer [1:1] [s_user]
 * </code>
 * The s_order_billingaddress table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 *   - UNIQUE KEY `FOREIGN` (`userID`)
 *   - UNIQUE KEY `FOREIGN` (`orderID`)
 * </code>
 *
 * @ORM\Entity()
 * @ORM\Table(name="s_order_billingaddress")
 * @ORM\HasLifecycleCallbacks()
 */
class Billing extends ModelEntity
{
    /*
     * HTML Cleansing trait for different attributes in a class (implemented in setters)
     * @see \Shopware\Components\Security\AttributeCleanerTrait
     */
    use AttributeCleanerTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=100, nullable=true)
     */
    protected $title;

    /**
     * Contains the additional address line data
     *
     * @var string
     *
     * @ORM\Column(name="additional_address_line1", type="string", length=255, nullable=true)
     */
    protected $additionalAddressLine1;

    /**
     * Contains the additional address line data 2
     *
     * @var string
     *
     * @ORM\Column(name="additional_address_line2", type="string", length=255, nullable=true)
     */
    protected $additionalAddressLine2;

    /**
     * INVERSE SIDE
     *
     * @var OrderBillingAttribute
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\OrderBilling", mappedBy="orderBilling", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * The id property is an identifier property which means
     * doctrine associations can be defined over this field
     *
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * If of the associated customer. Used as foreign key for the
     * order - billing association.
     *
     * @var int
     *
     * @ORM\Column(name="orderID", type="integer", nullable=false)
     */
    private $orderId;

    /**
     * If of the associated customer. Used as foreign key for the
     * customer - billing association.
     *
     * @var int
     *
     * @ORM\Column(name="userID", type="integer", nullable=true)
     */
    private $customerId;

    /**
     * Contains the id of the country. Used for the billing - country association.
     *
     * @var int
     *
     * @ORM\Column(name="countryID", type="integer", nullable=false)
     */
    private $countryId = 0;

    /**
     * Contains the id of the state. Used for billing - state association.
     *
     * @var int
     *
     * @ORM\Column(name="stateID", type="integer", nullable=true)
     */
    private $stateId;

    /**
     * Contains the name of the billing address company
     *
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=255, nullable=false)
     */
    private $company = '';

    /**
     * Contains the department name of the billing address company
     *
     * @var string
     *
     * @ORM\Column(name="department", type="string", length=35, nullable=false)
     */
    private $department = '';

    /**
     * Contains the customer salutation (Mr, Ms, Company)
     *
     * @var string
     *
     * @ORM\Column(name="salutation", type="string", length=30, nullable=false)
     */
    private $salutation = '';

    /**
     * Contains the unique customer number
     *
     * @var string
     *
     * @ORM\Column(name="customernumber", type="string", length=30, nullable=true)
     */
    private $number = '';

    /**
     * Contains the first name of the billing address
     *
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=50, nullable=false)
     */
    private $firstName = '';

    /**
     * Contains the last name of the billing address
     *
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=60, nullable=false)
     */
    private $lastName = '';

    /**
     * Contains the street name of the billing address
     *
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=255, nullable=false)
     */
    private $street = '';

    /**
     * Contains the zip code of the billing address
     *
     * @var string
     *
     * @ORM\Column(name="zipcode", type="string", length=50, nullable=false)
     */
    private $zipCode = '';

    /**
     * Contains the city name of the billing address
     *
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=70, nullable=false)
     */
    private $city = '';

    /**
     * Contains the phone number of the billing address
     *
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=40, nullable=false)
     */
    private $phone = '';

    /**
     * Contains the vat id of the billing address
     *
     * @var string
     *
     * @ORM\Column(name="ustid", type="string", length=50, nullable=true)
     */
    private $vatId = '';

    /**
     * The customer property is the owning side of the association between customer and billing.
     * The association is joined over the billing userID and the customer id
     *
     * @var \Shopware\Models\Customer\Customer
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Customer\Customer")
     * @ORM\JoinColumn(name="userID", referencedColumnName="id")
     */
    private $customer;

    /**
     * The order property is the owning side of the association between order and billing.
     * The association is joined over the billing orderID and the order id
     *
     * @var \Shopware\Models\Order\Order
     *
     * @ORM\OneToOne(targetEntity="Order", inversedBy="billing")
     * @ORM\JoinColumn(name="orderID", referencedColumnName="id")
     */
    private $order;

    /**
     * @var \Shopware\Models\Country\Country
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Country\Country")
     * @ORM\JoinColumn(name="countryID", referencedColumnName="id")
     */
    private $country;

    /**
     * @var \Shopware\Models\Country\State
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Country\State")
     * @ORM\JoinColumn(name="stateID", referencedColumnName="id")
     */
    private $state;

    /**
     * Getter function for the unique id identifier property
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Setter function for the company column property
     *
     * @param string $company
     *
     * @return Billing
     */
    public function setCompany($company)
    {
        $this->company = $this->cleanup($company);

        return $this;
    }

    /**
     * Getter function for the company column property.
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Setter function for the department column property.
     *
     * @param string $department
     *
     * @return Billing
     */
    public function setDepartment($department)
    {
        $this->department = $this->cleanup($department);

        return $this;
    }

    /**
     * Getter function for the department column property.
     *
     * @return string
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * Setter function for the salutation column property.
     *
     * @param string $salutation
     *
     * @return Billing
     */
    public function setSalutation($salutation)
    {
        $this->salutation = $this->cleanup($salutation);

        return $this;
    }

    /**
     * Getter function for the salutation column property.
     *
     * @return string
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * Setter function for the customer number column property.
     *
     * @param string $number
     *
     * @return Billing
     */
    public function setNumber($number)
    {
        $this->number = $this->cleanup($number);

        return $this;
    }

    /**
     * Getter function for the customer number column property.
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Setter function for the firstName column property.
     *
     * @param string $firstName
     *
     * @return Billing
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $this->cleanup($firstName);

        return $this;
    }

    /**
     * Getter function for the firstName column property.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Setter function for the lastName column property.
     *
     * @param string $lastName
     *
     * @return Billing
     */
    public function setLastName($lastName)
    {
        $this->lastName = $this->cleanup($lastName);

        return $this;
    }

    /**
     * Getter function for the lastName column property.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Setter function for the street column property.
     *
     * @param string $street
     *
     * @return Billing
     */
    public function setStreet($street)
    {
        $this->street = $this->cleanup($street);

        return $this;
    }

    /**
     * Getter function for the street column property.
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Setter function for the zipCode column property.
     *
     * @param string $zipCode
     *
     * @return Billing
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $this->cleanup($zipCode);

        return $this;
    }

    /**
     * Getter function for the zipCode column property.
     *
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * Setter function for the city column property.
     *
     * @param string $city
     *
     * @return Billing
     */
    public function setCity($city)
    {
        $this->city = $this->cleanup($city);

        return $this;
    }

    /**
     * Getter function for the city column property.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Setter function for the phone column property.
     *
     * @param string $phone
     *
     * @return Billing
     */
    public function setPhone($phone)
    {
        $this->phone = $this->cleanup($phone);

        return $this;
    }

    /**
     * Getter function for the phone column property.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Setter function for the vatId column property.
     * The vatId will be saved in the ustId table field.
     *
     * @param string $vatId
     *
     * @return Billing
     */
    public function setVatId($vatId)
    {
        $this->vatId = $this->cleanup($vatId);

        return $this;
    }

    /**
     * Getter function for the vatId column property.
     * The vatId is saved in the ustId table field.
     *
     * @return string
     */
    public function getVatId()
    {
        return $this->vatId;
    }

    /**
     * Returns the instance of the Shopware\Models\Customer\Customer model which
     * contains all data about the customer. The association is defined over
     * the Customer.billing property (INVERSE SIDE) and the Billing.customer (OWNING SIDE) property.
     * The customer data is joined over the s_user.id field.
     *
     * @return \Shopware\Models\Customer\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Setter function for the customer association property which contains an instance of the Shopware\Models\Customer\Customer model which
     * contains all data about the customer. The association is defined over
     * the Customer.billing property (INVERSE SIDE) and the Billing.customer (OWNING SIDE) property.
     * The customer data is joined over the s_user.id field.
     *
     * @param \Shopware\Models\Customer\Customer $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return \Shopware\Models\Order\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param \Shopware\Models\Order\Order $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * Getter for the country association
     *
     * @return \Shopware\Models\Country\Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Setter for the country association
     *
     * @param \Shopware\Models\Country\Country $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * Setter for the state association
     *
     * @param \Shopware\Models\Country\State|null $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Getter for the state association
     *
     * @return \Shopware\Models\Country\State
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return OrderBillingAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param OrderBillingAttribute|array|null $attribute
     *
     * @return Billing
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, OrderBillingAttribute::class, 'attribute', 'orderBilling');
    }

    /**
     * Setter function for the setAdditionalAddressLine2 column property.
     *
     * @param string $additionalAddressLine2
     */
    public function setAdditionalAddressLine2($additionalAddressLine2)
    {
        $this->additionalAddressLine2 = $this->cleanup($additionalAddressLine2);
    }

    /**
     * Getter function for the getAdditionalAddressLine2 column property.
     *
     * @return string
     */
    public function getAdditionalAddressLine2()
    {
        return $this->additionalAddressLine2;
    }

    /**
     * Setter function for the setAdditionalAddressLine1 column property.
     *
     * @param string $additionalAddressLine1
     */
    public function setAdditionalAddressLine1($additionalAddressLine1)
    {
        $this->additionalAddressLine1 = $this->cleanup($additionalAddressLine1);
    }

    /**
     * Getter function for the getAdditionalAddressLine1 column property.
     *
     * @return string
     */
    public function getAdditionalAddressLine1()
    {
        return $this->additionalAddressLine1;
    }

    /**
     * Transfer values from the new address object
     */
    public function fromAddress(Address $address)
    {
        $this->setCompany((string) $address->getCompany());
        $this->setDepartment((string) $address->getDepartment());
        $this->setSalutation((string) $address->getSalutation());
        $this->setFirstName((string) $address->getFirstname());
        $this->setLastName((string) $address->getLastname());
        $this->setStreet((string) $address->getStreet());
        $this->setCity((string) $address->getCity());
        $this->setZipCode((string) $address->getZipcode());
        $this->setAdditionalAddressLine1((string) $address->getAdditionalAddressLine1());
        $this->setAdditionalAddressLine2((string) $address->getAdditionalAddressLine2());
        $this->setCountry($address->getCountry());
        $this->setPhone((string) $address->getPhone());
        $this->setVatId((string) $address->getVatId());
        $this->setTitle($address->getTitle());

        if ($address->getState()) {
            $this->setState($address->getState());
        } else {
            $this->setState(null);
        }

        $attributeData = Shopware()->Models()->toArray($address->getAttribute());
        $this->setAttribute($attributeData);
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
}
