<?php

class Migrations_Migration714 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     * @return void
     */
    public function up($modus)
    {
        if ($modus == self::MODUS_INSTALL) {
            return;
        }

        $sql = <<<SQL
INSERT IGNORE INTO s_user_addresses_migration (user_id, company, department, salutation, firstname, lastname, street, zipcode, city, additional_address_line1, additional_address_line2, country_id, state_id, checksum, text1, text2, text3, text4, text5, text6)
(
  SELECT
    userID, company, department, salutation, firstname, lastname, street, zipcode, city, additional_address_line1, additional_address_line2, countryID, IF(stateID = 0, NULL, stateID),
    MD5(CONCAT_WS('', userID, company, department, salutation, firstname, lastname, street, zipcode, city, additional_address_line1, additional_address_line2, countryID, stateID, null, null)),
    attr.text1, attr.text2, attr.text3, attr.text4, attr.text5, attr.text6
  FROM s_user_shippingaddress
  LEFT JOIN s_user_shippingaddress_attributes AS attr ON attr.shippingID = s_user_shippingaddress.id
  INNER JOIN s_user ON s_user_shippingaddress.userID = s_user.id
  INNER JOIN s_core_countries ON s_user_shippingaddress.countryID = s_core_countries.id
)
SQL;

        $this->addSql($sql);
    }
}
