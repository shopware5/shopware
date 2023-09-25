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

namespace Shopware\Models\Shop;

/**
 * Representation of the current storefront shop (Shopware()->Shop()).
 *
 * This has the same identity as Shopware\Models\Shop\Shop
 * but is no doctrine managed entity.
 * The ORM\Entity annotation is omitted intentionally.
 */
class DetachedShop extends Shop
{
    /**
     * @return DetachedShop
     */
    public static function createFromShop(Shop $shop)
    {
        $self = new self();

        foreach ($shop as $field => $value) {
            if (\in_array($field, ['__initializer__', '__cloner__', '__isInitialized__'], true)) {
                /* skip properties from Doctrine proxies @see \Doctrine\Common\Proxy\ProxyGenerator */
                continue;
            }
            $self->{$field} = $value;
        }

        return $self;
    }
}
