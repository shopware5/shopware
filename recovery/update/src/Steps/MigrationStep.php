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

namespace Shopware\Recovery\Update\Steps;

use Shopware\Components\Migrations\AbstractMigration;
use Shopware\Components\Migrations\Manager;

class MigrationStep
{
    /**
     * @var \Shopware\Components\Migrations\Manager
     */
    private $migrationManager;

    public function __construct(Manager $migrationManager)
    {
        $this->migrationManager = $migrationManager;
    }

    /**
     * @param int $offset
     * @param int $totalCount
     *
     * @return ErrorResult|FinishResult|ValidResult
     */
    public function run($offset, $totalCount = null)
    {
        if ($offset == 0) {
            $this->migrationManager->createSchemaTable();
        }

        $currentVersion = $this->migrationManager->getCurrentVersion();

        if (!$totalCount) {
            $totalCount = count($this->migrationManager->getMigrationsForVersion($currentVersion));
        }

        $migration = $this->migrationManager->getNextMigrationForVersion($currentVersion);

        if ($migration === null) {
            return new FinishResult($offset, $totalCount);
        }

        try {
            $this->migrationManager->apply($migration, AbstractMigration::MODUS_UPDATE);
        } catch (\Exception $e) {
            $reflection = new \ReflectionClass(get_class($migration));
            $classFile = $reflection->getFileName();

            return new ErrorResult($e->getMessage(), $e, [
                'deltaFile' => $classFile,
                'deltaVersion' => $migration->getVersion(),
                'deltaLabel' => $migration->getLabel(),
            ]);
        }

        return new ValidResult($offset + 1, $totalCount, [
            'deltaVersion' => $migration->getVersion(),
            'deltaLabel' => $migration->getLabel(),
        ]);
    }
}
