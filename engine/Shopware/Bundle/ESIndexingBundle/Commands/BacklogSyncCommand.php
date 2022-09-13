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

namespace Shopware\Bundle\ESIndexingBundle\Commands;

use Shopware\Bundle\ESIndexingBundle\BacklogProcessorInterface;
use Shopware\Bundle\ESIndexingBundle\BacklogReaderInterface;
use Shopware\Bundle\ESIndexingBundle\IdentifierSelector;
use Shopware\Bundle\ESIndexingBundle\IndexFactoryInterface;
use Shopware\Bundle\ESIndexingBundle\MappingInterface;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Traversable;

class BacklogSyncCommand extends ShopwareCommand
{
    private int $batchSize;

    /**
     * @var list<MappingInterface>
     */
    private array $mappings;

    private BacklogReaderInterface $backlogReader;

    private IdentifierSelector $identifierSelector;

    private IndexFactoryInterface $indexFactory;

    private BacklogProcessorInterface $backlogProcessor;

    public function __construct(
        int $batchSize,
        Traversable $mappings,
        BacklogReaderInterface $backlogReader,
        IdentifierSelector $identifierSelector,
        IndexFactoryInterface $indexFactory,
        BacklogProcessorInterface $backlogProcessor
    ) {
        $this->batchSize = $batchSize;
        $this->mappings = iterator_to_array($mappings, false);
        $this->backlogReader = $backlogReader;
        $this->identifierSelector = $identifierSelector;
        $this->indexFactory = $indexFactory;
        $this->backlogProcessor = $backlogProcessor;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:es:backlog:sync')
            ->setDescription('Synchronize events from the backlog to the live index.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lastBackLogId = $this->backlogReader->getLastBacklogId();
        $backlogs = $this->backlogReader->read($lastBackLogId, $this->batchSize);

        $output->writeln(sprintf('Current last backlog id: %d', $lastBackLogId));

        $io = new SymfonyStyle($input, $output);

        if (empty($backlogs)) {
            $io->success('Backlog is empty');

            return 0;
        }

        foreach ($this->identifierSelector->getShops() as $shop) {
            foreach ($this->mappings as $mapping) {
                $index = $this->indexFactory->createShopIndex($shop, $mapping->getType());

                $this->backlogProcessor->process($index, $backlogs);
            }
        }
        $last = $backlogs[array_key_last($backlogs)];
        $this->backlogReader->setLastBacklogId($last->getId());

        $io->success(sprintf('Synchronized %d items', \count($backlogs)));

        return 0;
    }
}
