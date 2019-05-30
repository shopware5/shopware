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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator;

use Shopware\Bundle\StoreFrontBundle\Struct\Address;

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

    public function __construct(CountryHydrator $countryHydrator, AttributeHydrator $attributeHydrator)
    {
        $this->countryHydrator = $countryHydrator;
        $this->attributeHydrator = $attributeHydrator;
    }

    /**
     * @return Address
     */
    public function hydrate(array $data)
    {
        $address = new Address();
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
        $address->setAdditionalAddressLine2($data['__address_additional_address_line2']);

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
