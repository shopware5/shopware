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
        $sql = <<<'SQL'
        SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Frontend33' LIMIT 1);
        SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'noaccountdisable' and form_id=@parent LIMIT 1);
        UPDATE s_core_config_elements set type='select', options='a:4:{s:5:"store";s:52:"Shopware.apps.Base.store.DeactivateNoCustomerAccount";s:9:"queryMode";s:5:"local";s:8:"editable";b:0;s:14:"forceSelection";b:1;}', value='i:2;' where id=@elementId;

		UPDATE s_core_config_values SET value = 'i:2;'
        WHERE
          element_id = @elementId AND value = 'b:0;';
          
      UPDATE s_core_config_values SET value = 'i:0;'
        WHERE
          element_id = @elementId AND value = 'b:1;';
SQL;
        $this->addSql($sql);
    }
}
