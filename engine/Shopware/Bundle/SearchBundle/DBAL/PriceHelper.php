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

namespace Shopware\Bundle\SearchBundle\DBAL;

use Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @package Shopware\Bundle\SearchBundle\DBAL
 */
class PriceHelper
{
    const STATE_INCLUDES_CHEAPEST_PRICE = 'cheapest_price';

    /**
     * @param Struct\Customer\Group $current
     * @return string
     */
    public function getCheapestPriceSelection(Struct\Customer\Group $current)
    {
        return 'MIN(' . $this->getSelection($current) . ')';
    }

    /**
     * @param Struct\Customer\Group $current
     * @return string
     */
    public function getSelection(Struct\Customer\Group $current)
    {
        $selection = "ROUND(IF(customerPrice.id, customerPrice.price, defaultPrice.price)";

        $selection .= "* (priceVariant.minpurchase)";

        $selection .= "* ((100 - IFNULL(priceGroup.discount, 0)) / 100)";

        if ($current->displayGrossPrices()) {
            $selection .= " * ((tax.tax + 100) / 100)";
        }

        if ($current->useDiscount()) {
            $discount = (100 - (float) $current->getPercentageDiscount()) / 100;
            $selection .= " * " . $discount;
        }

        return $selection . ', 2)';
    }

    /**
     * @param QueryBuilder $query
     * @param Struct\Customer\Group $current
     * @param Struct\Customer\Group $fallback
     */
    public function joinPrices(QueryBuilder $query, Struct\Customer\Group $current, Struct\Customer\Group $fallback)
    {
        if ($query->hasState(self::STATE_INCLUDES_CHEAPEST_PRICE)) {
            return;
        }

        $query->innerJoin(
            'product',
            's_articles_prices',
            'defaultPrice',
            'defaultPrice.articleID = product.id
             AND defaultPrice.pricegroup = :fallbackCustomerGroup
             AND defaultPrice.from = 1'
        );

        $query->innerJoin(
            'defaultPrice',
            's_articles_details',
            'priceVariant',
            'priceVariant.id = defaultPrice.articledetailsID
             AND (product.laststock * priceVariant.instock) >= (product.laststock * priceVariant.minpurchase)'
        );

        $query->leftJoin(
            'product',
            's_articles_prices',
            'customerPrice',
            'customerPrice.articleID = product.id
             AND customerPrice.pricegroup = :currentCustomerGroup
             AND customerPrice.from = 1
             AND priceVariant.id = customerPrice.articledetailsID'
        );

        $query->leftJoin(
            'product',
            's_core_pricegroups_discounts',
            'priceGroup',
            'priceGroup.groupID = product.pricegroupID
             AND priceGroup.discountstart = 1
             AND priceGroup.customergroupID = :priceGroupCustomerGroup
             AND product.pricegroupActive = 1'
        );

        $query->setParameter(':currentCustomerGroup', $current->getKey())
            ->setParameter(':fallbackCustomerGroup', $fallback->getKey())
            ->setParameter(':priceGroupCustomerGroup', $current->getId());

        $query->addState(self::STATE_INCLUDES_CHEAPEST_PRICE);
    }
}
