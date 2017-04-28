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

class Migrations_Migration718 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     */
    public function up($modus)
    {
        if ($modus == self::MODUS_INSTALL) {
            return;
        }

        $sql = <<<'SQL'
SET foreign_key_checks=0;

UPDATE s_user
LEFT JOIN s_user_addresses user_shipping ON s_user.id = user_shipping.user_id AND user_shipping.original_type = 's_user_shippingaddress'
LEFT JOIN s_user_addresses order_shipping ON s_user.id = order_shipping.user_id AND order_shipping.original_type = 's_order_shippingaddress'
SET default_shipping_address_id = COALESCE(user_shipping.id, order_shipping.id, default_billing_address_id);

SET foreign_key_checks=1;
SQL;

        $this->addSql($sql);
    }
}
