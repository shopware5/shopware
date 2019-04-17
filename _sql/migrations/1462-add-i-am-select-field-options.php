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

class Migrations_Migration1462 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $options = [
            'editable' => false,
            'forceSelection' => true,
            'translateUsingSnippets' => true,
            'namespace' => 'backend/application/main',
            'store' => [
                [
                    0,
                    [
                        'snippet' => 'i_am_select_field_show',
                        'en_GB' => 'Yes',
                        'de_DE' => 'Ja'
                    ]
                ],
                [
                    1,
                    [
                        'snippet' => 'i_am_select_field_not_show_b2c',
                        'en_GB' => 'No. Customers register as B2C customers.',
                        'de_DE' => 'Nein. Kunden melden sich als B2C Kunden an.'
                    ]
                ],
                [
                    2,
                    [
                        'snippet' => 'i_am_select_field_not_show_b2b',
                        'en_GB' => 'No. Customers register as B2B customers.',
                        'de_DE' => 'Nein. Kunden melden sich als B2B Kunden an.'
                    ]
                ]
            ]
        ];

        $sql = <<<'SQL'
        SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Frontend33' LIMIT 1);
        SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'showCompanySelectField' and form_id=@parent LIMIT 1);

        UPDATE s_core_config_elements set type='select', options='%s', value='i:0;' where id=@elementId;
		UPDATE s_core_config_values SET value = 'i:1;' WHERE element_id = @elementId AND value = 'b:0;';
        UPDATE s_core_config_values SET value = 'i:0;' WHERE element_id = @elementId AND value = 'b:1;';
        UPDATE `s_core_config_elements` SET `description` = 'Das Auswahlfeld wird nur bei der Registrierung ausgeblendet, danach ist es beim Ändern der Benutzerdaten trotzdem verfügbar.' WHERE `id` = @element;
        UPDATE `s_core_config_element_translations` SET `description` = 'This option only affects the registration, it is still available when editing user data.' WHERE `element_id` = @element;
SQL;
        $this->addSql(sprintf($sql, serialize($options)));
    }
}
