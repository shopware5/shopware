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
use Shopware\Bundle\StoreFrontBundle\Struct\Media;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
interface MediaServiceInterface
{
    /**
     * @param int[]                $ids
     * @param ShopContextInterface $context
     *
     * @return Media[] Indexed by the media id
     */
    public function getList($ids, ShopContextInterface $context);

    /**
     * Selects first the media structs which have a configurator configuration for the provided product variant.
     * The normal product media structs which has no configuration, are appended to the configurator media structs.
     *
     * @param BaseProduct[]        $products
     * @param ShopContextInterface $context
     *
     * @return array indexed by the product order number, each array element contains a \Shopware\Bundle\StoreFrontBundle\Media array
     */
    public function getProductsMedia($products, ShopContextInterface $context);

    /**
     * @param BaseProduct[]        $products
     * @param ShopContextInterface $context
     *
     * @return Media[] Indexed by product number
     */
    public function getCovers($products, ShopContextInterface $context);
}
