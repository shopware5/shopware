<?php
class Migrations_Migration357 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up()
    {
        $dateTime = new DateTime();
        $date = $dateTime->format('Y-m-d H:i:s');

        $sql = <<<EOD
            ALTER TABLE  `s_articles_supplier` ADD  `changed` DATETIME NOT NULL DEFAULT "$date";
            ALTER TABLE  `s_cms_static` ADD  `changed` DATETIME NOT NULL DEFAULT "$date";
            ALTER TABLE  `s_core_paymentmeans` ADD  `mobileactive` INT( 1 ) NOT NULL DEFAULT 0;

EOD;
        $this->addSql($sql);
    }
}
