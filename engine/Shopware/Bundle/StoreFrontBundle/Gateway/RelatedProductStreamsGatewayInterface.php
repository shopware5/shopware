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

use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductStream;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

interface RelatedProductStreamsGatewayInterface
{
    /**
     * Returns an array which contains the product stream details of
     * each related product stream for the provided products.
     *
     * Example:
     * Provided products:  SW100, SW200
     *
     * Result:
     * array(
     *    123 => array({Struct\ProductStream}, {Struct\ProductStream})
     *    124 => array({Struct\ProductStream}, {Struct\ProductStream})
     * )
     *
     * @param BaseProduct[] $products
     *
     * @return array<int, array<ProductStream>> indexed by the product id
     */
    public function getList($products, ShopContextInterface $context);

    /**
     * Returns an array which contains the order number of
     * each related products for the provided product.
     *
     * Example result: array({Struct\ProductStream}, {Struct\ProductStream})
     *
     * @return array<ProductStream>|null Array of product stream structs
     */
    public function get(BaseProduct $product, ShopContextInterface $context);
}
