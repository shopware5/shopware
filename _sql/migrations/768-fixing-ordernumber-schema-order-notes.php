<?php

class Migrations_Migration768 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // s_order_notes
        $sql = <<<'EOD'
            ALTER TABLE `s_order_notes`
            MODIFY COLUMN `ordernumber` varchar(255) NOT NULL;
EOD;
        $this->addSql($sql);
    }
}
