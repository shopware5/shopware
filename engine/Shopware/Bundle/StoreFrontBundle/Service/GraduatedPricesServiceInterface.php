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

interface GraduatedPricesServiceInterface
{
    /**
     * @see \Shopware\Bundle\StoreFrontBundle\Service\Core\GraduatedPricesServiceInterface::get()
     *
     * @param ListProduct[] $products
     *
     * @return array indexed by the product number, each array element contains a PriceRule array
     */
    public function getList($products, ProductContextInterface $context);

    /**
     * Returns the graduated product prices for the provided context and product.
     *
     * If the current customer group has no specified prices, the function returns
     * the graduated product prices for the fallback customer group.
     *
     * In case that the product has an assigned price group, the graduated prices are build by the
     * price group discounts definition.
     *
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\GraduatedPricesGatewayInterface::get()
     *
     * @return PriceRule[]
     */
    public function get(ListProduct $product, ProductContextInterface $context);
}
