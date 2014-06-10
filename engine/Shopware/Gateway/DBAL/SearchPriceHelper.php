<?php

namespace Shopware\Gateway\DBAL;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Struct\Customer\Group;

class SearchPriceHelper
{
    const STATE_INCLUDES_CHEAPEST_PRICE = 'cheapest_price';

    public function getCheapestPriceSelection(Group $current)
    {
        return '(MIN(' . $this->getSelection($current) . '))';
    }

    public function getSelection(Group $current)
    {
        $selection = "(
            IF(customer_prices.id, customer_prices.price, default_prices.price)";

        $selection .= "* (priceVariant.minpurchase)";

        $selection .= "* ((100 - IFNULL(priceGroup.discount, 0)) / 100)";

        if ($current->displayGrossPrices()) {
            $selection .= " * ((tax.tax + 100) / 100)";
        }

        if ($current->useDiscount()) {
            $discount = (100 - (float)$current->getPercentageDiscount()) / 100;
            $selection .= " * " . $discount;
        }

        return $selection . ')';
    }


    public function joinPrices(QueryBuilder $query, Group $current, Group $fallback)
    {
        if ($query->hasState(self::STATE_INCLUDES_CHEAPEST_PRICE)) {
            return;
        }

        $query->innerJoin(
            'products',
            's_articles_prices',
            'default_prices',
            'default_prices.articleID = products.id
             AND default_prices.pricegroup = :fallbackCustomerGroup
		     AND default_prices.from = 1'
        );

        $query->innerJoin(
            'default_prices',
            's_articles_details',
            'priceVariant',
            'priceVariant.id = default_prices.articledetailsID
		     AND (products.laststock * priceVariant.instock) >= (products.laststock * priceVariant.minpurchase)'
        );

        $query->leftJoin(
            'products',
            's_articles_prices',
            'customer_prices',
            'customer_prices.articleID = products.id
             AND customer_prices.pricegroup = :currentCustomerGroup
             AND customer_prices.from = 1
             AND priceVariant.id = customer_prices.articledetailsID'
        );

        $query->leftJoin(
            'products',
            's_core_pricegroups_discounts',
            'priceGroup',
            'priceGroup.groupID = products.pricegroupID
             AND priceGroup.discountstart = 1
             AND pricegroup.customergroupID = :priceGroupCustomerGroup
             AND products.pricegroupActive = 1'
        );

        $query->setParameter(':currentCustomerGroup', $current->getKey())
            ->setParameter(':fallbackCustomerGroup', $fallback->getKey())
            ->setParameter(':priceGroupCustomerGroup', $current->getId());

        $query->addState(self::STATE_INCLUDES_CHEAPEST_PRICE);
    }
}
