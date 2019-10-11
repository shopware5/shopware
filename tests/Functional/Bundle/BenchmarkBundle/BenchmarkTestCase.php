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

namespace Shopware\Tests\Functional\Bundle\BenchmarkBundle;

abstract class BenchmarkTestCase extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $dbalConnection->beginTransaction();
        Shopware()->Container()->get('models')->clear();
    }

    public function tearDown(): void
    {
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $dbalConnection->rollBack();
    }

    /**
     * @param string $dataName
     */
    protected function installDemoData($dataName)
    {
        $dbalConnection = Shopware()->Container()->get('dbal_connection');

        $fileContent = $this->openDemoDataFile($dataName);
        $dbalConnection->exec($fileContent);
    }

    protected function getAssetsFolder()
    {
        return __DIR__ . '/assets/';
    }

    /**
     * @param string $fileName
     *
     * @throws \Exception
     *
     * @return bool|string
     */
    protected function openDemoDataFile($fileName)
    {
        $fileName .= '.sql';
        $path = $this->getAssetsFolder();
        $filePath = $path . $fileName;
        if (!file_exists($filePath)) {
            throw new \Exception(sprintf('File with name %s does not exist in path %s', $fileName, $path));
        }

        return file_get_contents($filePath);
    }
}
