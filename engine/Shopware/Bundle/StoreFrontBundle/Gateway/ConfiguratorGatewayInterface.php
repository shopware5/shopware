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

interface ConfiguratorGatewayInterface
{
    /**
     * The \Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Set requires the following data:
     * - Configurator set
     * - Core attribute of the configurator set
     * - Groups of the configurator set
     * - Options of each group
     *
     * Required translation in the provided context language:
     * - Configurator groups
     * - Configurator options
     *
     * The configurator groups and options has to be sorted in the following order:
     * - Group position
     * - Group name
     * - Option position
     * - Option name
     *
     * @return Struct\Configurator\Set
     */
    public function get(Struct\BaseProduct $product, Struct\ShopContextInterface $context);

    /**
     * Selects the first possible product image for the provided product configurator.
     * The product images are selected over the image mapping.
     * The image mapping defines which product image should be displayed for which configurator selection.
     * Returns for each configurator option the first possible image
     *
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct          $product
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface $context
     *
     * @return Struct\Media[] indexed by the configurator option id
     */
    public function getConfiguratorMedia(Struct\BaseProduct $product, Struct\ShopContextInterface $context);

    /**
     * Returns all possible configurator combinations for the provided product.
     * The returned array contains as array key the id of the configurator option.
     * The array value contains an imploded array with all possible configurator option ids
     * which can be combined with the option.
     *
     * Example (written with the configurator option names)
     * array(
     *     'white' => array('XL', 'L'),
     *     'red'   => array('S', ...)
     * )
     *
     * If the configurator contains only one group the function has to return an array indexed
     * by the ids, which are selectable:
     *
     * Example (written with the configurator option names)
     * array(
     *     'white' => array(),
     *     'red'   => array()
     * )
     *
     * @return array Indexed by the option id
     */
    public function getProductCombinations(Struct\BaseProduct $product);
}
