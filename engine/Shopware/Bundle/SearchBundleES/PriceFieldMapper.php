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
        $ids = array_map(function (VariantCondition $condition) {
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
