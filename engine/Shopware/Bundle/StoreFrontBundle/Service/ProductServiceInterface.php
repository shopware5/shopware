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

use Shopware\Bundle\StoreFrontBundle\Struct\Product;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

interface ProductServiceInterface extends ListProductServiceInterface
{
    /**
     * @see \Shopware\Bundle\StoreFrontBundle\Service\ProductServiceInterface::get()
     * @deprecated 5.7, interface will require a ShopContextInterface in 5.8
     *
     * @param string[] $numbers
     *
     * @return array<string, Product> Indexed by the product order number
     */
    public function getList(array $numbers, ProductContextInterface $context);

    /**
     * Returns a full \Shopware\Bundle\StoreFrontBundle\Struct\Product object.
     * A product struct contains all data about one single product.
     *
     * @deprecated 5.7, interface will require a ShopContextInterface in 5.8
     *
     * @param string $number
     *
     * @return Product|null
     */
    public function get($number, ProductContextInterface $context);
}
