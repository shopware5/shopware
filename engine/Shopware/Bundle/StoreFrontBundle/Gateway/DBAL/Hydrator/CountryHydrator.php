<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

/**
 * @package Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator
 */
class CountryHydrator extends Hydrator
{
    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    /**
     * @var array
     */
    private $translationCountryFields = array(
        'countryname' => '__country_countryname',
        'notice' => '__country_notice'
    );

    private $translationStateFields = array(
        'name' => '__countryState_name'
    );

    function __construct(AttributeHydrator $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }


    /**
     * @param array $data
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
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Country
     */
    public function hydrateCountry(array $data)
    {
        $country = new \Shopware\Bundle\StoreFrontBundle\Struct\Country();
        $translation = $this->getTranslation(
            $data,
            '__country_translation',
            $data['__country_id'],
            $this->translationCountryFields
        );

        $data = array_merge($data, $translation);

        $country->setId((int) $data['__country_id']);

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

        if ($data['__countryAttribute_id'] !== null) {
            $attribute = $this->attributeHydrator->hydrate(
                $this->extractFields('__countryAttribute_', $data)
            );
            $country->addAttribute('core', $attribute);
        }

        return $country;
    }

    private function getTranslation($data, $field, $id, $mapping = array())
    {
        if (!isset($data[$field])) {
            return array();
        }

        $translation = unserialize($data[$field]);

        if (empty($translation[$id])) {
            return array();
        }

        if (empty($mapping)) {
            return $translation;
        }

        return $this->convertArrayKeys($translation[$id], $mapping);
    }

    /**
     * @param array $data
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Country\State
     */
    public function hydrateState(array $data)
    {
        $state = new State();

        $translation = $this->getTranslation(
            $data,
            '__countryState_translation',
            $data['__countryState_id'],
            $this->translationStateFields
        );

        $data = array_merge($data, $translation);

        $state->setId((int) $data['__countryState_id']);

        if (isset($data['__countryState_name'])) {
            $state->setName($data['__countryState_name']);
        }

        if (isset($data['__countryState_shortcode'])) {
            $state->setCode($data['__countryState_shortcode']);
        }

        if ($data['__countryStateAttribute_id'] !== null) {
            $attribute = $this->attributeHydrator->hydrate(
                $this->extractFields('__countryStateAttribute_', $data)
            );
            $state->addAttribute('core', $attribute);
        }

        return $state;
    }
}
