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

interface VoteServiceInterface
{
    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\VoteAverageGatewayInterface::get()
     *
     * @return Struct\Product\VoteAverage
     */
    public function getAverage(Struct\BaseProduct $product, Struct\ShopContextInterface $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\VoteGatewayInterface::get()
     *
     * @param Struct\BaseProduct[] $products
     *
     * @return array indexed by the product order number, each array element contains a \Shopware\Bundle\StoreFrontBundle\Struct\Vote array
     */
    public function getList($products, Struct\ShopContextInterface $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\VoteGatewayInterface::get()
     *
     * @return Struct\Product\Vote[]
     */
    public function get(Struct\BaseProduct $product, Struct\ShopContextInterface $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\VoteAverageGatewayInterface::get()
     *
     * @param Struct\BaseProduct[] $products
     *
     * @return Struct\Product\VoteAverage[] Indexed by the product order number
     */
    public function getAverages($products, Struct\ShopContextInterface $context);
}
