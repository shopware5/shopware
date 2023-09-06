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
use Shopware\Bundle\StoreFrontBundle\Struct\Property\Set;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

interface ProductPropertyGatewayInterface
{
    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\ProductPropertyGatewayInterface::get()
     *
     * @param BaseProduct[] $products
     *
     * @return array<string, Set> Indexed by the product order number
     */
    public function getList($products, ShopContextInterface $context);

    /**
     * The \Shopware\Bundle\StoreFrontBundle\Struct\Property\Set requires the following data:
     * - Property set data
     * - Property groups data
     * - Property options data
     * - Core attribute of the property set
     *
     * Required translation in the provided context language:
     * - Property set
     * - Property groups
     * - Property options
     *
     * Required conditions for the selection:
     * - Selects only values which assigned to the provided products
     * - Property values has to be sorted by the \Shopware\Bundle\StoreFrontBundle\Struct\Property\Set sort mode.
     * - Sort mode equals to 1, the values are sorted by the numeric value
     * - Sort mode equals to 3, the values are sorted by the position
     * - In all other cases the values are sorted by their alphanumeric value
     *
     * @return Set|null
     */
    public function get(BaseProduct $product, ShopContextInterface $context);
}
