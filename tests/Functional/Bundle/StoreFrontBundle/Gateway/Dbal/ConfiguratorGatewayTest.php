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

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle\Gateway\Dbal;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Gateway\ConfiguratorGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\ConfiguratorGateway;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class ConfiguratorGatewayTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    public function testGetProductCombinations(): void
    {
        $sqlFile = file_get_contents(__DIR__ . '/fixtures/configurations.sql');
        static::assertIsString($sqlFile);
        $this->getContainer()->get(Connection::class)->executeStatement($sqlFile);

        $configuratorGateway = $this->getConfiguratorGateway();

        $baseProduct = new BaseProduct(444444, 444444, 'SWFOO');

        $configuration = $configuratorGateway->getProductCombinations($baseProduct);

        static::assertEquals([
            10 => [20, 30, 40],
            20 => [10, 11, 30, 40],
            30 => [10, 11, 20, 40],
            40 => [10, 11, 20, 30],
            11 => [20, 30, 40],
        ], $configuration);
    }

    public function testGetAvailableConfigurations(): void
    {
        $sqlFile = file_get_contents(__DIR__ . '/fixtures/configurations.sql');
        static::assertIsString($sqlFile);
        $this->getContainer()->get(Connection::class)->executeStatement($sqlFile);

        $configuratorGateway = $this->getConfiguratorGateway();

        $baseProduct = new BaseProduct(444444, 444444, 'SWFOO');

        $configuration = $configuratorGateway->getAvailableConfigurations($baseProduct);

        static::assertEquals([
            10 => [[10, 20, 30, 40]],
            20 => [[10, 20, 30, 40], [11, 20, 30, 40]],
            30 => [[10, 20, 30, 40], [11, 20, 30, 40]],
            40 => [[10, 20, 30, 40], [11, 20, 30, 40]],
            11 => [[11, 20, 30, 40]],
        ], $configuration);
    }

    public function getConfiguratorGateway(): ConfiguratorGateway
    {
        return $this->getContainer()->get(ConfiguratorGatewayInterface::class);
    }
}
