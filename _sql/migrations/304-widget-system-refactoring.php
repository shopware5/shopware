<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

class Migrations_Migration304 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
            SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'Widget' LIMIT 1);

            DELETE FROM s_core_config_element_translations
            WHERE element_id IN (
                SELECT id FROM s_core_config_elements WHERE form_id = @formId
            );

            DELETE FROM s_core_config_elements
            WHERE form_id = @formId;

            DELETE FROM s_core_config_form_translations
            WHERE form_id = @formId;

            DELETE FROM s_core_config_forms
            WHERE id = @formId;

            ALTER TABLE `s_core_widget_views` DROP `label`;

            ALTER TABLE `s_core_widgets` ADD `plugin_id` INT( 11 ) NULL ;

            ALTER TABLE `s_core_widgets` CHANGE `label` `label` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ;

EOD;

        $this->addSql($sql);

        $sql = "INSERT INTO s_core_acl_resources (name) VALUES ('widgets');";
        $this->addSql($sql);

        $sql = "
            SET @resourceId = (SELECT id FROM s_core_acl_resources WHERE name = 'widgets' LIMIT 1);

            INSERT INTO s_core_acl_privileges (resourceID, name) VALUES
            (@resourceId, 'read'),
            (@resourceId, 'swag-visitors-customers-widget'),
            (@resourceId, 'swag-last-orders-widget'),
            (@resourceId, 'swag-sales-widget'),
            (@resourceId, 'swag-merchant-widget'),
            (@resourceId, 'swag-upload-widget'),
            (@resourceId, 'swag-notice-widget');
        ";

        $this->addSql($sql);
    }
}
