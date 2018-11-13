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

use Shopware\Bundle\ESIndexingBundle\Struct\Backlog;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class BacklogSyncCommand extends ShopwareCommand
{
    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param int $batchSize
     */
    public function __construct($batchSize = 500)
    {
        $this->batchSize = (int) $batchSize;

        parent::__construct(null);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:es:backlog:sync')
            ->setDescription('Synchronize events from the backlog to the live index.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reader = $this->container->get('shopware_elastic_search.backlog_reader');
        $backlogs = $reader->read($reader->getLastBacklogId(), $this->batchSize);

        if (empty($backlogs)) {
            return;
        }

        /** @var Backlog $last */
        $last = $backlogs[count($backlogs) - 1];
        $reader->setLastBacklogId($last->getId());

        $shops = $this->container->get('shopware_elastic_search.identifier_selector')->getShops();
        foreach ($shops as $shop) {
            $index = $this->container->get('shopware_elastic_search.index_factory')->createShopIndex($shop, '');
            $this->container->get('shopware_elastic_search.backlog_processor')
                ->process($index, $backlogs);
        }
    }
}
