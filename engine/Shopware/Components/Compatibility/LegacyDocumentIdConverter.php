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

namespace Shopware\Components\Compatibility;

use Doctrine\DBAL\Connection;

/**
 * This class is necessary for a smooth migration to MySQL 8. MySQL 8 forces Ids in foreign key constraints to be lower case.
 *
 * This is a problem since we have an uppercase "ID" in table `s_order_documents`. MySQL doesn't care if we use "ID" in
 * the table and "id" in the constraint, but Doctrine needs both to be written in the same case. On new installations
 * of Shopware 5.5 this is already the case.
 *
 * So in order to support MySQL 8 on updates from older Shopware versions we need to change the case of the "id" column
 * in `s_order_documents`, which breaks support of blue/green deployments as older versions of Shopware (< 5.5) need
 * that column to be uppercase.
 *
 * Since this change is only really necessary if you are using MySQL 8, it is only enforced when a MySQL 8 server is
 * detected. A downgrade to an older Shopware installation wouldn't be possible anyway in that case, as Shopware 5.4
 * does not support MySQL 8 yet.
 */
class LegacyDocumentIdConverter
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var bool
     */
    private $isIdUpperCase;

    /**
     * @var bool
     */
    private $isMigrationNecessary;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return bool
     */
    public function isDocumentIdUpperCase()
    {
        if ($this->isIdUpperCase === null) {
            $this->isIdUpperCase = $this->isIdUpperCase();
        }

        return $this->isIdUpperCase;
    }

    public function isMigrationNecessary()
    {
        if ($this->isMigrationNecessary !== null) {
            return $this->isMigrationNecessary;
        }

        /*
         * If the migration already happened, everything is fine
         */
        if (!$this->isIdUpperCase()) {
            $this->isMigrationNecessary = false;

            return $this->isMigrationNecessary;
        }

        /*
         * If the migration to MySQL 8 support hasn't happened, but this server uses MySQL 8, then we force the migration
         */
        if ($this->isMySql8()) {
            $this->migrateTable();

            $this->isMigrationNecessary = false;

            return $this->isMigrationNecessary;
        }

        /*
         * At this point we know the migration hasn't happened but it also isn't necessary yet.
         */
        $this->isMigrationNecessary = false;

        return $this->isMigrationNecessary;
    }

    public function migrateTable()
    {
        $this->connection
            ->exec('ALTER TABLE `s_order_documents_attributes` DROP FOREIGN KEY `s_order_documents_attributes_ibfk_1`;');

        $this->connection
            ->exec('ALTER TABLE `s_order_documents` CHANGE COLUMN `ID` `id_tmp` INT(11) NOT NULL AUTO_INCREMENT FIRST;');

        $this->connection
            ->exec('ALTER TABLE `s_order_documents` CHANGE COLUMN `id_tmp` `id` INT(11) NOT NULL AUTO_INCREMENT FIRST;');

        $this->connection
            ->exec('ALTER TABLE `s_order_documents_attributes` ADD CONSTRAINT `s_order_documents_attributes_ibfk_1` FOREIGN KEY (`documentID`) REFERENCES `s_order_documents` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE;');
    }

    /**
     * @return bool
     */
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
