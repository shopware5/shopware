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

namespace Shopware\Bundle\StoreFrontBundle\Gateway;

use Shopware\Bundle\StoreFrontBundle\Struct;

interface CheapestPriceGatewayInterface
{
    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\CheapestPriceGatewayInterface::get()
     *
     * @param Struct\BaseProduct[] $products
     *
     * @return Struct\Product\PriceRule[] Indexed by the product id
     */
    public function getList($products, Struct\ShopContextInterface $context, Struct\Customer\Group $customerGroup);

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
     *  - Cheapest price is selected between all variants of the product
     *  - Cheapest price requires the selected variant unit for base price calculation
     *  - The variants has to be active
     *  - Closeout variants can only be selected if the stock > min purchase
     *
     * @return Struct\Product\PriceRule
     */
    public function get(Struct\BaseProduct $product, Struct\ShopContextInterface $context, Struct\Customer\Group $customerGroup);
}
