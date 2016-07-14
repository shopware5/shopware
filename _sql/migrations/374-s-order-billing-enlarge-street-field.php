<?php
class Migrations_Migration374 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
        ALTER TABLE `s_order_billingaddress` MODIFY `street` VARCHAR(255);
EOD;
        $this->addSql($sql);
    }
}
