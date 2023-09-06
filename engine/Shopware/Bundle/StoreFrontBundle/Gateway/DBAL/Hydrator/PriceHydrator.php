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

use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceDiscount;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceGroup;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule;

class PriceHydrator extends Hydrator
{
    private UnitHydrator $unitHydrator;

    private AttributeHydrator $attributeHydrator;

    private ProductHydrator $productHydrator;

    public function __construct(
        UnitHydrator $unitHydrator,
        AttributeHydrator $attributeHydrator,
        ProductHydrator $productHydrator
    ) {
        $this->unitHydrator = $unitHydrator;
        $this->attributeHydrator = $attributeHydrator;
        $this->productHydrator = $productHydrator;
    }

    /**
     * @return PriceRule
     */
    public function hydratePriceRule(array $data)
    {
        $price = new PriceRule();

        $price->setId((int) $data['__price_id']);
        $price->setFrom((int) $data['__price_from']);
        $price->setPrice((float) $data['__price_price']);
        $price->setPseudoPrice((float) $data['__price_pseudoprice']);
        $price->setRegulationPrice((float) $data['__price_regulation_price']);

        if (strtolower($data['__price_to']) === 'beliebig') {
            $price->setTo(null);
        } else {
            $price->setTo((int) $data['__price_to']);
        }

        if (isset($data['__priceAttribute_id'])) {
            $this->attributeHydrator->addAttribute($price, $data, 'priceAttribute');
        }

        return $price;
    }

    /**
     * Hydrates the data result of the cheapest price query.
     * This function uses the normally hydrate function of this class
     * and adds additionally the product unit information to the price.
     *
     * @return PriceRule
     */
    public function hydrateCheapestPrice(array $data)
    {
        $price = $this->hydratePriceRule($data);
        $translation = $this->productHydrator->getProductTranslation($data);
        $data = array_merge($data, $translation);

        $unit = $this->unitHydrator->hydrate($data);
        $price->setUnit($unit);

        return $price;
    }

    /**
     * @param array $data
     *
     * @return PriceGroup
     */
    public function hydratePriceGroup($data)
    {
        $group = new PriceGroup();

        $first = $data[0];

        $group->setId((int) $first['__priceGroup_id']);
        $group->setName($first['__priceGroup_description']);

        $discounts = [];
        foreach ($data as $row) {
            $discounts[] = $this->hydratePriceDiscount($row);
        }

        $group->setDiscounts($discounts);

        return $group;
    }

    /**
     * @return PriceDiscount
     */
    public function hydratePriceDiscount(array $data)
    {
        $discount = new PriceDiscount();
        $discount->setId((int) $data['__priceGroupDiscount_id']);
        $discount->setPercent((float) $data['__priceGroupDiscount_discount']);
        $discount->setQuantity((int) $data['__priceGroupDiscount_discountstart']);

        return $discount;
    }
}
