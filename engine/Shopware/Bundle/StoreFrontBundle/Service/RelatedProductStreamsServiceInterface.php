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

use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductStream;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

interface RelatedProductStreamsServiceInterface
{
    /**
     * @see \Shopware\Bundle\StoreFrontBundle\Service\RelatedProductStreamsServiceInterface::get()
     *
     * @param BaseProduct[] $products
     *
     * @return array<string, array<ProductStream>> indexed with the product number, each array element contains product stream structs
     */
    public function getList($products, ShopContextInterface $context);

    /**
     * Selects all related product stream ids for the provided product.
     *
     * @return array<ProductStream>|null array of product stream structs
     */
    public function get(BaseProduct $product, ShopContextInterface $context);
}
