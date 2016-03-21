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
namespace Shopware\Bundle\StoreFrontBundle\Gateway;

use Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Gateway
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
interface GraduatedPricesGatewayInterface
{
    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\GraduatedPricesGatewayInterface::get()
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\ShopContextInterface $context
     * @param Struct\Customer\Group $customerGroup
     * @return array Indexed by the product order number, each array element contains a Struct\Product\PriceRule array.
     */
    public function getList($products, Struct\ShopContextInterface $context, Struct\Customer\Group $customerGroup);

    /**
     * The \Struct\Product\PriceRule requires the following data:
     * - Price base data
     * - Core attribute of the price
     *
     * Required conditions for the selection:
     * - Sorted ascending with the \Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule::from property.
     *
     * @param Struct\ListProduct $product
     * @param Struct\ShopContextInterface $context
     * @param Struct\Customer\Group $customerGroup
     * @return Struct\Product\PriceRule[]
     */
    public function get(Struct\ListProduct $product, Struct\ShopContextInterface $context, Struct\Customer\Group $customerGroup);
}
