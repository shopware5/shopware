<?php
declare(strict_types=1);

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

namespace Shopware\Components\Migrations;

use Psr\Log\LoggerInterface;
use Shopware\Components\Plugin;

class PluginMigrationManager extends Manager
{
    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(\PDO $connection, Plugin $plugin, LoggerInterface $logger)
    {
        $this->plugin = $plugin;
        $this->logger = $logger;
        parent::__construct($connection, $plugin->getPath() . '/Resources/migrations');
    }

    /**
     * Creates schema version table if not exists
     */
    public function createSchemaTable(): void
    {
        $sql = '
            CREATE TABLE IF NOT EXISTS `s_plugin_schema_version` (
    `plugin_name` VARCHAR(255) NOT NULL COLLATE \'utf8_unicode_ci\',
    `version` INT(11) NOT NULL,
    `start_date` DATETIME NOT NULL,
    `complete_date` DATETIME NULL DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL COLLATE \'utf8_unicode_ci\',
    `error_msg` VARCHAR(255) NULL DEFAULT NULL COLLATE \'utf8_unicode_ci\',
    PRIMARY KEY (`plugin_name`, `version`)
)
COLLATE=\'utf8_unicode_ci\'
ENGINE=InnoDB
        ';
        $this->connection->exec($sql);
    }

    public function getCurrentVersion(): int
    {
        $sql = 'SELECT version FROM s_plugin_schema_version WHERE plugin_name = ? AND complete_date IS NOT NULL ORDER BY version DESC';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$this->plugin->getName()]);

        return (int) $stmt->fetchColumn();
    }

    public function apply(AbstractMigration $migration, $modus = AbstractMigration::MODUS_INSTALL, bool $keepUserData = false): void
    {
        if ($modus === AbstractPluginMigration::MODUS_UNINSTALL) {
            $this->downMigration($migration, $keepUserData);
        } else {
            parent::apply($migration, $modus);
        }
    }

    public function getMigrationsForDowngrade($currentVersion, $limit = null): array
    {
        $regexPattern = '/^([\d]*)-.+\.php$/i';

        $migrationPath = $this->getMigrationPath();

        $directoryIterator = new \DirectoryIterator($migrationPath);
        $regex = new \RegexIterator($directoryIterator, $regexPattern, \RecursiveRegexIterator::GET_MATCH);

        $migrations = [];

        foreach ($regex as $result) {
            $migrationVersion = $result['1'];

            if ($migrationVersion > $currentVersion) {
                continue;
            }

            $migrationClass = $this->loadMigration($result, $migrationPath);

            $migrations[$migrationClass->getVersion()] = $migrationClass;
        }

        ksort($migrations);

        $migrations = array_reverse($migrations);

        if ($limit !== null) {
            return array_slice($migrations, 0, $limit, true);
        }

        return $migrations;
    }

    public function run($modus = AbstractMigration::MODUS_INSTALL, bool $keepUserData = false): void
    {
        if ($modus !== AbstractPluginMigration::MODUS_UNINSTALL) {
            parent::run($modus);

            return;
        }

        $currentVersion = $this->getCurrentVersion();
        $this->log(sprintf('Current MigrationNumber: %s', $currentVersion));

        $migrations = $this->getMigrationsForDowngrade($currentVersion);

        $this->log(sprintf('Found %s migrations to apply', count($migrations)));

        foreach ($migrations as $migration) {
            $this->log(sprintf('Revert MigrationNumber: %s - %s', $migration->getVersion(), $migration->getLabel()));
            try {
                $this->apply($migration, $modus, $keepUserData);
            } catch (\Exception $e) {
                $this->log($e->getMessage());
                throw $e;
            }
        }
    }

    public function log($str): void
    {
        $this->logger->info(sprintf('[Migration from %s] %s', $this->plugin->getName(), $str));
    }

    protected function loadMigration(array $result, string $migrationPath): AbstractMigration
    {
        $migrationClassName = sprintf('%s\\Migrations\\Migration%d', $this->plugin->getName(), $result['1']);
        if (!class_exists($migrationClassName, false)) {
            $file = $migrationPath . '/' . $result['0'];
            require $file;
        }

        try {
            /** @var AbstractPluginMigration $migrationClass */
            $migrationClass = new $migrationClassName($this->getConnection());
        } catch (\Exception $e) {
            throw new \Exception('Could not instantiate Object');
        }

        $this->validateMigration($migrationClass, $result);

        return $migrationClass;
    }

    protected function insertMigration(AbstractMigration $migration): void
    {
        $sql = 'REPLACE s_plugin_schema_version (plugin_name, version, start_date, name) VALUES (:plugin_name, :version, :date, :name)';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':plugin_name' => $this->plugin->getName(),
            ':version' => $migration->getVersion(),
            ':date' => date('Y-m-d H:i:s'),
            ':name' => $migration->getLabel(),
        ]);
    }

    protected function markMigrationAsFinished(AbstractMigration $migration): void
    {
        $sql = 'UPDATE s_plugin_schema_version SET complete_date = :date WHERE plugin_name = :plugin_name AND version = :version';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            ':plugin_name' => $this->plugin->getName(),
            ':version' => $migration->getVersion(),
            ':date' => date('Y-m-d H:i:s'),
        ]);
    }

    protected function markMigrationAsFailed(AbstractMigration $migration, \Exception $e): void
    {
        $updateVersionSql = 'UPDATE s_plugin_schema_version SET error_msg = :msg WHERE plugin_name = :plugin_name AND version = :version';
        $stmt = $this->connection->prepare($updateVersionSql);
        $stmt->execute([
            ':plugin_name' => $this->plugin->getName(),
            ':version' => $migration->getVersion(),
            ':msg' => $e->getMessage(),
        ]);
    }

    protected function removeMigration(AbstractMigration $migration): void
    {
        $updateVersionSql = 'DELETE FROM s_plugin_schema_version WHERE plugin_name = :plugin_name AND version = :version';
        $stmt = $this->connection->prepare($updateVersionSql);
        $stmt->execute([
            ':plugin_name' => $this->plugin->getName(),
            ':version' => $migration->getVersion(),
        ]);
    }

    private function downMigration(AbstractMigration $migration, bool $keepUserData): void
    {
        try {
            $migration->down($keepUserData);

            foreach ($migration->getSql() as $sql) {
                $this->connection->exec($sql);
            }
        } catch (\Exception $e) {
            $this->markMigrationAsFailed($migration, $e);

            throw new \RuntimeException(sprintf(
                'Could not revert migration (%s). Error: %s ', get_class($migration), $e->getMessage()
            ));
        }

        $this->removeMigration($migration);
    }
}
