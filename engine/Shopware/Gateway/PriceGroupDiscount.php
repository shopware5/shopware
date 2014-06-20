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
interface PriceGroupDiscount
{
    /**
     * The \Shopware\Struct\Product\PriceDiscount requires the following data:
     * - Price group discount base data
     *
     * Required conditions for the selection:
     * - \Shopware\Struct\Product\PriceDiscount::quantity is less or equals the provided quantity.
     * - Selects only discounts for the passed customer group
     * - Selects the discount with the highest value
     *
     * @param Struct\Product\PriceGroup $priceGroup
     * @param Struct\Customer\Group $customerGroup
     * @param $quantity
     * @return Struct\Product\PriceDiscount
     */
    public function getHighestQuantityDiscount(
        Struct\Product\PriceGroup $priceGroup,
        Struct\Customer\Group $customerGroup,
        $quantity
    );

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\PriceGroupDiscount::getProductDiscount()
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Customer\Group $customerGroup
     * @param Struct\Context $context
     * @return array Indexed by the product number. Each element contains a Struct\Product\PriceDiscount array.
     */
    public function getProductsDiscounts(
        array $products,
        Struct\Customer\Group $customerGroup,
        Struct\Context $context
    );

    /**
     * The \Shopware\Struct\Product\PriceDiscount requires the following data:
     * - Price group discount base data
     *
     * Required conditions for the selection:
     * - Sorted ascending by the \Shopware\Struct\Product\PriceDiscount::quantity property.
     *
     * @param Struct\ListProduct $product
     * @param Struct\Customer\Group $customerGroup
     * @param Struct\Context $context
     * @return Struct\Product\PriceDiscount[]
     */
    public function getProductDiscount(
        Struct\ListProduct $product,
        Struct\Customer\Group $customerGroup,
        Struct\Context $context
    );
}