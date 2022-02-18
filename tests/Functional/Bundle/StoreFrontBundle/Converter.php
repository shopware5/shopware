<?php

declare(strict_types=1);
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

use Shopware\Bundle\StoreFrontBundle\Struct\Category;
use Shopware\Bundle\StoreFrontBundle\Struct\Currency;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group as CustomerGroup;
use Shopware\Bundle\StoreFrontBundle\Struct\Locale;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceDiscount;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceGroup;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Bundle\StoreFrontBundle\Struct\Tax as TaxStruct;
use Shopware\Models\Category\Category as CategoryModel;
use Shopware\Models\Customer\Group as CustomerGroupModel;
use Shopware\Models\Price\Group as PriceGroupModel;
use Shopware\Models\Shop\Currency as CurrencyModel;
use Shopware\Models\Shop\Locale as ShopLocale;
use Shopware\Models\Shop\Shop as ShopModel;
use Shopware\Models\Tax\Tax as TaxModel;

class Converter
{
    public function convertTax(TaxModel $tax): TaxStruct
    {
        $struct = new TaxStruct();
        $struct->setId($tax->getId());
        $struct->setTax((float) $tax->getTax());
        $struct->setName($tax->getName());

        return $struct;
    }

    public function convertPriceGroup(PriceGroupModel $entity): PriceGroup
    {
        $struct = new PriceGroup();

        $struct->setId($entity->getId());

        $struct->setName($entity->getName());

        $discounts = [];

        foreach ($entity->getDiscounts() as $discountEntity) {
            $discount = new PriceDiscount();

            $discount->setId($discountEntity->getId());

            $discount->setPercent($discountEntity->getDiscount());

            $discount->setQuantity($discountEntity->getStart());

            $discounts[] = $discount;
        }

        $struct->setDiscounts($discounts);

        return $struct;
    }

    public function convertCategory(CategoryModel $category): Category
    {
        $struct = new Category();
        $struct->setId($category->getId());
        $struct->setName($category->getName());
        $struct->setPath($category->getPath());

        return $struct;
    }

    /**
     * Converts a currency doctrine model to a currency struct
     */
    public function convertCurrency(CurrencyModel $currency): Currency
    {
        $struct = new Currency();

        $struct->setId($currency->getId());
        $struct->setName($currency->getName());
        $struct->setCurrency($currency->getCurrency());
        $struct->setFactor($currency->getFactor());
        $struct->setSymbol($currency->getSymbol());

        return $struct;
    }

    /**
     * Converts a shop doctrine model to a shop struct
     */
    public function convertShop(ShopModel $shop): Shop
    {
        $struct = new Shop();
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

        if ($shop->getLocale() instanceof ShopLocale) {
            $struct->setLocale($this->convertLocale($shop->getLocale()));
        }

        if ($shop->getCategory()) {
            $struct->setCategory($this->convertCategory($shop->getCategory()));
        }

        return $struct;
    }

    public function convertCustomerGroup(CustomerGroupModel $group): CustomerGroup
    {
        $customerGroup = new CustomerGroup();
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

    public function convertLocale(ShopLocale $locale): Locale
    {
        $struct = new Locale();

        $struct->setId($locale->getId());
        $struct->setLocale($locale->getLocale());
        $struct->setLanguage($locale->getLanguage());
        $struct->setTerritory($locale->getTerritory());

        return $struct;
    }
}
