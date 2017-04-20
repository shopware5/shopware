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

namespace Shopware\Bundle\CustomerSearchBundle\CustomerStream;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\ESIndexingBundle\Console\ProgressHelperInterface;
use Shopware\Bundle\ESIndexingBundle\LastIdQuery;

class CronJobSubscriber implements SubscriberInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var StreamIndexer
     */
    private $streamIndexer;

    /**
     * @var SearchIndexer
     */
    private $searchIndexer;

    public function __construct(Connection $connection, SearchIndexer $searchIndexer, StreamIndexer $streamIndexer)
    {
        $this->connection = $connection;
        $this->streamIndexer = $streamIndexer;
        $this->searchIndexer = $searchIndexer;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Shopware_CronJob_RefreshCustomerStreams' => 'refresh',
        ];
    }

    public function refresh(\Enlight_Event_EventArgs $args)
    {
        $helper = new CronJobProgressHelper();

        $query = $this->createQuery();

        while ($ids = $query->fetch()) {
            $this->searchIndexer->populate($ids);
        }

        $streams = $this->fetchStreams();

        if (empty($streams)) {
            return true;
        }

        foreach ($streams as $stream) {
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
        $query->select(['id', 'name']);
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

class CronJobProgressHelper implements ProgressHelperInterface
{
    public function start($count, $label = '')
    {
    }

    public function advance($step = 1)
    {
    }

    public function finish()
    {
    }
}
