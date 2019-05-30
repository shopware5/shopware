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

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Components\Security\AttributeCleanerTrait;
use Shopware\Models\Attribute\CustomerAddress as CustomerAddressAttribute;
use Shopware\Models\Country\Country;
use Shopware\Models\Country\State;

/**
 * Shopware customer address model represents a single address of a customer.
 *
 * The Shopware customer address model represents a row of the s_user_addresses table.
 * One address has the follows associations:
 * <code>
 *   - Customer =>  Shopware\Models\Customer\Customer [1:n] [s_user]
 *   - Country =>  Shopware\Models\Customer\Country [1:n] [s_countries]
 *   - State =>  Shopware\Models\Country\State [1:n] [s_countries_states]
 * </code>
 * The s_user_addresses table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 *   - KEY `user_id` (`user_id`)
 *   - KEY `country_id` (`country_id`)
 *   - KEY `state_id` (`state_id`)
 * </code>
 * The table has the following constraints
 * <code>
 *   - CONSTRAINT `s_user_addresses_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `s_core_countries` (`id`) ON UPDATE CASCADE,
 *   - CONSTRAINT `s_user_addresses_ibfk_2` FOREIGN KEY (`state_id`) REFERENCES `s_core_countries_states` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
 *   - CONSTRAINT `s_user_addresses_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `s_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
 * </code>
 *
 * @ORM\Entity(repositoryClass="AddressRepository")
 * @ORM\Table(name="s_user_addresses")
 */
class Address extends ModelEntity
{
    /*
     * HTML Cleansing trait for different attributes in a class (implemented in setters)
     * @see \Shopware\Components\Security\AttributeCleanerTrait
     */
    use AttributeCleanerTrait;

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
    protected $id;

    /**
     * Contains the name of the address address company
     *
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=255, nullable=true)
     */
    protected $company;

    /**
     * Contains the department name of the address address company
     *
     * @var string
     *
     * @ORM\Column(name="department", type="string", length=35, nullable=true)
     */
    protected $department;

    /**
     * Contains the customer salutation (Mr, Ms, Company)
     *
     * @var string
     *
     * @ORM\Column(name="salutation", type="string", length=30, nullable=false)
     */
    protected $salutation = '';

    /**
     * Contains the first name of the address
     *
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=50, nullable=false)
     */
    protected $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=100, nullable=true)
     */
    protected $title;

    /**
     * Contains the last name of the address
     *
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=60, nullable=false)
     */
    protected $lastname;

    /**
     * Contains the street name of the address
     *
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=255, nullable=false)
     */
    protected $street;

    /**
     * Contains the zip code of the address
     *
     * @var string
     *
     * @ORM\Column(name="zipcode", type="string", length=50, nullable=false)
     */
    protected $zipcode;

    /**
     * Contains the city name of the address
     *
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=70, nullable=false)
     */
    protected $city;

    /**
     * Contains the phone number of the address
     *
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=40, nullable=true)
     */
    protected $phone;

    /**
     * Contains the vat id of the address
     *
     * @var string
     *
     * @ORM\Column(name="ustid", type="string", length=50, nullable=true)
     */
    protected $vatId;

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
     * Contains the id of the country.
     *
     * @var int
     *
     * @ORM\Column(name="country_id", type="integer", nullable=false)
     */
    protected $countryId;

    /**
     * Contains the id of the state.
     *
     * @var int
     *
     * @ORM\Column(name="state_id", type="integer", nullable=true)
     */
    protected $stateId;

    /**
     * OWNING SIDE
     * The customer property is the owning side of the association between customer and address.
     * The association is joined over the address user_id and the customer id
     *
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Customer\Customer")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $customer;

    /**
     * INVERSE SIDE
     *
     * @var CustomerAddressAttribute
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\CustomerAddress", mappedBy="customerAddress", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Country\Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     */
    protected $country;

    /**
     * @var State
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Country\State")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id")
     */
    protected $state;

    /**
     * @var array
     */
    protected $additional;

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
     */
    public function setCompany($company)
    {
        $this->company = $this->cleanup($company);
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
     */
    public function setDepartment($department)
    {
        $this->department = $this->cleanup($department);
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
     */
    public function setSalutation($salutation)
    {
        $this->salutation = $this->cleanup($salutation);
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
     * Setter function for the firstname column property.
     *
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $this->cleanup($firstname);
    }

    /**
     * Getter function for the firstname column property.
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Setter function for the lastname column property.
     *
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * Getter function for the lastname column property.
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Setter function for the street column property.
     *
     * @param string $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
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
     * Setter function for the zipcode column property.
     *
     * @param string $zipcode
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $this->cleanup($zipcode);
    }

    /**
     * Getter function for the zipcode column property.
     *
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Setter function for the city column property.
     *
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $this->cleanup($city);
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
     */
    public function setPhone($phone)
    {
        $this->phone = $this->cleanup($phone);
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
     */
    public function setVatId($vatId)
    {
        $this->vatId = $this->cleanup($vatId);
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
     * @return CustomerAddressAttribute|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param CustomerAddressAttribute|array|null $attribute
     *
     * @return Address
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, CustomerAddressAttribute::class, 'attribute', 'customerAddress');
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
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
     * @return Country|null
     */
    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry(Country $country)
    {
        $this->country = $country;
    }

    /**
     * @return State|null
     */
    public function getState()
    {
        return $this->state;
    }

    public function setState(State $state = null)
    {
        $this->state = $state;
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
