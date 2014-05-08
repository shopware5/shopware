<?php
class Migrations_Migration365 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up()
    {
        $sql = <<<'EOD'
       ALTER TABLE `s_emotion` ADD `device` INT( 1 ) NOT NULL AFTER `container_width` ;
EOD;
        $this->addSql($sql);
    }
}



