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

class Migrations_Migration713 extends Shopware\Components\Migrations\AbstractMigration
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
    's_user_shippingaddress' as original_type,
    s_user_shippingaddress.id as original_id,
    userID, company, department, s_user_shippingaddress.salutation, s_user_shippingaddress.firstname, s_user_shippingaddress.lastname, street, zipcode, city, additional_address_line1, additional_address_line2, countryID, IF(stateID = 0, NULL, stateID),
    MD5(CONCAT_WS('', userID, company, department, s_user_shippingaddress.salutation, s_user_shippingaddress.firstname, s_user_shippingaddress.lastname, street, zipcode, city, additional_address_line1, additional_address_line2, countryID, IF(stateID = 0, NULL, stateID)))
  FROM s_user_shippingaddress
  INNER JOIN s_user ON s_user_shippingaddress.userID = s_user.id
  INNER JOIN s_core_countries ON s_user_shippingaddress.countryID = s_core_countries.id
)
SQL;

        $this->addSql($sql);
    }
}
