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

namespace Shopware\Gateway\DBAL\Hydrator;

use Shopware\Struct;

/**
 * @package Shopware\Gateway\DBAL\Hydrator
 */
class Price extends Hydrator
{
    /**
     * @var CustomerGroup
     */
    private $customerGroupHydrator;

    /**
     * @var Unit
     */
    private $unitHydrator;

    /**
     * @var Attribute
     */
    private $attributeHydrator;

    private $translationMapping = array(
        'txtpackunit' => '__unit_packunit',
    );

    /**
     * @param CustomerGroup $customerGroupHydrator
     * @param Unit $unitHydrator
     * @param Attribute $attributeHydrator
     */
    function __construct(
        CustomerGroup $customerGroupHydrator,
        Unit $unitHydrator,
        Attribute $attributeHydrator
    ) {
        $this->customerGroupHydrator = $customerGroupHydrator;
        $this->unitHydrator = $unitHydrator;
        $this->attributeHydrator = $attributeHydrator;

    }

    /**
     * @param array $data
     * @return \Shopware\Struct\Product\PriceRule
     */
    public function hydratePriceRule(array $data)
    {
        $price = new Struct\Product\PriceRule();

        $price->setId((int) $data['__price_id']);

        $price->setFrom((int) $data['__price_from']);

        $price->setPrice((float) $data['__price_price']);

        $price->setPseudoPrice((float) $data['__price_pseudoprice']);

        if (strtolower($data['__price_to']) == 'beliebig') {
            $price->setTo(null);
        } else {
            $price->setTo((int) $data['__price_to']);
        }

        if (isset($data['__price___attribute_id'])) {
            $attribute = $this->attributeHydrator->hydrate(
                $this->extractFields('__attribute_', $data)
            );

            $price->addAttribute('core', $attribute);
        }

        return $price;
    }

    /**
     * Hydrates the data result of the cheapest price query.
     * This function uses the normally hydrate function of this class
     * and adds additionally the product unit information to the price.
     *
     * @param array $data
     * @return Struct\Product\PriceRule
     */
    public function hydrateCheapestPrice(array $data)
    {
        $price = $this->hydratePriceRule($data);

        $data = array_merge(
            $data,
            $this->getVariantTranslation($data)
        );

        $unit = $this->unitHydrator->hydrate($data);

        $price->setUnit($unit);

        return $price;
    }

    private function getVariantTranslation(array $data)
    {
        $translation = array();

        if (isset($data['__variant_translation'])) {
            $translation = unserialize($data['__variant_translation']);
        }

        if (empty($translation)) {
            return $translation;
        }

        return $this->convertArrayKeys(
            $translation,
            $this->translationMapping
        );
    }

    public function hydratePriceDiscount(array $data)
    {
        $discount = new Struct\Product\PriceDiscount();

        $discount->setId((int) $data['__priceGroupDiscount_groupID']);

        $discount->setPercent((float) $data['__priceGroupDiscount_discount']);

        $discount->setQuantity((int) $data['__priceGroupDiscount_discountstart']);

        return $discount;
    }
}
