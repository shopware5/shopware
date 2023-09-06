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
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

interface ProductConfigurationGatewayInterface
{
    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\ProductConfigurationGatewayInterface::get()
     *
     * @param BaseProduct[] $products
     *
     * @return array<string, array<Group>> indexed by the product order number, each array element contains a Struct\Configurator\Group array
     */
    public function getList($products, ShopContextInterface $context);

    /**
     * The \Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Group requires the following data:
     * - Configurator group data
     * - Only Configurator options which assigned to the product
     *
     * Required translation in the provided context language:
     * - Configurator groups
     * - Configurator options
     *
     * @return Group[]|null
     */
    public function get(BaseProduct $product, ShopContextInterface $context);
}
