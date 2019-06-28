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

class Migrations_Migration1617 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<SQL
        SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Frontend33' LIMIT 1);
        
        INSERT IGNORE INTO s_core_config_elements (form_id, name, value, label, description, type, required, position, scope, options) VALUES (@parent, 'shareSessionBetweenLanguageShops', 'b:1;', 'Browser-Sitzung zwischen Sprachshops teilen', 'Wenn aktiv, wird die Browser-Sitzung zwischen Sprachshops geteilt. Somit verliert der Kunde den Warenkorb beim Wechseln nicht.<br><br><strong>Achtung:</strong> Sind Artikel in einzelnen Sprachvarianten nicht verfügbar, werden diese aus Warenkörben entfernt und können innerhalb der Sitzung nicht mehr wiederhergestellt werden.', 'boolean', 0, 0, 0, null);
        
        SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'shareSessionBetweenLanguageShops' LIMIT 1);
        INSERT INTO s_core_config_element_translations (element_id, locale_id, label, description) VALUES (@elementId, 2, 'Share browser session between language shops', 'When active, the browser session is shared between language shops. Thus the customer does not lose the shopping basket when switching');
SQL;

        $this->addSql($sql);

        if ($modus === self::MODUS_UPDATE) {
            $sql = "INSERT INTO `s_core_config_values` (`element_id`, `shop_id`, `value`)
                    SELECT 
                      @elementId,
                      `id`,
                      'b:0;'
                    FROM s_core_shops
                    WHERE id NOT IN (SELECT `shop_id` FROM `s_core_config_values` WHERE `element_id` = @elementId)";
            $this->addSql($sql);
        }
    }
}
