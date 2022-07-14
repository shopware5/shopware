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

class Migrations_Migration746 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<SQL
INSERT IGNORE INTO s_user_shippingaddress
	(`userID`, `company`, `department`, `salutation`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `countryID`, `stateID`, `additional_address_line1`, `additional_address_line2`, `title`)
SELECT
	 addresses.`user_id` as userID, addresses.`company`, addresses.`department`, addresses.`salutation`, addresses.`firstname`, addresses.`lastname`, addresses.`street`, addresses.`zipcode`, addresses.`city`, addresses.`country_id` as countryID, addresses.`state_id` as stateID, addresses.`additional_address_line1`, addresses.`additional_address_line2`, addresses.`title`
FROM 
    s_user_addresses addresses
INNER JOIN s_user user ON user.default_shipping_address_id = addresses.id
SQL;

        $this->addSql($sql);
    }
}
