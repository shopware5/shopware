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

use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopPage;

interface ShopPageChildrenGatewayInterface
{
    /**
     * The \Shopware\Bundle\StoreFrontBundle\Struct\ShopPage requires the following data:
     * - shop page data
     * - Core attribute of the shop page
     *
     * Required translation in the provided context language:
     * - Shop page
     *
     * @param int[] $ids
     *
     * @return array<int, ShopPage> Indexed by the shop page id
     */
    public function getList(array $ids, ShopContextInterface $context);
}
