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

namespace Shopware\Components\Cart;

use Shopware\Bundle\CartBundle\CartKey;

class ProportionalCartNormalizer implements ProportionalCartNormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize(array $cart)
    {
        foreach ($cart[CartKey::POSITIONS] as $key => $item) {
            if (!isset($item['proportion'])) {
                continue;
            }

            foreach ($item['proportion'] as $proportionalItem) {
                $cart[CartKey::POSITIONS][] = $proportionalItem;
            }

            unset($cart[CartKey::POSITIONS][$key]);
        }

        return $cart;
    }
}
