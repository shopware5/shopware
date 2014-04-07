<?php
class Migrations_Migration357 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up()
    {
        $sql = <<<'EOD'
            ALTER TABLE  `s_articles_supplier` ADD  `changed` DATETIME NOT NULL ;
            ALTER TABLE  `s_cms_static` ADD  `changed` DATETIME NOT NULL ;

EOD;
        $this->addSql($sql);    }
}
