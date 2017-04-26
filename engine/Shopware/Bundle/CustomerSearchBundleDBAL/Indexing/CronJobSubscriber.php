<?php
/**
 * Created by PhpStorm.
 * User: x
 * Date: 25.04.17
 * Time: 14:18
 */

namespace Shopware\Bundle\CustomerSearchBundleDBAL\Indexing;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\ESIndexingBundle\LastIdQuery;
use Shopware\Bundle\ESIndexingBundle\Console\ProgressHelperInterface;
use Shopware\Components\CustomerStream\StreamIndexer;

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

    public function refresh()
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
