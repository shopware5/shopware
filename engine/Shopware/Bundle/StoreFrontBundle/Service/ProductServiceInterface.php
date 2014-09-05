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
     * @param Struct\ProductContext $context
     * @return Struct\Product[] Indexed by the product order number
     */
    public function getList($numbers, Struct\ProductContext $context);

    /**
     * Returns a full \Shopware\Bundle\StoreFrontBundle\Struct\Product object which all required data.
     *
     * A full \Shopware\Bundle\StoreFrontBundle\Struct\Product is build over the following classes:
     * - \Shopware\Bundle\StoreFrontBundle\Gateway\ProductGatewayInterfaceServiceInterface
     * - \Shopware\Bundle\StoreFrontBundle\Service\MediaServiceInterface
     * - \Shopware\Bundle\StoreFrontBundle\Service\GraduatedPricesServiceInterface
     * - \Shopware\Bundle\StoreFrontBundle\Service\VoteServiceInterface
     * - \Shopware\Bundle\StoreFrontBundle\Service\RelatedProductsServiceInterface
     * - \Shopware\Bundle\StoreFrontBundle\Service\SimilarProductsServiceInterface
     * - \Shopware\Bundle\StoreFrontBundle\Service\ProductDownloadServiceInterface
     * - \Shopware\Bundle\StoreFrontBundle\Service\ProductLinkServiceInterface
     * - \Shopware\Bundle\StoreFrontBundle\Service\PropertyServiceInterface
     * - \Shopware\Bundle\StoreFrontBundle\Service\ConfiguratorServiceInterface
     * - \Shopware\Bundle\StoreFrontBundle\Service\CheapestPriceServiceInterface
     * - \Shopware\Bundle\StoreFrontBundle\Service\MarketingServiceInterface
     *
     * The different services selects the specify product associated data
     * for the provided product.
     *
     * The function injects the different sources into the \Shopware\Bundle\StoreFrontBundle\Struct\Product class
     * and calculates the prices for the store front through a \Shopware\Bundle\StoreFrontBundle\Service\PriceCalculationServiceInterface class.
     *
     * @param $number
     * @param Struct\ProductContext $context
     * @return Struct\Product
     */
    public function get($number, Struct\ProductContext $context);
}
