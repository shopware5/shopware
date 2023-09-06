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

namespace Shopware\Bundle\StoreFrontBundle\Gateway;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

interface VariantCheapestPriceGatewayInterface
{
    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\VariantCheapestPriceGatewayInterface::get()
     *
     * @param BaseProduct[] $products
     *
     * @return array<string, array{price: PriceRule, different_price_count: int}> Indexed by the ordernumber
     */
    public function getList($products, ShopContextInterface $context, Group $customerGroup, Criteria $criteria);

    /**
     * The cheapest product price is only selected for the provided customer group.
     *
     * If the provided customer group has no defined price, the function has to return null.
     *
     * The \Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule requires the following data:
     * - The price data
     * - Core attribute of the price
     * - Variant unit of the selected cheapest price.
     *
     * Required translation in the provided context language:
     * - Variant unit data.
     *
     * Required conditions for the selection:
     *  - Only the first graduated price
     *  - Cheapest price is selected between all variants of the product related to the given criteria
     *  - Cheapest price requires the selected variant unit for base price calculation
     *  - The variants has to be active
     *  - Closeout variants can only be selected if the stock > min purchase
     *
     * @return array{price: PriceRule, different_price_count: int}|null
     */
    public function get(BaseProduct $product, ShopContextInterface $context, Group $customerGroup, Criteria $criteria);
}
