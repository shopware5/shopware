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

class Migrations_Migration1200 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $parent = $this->connection->query("SELECT `id` FROM `s_core_config_forms` WHERE `name` LIKE 'Frontend30';")->fetchColumn();
        $basic = serialize('basic');

        $sql = <<<SQL
          INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`)
          VALUES (:parent, 'manufacturerProductBoxLayout', :basic, 'Produktlayout im Herstellerlisting', '', 'product-box-layout-select', 0, 0, 1);
SQL;
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':parent' => $parent,
            ':basic' => $basic,
        ]);

        $stmt = $this->connection->prepare("SELECT `id` FROM `s_core_config_elements` WHERE `form_id` = :parent AND `name`= 'manufacturerProductBoxLayout' LIMIT 1");
        $stmt->execute([
            ':parent' => $parent,
        ]);
        $elem = $stmt->fetchColumn();

        $sql = <<<SQL
            INSERT IGNORE INTO `s_core_config_element_translations` (`label`, `description`, `locale_id`, `element_id`)
            VALUES ('Manufacturer page product layout', '', 2, :elem);
SQL;
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':elem' => $elem,
        ]);
    }
}
