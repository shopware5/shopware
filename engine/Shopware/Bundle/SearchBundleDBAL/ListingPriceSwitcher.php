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

namespace Shopware\Bundle\SearchBundleDBAL;

use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandler\PriceConditionHandler;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ListingPriceSwitcher
{
    private ListingPriceTableInterface $listingPriceTable;

    private VariantHelperInterface $variantHelper;

    public function __construct(ListingPriceTableInterface $listingPriceTable, VariantHelperInterface $variantHelper)
    {
        $this->listingPriceTable = $listingPriceTable;
        $this->variantHelper = $variantHelper;
    }

    /**
     * @return void
     */
    public function joinPrice(QueryBuilder $query, Criteria $criteria, ShopContextInterface $context)
    {
        if ($query->hasState(PriceConditionHandler::LISTING_PRICE_JOINED)) {
            return;
        }

        $query->addState(PriceConditionHandler::LISTING_PRICE_JOINED);

        if (!$criteria->hasConditionOfClass(VariantCondition::class)) {
            $this->joinCheapestProductPrice($query, $context);

            return;
        }

        $this->variantHelper->joinPrices($query, $context, $criteria);
    }

    private function joinCheapestProductPrice(QueryBuilder $query, ShopContextInterface $context): void
    {
        $table = $this->listingPriceTable->get($context);
        $query->innerJoin('product', '(' . $table->getSQL() . ')', 'listing_price', 'listing_price.articleID = product.id');

        foreach ($table->getParameters() as $key => $value) {
            $query->setParameter($key, $value);
        }
    }
}
