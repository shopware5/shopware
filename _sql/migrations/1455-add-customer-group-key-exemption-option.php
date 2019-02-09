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

class Migrations_Migration1455 extends \Shopware\Components\Migrations\AbstractMigration
{
    /**
     * Migration to make new "Exclude Customergroup" option visible in advanced menu without reinstallation
     *
     * @param string $modus
     */
    public function up($modus)
    {
        $statement = $this->connection->query("SELECT `id` FROM `s_core_plugins` WHERE `name`='AdvancedMenu' AND `installation_date` IS NOT NULL");

        $result = $statement->fetch(PDO::FETCH_NUM);

        if (empty($result)) {
            return;
        }

        $sql = <<<'SQL'
SET @pluginID = (SELECT `id` FROM `s_core_plugins` WHERE `name`='AdvancedMenu');
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
SET @formID = (SELECT `id` FROM `s_core_config_forms` WHERE `plugin_id`=@pluginID);
SQL;
        $this->addSql($sql);
        $sql = <<<'SQL'
INSERT IGNORE INTO s_core_config_elements (form_id, name, value, label, description, type, required, position, scope, options) VALUES(
@formID,
'excludeCustomergroup',
'b:0;',
'Kundengruppen ausschließen',
'Alle Kundengruppen erhalten das gleiche Menü (bessere Perfomance)',
'boolean',
0,
0,
1,
0x613A303A7B7D
);
SQL;
        $this->addSql($sql);
        $sql = <<<'SQL'
SET @elementID = (SELECT id FROM s_core_config_elements WHERE form_id=@formID AND `name`='excludeCustomergroup');
SQL;
        $this->addSql($sql);
        $sql = <<<'SQL'
INSERT IGNORE INTO s_core_config_element_translations (element_id, locale_id, label, description) VALUES (
@elementID,
2,
'Exclude Customergroup',
'All customergroups will have the same menu (better perfomance)');
SQL;
        $this->addSql($sql);
    }
}
