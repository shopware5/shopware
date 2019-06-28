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

use Shopware\Bundle\StoreFrontBundle\Struct\Product;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;

interface ProductServiceInterface
{
    /**
     * @see \Shopware\Bundle\StoreFrontBundle\Service\ProductServiceInterface::get()
     *
     * @param string[] $numbers
     *
     * @return Product[] Indexed by the product order number
     */
    public function getList(array $numbers, ProductContextInterface $context);

    /**
     * Returns a full \Shopware\Bundle\StoreFrontBundle\Struct\Product object.
     * A product struct contains all data about one single product.
     *
     * @param string $number
     *
     * @return Product|null
     */
    public function get($number, ProductContextInterface $context);
}
