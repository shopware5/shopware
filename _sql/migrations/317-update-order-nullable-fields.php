<?php
class Migrations_Migration317 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up()
    {
        $this->addSql("
            ALTER TABLE `s_order` CHANGE `partnerID` `partnerID` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;
            ALTER TABLE `s_order` CHANGE `transactionID` `transactionID` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;
            ALTER TABLE `s_order` CHANGE `temporaryID` `temporaryID` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;
            ALTER TABLE `s_order` CHANGE `referer` `referer` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
            ALTER TABLE `s_order` CHANGE `trackingcode` `trackingcode` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;
            ALTER TABLE `s_order` CHANGE `remote_addr` `remote_addr` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;
            ALTER TABLE `s_order` CHANGE `comment` `comment` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
            ALTER TABLE `s_order` CHANGE `customercomment` `customercomment` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
            ALTER TABLE `s_order` CHANGE `internalcomment` `internalcomment` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
        ");
    }
}