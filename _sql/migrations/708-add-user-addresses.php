<?php

class Migrations_Migration708 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     * @return void
     */
    public function up($modus)
    {
        $this->changeConfusingVatLabel();
        $this->createAddressTables();
        $this->createDefaultShippingBillingRelations();
    }

    private function createAddressTables()
    {
        $sql = <<<SQL
CREATE TABLE `s_user_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `company` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `department` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL,
  `salutation` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zipcode` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `countryID` int(11) NOT NULL,
  `stateID` int(11) DEFAULT NULL,
  `ustid` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_address_line1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_address_line2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`),
  KEY `countryID` (`countryID`),
  KEY `stateID` (`stateID`),
  CONSTRAINT `s_user_addresses_ibfk_1` FOREIGN KEY (`countryID`) REFERENCES `s_core_countries` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `s_user_addresses_ibfk_2` FOREIGN KEY (`stateID`) REFERENCES `s_core_countries_states` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `s_user_addresses_ibfk_3` FOREIGN KEY (`userID`) REFERENCES `s_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
SQL;

        $this->addSql($sql);

        $sql = <<<SQL
CREATE TABLE `s_user_addresses_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `addressID` int(11) NOT NULL,
  `text1` VARCHAR(255) DEFAULT NULL,
  `text2` VARCHAR(255) DEFAULT NULL,
  `text3` VARCHAR(255) DEFAULT NULL,
  `text4` VARCHAR(255) DEFAULT NULL,
  `text5` VARCHAR(255) DEFAULT NULL,
  `text6` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `addressID` (`addressID`),
  CONSTRAINT `s_user_addresses_attributes_ibfk_1` FOREIGN KEY (`addressID`) REFERENCES `s_user_addresses` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
SQL;
        $this->addSql($sql);
    }

    private function changeConfusingVatLabel()
    {
        $sql = <<<SQL
UPDATE
  s_core_config_elements origin
JOIN
  s_core_config_element_translations translation
  ON origin.id = translation.element_id AND translation.locale_id = 2
SET
  origin.label = 'USt-IdNr. für Firmenkunden als Pflichtfeld markieren',
  translation.label = 'Mark VAT ID number as required for company customers'
WHERE
  origin.name = 'vatcheckrequired'
SQL;
        $this->addSql($sql);
    }

    private function createDefaultShippingBillingRelations()
    {
        $sql = <<<SQL

ALTER TABLE `s_user`
ADD `defaultBillingAddressID` int(11) DEFAULT NULL,
ADD `defaultShippingAddressID` int(11) DEFAULT NULL AFTER `defaultBillingAddressID`,
ADD INDEX `defaultBillingAddressID` (`defaultBillingAddressID`),
ADD INDEX `defaultShippingAddressID` (`defaultShippingAddressID`),
ADD FOREIGN KEY (`defaultBillingAddressID`) REFERENCES `s_user_addresses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
ADD FOREIGN KEY (`defaultShippingAddressID`) REFERENCES `s_user_addresses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
SQL;

        $this->addSql($sql);
    }
}
