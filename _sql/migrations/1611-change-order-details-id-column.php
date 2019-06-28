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

class Migrations_Migration1611 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $columns = $this->connection->query('DESCRIBE `s_order_documents`')->fetchAll(\PDO::FETCH_ASSOC);

        if (in_array('id', array_column($columns, 'Field'), true)) {
            return;
        }

        if ($modus === self::MODUS_INSTALL) {
            return;
        }

        $sql = <<<'SQL'
ALTER TABLE `s_order_documents` CHANGE COLUMN `ID` `id_tmp` int auto_increment;
SQL;
        $this->addSql($sql);
        $sql = <<<'SQL'
ALTER TABLE `s_order_documents` CHANGE COLUMN `id_tmp` `id` int auto_increment;
SQL;
        $this->addSql($sql);
    }
}
