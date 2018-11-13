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

namespace Shopware\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshSearchIndexCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:refresh:search:index')
            ->setDescription('Refreshes and regenerates the search index')
            ->setHelp('The <info>%command.name%</info> regenerates the search index')
            ->addOption(
                'clear-table',
                null,
                InputOption::VALUE_NONE,
                'Clears whole search index before regeneration'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clearTable = $input->getOption('clear-table');
        if ($clearTable) {
            $output->writeln('Deleting the search index...');

            /** @var \Doctrine\DBAL\Connection $connection */
            $connection = $this->container->get('dbal_connection');
            $connection->delete('s_search_index', ['*']);
            $connection->delete('s_search_keywords', ['*']);
        }
        $output->writeln('Creating the search index. This may take a while depending on the shop size.');
        /* @var \Shopware\Bundle\SearchBundleDBAL\SearchTerm\SearchIndexerInterface; $indexer */
        $indexer = $this->container->get('shopware_searchdbal.search_indexer');
        $indexer->build();

        $output->writeln('The search index was created successfully.');
    }
}
