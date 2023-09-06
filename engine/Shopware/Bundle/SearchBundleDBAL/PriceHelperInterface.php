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

use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

interface PriceHelperInterface
{
    /**
     * Returns the full selection for the cheapest price
     *
     * @deprecated 5.7, interface will require a ShopContextInterface in 5.8
     *
     * @return string
     */
    public function getSelection(ProductContextInterface $context);

    /**
     * Joins the customer prices to the provided query
     *
     * @return void
     */
    public function joinPrices(QueryBuilder $query, ShopContextInterface $context);

    /**
     * Joins the fallback customer group prices for the provided query
     *
     * @return void
     */
    public function joinDefaultPrices(QueryBuilder $query, ShopContextInterface $context);

    /**
     * Joins variants to the selected products which are available
     *
     * @return void
     */
    public function joinAvailableVariant(QueryBuilder $query);
}
