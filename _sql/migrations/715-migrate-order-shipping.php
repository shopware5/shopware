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

class Migrations_Migration715 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     *
     * @return void
     */
    public function up($modus)
    {
        if ($modus == self::MODUS_INSTALL) {
            return;
        }

        $sql = <<<SQL
INSERT IGNORE INTO s_user_addresses_migration (original_type, original_id, user_id, company, department, salutation, firstname, lastname, street, zipcode, city, additional_address_line1, additional_address_line2, country_id, state_id, checksum)
(
  SELECT
    's_order_shippingaddress' as original_type,
    s_order_shippingaddress.id as original_id,
    userID, company, department, s_order_shippingaddress.salutation, s_order_shippingaddress.firstname, s_order_shippingaddress.lastname, street, zipcode, city, additional_address_line1, additional_address_line2, countryID, IF(stateID = 0, NULL, stateID),
    MD5(CONCAT_WS('', userID, company, department, s_order_shippingaddress.salutation, s_order_shippingaddress.firstname, s_order_shippingaddress.lastname, street, zipcode, city, additional_address_line1, additional_address_line2, countryID, IF(stateID = 0, NULL, stateID)))
  FROM s_order_shippingaddress
  INNER JOIN s_user ON s_order_shippingaddress.userID = s_user.id
  INNER JOIN s_core_countries ON s_order_shippingaddress.countryID = s_core_countries.id
)
SQL;
        $this->addSql($sql);
    }
}
