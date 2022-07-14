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

class Migrations_Migration302 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $statement = $this->getConnection()->prepare(
            "SELECT * FROM s_core_plugins WHERE name = 'Cron' AND installation_date IS NOT NULL"
        );

        $statement->execute();
        $data = $statement->fetchAll();

        if (!empty($data)) {
            $sql = <<<'EOD'
                SET @parentId = (SELECT id FROM s_core_config_forms WHERE name = 'Other' LIMIT 1);
                SET @pluginId = (SELECT id FROM s_core_plugins WHERE name = 'Cron' LIMIT 1);

                INSERT IGNORE INTO `s_core_config_forms`
                (`parent_id`, `name`, `label`, `description`, `position`, `scope`, `plugin_id`)
                VALUES (@parentId, 'CronSecurity', 'Cron-Sicherheit', NULL, '0', '0', @pluginId);

                SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'CronSecurity' LIMIT 1);

                INSERT IGNORE INTO `s_core_config_form_translations` (`form_id`, `locale_id`, `label`, `description`)
                VALUES (@formId, '2', 'Cron security', NULL);

                INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`)
                VALUES (@formId, 'cronSecureAllowedKey', 's:0:"";', 'Gültiger Schlüssel', 'Hinterlegen Sie hier einen Key zum Ausführen der Cronjobs.', 'text', 0, 0, 0, NULL, NULL);

                INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`)
                VALUES (@formId, 'cronSecureAllowedIp', 's:0:"";', 'Zulässige IP(s)', 'Nur angegebene IP-Adressen können die Cron Anfragen auslösen. Mehrere IP-Adressen müssen durch ein '';'' getrennt werden.', 'text', 0, 0, 0, NULL, NULL);

                INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`)
                VALUES (@formId, 'cronSecureByAccount', 'b:0;', 'Durch Benutzerkonto absichern', 'Es werden nur Anfragen von authentifizierten Administratoren akzeptieren', 'boolean', 0, 0, 0, NULL, NULL);

                SET @cronSecureAllowedKeyId = (SELECT id FROM s_core_config_elements WHERE form_id = @formId AND `name` = 'cronSecureAllowedKey');
                SET @cronSecureAllowedIpId = (SELECT id FROM s_core_config_elements WHERE form_id = @formId AND `name` = 'cronSecureAllowedIp');
                SET @cronSecureByAccountId = (SELECT id FROM s_core_config_elements WHERE form_id = @formId AND `name` = 'cronSecureByAccount');

                INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
                VALUES (@cronSecureAllowedKeyId, 2, 'Allowed key', 'If provided, cron requests will be executed if the inserted value is provided as ''key'' in the request');

                INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
                VALUES (@cronSecureAllowedIpId, 2, 'If provided, cron requests will be executed if triggered from the given IP address(es). Use '';'' to separate multiple addresses.');

                INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
                VALUES (@cronSecureByAccountId, 2, 'Secure using account', 'If set, requests received from authenticated admin users will be accepted');
EOD;

            $this->addSql($sql);
        }
    }
}
