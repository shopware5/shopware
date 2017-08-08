<?php

namespace Shopware\PriceGroupDiscount\Struct;

class PriceGroupDiscountHydrator
{
    public function hydrate(array $data): PriceGroupDiscount
    {
        $discount = new PriceGroupDiscount();
        $discount->setId((int) $data['__priceGroupDiscount_id']);
        $discount->setPercent((float) $data['__priceGroupDiscount_discount']);
        $discount->setQuantity((int) $data['__priceGroupDiscount_discountstart']);

        return $discount;
    }
}