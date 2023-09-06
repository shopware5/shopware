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

namespace Shopware\Bundle\StoreFrontBundle\Service;

use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

interface PriceCalculationServiceInterface
{
    /**
     * Calculates all prices of the product.
     *
     * The product contains two different prices, the graduated prices and the cheapest price.
     * Each price type contains a single or multiple \Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule elements.
     *
     * Each price rule contains a price, pseudo price and a reference price which calculates over
     * the assigned price unit based on the original price.
     *
     * The calculated \Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule structs are wrapped into a \Shopware\Bundle\StoreFrontBundle\Struct\Product\Price
     * struct which contains only the calculated price values and the reference to his rule.
     *
     * @deprecated 5.7, interface will require a ShopContextInterface in 5.8
     *
     * @return void
     */
    public function calculateProduct(ListProduct $product, ProductContextInterface $context);
}
