<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
namespace Shopware\Gateway;

use Shopware\Struct;

/**
 * @package Shopware\Gateway
 */
interface Vote
{
    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\Vote::get()
     *
     * @param Struct\ListProduct[] $products
     * @return array Indexed by the product number. Each elements contains a Struct\Product\Vote array.
     */
    public function getList(array $products);

    /**
     * The \Shopware\Struct\Vote requires the following data:
     * - Vote base data
     *
     * Required conditions for the selection:
     * - Sorted by the vote create date
     *
     * @param Struct\ListProduct $product
     * @return Struct\Product\Vote[]
     */
    public function get(Struct\ListProduct $product);
}