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

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration931 extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up($modus)
    {
        $this->addSql("SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Frontend33' LIMIT 1);");

        $sql = <<<'SQL'
INSERT INTO s_core_config_elements (form_id, name, value, label, description, type, required, position, scope, options)
VALUES (@parent, 'useSltCookie', 'b:1;', 'Shopware Login Cookie erstellen', 'Es wird ein Cookie gespeichert, an dem der Benutzer wieder identifiziert werden kann. Dieser wird nur für das Setzen der aktuellen Kundengruppe sowie aktiven Customer Streams verwendet', 'boolean', 1, 0, 0, NULL);
SQL;
        $this->addSql($sql);

        $this->addSql("SET @parent = (SELECT id FROM s_core_config_elements WHERE name = 'useSltCookie' LIMIT 1);");

        $this->addSql("INSERT IGNORE INTO s_core_config_element_translations (element_id, locale_id, label, description) VALUES(@elementID, 2, 'Create shopware login cookie', 'A cookie is stored, where the user can be identified again. This cookie is only used for setting the current customer group and the active Customer Streams');");
    }
}
