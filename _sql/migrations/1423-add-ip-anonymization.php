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
class Migrations_Migration1423 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * We need to rerun the Migrations from 5.4.5 <-> 5.4.6, to make the 5.5 beta 1 updatable
     *
     * @param string $modus
     */
    public function up($modus)
    {
        if ($this->connection->query('SELECT 1 FROM s_schema_version WHERE version = 1223')->fetchColumn()) {
            return;
        }

        $this->addSql('SET @privacyFormId = ( SELECT id FROM `s_core_config_forms` WHERE name = "Privacy" LIMIT 1 )');

        $sql = "INSERT INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`)
                VALUES (@privacyFormId, 'anonymizeIp', '" . serialize(true) . "', 'Kunden IPs anonymisieren', 'Entfernt die letzten zwei Blöcke einer IPv4, resp. drei Blöcke einer IPv6 Adresse in Statistiken und Bestellungen, um rechtlichen Rahmenbedingungen einzuhalten.', 'boolean', 0, 40, 0);";
        $this->addSql($sql);
        $this->addSql("SET @elementId = ( SELECT id FROM `s_core_config_elements` WHERE name = 'anonymizeIp' LIMIT 1 );");

        // Translation for the new menu element
        $sql = "INSERT INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
                VALUES (@elementId, '2', 'Anonymize customer IPs', 'Removes the last two blocks of IPv4 and three blocks of IPv6 addresses in statistics and orders to comply with privacy laws.');";
        $this->addSql($sql);
    }
}
