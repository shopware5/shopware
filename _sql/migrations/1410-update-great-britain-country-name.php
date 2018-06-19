<?php

class Migrations_Migration1410 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up($modus)
    {
        $sql = <<<'EOD'
UPDATE s_core_countries SET `countryname` = 'Great Britain' WHERE `countryiso` = 'GB' AND `countryname` = 'Great britain';
EOD;
        $this->addSql($sql);
	}

}
