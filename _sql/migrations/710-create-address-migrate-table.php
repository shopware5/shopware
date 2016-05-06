<?php

class Migrations_Migration710 extends Shopware\Components\Migrations\AbstractMigration
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

        $this->removeCompanySalutation();

        $this->createMigrationFields();

        $sql = <<<SQL
CREATE TABLE `s_user_addresses_migration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `company` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `department` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL,
  `salutation` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zipcode` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `country_id` int(11) NOT NULL,
  `state_id` int(11) DEFAULT NULL,
  `ustid` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_address_line1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_address_line2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `checksum` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE `unik` (`checksum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
SQL;

        $this->addSql($sql);
    }

    private function createMigrationFields()
    {
        $sql = <<<SQL
ALTER TABLE `s_user_addresses`
  ADD `migration_id` int(11) DEFAULT NULL,
  ADD INDEX `migrate` (`migration_id`);
SQL;

        $this->addSql($sql);
    }

    private function removeCompanySalutation()
    {
        $this->addSql("UPDATE `s_user_billingaddress` SET salutation = 'mr' WHERE salutation = 'company';");
        $this->addSql("UPDATE `s_user_shippingaddress` SET salutation = 'mr' WHERE salutation = 'company';");
        $this->addSql("UPDATE `s_order_billingaddress` SET salutation = 'mr' WHERE salutation = 'company';");
        $this->addSql("UPDATE `s_order_shippingaddress` SET salutation = 'mr' WHERE salutation = 'company';");
    }
}
