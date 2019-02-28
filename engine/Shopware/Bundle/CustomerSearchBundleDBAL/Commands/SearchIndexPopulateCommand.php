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

namespace Shopware\Bundle\CustomerSearchBundleDBAL\Commands;

use Shopware\Bundle\ESIndexingBundle\Console\ConsoleProgressHelper;
use Shopware\Bundle\ESIndexingBundle\LastIdQuery;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SearchIndexPopulateCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:customer:search:index:populate')
            ->setDescription('Refreshes the search index for the customer search')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $query = $this->createQuery();

        $helper = new ConsoleProgressHelper($output);
        $helper->start($query->fetchCount(), 'Start indexing stream search data');

        $this->container->get('dbal_connection')->transactional(
            function () use ($helper, $query) {
                $this->container->get('dbal_connection')->executeUpdate('DELETE FROM s_customer_search_index');

                $indexer = $this->container->get('customer_search.dbal.indexing.indexer');

                while ($ids = $query->fetch()) {
                    $indexer->populate($ids);
                    $helper->advance(count($ids));
                }
            }
        );

        $helper->finish();
    }

    private function createQuery()
    {
        $query = $this->container->get('dbal_connection')->createQueryBuilder();
        $query->select(['id', 'id']);
        $query->from('s_user', 'u');
        $query->where('u.id > :lastId');
        $query->setParameter(':lastId', 0);
        $query->orderBy('u.id', 'ASC');
        $query->setMaxResults(100);

        return new LastIdQuery($query);
    }
}
