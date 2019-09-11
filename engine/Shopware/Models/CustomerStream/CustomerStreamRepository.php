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

namespace Shopware\Models\CustomerStream;

use DateInterval;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PDO;

class CustomerStreamRepository implements CustomerStreamRepositoryInterface
{
    const INDEXING_LIMIT = 250;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function hasCustomerStreamEmotions($categoryId)
    {
        return $this->connection->fetchColumn(
            'SELECT emotion.id 
            FROM s_emotion emotion 
            INNER JOIN s_emotion_categories categories 
                ON categories.emotion_id = emotion.id 
                AND categories.category_id = :id
            WHERE emotion.customer_stream_ids IS NOT NULL AND emotion.active = 1
            LIMIT 1',
            [':id' => $categoryId]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function fetchBackendListing(array $ids)
    {
        $streams = $this->fetchStreamsForCustomers($ids);

        /** @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = $this->connection->createQueryBuilder();
        $query->select('*');
        $query->from('s_customer_search_index', 'search_index');
        $query->where('search_index.id IN (:ids)');
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $dataRows = $query->execute()->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);

        $sorted = [];
        foreach ($ids as $id) {
            $data = $dataRows[$id];
            $data['id'] = $id;

            $data['interests'] = json_decode($data['interests'], true);

            $data['streams'] = [];
            if (array_key_exists($id, $streams)) {
                $data['streams'] = $streams[$id];
            }

            $sorted[$id] = $data;
        }

        return $sorted;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchStreamsCustomerCount(array $streamIds)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select([
            'stream_id',
            'COUNT(DISTINCT customer_id) as customer_count',
            'SUM(IF(campaign.id IS NULL, 0, 1)) as newsletter_count',
        ]);

        $query->from('s_customer_streams_mapping', 'mapping');
        $query->leftJoin('mapping', 's_user', 'customer', 'customer.id = mapping.customer_id');
        $query->leftJoin('mapping', 's_campaigns_mailaddresses', 'campaign', 'campaign.email = customer.email');
        $query->where('mapping.stream_id IN (:ids)');
        $query->setParameter(':ids', $streamIds, Connection::PARAM_INT_ARRAY);
        $query->groupBy('stream_id');

        return $query->execute()->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);
    }

    /**
     * {@inheritdoc}
     */
    public function getNotIndexedCount()
    {
        $now = new DateTime();

        return (int) $this->connection->fetchColumn('
            SELECT COUNT(customers.id)
            FROM s_user customers
            WHERE customers.id NOT IN (
                SELECT search_index.id 
                FROM s_customer_search_index search_index
                WHERE search_index.index_time >= :indexTime
            )',
            [':indexTime' => $now->format('Y-m-d')]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerCount()
    {
        return (int) $this->connection->fetchColumn('SELECT COUNT(id) FROM s_user');
    }

    /**
     * {@inheritdoc}
     */
    public function fetchSearchIndexIds($offset, $full = false)
    {
        /** @var QueryBuilder $query */
        $query = $this->connection->createQueryBuilder();

        $query->select('DISTINCT id');
        $query->from('s_user', 'user');

        if ($full) {
            $query->andWhere('user.id > :lastId');
            $query->setParameter(':lastId', $offset);
        } else {
            $query->andWhere('user.id NOT IN (
                SELECT search_index.id 
                FROM s_customer_search_index search_index
                WHERE search_index.index_time >= :indexTime)'
            );
            $now = new DateTime();
            $query->setParameter(':indexTime', $now->format('Y-m-d'));
        }

        $query->setMaxResults(self::INDEXING_LIMIT);

        return $query->execute()->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchCustomerAmount($streamId = null, $month = 12)
    {
        $date = (new DateTime())->sub(new DateInterval('P' . $month . 'M'));
        $now = new DateTime();

        $query = $this->createStreamAmountQuery($date, $streamId);

        $data = $query->execute()->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);
        $diff = $now->diff($date);
        $total = $diff->m + ($diff->y * 12);

        $chart = [];
        for ($i = 0; $i < $total; ++$i) {
            $month = $date->add(new DateInterval('P' . 1 . 'M'));
            $format = $month->format('Y-m');

            if (array_key_exists($format, $data)) {
                $chart[] = $data[$format];
            } else {
                $chart[] = [
                    'yearMonth' => $format,
                    'count_orders' => 0,
                    'invoice_amount_sum' => 0,
                    'invoice_amount_avg' => 0,
                    'invoice_amount_min' => 0,
                    'invoice_amount_max' => 0,
                    'first_order_time' => 0,
                    'last_order_time' => 0,
                    'product_avg' => 0,
                ];
            }
        }

        return $chart;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAmountPerStreamChart()
    {
        $streams = $this->connection
            ->createQueryBuilder()
            ->select(['id', 'name'])
            ->from('s_customer_streams')
            ->execute()->fetchAll(PDO::FETCH_KEY_PAIR);

        $date = (new \DateTime())->sub(new \DateInterval('P' . (int) 12 . 'M'));

        $query = $this->createAmountPerMonthQuery($date);
        $query->addSelect('stream_mapping.stream_id as stream');
        $query->innerJoin('orders', 's_customer_streams_mapping', 'stream_mapping', 'stream_mapping.customer_id = orders.userID');
        $query->addGroupBy('stream_mapping.stream_id');
        $streamAmount = $query->execute()->fetchAll(PDO::FETCH_GROUP);

        $query = $this->createAmountPerMonthQuery($date);
        $query->andWhere('orders.userID NOT IN (SELECT DISTINCT customer_id FROM s_customer_streams_mapping)');
        $amount = $query->execute()->fetchAll(PDO::FETCH_KEY_PAIR);

        $default = ['unassigned' => 0];
        foreach (array_keys($streams) as $id) {
            $default['stream_' . $id] = 0;
        }

        $now = new DateTime();
        $diff = $now->diff($date);
        $total = $diff->m + ($diff->y * 12);

        $chart = [];
        for ($i = 0; $i < $total; ++$i) {
            $month = $date->add(new DateInterval('P' . 1 . 'M'));
            $format = $month->format('Y-m');

            $chart[$format] = array_merge(['yearMonth' => $format], $default);

            if (array_key_exists($format, $amount)) {
                $chart[$format]['unassigned'] = (float) $amount[$format];
            }

            if (!isset($streamAmount[$format])) {
                continue;
            }

            foreach ($streamAmount[$format] as $row) {
                $stream = 'stream_' . $row['stream'];
                $chart[$format][$stream] += (float) $row['invoice_amount_sum'];
            }
        }

        return $chart;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastFillIndexDate()
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('MIN(index_time)');
        $query->from('s_customer_search_index', 'search_index');

        return $query->execute()->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchStreamsForCustomers(array $customerIds)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['customer_id', 'stream_id']);
        $query->from('s_customer_streams_mapping');
        $query->where('customer_id IN (:ids)');
        $query->setParameter(':ids', $customerIds, Connection::PARAM_INT_ARRAY);

        $mapping = $query->execute()->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

        if (empty($mapping)) {
            return [];
        }

        $streamIds = [];
        foreach ($mapping as $row) {
            $streamIds = array_merge($streamIds, $row);
        }
        $streamIds = array_keys(array_flip($streamIds));

        $query = $this->connection->createQueryBuilder();
        $query->select(['streams.id as array_key', 'streams.id', 'streams.name']);
        $query->from('s_customer_streams', 'streams');
        $query->where('streams.id IN (:ids)');
        $query->setParameter(':ids', $streamIds, Connection::PARAM_INT_ARRAY);
        $streams = $query->execute()->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);

        $result = [];
        foreach ($mapping as $customerId => $row) {
            $result[$customerId] = array_values(array_intersect_key($streams, array_flip($row)));
        }

        return $result;
    }

    /**
     * @return QueryBuilder
     */
    private function createAmountPerMonthQuery(DateTime $date)
    {
        $format = "DATE_FORMAT(orders.ordertime,'%Y-%m')";

        /** @var QueryBuilder $query */
        $query = $this->connection->createQueryBuilder();
        $query->select([
            $format,
            'ROUND(SUM(orders.invoice_amount / orders.currencyFactor), 2) as invoice_amount_sum',
        ]);

        $query->from('s_order', 'orders');
        $query->andWhere('orders.status != :cancelStatus');
        $query->andWhere('orders.ordernumber IS NOT NULL');
        $query->andWhere('orders.ordertime >= :orderTime');
        $query->andWhere('orders.ordernumber != 0');
        $query->setParameter(':cancelStatus', -1);
        $query->setParameter(':orderTime', $date->format('Y-m'));
        $query->addGroupBy($format);

        return $query;
    }

    /**
     * @param int|null $streamId
     *
     * @return QueryBuilder
     */
    private function createStreamAmountQuery(DateTime $date, $streamId = null)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = $this->connection->createQueryBuilder();
        $query->select([
            "DATE_FORMAT(orders.ordertime, '%Y-%m')",
            "DATE_FORMAT(orders.ordertime, '%Y-%m') as yearMonth",
            'COUNT(DISTINCT orders.id) count_orders',
            'MIN(orders.invoice_amount / orders.currencyFactor) as invoice_amount_min',
            'MAX(orders.invoice_amount / orders.currencyFactor) as invoice_amount_max',
            'MIN(orders.ordertime) as first_order_time',
            'MAX(orders.ordertime) as last_order_time',
            'ROUND(AVG(details.price / orders.currencyFactor), 2) as product_avg',
        ]);
        $query->from('s_order', 'orders');
        $query->innerJoin('orders', 's_order_details', 'details', 'details.orderID = orders.id AND details.modus = 0');
        $query->andWhere('orders.status != :cancelStatus');
        $query->andWhere('orders.ordernumber IS NOT NULL');
        $query->andWhere('orders.ordertime >= :orderTime');
        $query->andWhere('orders.ordernumber != 0');
        $query->setParameter(':cancelStatus', -1);
        $query->setParameter(':orderTime', $date->format('Y-m'));
        $query->groupBy("DATE_FORMAT(orders.ordertime,'%Y-%m')");

        if ($streamId) {
            $query->innerJoin('orders', 's_customer_streams_mapping', 'stream', 'stream.customer_id = orders.userID AND stream.stream_id = :streamId');
            $query->setParameter(':streamId', $streamId);
        }

        return $query;
    }
}
