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
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;

interface SimilarProductsServiceInterface
{
    /**
     * @see Shopware\Bundle\StoreFrontBundle\Service\SimilarProductsServiceInterface::get()
     *
     * @param ListProduct[] $products
     *
     * @return array indexed with the product number, the values are a list of ListProduct structs
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
     *
     * @return ListProduct[] indexed by the product order number
     */
    public function get(ListProduct $product, ProductContextInterface $context);
}
