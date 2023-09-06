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

use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Media;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

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
     * @return Media
     */
    public function get($id, ShopContextInterface $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\ProductMediaGatewayInterface::get()
     *
     * @param int[] $ids
     *
     * @return array<int, Media> Indexed by the media id
     */
    public function getList($ids, ShopContextInterface $context);

    /**
     * @see \Shopware\Bundle\StoreFrontBundle\Service\MediaServiceInterface::getProductMedia()
     *
     * @param BaseProduct[] $products
     *
     * @return array<string, array<Media>> indexed by the product order number, each array element contains a \Shopware\Bundle\StoreFrontBundle\Struct\Media array
     */
    public function getProductsMedia($products, ShopContextInterface $context);

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
     * @return Media|null
     */
    public function getCover(BaseProduct $product, ShopContextInterface $context);

    /**
     * @see \Shopware\Bundle\StoreFrontBundle\Service\MediaServiceInterface::getCover()
     *
     * @param BaseProduct[] $products
     *
     * @return array<string, Media> Indexed by product number
     */
    public function getCovers($products, ShopContextInterface $context);

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
     * @return Media[]|null
     */
    public function getProductMedia(BaseProduct $product, ShopContextInterface $context);
}
