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

namespace Shopware\Bundle\StoreFrontBundle\Gateway;

use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Media;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

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
     * @param BaseProduct[] $products
     *
     * @return array<string, array<int, Media>> Indexed by product number. Each element contains a \Shopware\Bundle\StoreFrontBundle\Struct\Media array.
     */
    public function getList($products, ShopContextInterface $context);

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
     * @return Media|null
     */
    public function getCover(BaseProduct $product, ShopContextInterface $context);

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
     * @return array<int, Media>|null
     */
    public function get(BaseProduct $product, ShopContextInterface $context);

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
     * @param BaseProduct[] $products
     *
     * @return array<string, Media> Indexed by the product order number
     */
    public function getCovers($products, ShopContextInterface $context);
}
