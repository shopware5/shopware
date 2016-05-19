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

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class UnitHydrator extends Hydrator
{
    /**
     * @var array
     */
    private $translationMapping = [
        'unit' => '__unit_unit',
        'description' => '__unit_description'
    ];

    /**
     * @param array $data
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Product\Unit
     */
    public function hydrate(array $data)
    {
        $unit = new Struct\Product\Unit();

        $this->assignUnitData($unit, $data);

        return $unit;
    }

    /**
     * Extracts and unserialize the unit translation
     *
     * @param $data
     * @return array|mixed
     */
    private function getTranslation($data)
    {
        $translation = [];

        if (isset($data['__unit_translation']) && !empty($data['__unit_translation'])) {
            $result = unserialize($data['__unit_translation']) ? : [];

            if (isset($result[$data['__unit_id']])) {
                $translation = $result[$data['__unit_id']] ? : [];
            }
        }

        if (isset($data['__unit_translation_fallback'])
            && !empty($data['__unit_translation_fallback'])
        ) {
            $fallbackResult = unserialize($data['__unit_translation_fallback']) ? : [];
            $fallbackTranslation = $fallbackResult[$data['__unit_id']] ? : [];
            $translation += $fallbackTranslation;
        }

        if (empty($translation)) {
            return [];
        }

        return $this->convertArrayKeys(
            $translation,
            $this->translationMapping
        );
    }

    /**
     * Assigns the passed data array to the passed unit instance.
     *
     * @param Struct\Product\Unit $unit
     * @param array $data
     */
    private function assignUnitData(Struct\Product\Unit $unit, array $data)
    {
        $translation = $this->getTranslation($data);
        $data = array_merge($data, $translation);

        if (isset($data['__unit_id'])) {
            $unit->setId((int) $data['__unit_id']);
        }

        if (isset($data['__unit_description'])) {
            $unit->setName($data['__unit_description']);
        }

        if (isset($data['__unit_unit'])) {
            $unit->setUnit($data['__unit_unit']);
        }

        if (isset($data['__unit_packunit'])) {
            $unit->setPackUnit($data['__unit_packunit']);
        }

        if (isset($data['__unit_purchaseunit'])) {
            $unit->setPurchaseUnit((float) $data['__unit_purchaseunit']);
        }

        if (isset($data['__unit_referenceunit'])) {
            $unit->setReferenceUnit((float) $data['__unit_referenceunit']);
        }

        if (isset($data['__unit_purchasesteps'])) {
            $unit->setPurchaseStep((int) $data['__unit_purchasesteps']);
        }

        if (isset($data['__unit_minpurchase'])) {
            $unit->setMinPurchase((int) $data['__unit_minpurchase']);
        }

        if (isset($data['__unit_maxpurchase'])) {
            $unit->setMaxPurchase((int) $data['__unit_maxpurchase']);
        }
    }
}
