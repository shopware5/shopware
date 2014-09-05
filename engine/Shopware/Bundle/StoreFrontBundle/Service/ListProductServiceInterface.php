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
interface ListProductServiceInterface
{
    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface::get()
     *
     * @param array $numbers
     * @param Struct\ProductContext $context
     * @return Struct\ListProduct[] Indexed by the product order number.
     */
    public function getList(array $numbers, Struct\ProductContext $context);

    /**
     * Returns a full \Shopware\Bundle\StoreFrontBundle\Struct\ListProduct object.
     *
     * A full \Shopware\Bundle\StoreFrontBundle\Struct\ListProduct is build over the following classes:
     * - \Shopware\Bundle\StoreFrontBundle\Gateway\ListProductGatewayInterface      > Selects the base product data
     * - \Shopware\Bundle\StoreFrontBundle\Service\MediaServiceInterface            > Selects the cover
     * - \Shopware\Bundle\StoreFrontBundle\Service\GraduatedPricesServiceInterface  > Selects the graduated prices
     * - \Shopware\Bundle\StoreFrontBundle\Service\CheapestPriceServiceInterface    > Selects the cheapest price
     *
     * This data will be injected into the generated \Shopware\Bundle\StoreFrontBundle\Struct\ListProduct object
     * and will be calculated through the \Shopware\Bundle\StoreFrontBundle\Service\PriceCalculationServiceInterface class.
     *
     * @param string $number
     * @param Struct\ProductContext $context
     * @return Struct\ListProduct
     */
    public function get($number, Struct\ProductContext $context);
}
