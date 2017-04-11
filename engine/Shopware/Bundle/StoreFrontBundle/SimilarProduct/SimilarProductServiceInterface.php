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

namespace Shopware\Bundle\StoreFrontBundle\SimilarProduct;

use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
interface SimilarProductServiceInterface
{
    /**
     * Selects all similar products for the provided product.
     *
     * The relation between the products are selected over the \Shopware\Bundle\StoreFrontBundle\Gateway\SimilarProductGateway class.
     * After the relation is selected, the \Shopware\Bundle\StoreFrontBundle\Product\ListProductServiceInterface is used to load
     * the whole product data for the relations.
     *
     * If the product has no manually assigned similar products, the function selects the fallback similar products
     * over the same category.
     *
     * @param \Shopware\Bundle\StoreFrontBundle\Product\ListProduct[]        $products
     * @param \Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface $context
     *
     * @return array indexed with the product number, the values are a list of ListProduct structs
     */
    public function getList($products, ShopContextInterface $context);
}
