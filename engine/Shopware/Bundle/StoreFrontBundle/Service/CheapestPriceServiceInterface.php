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

namespace Shopware\Bundle\StoreFrontBundle\Service;

use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;

interface CheapestPriceServiceInterface
{
    /**
     * @see \Shopware\Bundle\StoreFrontBundle\Service\Core\CheapestPriceServiceInterface::get()
     *
     * @param ListProduct[] $products
     *
     * @return PriceRule[] Indexed by product number
     */
    public function getList($products, ProductContextInterface $context);

    /**
     * Returns the cheapest product price for the provided context and product.
     *
     * If the current customer group has no specified prices, the function returns
     * the cheapest product price for the fallback customer group.
     *
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\CheapestPriceGatewayInterface::get()
     *
     * @return PriceRule
     */
    public function get(ListProduct $product, ProductContextInterface $context);
}
