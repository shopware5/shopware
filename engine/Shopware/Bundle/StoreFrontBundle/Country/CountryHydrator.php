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

namespace Shopware\Bundle\StoreFrontBundle\Country;

use Shopware\Bundle\StoreFrontBundle\Common\AttributeHydrator;
use Shopware\Bundle\StoreFrontBundle\Common\Hydrator;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CountryHydrator extends Hydrator
{
    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    /**
     * @param AttributeHydrator $attributeHydrator
     */
    public function __construct(AttributeHydrator $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }

    /**
     * @param array $data
     *
     * @return Area
     */
    public function hydrateArea(array $data)
    {
        $area = new Area();
        $area->setId((int) $data['__countryArea_id']);
        $area->setName($data['__countryArea_name']);

        return $area;
    }

    /**
     * @param array $data
     *
     * @return Country
     */
    public function hydrateCountry(array $data)
    {
        $country = new Country();
        $id = (int) $data['__country_id'];

        $translation = $this->getTranslation($data, '__country', [], $id);
        $data = array_merge($data, $translation);

        $country->setId($id);
        $country->setName($data['__country_countryname']);

        if ($data['__countryArea_id']) {
            $country->setArea($this->hydrateArea($data));
        }

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

        if (isset($data['__country_shippingfree'])) {
            $country->setShippingFree((bool) $data['__country_shippingfree']);
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

        $country->setPosition((int) $data['__country_position']);
        $country->setActive((bool) $data['__country_active']);

        if ($data['__countryAttribute_id'] !== null) {
            $this->attributeHydrator->addAttribute($country, $data, 'countryAttribute');
        }

        return $country;
    }

    /**
     * @param array $data
     *
     * @return State
     */
    public function hydrateState(array $data)
    {
        $state = new State();

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
        $state->setPosition((int) $data['__countryState_position']);

        if ($data['__countryStateAttribute_id'] !== null) {
            $this->attributeHydrator->addAttribute($state, $data, 'countryStateAttribute');
        }

        return $state;
    }
}
