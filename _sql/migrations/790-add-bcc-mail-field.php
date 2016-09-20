<?php

class Migrations_Migration790 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('SET @menu_id = (SELECT id FROM s_core_config_forms WHERE label=\'Stammdaten\');');
        $this->addSql('INSERT INTO s_core_config_elements (form_id, name, value, label, description, type, required, scope) VALUES(@menu_id, "mailBcc", "s:0:\"\";", "Shopbetreiber BCC E-Mail", "Setzt bei jeder Mail einen BCC (Mehrere E-Mail Adressen können mit Komma getrennt werden)", "text", 0, 1);');
        $this->addSql('SET @element_id = (SELECT id FROM s_core_config_elements WHERE name = "mailBcc");');
        $this->addSql('INSERT INTO s_core_config_element_translations (element_id, locale_id, label, description) VALUES(@element_id, 2, "Show owner bcc mail", "Allows to set on any mail a bcc (multiple email addresses can be separated by commas)");');
    }
}
