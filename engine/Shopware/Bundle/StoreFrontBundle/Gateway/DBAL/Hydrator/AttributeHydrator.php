<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator;

use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;

class AttributeHydrator extends Hydrator
{
    /**
     * @return Attribute
     */
    public function hydrate(array $data)
    {
        $attribute = new Attribute();
        $translation = $this->getTranslation($data, null);
        $translation = $this->extractFields('___attribute_', $translation);
        unset($data['translation'], $data['translation_fallback']);

        foreach ($data as $key => $value) {
            if (isset($translation[$key])) {
                $attribute->set($key, $translation[$key]);
            } else {
                $attribute->set($key, $value);
            }
        }

        return $attribute;
    }

    /**
     * @param array       $data
     * @param string      $arrayKey
     * @param string      $attributeKey
     * @param string|null $translationKey
     *
     * @return void
     */
    public function addAttribute(Extendable $struct, $data, $arrayKey, $attributeKey = null, $translationKey = null)
    {
        $arrayKey = '__' . $arrayKey . '_';
        $attribute = $this->extractFields($arrayKey, $data);

        if ($attributeKey === null) {
            $attributeKey = 'core';
        }

        if ($translationKey) {
            $translationKey = '__' . $translationKey . '_translation';
            $attribute['translation'] = !empty($data[$translationKey]) ? $data[$translationKey] : null;
            $attribute['translation_fallback'] = !empty($data[$translationKey . '_fallback']) ? $data[$translationKey . '_fallback'] : null;
        }

        $struct->addAttribute($attributeKey, $this->hydrate($attribute));
    }
}
