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

interface AdditionalTextServiceInterface
{
    /**
     * Determines the 'additional text' value for a single ListProduct.
     *
     * If the ListProduct's 'additional' field is not empty, its value is returned.
     * For products with configurator groups and empty 'additional' field, the new value is calculated
     * based on the associated group options
     *
     * This behaviour can be optionally disabled using the backend settings
     *
     * @return Struct\ListProduct $product
     */
    public function buildAdditionalText(Struct\ListProduct $product, Struct\ShopContextInterface $context);

    /**
     * Determines the 'additional text' value for multiple ListProduct.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Service\AdditionalTextServiceInterface::buildAdditionalText()
     *
     * @param Struct\ListProduct[] $products
     *
     * @return Struct\ListProduct[] $products
     */
    public function buildAdditionalTextLists($products, Struct\ShopContextInterface $context);
}
