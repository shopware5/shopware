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

use Shopware\Bundle\StoreFrontBundle\Struct;

class CountryHydrator extends Hydrator
{
    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    public function __construct(AttributeHydrator $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }

    /**
     * @return Struct\Country\Area
     */
    public function hydrateArea(array $data)
    {
        $area = new Struct\Country\Area();
        $area->setId((int) $data['__countryArea_id']);
        $area->setName($data['__countryArea_name']);

        return $area;
    }

    /**
     * @return Struct\Country
     */
    public function hydrateCountry(array $data)
    {
        $country = new Struct\Country();
        $id = (int) $data['__country_id'];

        $translation = $this->getTranslation($data, '__country', [], $id);
        $data = array_merge($data, $translation);

        $country->setId($id);
        $country->setName($data['__country_countryname']);

        if (isset($data['__country_countryiso'])) {
            $country->setIso($data['__country_countryiso']);
        }

        if (isset($data['__country_iso3'])) {
            $country->setIso3($data['__country_iso3']);
        }

        if (isset($data['__country_notice'])) {
            $country->setDescription($data['__country_notice']);
        }

        if (isset($data['__country_countryen'])) {
            $country->setEn($data['__country_countryen']);
        }

        if (isset($data['__country_display_state_in_registration'])) {
            $country->setDisplayStateSelection((bool) $data['__country_display_state_in_registration']);
        }

        if (isset($data['__country_force_state_in_registration'])) {
            $country->setRequiresStateSelection((bool) $data['__country_force_state_in_registration']);
        }

        if (isset($data['__country_allow_shipping'])) {
            $country->setAllowShipping((bool) $data['__country_allow_shipping']);
        }

        if (isset($data['__country_taxfree'])) {
            $country->setTaxFree((bool) $data['__country_taxfree']);
        }

        if (isset($data['__country_taxfree_ustid'])) {
            $country->setTaxFreeForVatId((bool) $data['__country_taxfree_ustid']);
        }

        if (isset($data['__country_taxfree_ustid_checked'])) {
            $country->setVatIdCheck((bool) $data['__country_taxfree_ustid_checked']);
        }

        if (isset($data['__country_areaID'])) {
            $country->setAreaId((int) $data['__country_areaID']);
        }

        $country->setPosition((int) $data['__country_position']);
        $country->setActive((bool) $data['__country_active']);

        if ($data['__countryAttribute_id'] !== null) {
            $this->attributeHydrator->addAttribute($country, $data, 'countryAttribute');
        }

        return $country;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Country\State
     */
    public function hydrateState(array $data)
    {
        $state = new Struct\Country\State();

        $id = (int) $data['__countryState_id'];

        $translation = $this->getTranslation($data, '__countryState', [], $id);
        $data = array_merge($data, $translation);

        $state->setId($id);

        if (isset($data['__countryState_name'])) {
            $state->setName($data['__countryState_name']);
        }

        if (isset($data['__countryState_shortcode'])) {
            $state->setCode($data['__countryState_shortcode']);
        }

        if (isset($data['__countryState_active'])) {
            $state->setActive((bool) $data['__countryState_active']);
        }

        $state->setPosition((int) $data['__countryState_position']);

        if ($data['__countryStateAttribute_id'] !== null) {
            $this->attributeHydrator->addAttribute($state, $data, 'countryStateAttribute');
        }

        return $state;
    }
}
