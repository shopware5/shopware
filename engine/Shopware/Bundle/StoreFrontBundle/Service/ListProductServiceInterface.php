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
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

interface ListProductServiceInterface
{
    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @deprecated 5.7, interface will require a ShopContextInterface in 5.8
     * @see \Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface::get()
     *
     * @param array<string> $numbers
     *
     * @return array<string, ListProduct> indexed by the product order number
     */
    public function getList(array $numbers, ProductContextInterface $context);

    /**
     * Returns a full \Shopware\Bundle\StoreFrontBundle\Struct\ListProduct object.
     * A list product contains all required data to display products in small views like listings, sliders or emotions.
     *
     * @deprecated 5.7, interface will require a ShopContextInterface in 5.8
     *
     * @param string $number
     *
     * @return ListProduct|null
     */
    public function get($number, ProductContextInterface $context);
}
