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

namespace Shopware\Components\MultiEdit\Resource\Product;

use Shopware\Models\Article\Detail;
use Shopware\Models\MultiEdit\QueueArticle;

/**
 * The queue class will handle the queues
 */
class Queue
{
    /**
     * @var DqlHelper
     */
    protected $dqlHelper;

    /**
     * @var Filter
     */
    protected $filterResource;

    /**
     * @var Backup
     */
    protected $backupResource;

    public function __construct(
        DqlHelper $dqlHelper,
        Filter $filter,
        Backup $backup)
    {
        $this->dqlHelper = $dqlHelper;
        $this->filterResource = $filter;
        $this->backupResource = $backup;
    }

    /**
     * @return DqlHelper
     */
    public function getDqlHelper()
    {
        return $this->dqlHelper;
    }

    /**
     * @return Filter
     */
    public function getFilterResource()
    {
        return $this->filterResource;
    }

    /**
     * @return Backup
     */
    public function getBackupResource()
    {
        return $this->backupResource;
    }

    /**
     * Pops a number of entries from the queue
     *
     * @param int $queueId
     * @param int $number
     */
    public function pop($queueId, $number)
    {
        $entityManager = $this->getDqlHelper()->getEntityManager();

        // Read details
        $query = $entityManager->createQuery('SELECT q.detailId from \Shopware\Models\MultiEdit\QueueArticle q WHERE q.queueId = ?1 ORDER BY q.id ASC');
        $query->setParameter(1, $queueId);
        $query->setMaxResults($number);
        $result = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        // We only need the first column of the result
        $result = array_map(
            function ($item) {
                return (int) array_pop($item);
            },
            $result
        );

        if (empty($result)) {
            return $result;
        }

        // Delete
        $builder = $entityManager->createQueryBuilder();
        $query = $entityManager->createQueryBuilder()
                ->delete('\Shopware\Models\MultiEdit\QueueArticle', 'q')
                ->where($builder->expr()->in('q.detailId', $result))
                ->getQuery();
        $query->execute();

        return $result;
    }

    /**
     * Create queue for a given filter array. If an queueId is passed, the existing queue will be used
     *
     * @param array $filterArray
     * @param array $operations
     * @param int   $offset
     * @param int   $limit
     * @param int   $queueId
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    public function create($filterArray, $operations, $offset, $limit, $queueId)
    {
        $entityManager = $this->getDqlHelper()->getEntityManager();

        $filterString = $this->getFilterResource()->filterArrayToString($filterArray);

        $query = $this->getFilterResource()->getFilterQuery($filterArray, $offset, $limit);
        /** @var int[] $results */
        list($results, $totalCount) = $this->getFilterResource()->getPaginatedResult($query);

        if (!empty($queueId)) {
            $newBackup = false;
            /** @var \Shopware\Models\MultiEdit\Queue|null $queue */
            $queue = $entityManager->find(\Shopware\Models\MultiEdit\Queue::class, $queueId);
            if (!$queue) {
                throw new \RuntimeException(sprintf('Queue with ID %s not found', $queueId));
            }
        } else {
            $newBackup = true;
            $queue = new \Shopware\Models\MultiEdit\Queue('product');
            $queue->setFilterString($filterString);
            $queue->setOperations(json_encode($operations));
            $queue->setCreated(new \DateTime());
            $queue->setActive(false);
            $queue->setInitialSize($totalCount);

            $entityManager->persist($queue);
            $entityManager->flush();

            $queueId = $queue->getId();
        }

        $this->getBackupResource()->create($results, $operations, $newBackup, $queue->getCreated()->getTimestamp());

        // Tested this with ~140k products - compared with pure SQL this is reasonable fast
        // In most cases the filter query will be the bottleneck
        $i = 0;
        $model = null;
        foreach ($results as $detailId) {
            // Flush after 20 entities
            if (($i++ % 20) === 0) {
                $entityManager->flush($model);
                $entityManager->clear();

                /** @var \Shopware\Models\MultiEdit\Queue $queue */
                $queue = $entityManager->getReference(\Shopware\Models\MultiEdit\Queue::class, $queueId);
            }

            /** @var Detail $detail */
            $detail = $entityManager->getReference(\Shopware\Models\Article\Detail::class, $detailId);

            $model = new QueueArticle();
            $model->setQueue($queue);
            $model->setDetail($detail);
            $entityManager->persist($model);
        }

        $done = ($offset + $limit) >= $totalCount;

        // When done, set the queue to active and finish the backup
        if ($done) {
            $queue->setActive(true);
            $this->getBackupResource()->finishBackup($filterString, $operations, $queue->getInitialSize(), $queue->getCreated()->getTimestamp());
        }

        $entityManager->flush();
        $entityManager->clear();

        return [
            'totalCount' => $totalCount,
            'offset' => $offset + $limit,
            'queueId' => $queueId,
            'done' => $done,
        ];
    }
}
