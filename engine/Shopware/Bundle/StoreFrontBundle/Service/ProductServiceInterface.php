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
interface ProductServiceInterface
{
    /**
     * @see \Shopware\Bundle\StoreFrontBundle\Service\ProductServiceInterface::get()
     *
     * @param $numbers
     * @param Struct\ProductContextInterface $context
     * @return Struct\Product[] Indexed by the product order number
     */
    public function getList(array $numbers, Struct\ProductContextInterface $context);

    /**
     * Returns a full \Shopware\Bundle\StoreFrontBundle\Struct\Product object.
     * A product struct contains all data about one single product.
     *
     * @param $number
     * @param Struct\ProductContextInterface $context
     * @return Struct\Product
     */
    public function get($number, Struct\ProductContextInterface $context);
}
