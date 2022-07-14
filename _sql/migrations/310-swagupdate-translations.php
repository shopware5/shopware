<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

class Migrations_Migration310 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
SET @plugin_id = (SELECT id FROM s_core_plugins WHERE name='SwagUpdate');
SET @form_id   = (SELECT id FROM s_core_config_forms WHERE plugin_id = @plugin_id);
SET @locale_id = (SELECT id FROM s_core_locales WHERE locale LIKE "de_DE");

SET @element_id_channel = (SELECT id FROM s_core_config_elements WHERE form_id = @form_id and name LIKE "update-channel" LIMIT 1);
SET @element_id_code = (SELECT id FROM s_core_config_elements WHERE form_id = @form_id and name LIKE "update-code" LIMIT 1);
SET @element_id_feedback = (SELECT id FROM s_core_config_elements WHERE form_id = @form_id and name LIKE "update-send-feedback" LIMIT 1);

INSERT IGNORE INTO s_core_config_element_translations (element_id, locale_id, label) VALUES
(@element_id_feedback, @locale_id, 'Feedback senden'),
(@element_id_code,     @locale_id, 'Aktionscode'),
(@element_id_channel,  @locale_id, 'Update Kanal');
EOD;
        $this->addSql($sql);
    }
}
