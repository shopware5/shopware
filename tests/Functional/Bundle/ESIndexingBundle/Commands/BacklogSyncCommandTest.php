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

namespace Shopware\Tests\Functional\Bundle\ESIndexingBundle\Commands;

use Doctrine\Common\Collections\ArrayCollection;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\ESIndexingBundle\BacklogProcessorInterface;
use Shopware\Bundle\ESIndexingBundle\BacklogReaderInterface;
use Shopware\Bundle\ESIndexingBundle\Commands\BacklogSyncCommand;
use Shopware\Bundle\ESIndexingBundle\IdentifierSelector;
use Shopware\Bundle\ESIndexingBundle\IndexFactory;
use Shopware\Bundle\ESIndexingBundle\Product\ProductMapping;
use Shopware\Bundle\ESIndexingBundle\Struct\Backlog;
use Shopware\Bundle\ESIndexingBundle\Subscriber\ORMBacklogSubscriber;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Symfony\Component\Console\Tester\CommandTester;

class BacklogSyncCommandTest extends TestCase
{
    use DatabaseTransactionBehaviour;
    use ContainerTrait;

    public function testLastBacklogIdIsNotSetIfProcessingFails(): void
    {
        $command = $this->createCommand();
        $commandTester = new CommandTester($command);

        $this->expectException(NoNodesAvailableException::class);
        $this->expectExceptionMessage('No alive nodes found in your cluster');
        $commandTester->execute([]);
    }

    private function createCommand(): BacklogSyncCommand
    {
        $backlogReader = $this->createBacklogReader();

        return new BacklogSyncCommand(
            $this->getContainer()->getParameter('shopware.es.batchsize'),
            new ArrayCollection([$this->getContainer()->get(ProductMapping::class)]),
            $backlogReader,
            $this->getContainer()->get(IdentifierSelector::class),
            $this->getContainer()->get(IndexFactory::class),
            $this->getContainer()->get(BacklogProcessorInterface::class)
        );
    }

    private function createBacklogReader(): BacklogReaderInterface
    {
        $backlogReader = $this->createMock(BacklogReaderInterface::class);
        $backlogReader->method('read')->willReturn(
            [
                new Backlog(
                    ORMBacklogSubscriber::EVENT_SUPPLIER_DELETED,
                    ['id' => 1]
                ),
                new Backlog(
                    ORMBacklogSubscriber::EVENT_VARIANT_UPDATED,
                    ['number' => 'SW10003']
                ),
            ]
        );
        $backlogReader->expects(static::never())->method('setLastBacklogId');

        return $backlogReader;
    }
}
