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

class Migrations_Migration1459 extends Shopware\Components\Migrations\AbstractMigration
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
                        'snippet' => 'shipping_calculations_not_show',
                        'en_GB' => 'No',
                        'de_DE' => 'Nein'
                    ],
                ],
                [
                    1,
                    [
                        'snippet' => 'shipping_calculations_show_folded',
                        'en_GB' => 'Collapsed',
                        'de_DE' => 'Eingeklappt'
                    ]
                ],
                [
                    2,
                    [
                        'snippet' => 'shipping_calculations_show_expanded',
                        'en_GB' => 'Expanded',
                        'de_DE' => 'Ausgeklappt'
                    ]
                ]
            ]
        ];

        $sql = <<<'SQL'
        SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Frontend79' LIMIT 1);
        SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'basketShowCalculation' and form_id=@parent LIMIT 1);
        SET @value = (SELECT value FROM `s_core_config_values` WHERE `element_id` = @elementId);
        UPDATE s_core_config_elements set `type`='select', `position`=5, options='%s', `value`='i:1;', label='Versandkostenberechnung im Warenkorb anzeigen' where id=@elementId;
        UPDATE `s_core_config_element_translations` set `label`='Show shipping costs calculation in shopping cart' where element_id=@elementId;

		UPDATE s_core_config_values SET value = 'i:0;'
        FROM s_core_config_values
        WHERE element_id = @elementId);
SQL;
        $this->addSql(sprintf($sql, serialize($options)));
    }
}
