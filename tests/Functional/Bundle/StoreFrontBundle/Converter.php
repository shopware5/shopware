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

namespace Shopware\tests\Functional\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Category\Category;
use Shopware\Bundle\StoreFrontBundle\Common\Struct;
use Shopware\Models;

class Converter
{
    /**
     * @param Models\Tax\Tax $tax
     *
     * @return \Shopware\Bundle\StoreFrontBundle\Tax\Tax
     */
    public function convertTax(Models\Tax\Tax $tax)
    {
        return new \Shopware\Bundle\StoreFrontBundle\Tax\Tax(
            $tax->getId(),
            $tax->getName(),
            $tax->getTax()
        );
    }

    /**
     * @param Models\Price\Group $entity
     *
     * @return \Shopware\Bundle\StoreFrontBundle\PriceGroup\PriceGroup
     */
    public function convertPriceGroup(Models\Price\Group $entity)
    {
        $struct = new \Shopware\Bundle\StoreFrontBundle\PriceGroup\PriceGroup();

        $struct->setId($entity->getId());

        $struct->setName($entity->getName());

        $discounts = [];

        foreach ($entity->getDiscounts() as $discountEntity) {
            $discount = new \Shopware\Bundle\StoreFrontBundle\PriceGroup\PriceDiscount();

            $discount->setId($discountEntity->getId());

            $discount->setPercent($discountEntity->getDiscount());

            $discount->setQuantity($discountEntity->getStart());

            $discounts[] = $discount;
        }

        $struct->setDiscounts($discounts);

        return $struct;
    }

    /**
     * Converts a currency doctrine model to a currency struct
     *
     * @param \Shopware\Models\Shop\Currency $currency
     *
     * @return \Shopware\Bundle\StoreFrontBundle\Currency\Currency
     */
    public function convertCurrency(Models\Shop\Currency $currency)
    {
        $struct = new \Shopware\Bundle\StoreFrontBundle\Currency\Currency();

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
     * @return \Shopware\Bundle\StoreFrontBundle\Shop\Shop
     */
    public function convertShop(Models\Shop\Shop $shop)
    {
        $struct = new \Shopware\Bundle\StoreFrontBundle\Shop\Shop();
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
            $category = new Category(
                $shop->getCategory()->getId(),
                $shop->getCategory()->getParentId(),
                array_filter(explode('|', (string) $shop->getCategory()->getPath())),
                $shop->getCategory()->getName()
            );

            $category->assign([
                'position' => (int) $shop->getCategory()->getPosition(),
            ]);

            $struct->setCategory($category);
        }

        $country = new \Shopware\Bundle\StoreFrontBundle\Country\Country();
        $country->setId($shop->getCountry()->getId());
        $country->setName($shop->getCountry()->getName());
        $struct->setCountry($country);

        return $struct;
    }

    /**
     * @param Models\Customer\Group $group
     *
     * @return \Shopware\Bundle\StoreFrontBundle\CustomerGroup\CustomerGroup
     */
    public function convertCustomerGroup(Models\Customer\Group $group)
    {
        $customerGroup = new \Shopware\Bundle\StoreFrontBundle\CustomerGroup\CustomerGroup();
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
     * @return \Shopware\Bundle\StoreFrontBundle\Shop\Locale
     */
    public function convertLocale($locale)
    {
        $struct = new \Shopware\Bundle\StoreFrontBundle\Shop\Locale();
        if (!$locale) {
            return new \Shopware\Bundle\StoreFrontBundle\Shop\Locale();
        }

        $struct->setId($locale->getId());
        $struct->setLocale($locale->getLocale());
        $struct->setLanguage($locale->getLanguage());
        $struct->setTerritory($locale->getTerritory());

        return $struct;
    }
}
