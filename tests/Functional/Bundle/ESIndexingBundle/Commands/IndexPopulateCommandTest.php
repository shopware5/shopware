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

namespace Shopware\Tests\Functional\Bundle\ESIndexingBundle\Commands;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\ESIndexingBundle\Commands\IndexPopulateCommand;
use Shopware\Bundle\ESIndexingBundle\Console\EvaluationHelperInterface;
use Shopware\Bundle\ESIndexingBundle\IdentifierSelector;
use Shopware\Bundle\ESIndexingBundle\ShopIndexerInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\ShopGatewayInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Symfony\Component\Console\Tester\CommandTester;

class IndexPopulateCommandTest extends TestCase
{
    use ContainerTrait;

    public function testExecute(): void
    {
        $command = new IndexPopulateCommand(
            $this->createMock(ModelManager::class),
            $this->createMock(ShopIndexerInterface::class),
            $this->createMock(EvaluationHelperInterface::class),
            $this->createMock(IdentifierSelector::class),
            $this->getContainer()->get(ShopGatewayInterface::class)
        );
        $commandTester = new CommandTester($command);

        $input = [
            '--shopId' => [1, 22],
        ];

        $commandTester->execute($input);
        $output = $commandTester->getDisplay();
        static::assertStringContainsString('Shops with following IDs not found: 22', $output);
        static::assertStringContainsString('## Indexing shop Deutsch ##', $output);
    }
}
