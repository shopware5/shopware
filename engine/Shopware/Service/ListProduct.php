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

namespace Shopware\Service;

use Shopware\Struct;

/**
 * @package Shopware\Service
 */
interface ListProduct
{
    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Service\ListProduct::get()
     *
     * @param array $numbers
     * @param Struct\Context $context
     * @return Struct\ListProduct[] Indexed by the product order number.
     */
    public function getList(array $numbers, Struct\Context $context);

    /**
     * Returns a full \Shopware\Struct\ListProduct object.
     *
     * A full \Shopware\Struct\ListProduct is build over the following classes:
     * - \Shopware\Gateway\ListProduct      > Selects the base product data
     * - \Shopware\Service\Media            > Selects the cover
     * - \Shopware\Service\GraduatedPrices  > Selects the graduated prices
     * - \Shopware\Service\CheapestPrice    > Selects the cheapest price
     *
     * This data will be injected into the generated \Shopware\Struct\ListProduct object
     * and will be calculated through the \Shopware\Service\PriceCalculation class.
     *
     * @param string $number
     * @param Struct\Context $context
     * @return Struct\ListProduct
     */
    public function get($number, Struct\Context $context);
}
