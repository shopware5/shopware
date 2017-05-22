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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\ESIndexingBundle\Console\ConsoleProgressHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class StreamIndexPopulateCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:customer:stream:index:populate')
            ->setDescription('Refreshs all Customer Streams with the saved conditions')
            ->addOption('streamId', null, InputOption::VALUE_OPTIONAL)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $streamIds = [];
        if ($streamId = $input->getOption('streamId')) {
            $streamIds = [$streamId];
        }
        $streams = $this->getStreams($streamIds);

        $indexer = $this->container->get('shopware.customer_stream.stream_indexer');

        $helper = new ConsoleProgressHelper($output);

        foreach ($streams as $stream) {
            $output->writeln("\n## Indexing Customer Stream: " . $stream['name'] . ' ##');
            $indexer->populate($stream['id'], $helper);
        }
    }

    /**
     * @param array $ids
     *
     * @return \array[]|false
     */
    private function getStreams($ids = [])
    {
        $query = $this->container->get('dbal_connection')->createQueryBuilder();
        $query->select('*');
        $query->from('s_customer_streams');
        if (!empty($ids)) {
            $query->andWhere('id IN (:ids)');
            $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);
        }

        return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    }
}
