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
use Shopware\Models\Country\Area;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
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
    private $translationCountryFields = [
        'countryname' => '__country_countryname',
        'notice' => '__country_notice'
    ];

    /**
     * @var array
     */
    private $translationStateFields = [
        'name' => '__countryState_name'
    ];

    /**
     * @param AttributeHydrator $attributeHydrator
     */
    public function __construct(AttributeHydrator $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }

    /**
     * @param array $data
     * @return Area
     */
    public function hydrateArea(array $data)
    {
        $area = new Struct\Country\Area();

        $area->setId((int) $data['__countryArea_id']);

        $area->setName($data['__countryArea_name']);

        return $area;
    }

    /**
     * @param array $data
     * @return Struct\Country
     */
    public function hydrateCountry(array $data)
    {
        $country = new Struct\Country();
        $translation = $this->getTranslation(
            $data,
            '__country_translation',
            '__country_translation_fallback',
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

    /**
     * @param $data
     * @param $arrayKey
     * @param $fallbackArrayKey
     * @param $id
     * @param array $mapping
     * @return array|mixed
     */
    private function getTranslation($data, $arrayKey, $fallbackArrayKey, $id, $mapping = [])
    {
        if (!isset($data[$arrayKey])
            || empty($data[$arrayKey])
        ) {
            $translation = [];
        } else {
            $translation = unserialize($data[$arrayKey]);
        }

        if (isset($data[$fallbackArrayKey])
            && !empty($data[$fallbackArrayKey])
        ) {
            $fallbackTranslation = unserialize($data[$fallbackArrayKey]);
            $translation += $fallbackTranslation;
        }

        if (empty($translation)) {
            return [];
        }

        return $this->convertArrayKeys($translation[$id], $mapping);
    }

    /**
     * @param array $data
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Country\State
     */
    public function hydrateState(array $data)
    {
        $state = new Struct\Country\State();

        $translation = $this->getTranslation(
            $data,
            '__countryState_translation',
            '__countryState_translation_fallback',
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
