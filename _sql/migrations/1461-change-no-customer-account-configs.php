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

class Migrations_Migration1461 extends Shopware\Components\Migrations\AbstractMigration
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
                        'snippet' => 'deactivate_no_customer_account_true',
                        'en_GB' => 'Yes',
                        'de_DE' => 'Ja'
                    ]
                ],
                [
                    1,
                    [
                        'snippet' => 'deactivate_no_customer_account_preselected',
                        'en_GB' => 'No: Option is preselected',
                        'de_DE' => 'Nein: Option ist vorausgewählt'
                    ]
                ],
                [
                    2,
                    [
                        'snippet' => 'deactivate_no_customer_account_unselected',
                        'en_GB' => 'No: Option is not preselected',
                        'de_DE' => 'Nein: Option ist nicht vorausgewählt.'
                    ]
                ]
            ]
        ];

        $sql = <<<'SQL'
        SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Frontend33' LIMIT 1);
        SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'noaccountdisable' and form_id=@parent LIMIT 1);

        UPDATE s_core_config_elements set type='select', options='%s', value='i:2;' where id=@elementId;
		UPDATE s_core_config_values SET value = 'i:2;' WHERE element_id = @elementId AND value = 'b:0;';
        UPDATE s_core_config_values SET value = 'i:0;' WHERE element_id = @elementId AND value = 'b:1;';
SQL;
        $this->addSql(sprintf($sql, serialize($options)));
    }
}
