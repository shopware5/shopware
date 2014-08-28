<?php
class Migrations_Migration376 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
        ALTER TABLE `s_order_billingaddress` DROP `streetnumber`;
EOD;
        $this->addSql($sql);
    }
}
