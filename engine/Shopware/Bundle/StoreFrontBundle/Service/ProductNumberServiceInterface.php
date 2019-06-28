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

use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

interface ProductNumberServiceInterface
{
    /**
     * Returns the first possible product number that is available for purchase.
     *
     * Validates the provided parameters in the following order:
     * 1. Checks if the provided configuration selection matches an available order number
     * 2. Checks if the provided product number matches an available order number
     * 3. Returns the first available order number
     * 4. Returns the first active order number
     *
     * @param string $number
     * @param array  $selection Key - value array, key contains the configurator group id, value contains the configurator option id
     *
     * @return string
     */
    public function getAvailableNumber($number, ShopContextInterface $context, $selection = []);

    /**
     * Returns the main product number of the provided product id.
     *
     * @param int $productId
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function getMainProductNumberById($productId);
}
