<?php

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration1619 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
SET @formId = (SELECT `id` FROM `s_core_config_forms` WHERE `name`='Mail');
SET @localeId = (SELECT `id` FROM `s_core_locales` WHERE `locale`='en_GB');

SET @elementId = (SELECT `id` FROM `s_core_config_elements` WHERE form_id=@formId AND `name`='mailer_mailer');
UPDATE `s_core_config_element_translations` SET `label`='Sending method', `description`='\'smtp\' or \'file\'. \'mail\' is still supported but will be removed in Shopware 5.7. If you\'re currently using \'mail\', please switch to \'smtp\'.' WHERE `element_id`=@elementId AND `locale_id`=@localeId;
EOD;

        $this->addSql($sql);

        // Change selection order, add new description
        $this->addSql(
            sprintf(
                'UPDATE `s_core_config_elements` SET `options` = \'%s\', `type` = "combo", `description` = "\'smtp\' oder \'file\'. \'mail\' wird ggw. noch unterstützt, jedoch mit Shopware 5.7 entfernt. Wenn Sie aktuell \'mail\' einsetzen, wechseln Sie bitte zu \'smtp\'." WHERE `name` = "mailer_mailer"',
                serialize([
                    'displayValue' => 'name',
                    'valueField' => 'name',
                    'store' => 'new Ext.create("Ext.data.Store",{fields: [{name: "name", type: "string"}], data:[{"name": "smtp"},{"name": "file"},{"name": "mail"}]});',
                    'queryMode' => 'local',
                ])
            )
        );

        // Only change default for new installations
        if ($modus === AbstractMigration::MODUS_UPDATE) {
            return;
        }

        $sql = <<<'EOD'
SET @formId = (SELECT id FROM s_core_config_forms WHERE `name`='Mail');
UPDATE `s_core_config_elements` SET `value`='s:4:"smtp";' WHERE `form_id`=@formId AND `name`='mailer_mailer';
EOD;

        $this->addSql($sql);
    }
}

