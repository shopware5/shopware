<?php

class Migrations_Migration749 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
ALTER TABLE `s_articles_img` CHANGE `img` `img` VARCHAR(255);
EOD;

        $this->addSql($sql);
    }
}
