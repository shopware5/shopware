<?php
class Migrations_Migration383 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
        ALTER TABLE `s_user_shippingaddress` MODIFY `street` VARCHAR(255);
EOD;
        $this->addSql($sql);
    }
}
