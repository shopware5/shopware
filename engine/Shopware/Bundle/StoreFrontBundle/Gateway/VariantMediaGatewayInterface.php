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

interface VariantMediaGatewayInterface
{
    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\VariantMediaGatewayInterface::get()
     *
     * The passed $products array contains in some case two variations of the same product.
     * For example:
     *  - Product.1  (white / XL)
     *  - Product.2  (black / L)
     *
     * The
     * <php>
     * array(
     *     'Product.1' => array(
     *          Shopware\Bundle\StoreFrontBundle\Struct\Media(id=3)  (configuration: color=white / size=XL)
     *          Shopware\Bundle\StoreFrontBundle\Struct\Media(id=4)  (configuration: color=white)
     *      ),
     *     'Product.2' => array(
     *          Shopware\Bundle\StoreFrontBundle\Struct\Media(id=1)  (configuration: color=black)
     *          Shopware\Bundle\StoreFrontBundle\Struct\Media(id=2)  (configuration: size=L)
     *      )
     * )
     * </php>
     *
     * @param Struct\BaseProduct[] $products
     *
     * @return array Indexed by product number. Each element contains a \Shopware\Bundle\StoreFrontBundle\Struct\Media array.
     */
    public function getList($products, Struct\ShopContextInterface $context);

    /**
     * The \Shopware\Bundle\StoreFrontBundle\Struct\Media requires the following data:
     * - Product image data
     * - Media data
     * - Core attribute of the product image
     * - Core attribute of the media
     *
     * Required translation in the provided context language:
     * - Product image
     *
     * Required conditions for the selection:
     * - Selects only product media which has no configurator configuration
     * - Sorted ascending by the image main flag and position
     *
     * @return Struct\Media
     */
    public function getCover(Struct\BaseProduct $product, Struct\ShopContextInterface $context);

    /**
     * The \Shopware\Bundle\StoreFrontBundle\Struct\Media requires the following data:
     * - Product image data
     * - Media data
     * - Core attribute of the product image
     * - Core attribute of the media
     *
     * Required translation in the provided context language:
     * - Product image
     *
     * Required conditions for the selection:
     * - Selects only product media which has a configurator configuration for the provided variants.
     * - Sorted ascending by the image main flag and image position
     *
     * @return Struct\Media[]
     */
    public function get(Struct\BaseProduct $product, Struct\ShopContextInterface $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\VariantMediaGatewayInterface::getCover()
     *
     * The passed $products array contains in some case two variations of the same product.
     * For example:
     *  - Product.1  (white / XL)
     *  - Product.2  (black / L)
     *
     * The
     * <php>
     * array(
     *     'Product.1' => Shopware\Bundle\StoreFrontBundle\Struct\Media(id=4)  (configuration: color=white)
     *     'Product.2' => Shopware\Bundle\StoreFrontBundle\Struct\Media(id=1)  (configuration: color=black)
     * )
     * </php>
     *
     * @param Struct\BaseProduct[] $products
     *
     * @return Struct\Media[] Indexed by the product order number
     */
    public function getCovers($products, Struct\ShopContextInterface $context);
}
