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

namespace Shopware\Tests\Functional\Bundle\BenchmarkBundle;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopware\Components\Model\ModelManager;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

abstract class BenchmarkTestCase extends TestCase
{
    use DatabaseTransactionBehaviour;
    use ContainerTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->getContainer()->get(ModelManager::class)->clear();
    }

    protected function installDemoData(string $dataName): void
    {
        $dbalConnection = $this->getContainer()->get(Connection::class);

        $fileContent = $this->openDemoDataFile($dataName);
        $dbalConnection->executeStatement($fileContent);
    }

    protected function getAssetsFolder(): string
    {
        return __DIR__ . '/assets/';
    }

    protected function openDemoDataFile(string $fileName): string
    {
        $fileName .= '.sql';
        $path = $this->getAssetsFolder();
        $filePath = $path . $fileName;
        if (!file_exists($filePath)) {
            throw new RuntimeException(sprintf('File with name %s does not exist in path %s', $fileName, $path));
        }

        $content = file_get_contents($filePath);
        static::assertIsString($content);

        return $content;
    }
}
