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
class Migrations_Migration1439 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        /*
         * Column case change does not flush cache and we still get in the constraint upper case column name
         * We need to rename it to a another name and then back
         * @ticket: https://jira.mariadb.org/browse/MDEV-13671
         */
        if ($modus === self::MODUS_INSTALL) {
            return;
        }

        // When this system comes form the 5.5 Beta / RC
        if (!$this->isIdUpperCase() && !$this->isMySql8()) {
            $this->addSql(
                'ALTER TABLE `s_order_documents_attributes` DROP FOREIGN KEY `s_order_documents_attributes_ibfk_1`;'
            );
            $this->addSql(
                'ALTER TABLE `s_order_documents` CHANGE COLUMN `id` `id_tmp` INT(11) NOT NULL AUTO_INCREMENT FIRST;'
            );
        }
    }

    private function isMySql8()
    {
        $result = false;

        try {
            $result = $this->connection
                ->query('SELECT @@version AS version')
                ->fetch(\PDO::FETCH_COLUMN);
        } catch (\Exception $exception) {
            // Silent catch
        }

        if (empty($result)) {
            return false;
        }

        return version_compare($result, '8.0.0', '>=');
    }

    /**
     * @return bool
     */
    private function isIdUpperCase()
    {
        $result = false;

        try {
            $result = $this->connection
                ->query('SHOW COLUMNS FROM `s_order_documents`')
                ->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $exception) {
            // Silent catch
        }

        if (!$result) {
            return false;
        }

        $isUpperCase = false;
        foreach ($result as $row) {
            if ($row['Field'] === 'id') {
                break;
            }
            if ($row['Field'] === 'ID') {
                $isUpperCase = true;
                break;
            }
        }

        return $isUpperCase;
    }
}
