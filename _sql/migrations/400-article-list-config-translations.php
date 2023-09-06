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

class Migrations_Migration400 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = "SET @formId = (SELECT `id` FROM `s_core_config_forms` WHERE name = 'SwagMultiEdit');";
        $this->addSql($sql);

        $fixed = $this->connection->query("
            SELECT id
            FROM s_core_config_elements
            WHERE `form_id`= (SELECT `id` FROM `s_core_config_forms` WHERE name = 'SwagMultiEdit' LIMIT 1)
            AND name = 'clearCache'
        ")->fetchColumn(0);

        $wrong = $this->connection->query("
            SELECT id
            FROM s_core_config_elements
            WHERE `form_id`= (SELECT `id` FROM `s_core_config_forms` WHERE name = 'SwagMultiEdit' LIMIT 1)
            AND name = 'b:0;'
        ")->fetchColumn(0);

        if ($fixed && $wrong) {
            $this->addSql('DELETE FROM s_core_config_elements WHERE id = ' . $wrong);
        } elseif (!$fixed && $wrong) {
            // Fix broken config name
            $sql = "UPDATE s_core_config_elements SET name = 'clearCache', value = 'b:0;' WHERE id = " . $wrong;
            $this->addSql($sql);
        }

        // Translate queue config
        $sql = "SET @elementId = (SELECT `id`
            FROM `s_core_config_elements`
            WHERE `form_id`=@formId
            AND `name`='addToQueuePerRequest'
            LIMIT 1
        )";
        $this->addSql($sql);
        $sql = "INSERT IGNORE INTO `s_core_config_element_translations`
        (`label`, `description`, `locale_id`, `element_id`)
        VALUES ('Anzahl der Produkte pro Queue-Request', 'Anzahl der Produkte, die je Request in den Queue geladen werden. Je größer die Zahl, desto länger dauern die Requests. Zu kleine Werte erhöhen den Overhead.', 1, @elementId)";
        $this->addSql($sql);

        // Translate batch config
        $sql = "SET @elementId = (SELECT `id`
            FROM `s_core_config_elements`
            WHERE `form_id`=@formId
            AND `name`='batchItemsPerRequest'
            LIMIT 1
        )";
        $this->addSql($sql);
        $sql = "INSERT IGNORE INTO `s_core_config_element_translations`
        (`label`, `description`, `locale_id`, `element_id`)
        VALUES ('Anzahl der Produkte pro Batch-Request', 'Anzahl der Produkte, die je Request verarbeitet werden. Je größer die Zahl, desto länger dauern die Requests. Zu kleine Werte erhöhen den Overhead.', 1, @elementId)";

        $this->addSql($sql);

        // Translate restore config
        $sql = "SET @elementId = (SELECT `id`
            FROM `s_core_config_elements`
            WHERE `form_id`=@formId
            AND `name`='enableBackup'
            LIMIT 1
        )";
        $this->addSql($sql);
        $sql = "INSERT IGNORE INTO `s_core_config_element_translations`
        (`label`, `description`, `locale_id`, `element_id`)
        VALUES ('Rückgängig-Funktion aktivieren', 'Ermöglicht es, einzelne Mehrfach-Änderungen rückgängig zu machen. Diese Funktion ersetzt kein Backup.', 1, @elementId)";
        $this->addSql($sql);

        // Translate clear cache config
        $sql = "SET @elementId = (SELECT `id`
            FROM `s_core_config_elements`
            WHERE `form_id`=@formId
            AND `name`='clearCache'
            LIMIT 1
        )";
        $this->addSql($sql);
        $sql = "INSERT IGNORE INTO `s_core_config_element_translations`
        (`label`, `description`, `locale_id`, `element_id`)
        VALUES ('Automatische Cache-Invalidierung aktivieren', 'Invalidiert den Cache für jedes Produkt, das geändert wird. Bei vielen Produkten kann sich das negativ auf die Dauer des Vorgangs auswirken. Es wird daher empfohlen, den Cache nach Ende des Vorgangs manuell zu leeren.', 1, @elementId)";
        $this->addSql($sql);
    }
}
