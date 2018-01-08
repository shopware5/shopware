<?php

namespace Shopware\Bundle\SearchBundleES;

use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class PriceFieldMapper
{
    public function getPriceField(Criteria $criteria, ShopContextInterface $context)
    {
        $customerGroup = $context->getCurrentCustomerGroup()->getKey();
        $currency = $context->getCurrency()->getId();

        $conditions = $criteria->getConditionsByClass(VariantCondition::class);
        $ids = array_map(function(VariantCondition $condition) {
            return $condition->getGroupId();
        }, $conditions);

        if (empty($conditions)) {
            return 'calculatedPrices.' . $customerGroup . '_' . $currency . '.calculatedPrice';
        }

        $ids = array_filter($ids);
        sort($ids, SORT_NUMERIC);

        $first = $customerGroup . '_' . $currency;
        $second = 'g' . implode('-', $ids);

        return 'listingVariationPrices.' . $first . '.' . $second;
    }
}