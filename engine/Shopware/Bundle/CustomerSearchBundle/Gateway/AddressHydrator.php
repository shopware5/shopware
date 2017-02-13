<?php

namespace Shopware\Bundle\CustomerSearchBundle\Gateway;

use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\AttributeHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\CountryHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\Hydrator;

class AddressHydrator extends Hydrator
{
    /**
     * @var CountryHydrator
     */
    private $countryHydrator;

    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    /**
     * @param CountryHydrator $countryHydrator
     * @param AttributeHydrator $attributeHydrator
     */
    public function __construct(CountryHydrator $countryHydrator, AttributeHydrator $attributeHydrator)
    {
        $this->countryHydrator = $countryHydrator;
        $this->attributeHydrator = $attributeHydrator;
    }

    /**
     * @param array $data
     * @return AddressStruct
     */
    public function hydrate(array $data)
    {
        $address = new AddressStruct();
        $address->setId((int) $data['__address_id']);
        $address->setCompany($data['__address_company']);
        $address->setDepartment($data['__address_department']);
        $address->setSalutation($data['__address_salutation']);
        $address->setTitle($data['__address_title']);
        $address->setFirstname($data['__address_firstname']);
        $address->setLastname($data['__address_lastname']);
        $address->setStreet($data['__address_street']);
        $address->setZipcode($data['__address_zipcode']);
        $address->setCity($data['__address_city']);
        $address->setCountryId((int) $data['__address_country_id']);
        $address->setStateId((int) $data['__address_state_id']);
        $address->setVatId($data['__address_ustid']);
        $address->setPhone($data['__address_phone']);
        $address->setAdditionalAddressLine1($data['__address_additional_address_line1']);
        $address->setAdditionalAddressLine1($data['__address_additional_address_line2']);

        if ($address->getCountryId()) {
            $address->setCountry(
                $this->countryHydrator->hydrateCountry($data)
            );
        }
        if ($address->getStateId()) {
            $address->setState(
                $this->countryHydrator->hydrateState($data)
            );
        }

        if ($data['__addressAttribute_id']) {
            $this->attributeHydrator->addAttribute($address, $data, 'addressAttribute');
        }
        return $address;
    }
}
