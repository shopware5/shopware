<?php

declare(strict_types=1);
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

namespace Shopware\Components\Migrations;

use DirectoryIterator;
use Exception;
use PDO;
use RecursiveRegexIterator;
use RegexIterator;
use RuntimeException;

/**
 * Shopware migration manager
 *
 * <code>
 * $migrationManager = new Manager($conn, '/path/to/migrations');
 * $migrationManager->run();
 * </code>
 */
class Manager
{
    /**
     * @var PDO
     */
    protected $connection;

    /**
     * @var string
     */
    protected $migrationPath;

    /**
     * @param string $migrationPath
     */
    public function __construct(PDO $connection, $migrationPath)
    {
        $this->migrationPath = $migrationPath;

        $this->connection = $connection;
    }

    /**
     * @return Manager
     */
    public function setConnection(PDO $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param string $migrationPath
     *
     * @return Manager
     */
    public function setMigrationPath($migrationPath)
    {
        $this->migrationPath = $migrationPath;

        return $this;
    }

    public function getMigrationPath()
    {
        return $this->migrationPath;
    }

    /**
     * Log string to stdout
     *
     * @param string $str
     */
    public function log($str)
    {
        if (PHP_SAPI === 'cli') {
            echo $str . "\n";
        }
    }

    /**
     * Creates schema version table if not exists
     */
    public function createSchemaTable()
    {
        $sql = '
            CREATE TABLE IF NOT EXISTS `s_schema_version` (
            `version` int(11) NOT NULL,
            `start_date` datetime NOT NULL,
            `complete_date` datetime DEFAULT NULL,
            `name` VARCHAR( 255 ) NOT NULL,
            `error_msg` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`version`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ';
        $this->connection->exec($sql);
    }

    /**
     * Returns current schema version found in database
     *
     * @return int
     */
    public function getCurrentVersion()
    {
        $sql = 'SELECT version FROM s_schema_version WHERE complete_date IS NOT NULL ORDER BY version DESC';

        return (int) $this->connection->query($sql)->fetchColumn();
    }

    /**
     * Returns next Migration that is higher than $currentVersion
     *
     * @param int $currentVersion
     *
     * @return AbstractMigration|null
     */
    public function getNextMigrationForVersion($currentVersion)
    {
        $migrations = $this->getMigrationsForVersion($currentVersion, 1);

        if (empty($migrations)) {
            return null;
        }

        return array_shift($migrations);
    }

    /**
     * Return an array of Migrations that have a higher version than $currentVersion
     * The array is indexed by Version
     *
     * @param int $currentVersion
     * @param int $limit
     *
     * @throws Exception
     *
     * @return array
     */
    public function getMigrationsForVersion($currentVersion, $limit = null)
    {
        $regexPattern = '/^([0-9]*)-.+\.php$/i';

        $migrationPath = $this->getMigrationPath();

        $directoryIterator = new DirectoryIterator($migrationPath);
        $regex = new RegexIterator($directoryIterator, $regexPattern, RecursiveRegexIterator::GET_MATCH);

        $migrations = [];

        foreach ($regex as $result) {
            $migrationVersion = $result['1'];
            if ($migrationVersion <= $currentVersion) {
                continue;
            }

            $migrationClass = $this->loadMigration($result, $migrationPath);

            $migrations[$migrationClass->getVersion()] = $migrationClass;
        }

        ksort($migrations);

        if ($limit !== null) {
            return \array_slice($migrations, 0, $limit, true);
        }

        return $migrations;
    }

    /**
     * Applies given $migration to database
     *
     * @param AbstractMigration::MODUS_* $modus
     *
     * @throws Exception
     */
    public function apply(AbstractMigration $migration, $modus = AbstractMigration::MODUS_INSTALL)
    {
        $this->insertMigration($migration);

        try {
            $migration->up($modus);

            foreach ($migration->getSql() as $sql) {
                $this->connection->exec($sql);
            }
        } catch (Exception $e) {
            $this->markMigrationAsFailed($migration, $e);

            throw new Exception(sprintf('Could not apply migration (%s). Error: %s ', \get_class($migration), $e->getMessage()));
        }

        $this->markMigrationAsFinished($migration);
    }

    /**
     * Composite Method to apply all migrations
     *
     * @param AbstractMigration::MODUS_* $modus
     */
    public function run($modus = AbstractMigration::MODUS_INSTALL)
    {
        $this->createSchemaTable();

        $currentVersion = $this->getCurrentVersion();
        $this->log(sprintf('Current MigrationNumber: %s', $currentVersion));

        $migrations = $this->getMigrationsForVersion($currentVersion);

        $this->log(sprintf('Found %s migrations to apply', \count($migrations)));

        foreach ($migrations as $migration) {
            $this->log(sprintf('Apply MigrationNumber: %s - %s', $migration->getVersion(), $migration->getLabel()));
            try {
                $this->apply($migration, $modus);
            } catch (Exception $e) {
                $this->log($e->getMessage());
                throw $e;
            }
        }
    }

    protected function loadMigration(array $result, string $migrationPath): AbstractMigration
    {
        $migrationClassName = 'Migrations_Migration' . $result['1'];
        if (!class_exists($migrationClassName, false)) {
            $file = $migrationPath . '/' . $result['0'];
            require $file;
        }

        try {
            /** @var AbstractMigration $migrationClass */
            $migrationClass = new $migrationClassName($this->getConnection());
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('Could not instantiate Object of class "%s"', $migrationClassName));
        }

        $this->validateMigration($migrationClass, $result);

        return $migrationClass;
    }

    protected function insertMigration(AbstractMigration $migration): void
    {
        $sql = 'REPLACE s_schema_version (version, start_date, name) VALUES (:version, :date, :name)';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':version' => $migration->getVersion(),
            ':date' => date('Y-m-d H:i:s'),
            ':name' => $migration->getLabel(),
        ]);
    }

    protected function markMigrationAsFinished(AbstractMigration $migration): void
    {
        $sql = 'UPDATE s_schema_version SET complete_date = :date WHERE version = :version';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':version' => $migration->getVersion(),
            ':date' => date('Y-m-d H:i:s'),
        ]);
    }

    protected function markMigrationAsFailed(AbstractMigration $migration, Exception $e): void
    {
        $updateVersionSql = 'UPDATE s_schema_version SET error_msg = :msg WHERE version = :version';
        $stmt = $this->connection->prepare($updateVersionSql);
        $stmt->execute([
            ':version' => $migration->getVersion(),
            ':msg' => $e->getMessage(),
        ]);
    }

    protected function validateMigration(AbstractMigration $migrationClass, $result): void
    {
        $version = (int) $result['0'];

        if ($migrationClass->getVersion() !== $version) {
            throw new Exception(sprintf('Version mismatch. Version in filename: %s, Version in Class: %s', $result['1'], $migrationClass->getVersion()));
        }
    }
}
