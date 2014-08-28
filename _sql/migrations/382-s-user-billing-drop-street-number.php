<?php
class Migrations_Migration382 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
        ALTER TABLE `s_user_billingaddress` DROP `streetnumber`;
EOD;
        $this->addSql($sql);
    }
}
