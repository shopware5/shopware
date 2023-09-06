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

namespace Shopware\Bundle\StoreFrontBundle\Service;

use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;

interface SimilarProductsServiceInterface
{
    /**
     * @see \Shopware\Bundle\StoreFrontBundle\Service\SimilarProductsServiceInterface::get()
     * @deprecated 5.7, interface will require a ShopContextInterface in 5.8
     *
     * @param ListProduct[] $products
     *
     * @return array<string, array<string, ListProduct>> indexed with the product number, the values are a list of ListProduct structs
     */
    public function getList($products, ProductContextInterface $context);

    /**
     * Selects all similar products for the provided product.
     *
     * The relation between the products are selected over the \Shopware\Bundle\StoreFrontBundle\Gateway\SimilarProductsGatewayInterface class.
     * After the relation is selected, the \Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface is used to load
     * the whole product data for the relations.
     *
     * If the product has no manually assigned similar products, the function selects the fallback similar products
     * over the same category.
     *
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface::get()
     * @deprecated 5.7, interface will require a ShopContextInterface in 5.8
     *
     * @return array<string, ListProduct>|null indexed by the product order number
     */
    public function get(ListProduct $product, ProductContextInterface $context);
}
