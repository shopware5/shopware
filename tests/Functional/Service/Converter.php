<?php

namespace Shopware\Tests\Service;

use Shopware\Models;
use Shopware\Bundle\StoreFrontBundle\Struct;

class Converter
{
    /**
     * @param Models\Tax\Tax $tax
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
     * @param Models\Price\Group $entity
     * @return Struct\Product\PriceGroup
     */
    public function convertPriceGroup(Models\Price\Group $entity)
    {
        $struct = new Struct\Product\PriceGroup();

        $struct->setId($entity->getId());

        $struct->setName($entity->getName());

        $discounts = array();

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
     * @param \Shopware\Models\Shop\Shop $shop
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
        $struct->setSecureHost($shop->getSecureHost());
        $struct->setSecurePath($struct->getSecurePath());

        if ($shop->getCategory()) {
            $struct->setCategory(
                $this->convertCategory($shop->getCategory())
            );
        }

        return $struct;
    }

    /**
     * @param Models\Customer\Group $group
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
        $customerGroup->setMinimumOrderValue($group->getMinimumOrder());
        $customerGroup->setSurcharge($group->getMinimumOrderSurcharge());

        return $customerGroup;
    }
}
