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

namespace Shopware\Tests\Functional\Traits;

use PDO;
use Shopware\Components\Migrations\AbstractMigration;
use Shopware\Components\Migrations\Manager;
use Shopware\Recovery\Install\DatabaseFactory;
use Shopware\Recovery\Install\Struct\DatabaseConnectionInformation;
use UnexpectedValueException;

trait MigrationTestTrait
{
    public function getMigrationManager(PDO $connection): Manager
    {
        return new Manager($connection, __DIR__ . '/../../../_sql/migrations');
    }

    public function getMigration(PDO $connection, int $number): AbstractMigration
    {
        return $this->getMigrationManager($connection)->getMigrationsForVersion(0)[$number];
    }

    public function createPDOConnection(): PDO
    {
        $rootDirectory = $this->getRootDirectory();

        require_once $rootDirectory . '/recovery/install/src/DatabaseFactory.php';
        require_once $rootDirectory . '/recovery/install/src/Struct/Struct.php';
        require_once $rootDirectory . '/recovery/install/src/Struct/DatabaseConnectionInformation.php';

        $configPath = $rootDirectory . '/config.php';
        if (!is_file($configPath)) {
            throw new UnexpectedValueException(sprintf('Config file not found: %s', $configPath));
        }

        $config = require $configPath;

        $connectionInfo = new DatabaseConnectionInformation();
        $connectionInfo->username = $config['db']['username'];
        $connectionInfo->hostname = $config['db']['host'];
        $connectionInfo->port = $config['db']['port'];
        $connectionInfo->databaseName = $config['db']['dbname'];
        $connectionInfo->password = $config['db']['password'];

        $databaseFactory = new DatabaseFactory();

        return $databaseFactory->createPDOConnection($connectionInfo);
    }

    private function getRootDirectory(): string
    {
        $rootDirectory = realpath(__DIR__ . '/../../../');

        if (!\is_string($rootDirectory)) {
            throw new UnexpectedValueException(sprintf('Root directory not found: %s', $rootDirectory));
        }

        return $rootDirectory;
    }
}
