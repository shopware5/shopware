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
     *    'SW100' => array({Struct\ProductStream}, {Struct\ProductStream})
     *    'SW200' => array({Struct\ProductStream}, {Struct\ProductStream})
     * )
     *
     * @param Struct\BaseProduct[] $products
     *
     * @return array indexed by the product number
     */
    public function getList($products, Struct\ShopContextInterface $context);

    /**
     * Returns an array which contains the order number of
     * each related products for the provided product.
     *
     * Example result: array({Struct\ProductStream}, {Struct\ProductStream})
     *
     * @return array Array of order numbers
     */
    public function get(Struct\BaseProduct $product, Struct\ShopContextInterface $context);
}
