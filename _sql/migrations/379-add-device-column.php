<?php
class Migrations_Migration379 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up()
    {
        $sql = <<<'EOD'
       ALTER TABLE `s_emotion` ADD `device` INT( 1 ) NOT NULL;
EOD;
        $this->addSql($sql);
    }
}