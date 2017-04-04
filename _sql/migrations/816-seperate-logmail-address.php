<?php

class Migrations_Migration816 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'SQL'
SET @formId = (SELECT id FROM `s_core_config_forms` WHERE name='Log');
SQL;

        $this->addSql("SET @localeID = (SELECT `id` FROM `s_core_locales` WHERE `locale` = 'en_GB' LIMIT 1);");
        $this->addSql($sql);

        $sql = <<<'SQL'
      INSERT IGNORE INTO `s_core_config_elements`
                (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
                VALUES
                (@formID, 'logMailAddress', 's:0:"";', 'Seperate Mail-Adresse für Fehlermeldungen', 'Wenn dieses Feld leer ist, wird die Shopbetreiber Mail-Adresse verwendet', 'text', 1, 0, 0, NULL)
SQL;
        $this->addSql($sql);
        $this->addSql('SET @elementID = (SELECT id FROM s_core_config_elements WHERE name = "logMailAddress")');

        $sql = <<<EOD
                INSERT IGNORE INTO `s_core_config_element_translations`
                (`element_id`, `locale_id`, `label`, `description`)
                VALUES
                (@elementID, @localeID, 'Alternative e-mail address for errors', 'If this field is empty, the shop e-mail address will be used');
EOD;
        $this->addSql($sql);
    }
}
