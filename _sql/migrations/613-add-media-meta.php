<?php

class Migrations_Migration613 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
ALTER TABLE `s_media` ADD `width` INT(11) UNSIGNED NULL AFTER `file_size`, ADD `height` INT(11) UNSIGNED NULL AFTER `width`;
EOD;

        $this->addSql($sql);
    }
}
