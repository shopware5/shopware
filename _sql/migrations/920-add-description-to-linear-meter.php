<?php

class Migrations_Migration920 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // detect if english shop is installed
        $isEnglish = (bool) $this->getConnection()->query('SELECT 1 FROM s_core_units WHERE id = 9 AND unit = "unit" AND description = "Unit"')->rowCount();
        if ($isEnglish) {
            $this->addSql('UPDATE s_core_units SET description = "Linear Meter(s)", unit = "lm" WHERE id = 5');
            return;
        }

        $this->addSql('UPDATE s_core_units SET description = "Laufende(r) Meter" WHERE id = 5');
    }
}
