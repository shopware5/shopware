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
interface Product
{
    /**
     * @see \Shopware\Service\Product::get()
     *
     * @param $numbers
     * @param Struct\Context $context
     * @return Struct\Product[] Indexed by the product order number
     */
    public function getList($numbers, Struct\Context $context);

    /**
     * Returns a full \Shopware\Struct\Product object which all required data.
     *
     * A full \Shopware\Struct\Product is build over the following classes:
     * - \Shopware\Gateway\Product
     * - \Shopware\Service\Media
     * - \Shopware\Service\GraduatedPrices
     * - \Shopware\Service\Vote
     * - \Shopware\Service\RelatedProducts
     * - \Shopware\Service\SimilarProducts
     * - \Shopware\Service\ProductDownload
     * - \Shopware\Service\ProductLink
     * - \Shopware\Service\Property
     * - \Shopware\Service\Configurator
     * - \Shopware\Service\CheapestPrice
     * - \Shopware\Service\Marketing
     *
     * The different services selects the specify product associated data
     * for the provided product.
     *
     * The function injects the different sources into the \Shopware\Struct\Product class
     * and calculates the prices for the store front through a \Shopware\Service\PriceCalculation class.
     *
     * @param $number
     * @param Struct\Context $context
     * @return Struct\Product
     */
    public function get($number, Struct\Context $context);
}
