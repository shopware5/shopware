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

namespace Shopware\Bundle\ESIndexingBundle;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Shopware\Bundle\ESIndexingBundle\Struct\Backlog;
use Shopware\Bundle\ESIndexingBundle\Struct\ShopIndex;

class BacklogProcessor implements BacklogProcessorInterface
{
    /**
     * @var \Enlight_Event_EventManager
     */
    protected $eventManager;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var SynchronizerInterface[]
     */
    private $synchronizers;

    public function __construct(
        Connection $connection,
        \IteratorAggregate $synchronizers,
        \Enlight_Event_EventManager $eventManager
    ) {
        $this->connection = $connection;
        $this->synchronizers = iterator_to_array($synchronizers, true);
        $this->eventManager = $eventManager;

        $this->collectSynchronizer();
    }

    /**
     * {@inheritdoc}
     */
    public function add($backlogs)
    {
        if (empty($backlogs)) {
            return;
        }

        $this->writeBacklog($backlogs);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ShopIndex $shopIndex, $backlogs)
    {
        foreach ($this->synchronizers as $synchronizer) {
            if ($synchronizer->supports() === $shopIndex->getType()) {
                $synchronizer->synchronize($shopIndex, $backlogs);
            }
        }
    }

    /**
     * @return SynchronizerInterface[]
     */
    private function collectSynchronizer(): array
    {
        $collection = new ArrayCollection();
        $this->eventManager->collect(
            'Shopware_ESIndexingBundle_Collect_Synchronizer',
            $collection
        );

        return array_merge($collection->toArray(), $this->synchronizers);
    }

    /**
     * @param Backlog[] $backlogs
     */
    private function writeBacklog(array $backlogs)
    {
        $statement = $this->connection->prepare('
            INSERT IGNORE INTO s_es_backlog (`event`, `payload`, `time`)
            VALUES (:event, :payload, :time);
        ');

        foreach ($backlogs as $backlog) {
            $statement->execute([
                ':event' => $backlog->getEvent(),
                ':payload' => json_encode($backlog->getPayload()),
                ':time' => $backlog->getTime()->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
