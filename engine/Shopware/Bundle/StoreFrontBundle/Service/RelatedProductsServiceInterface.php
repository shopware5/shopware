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

use Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Service
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
interface RelatedProductsServiceInterface
{
    /**
     * @see \Shopware\Bundle\StoreFrontBundle\Service\RelatedProductsServiceInterface::get()
     *
     * @param Struct\BaseProduct[] $products
     * @param Struct\ProductContextInterface $context
     * @return array Indexed with the product number, each array element contains a \Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct array.
     */
    public function getList($products, Struct\ProductContextInterface $context);

    /**
     * Selects all related products for the provided product.
     *
     * The relation between the products are selected over the \Shopware\Bundle\StoreFrontBundle\Gateway\RelatedProductsGatewayInterface class.
     * After the relation is selected, the \Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface is used to load
     * the whole product data for the relations.
     *
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface::get()
     *
     * @param Struct\BaseProduct $product
     * @param Struct\ProductContextInterface $context
     * @return Struct\BaseProduct[] Indexed by the product order number.
     */
    public function get(Struct\BaseProduct $product, Struct\ProductContextInterface $context);
}
