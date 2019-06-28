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

interface MediaServiceInterface
{
    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\ProductMediaGatewayInterface::get()
     *
     * @param int $id
     *
     * @return Struct\Media
     */
    public function get($id, Struct\ShopContextInterface $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\ProductMediaGatewayInterface::get()
     *
     * @param int[] $ids
     *
     * @return Struct\Media[] Indexed by the media id
     */
    public function getList($ids, Struct\ShopContextInterface $context);

    /**
     * @see \Shopware\Bundle\StoreFrontBundle\Service\MediaServiceInterface::getProductMedia()
     *
     * @param Struct\BaseProduct[] $products
     *
     * @return array indexed by the product order number, each array element contains a \Shopware\Bundle\StoreFrontBundle\Struct\Media array
     */
    public function getProductsMedia($products, Struct\ShopContextInterface $context);

    /**
     * If the forceArticleMainImageInListing configuration is activated,
     * the function try to selects the first product media which has a configurator configuration
     * for the provided product.
     *
     * If no configurator image exist, the function returns the fallback main image of the product.
     *
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\VariantMediaGatewayInterface::getCover()
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\ProductMediaGatewayInterface::getCover()
     *
     * @return Struct\Media
     */
    public function getCover(Struct\BaseProduct $product, Struct\ShopContextInterface $context);

    /**
     * @see \Shopware\Bundle\StoreFrontBundle\Service\MediaServiceInterface::getCover()
     *
     * @param Struct\BaseProduct[]                                          $products
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface $context
     *
     * @return Struct\Media[] Indexed by product number
     */
    public function getCovers($products, Struct\ShopContextInterface $context);

    /**
     * Selects first the media structs which have a configurator configuration for the provided product variant.
     * The normal product media structs which has no configuration, are appended to the configurator media structs.
     *
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\ProductMediaGatewayInterface::get()
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\VariantMediaGatewayInterface::get()
     *
     * @return Struct\Media[]
     */
    public function getProductMedia(Struct\BaseProduct $product, Struct\ShopContextInterface $context);
}
