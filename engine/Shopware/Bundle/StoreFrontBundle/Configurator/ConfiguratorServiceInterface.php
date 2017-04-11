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

namespace Shopware\Bundle\StoreFrontBundle\Configurator;

use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\Product\BaseProduct;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
interface ConfiguratorServiceInterface
{
    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Product\BaseProduct[]        $products
     * @param \Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface $context
     *
     * @return array Each array element contains a Configurator\PropertyGroup[] array. The first level is indexed with the product number
     */
    public function getProductsConfigurations($products, ShopContextInterface $context);

    /**
     * @param BaseProduct                                                    $product
     * @param \Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface $context
     * @param array                                                          $selection
     *
     * @return ConfiguratorSet
     */
    public function getProductConfigurator(BaseProduct $product, ShopContextInterface $context, array $selection);
}
