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

class Migrations_Migration1613 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<SQL
        SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Frontend79' LIMIT 1);
        
        INSERT IGNORE INTO s_core_config_elements (form_id, name, value, label, description, type, required, position, scope, options) VALUES (@parent, 'clearBasketAfterLogout', 'b:1;', 'Warenkorb beim Logout leeren', 'Falls aktiv, wird der Warenkorb nach einem Logout geleert.<br>Falls inaktiv, wird der Warenkorb beim Logout nicht geleert und bleibt für einen späteren Login erhalten.', 'boolean', 0, 0, 0, null);
        
        SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'clearBasketAfterLogout' LIMIT 1);
        INSERT INTO s_core_config_element_translations (element_id, locale_id, label, description) VALUES (@elementId, 2, 'Clear basket after logout', 'If active, the shopping cart will be cleared after a logout. <br>If inactive, the shopping cart will not be cleared after a logout and will be retained for a later login.');
SQL;

        $this->addSql($sql);
    }
}
