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

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @deprecated Since 5.2 removed in 5.3 use \Shopware\Models\Customer\Address
 * Shopware customer billing model represents a single billing address of a customer.
 *
 * The Shopware customer billing model represents a row of the s_user_billingaddress table.
 * The billing model data set from the Shopware\Models\Customer\Repository.
 * One billing address has the follows associations:
 * <code>
 *   - Customer =>  Shopware\Models\Customer\Customer [1:1] [s_user]
 * </code>
 * The s_user_billingaddress table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 *   - UNIQUE KEY `FOREIGN` (`userID`)
 * </code>
 *
 * @ORM\Entity(repositoryClass="BillingRepository")
 * @ORM\Table(name="s_user_billingaddress")
 * @ORM\HasLifecycleCallbacks
 */
class Billing extends ModelEntity
{
    /**
     * The id property is an identifier property which means
     * doctrine associations can be defined over this field
     *
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * If of the associated customer. Used as foreign key for the
     * customer - billing association.
     *
     * @var int
     * @ORM\Column(name="userID", type="integer", nullable=false)
     */
    protected $customerId;

    /**
     * Contains the id of the country. Used for the billing - country association.
     *
     * @var int
     * @ORM\Column(name="countryID", type="integer", nullable=false)
     */
    protected $countryId = 0;

    /**
     * Contains the id of the state. Used for billing - state association.
     *
     * @var int
     * @ORM\Column(name="stateID", type="integer", nullable=true)
     */
    protected $stateId = null;

    /**
     * Contains the name of the billing address company
     *
     * @var string
     * @ORM\Column(name="company", type="string", length=255, nullable=false)
     */
    protected $company = '';

    /**
     * Contains the department name of the billing address company
     *
     * @var string
     * @ORM\Column(name="department", type="string", length=35, nullable=false)
     */
    protected $department = '';

    /**
     * Contains the customer salutation (Mr, Ms, Company)
     *
     * @var string
     * @ORM\Column(name="salutation", type="string", length=30, nullable=false)
     */
    protected $salutation = '';

    /**
     * @var string
     * @ORM\Column(name="title", type="string", length=100, nullable=true)
     */
    protected $title;

    /**
     * Contains the first name of the billing address
     *
     * @var string
     * @ORM\Column(name="firstname", type="string", length=50, nullable=false)
     */
    protected $firstName = '';

    /**
     * Contains the last name of the billing address
     *
     * @var string
     * @ORM\Column(name="lastname", type="string", length=60, nullable=false)
     */
    protected $lastName = '';

    /**
     * Contains the street name of the billing address
     *
     * @var string
     * @ORM\Column(name="street", type="string", length=255, nullable=false)
     */
    protected $street = '';

    /**
     * Contains the zip code of the billing address
     *
     * @var string
     * @ORM\Column(name="zipcode", type="string", length=50, nullable=false)
     */
    protected $zipCode = '';

    /**
     * Contains the city name of the billing address
     *
     * @var string
     * @ORM\Column(name="city", type="string", length=70, nullable=false)
     */
    protected $city = '';

    /**
     * Contains the phone number of the billing address
     *
     * @var string
     * @ORM\Column(name="phone", type="string", length=40, nullable=false)
     */
    protected $phone = '';

    /**
     * Contains the vat id of the billing address
     *
     * @var string
     * @ORM\Column(name="ustid", type="string", length=50, nullable=true)
     */
    protected $vatId = '';

    /**
     * Contains the additional address line data
     *
     * @var string
     * @ORM\Column(name="additional_address_line1", type="string", length=255, nullable=true)
     */
    protected $additionalAddressLine1 = null;

    /**
     * Contains the additional address line data 2
     *
     * @var string
     * @ORM\Column(name="additional_address_line2", type="string", length=255, nullable=true)
     */
    protected $additionalAddressLine2 = null;

    /**
     * OWNING SIDE
     * The customer property is the owning side of the association between customer and billing.
     * The association is joined over the billing userID and the customer id
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Customer\Customer", inversedBy="billing")
     * @ORM\JoinColumn(name="userID", referencedColumnName="id")
     *
     * @var \Shopware\Models\Customer\Customer
     */
    protected $customer;

    /**
     * INVERSE SIDE
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\CustomerBilling", mappedBy="customerBilling", orphanRemoval=true, cascade={"persist"})
     *
     * @var \Shopware\Models\Attribute\CustomerBilling
     */
    protected $attribute;

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
        $this->company = $company;

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
        $this->department = $department;

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
        $this->salutation = $salutation;

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
     * Setter function for the firstName column property.
     *
     * @param string $firstName
     *
     * @return Billing
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

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
        $this->lastName = $lastName;

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
        $this->street = $street;

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
        $this->zipCode = $zipCode;

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
        $this->city = $city;

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
        $this->phone = $phone;

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
     * Setter function for the countryId column property.
     *
     * @param int $countryId
     *
     * @return Billing
     */
    public function setCountryId($countryId)
    {
        $this->countryId = $countryId;

        return $this;
    }

    /**
     * Getter function for the countryId column property.
     *
     * @return int
     */
    public function getCountryId()
    {
        return $this->countryId;
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
        $this->vatId = $vatId;

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
     * @return \Shopware\Models\Attribute\CustomerBilling
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\CustomerBilling|array|null $attribute
     *
     * @return \Shopware\Models\Attribute\CustomerBilling
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\CustomerBilling', 'attribute', 'customerBilling');
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
     * @param \Shopware\Models\Customer\Customer|array|null $customer
     *
     * @return \Shopware\Models\Customer\Billing
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @param int $stateId
     */
    public function setStateId($stateId)
    {
        $this->stateId = $stateId;
    }

    /**
     * @return int
     */
    public function getStateId()
    {
        return $this->stateId;
    }

    /**
     * Setter function for the setAdditionalAddressLine2 column property.
     *
     * @param string $additionalAddressLine2
     */
    public function setAdditionalAddressLine2($additionalAddressLine2)
    {
        $this->additionalAddressLine2 = $additionalAddressLine2;
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
        $this->additionalAddressLine1 = $additionalAddressLine1;
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
     *
     * @param Address $address
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
        $this->setCountryId($address->getCountry()->getId());
        $this->setPhone((string) $address->getPhone());
        $this->setVatId((string) $address->getVatId());
        $this->setTitle($address->getTitle());
        if ($address->getState()) {
            $this->setStateId($address->getState()->getId());
        } else {
            $this->setStateId(null);
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
        $this->title = $title;
    }
}
