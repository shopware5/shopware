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

namespace Shopware\Bundle\CustomerSearchBundleDBAL\Indexing;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\ESIndexingBundle\LastIdQuery;
use Shopware\Components\Api\Resource\CustomerStream;
use Shopware\Components\CustomerStream\StreamIndexerInterface;

class CronJobSubscriber implements SubscriberInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var StreamIndexerInterface
     */
    private $streamIndexer;

    /**
     * @var SearchIndexerInterface
     */
    private $searchIndexer;

    /**
     * @var CustomerStream
     */
    private $customerStream;

    public function __construct(Connection $connection, SearchIndexerInterface $searchIndexer, StreamIndexerInterface $streamIndexer, CustomerStream $customerStream)
    {
        $this->connection = $connection;
        $this->streamIndexer = $streamIndexer;
        $this->searchIndexer = $searchIndexer;
        $this->customerStream = $customerStream;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Shopware_CronJob_RefreshCustomerStreams' => 'refresh',
        ];
    }

    public function refresh()
    {
        $helper = new CronJobProgressHelper();

        $query = $this->createQuery();

        $this->connection->transactional(function () use ($query) {
            $this->connection->executeUpdate('DELETE FROM s_customer_search_index');

            while ($ids = $query->fetch()) {
                $this->searchIndexer->populate($ids);
            }
        });

        $streams = $this->fetchStreams();

        if (empty($streams)) {
            return true;
        }

        foreach ($streams as $stream) {
            if ($stream['freeze_up']) {
                $stream['freeze_up'] = new \DateTime($stream['freeze_up']);
            }
            $result = $this->customerStream->updateFrozenState($stream['id'], $stream['freeze_up'], $stream['conditions']);
            if ($result) {
                $stream['static'] = $result['static'];
            }

            if ($stream['static']) {
                continue;
            }

            $this->streamIndexer->populate($stream['id'], $helper);
        }

        return true;
    }

    /**
     * @return array
     */
    private function fetchStreams()
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['id', 'name', 'conditions', 'freeze_up', 'static']);
        $query->from('s_customer_streams', 'streams');

        return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function createQuery()
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['id', 'id']);
        $query->from('s_user', 'u');
        $query->where('u.id > :lastId');
        $query->setParameter(':lastId', 0);
        $query->orderBy('u.id', 'ASC');
        $query->setMaxResults(100);

        return new LastIdQuery($query);
    }
}
