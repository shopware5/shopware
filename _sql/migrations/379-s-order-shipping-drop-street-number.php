<?php
class Migrations_Migration379 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
        ALTER TABLE `s_order_shippingaddress` DROP `streetnumber`;
EOD;
        $this->addSql($sql);
    }
}
