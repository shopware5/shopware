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

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Models;

class Converter
{
    /**
     * @return Struct\Tax
     */
    public function convertTax(Models\Tax\Tax $tax)
    {
        $struct = new Struct\Tax();
        $struct->setId($tax->getId());
        $struct->setTax($tax->getTax());
        $struct->setName($tax->getName());

        return $struct;
    }

    /**
     * @return Struct\Product\PriceGroup
     */
    public function convertPriceGroup(Models\Price\Group $entity)
    {
        $struct = new Struct\Product\PriceGroup();

        $struct->setId($entity->getId());

        $struct->setName($entity->getName());

        $discounts = [];

        foreach ($entity->getDiscounts() as $discountEntity) {
            $discount = new Struct\Product\PriceDiscount();

            $discount->setId($discountEntity->getId());

            $discount->setPercent($discountEntity->getDiscount());

            $discount->setQuantity($discountEntity->getStart());

            $discounts[] = $discount;
        }

        $struct->setDiscounts($discounts);

        return $struct;
    }

    public function convertCategory(Models\Category\Category $category)
    {
        $struct = new Struct\Category();
        $struct->setId($category->getId());
        $struct->setName($category->getName());
        $struct->setPath($category->getPath());

        return $struct;
    }

    /**
     * Converts a currency doctrine model to a currency struct
     *
     * @param \Shopware\Models\Shop\Currency $currency
     *
     * @return Struct\Currency
     */
    public function convertCurrency(Models\Shop\Currency $currency)
    {
        $struct = new Struct\Currency();

        $struct->setId($currency->getId());
        $struct->setName($currency->getName());
        $struct->setCurrency($currency->getCurrency());
        $struct->setFactor($currency->getFactor());
        $struct->setSymbol($currency->getSymbol());

        return $struct;
    }

    /**
     * Converts a shop doctrine model to a shop struct
     *
     * @param \Shopware\Models\Shop\Shop $shop
     *
     * @return Struct\Shop
     */
    public function convertShop(Models\Shop\Shop $shop)
    {
        $struct = new Struct\Shop();
        $struct->setId($shop->getId());

        $struct->setName($shop->getName());
        $struct->setHost($shop->getHost());
        $struct->setPath($shop->getBasePath());
        $struct->setUrl($shop->getBaseUrl());
        $struct->setSecure($shop->getSecure());

        if ($shop->getMain()) {
            $struct->setParentId($shop->getMain()->getId());
        } else {
            $struct->setParentId($struct->getId());
        }

        $struct->setLocale(
            $this->convertLocale($shop->getLocale())
        );

        if ($shop->getCategory()) {
            $struct->setCategory(
                $this->convertCategory($shop->getCategory())
            );
        }

        return $struct;
    }

    /**
     * @return Struct\Customer\Group
     */
    public function convertCustomerGroup(Models\Customer\Group $group)
    {
        $customerGroup = new Struct\Customer\Group();
        $customerGroup->setKey($group->getKey());
        $customerGroup->setUseDiscount($group->getMode());
        $customerGroup->setId($group->getId());
        $customerGroup->setPercentageDiscount($group->getDiscount());
        $customerGroup->setDisplayGrossPrices($group->getTax());
        $customerGroup->setInsertedGrossPrices($group->getTaxInput());
        $customerGroup->setMinimumOrderValue($group->getMinimumOrder());
        $customerGroup->setSurcharge($group->getMinimumOrderSurcharge());

        return $customerGroup;
    }

    /**
     * @param Models\Shop\Locale $locale
     *
     * @return Struct\Locale
     */
    public function convertLocale($locale)
    {
        $struct = new Struct\Locale();
        if (!$locale) {
            return $locale;
        }

        $struct->setId($locale->getId());
        $struct->setLocale($locale->getLocale());
        $struct->setLanguage($locale->getLanguage());
        $struct->setTerritory($locale->getTerritory());

        return $struct;
    }
}
