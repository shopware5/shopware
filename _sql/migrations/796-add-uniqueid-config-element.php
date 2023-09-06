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

class Migrations_Migration796 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<SQL
INSERT INTO s_core_config_elements (form_id, name, value, label, description, type, required, position, scope)
VALUES ('0', 'trackingUniqueId', 's:0:"";', 'Unique identifier', '', 'text', '0', '0', '1')
SQL;

        $statement = $this->getConnection()->prepare("SELECT id FROM s_core_config_elements WHERE name = 'update-unique-id'");
        $statement->execute();
        $id = $statement->fetchColumn();

        if (!empty($id)) {
            $sql = 'UPDATE s_core_config_elements SET name="trackingUniqueId" WHERE id=' . $this->getConnection()->quote($id);
        }

        $this->addSql($sql);
    }
}
