<?php

class Migrations_Migration818 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('ALTER TABLE s_order CHANGE COLUMN partnerID partnerID VARCHAR(255) CHARACTER SET \'utf8\' COLLATE \'utf8_unicode_ci\' NULL;');
        $this->addSql('ALTER TABLE s_order CHANGE COLUMN remote_addr remote_addr VARCHAR(255) CHARACTER SET \'utf8\' COLLATE \'utf8_unicode_ci\' NULL;');
        $this->addSql('ALTER TABLE s_order_details CHANGE COLUMN ordernumber ordernumber VARCHAR(255) CHARACTER SET \'utf8\' COLLATE \'utf8_unicode_ci\' DEFAULT \'\';');
    }
}
