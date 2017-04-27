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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Components\CustomerStream\StreamIndexer;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Customer\CustomerStream;

class Shopware_Controllers_Backend_CustomerStream extends Shopware_Controllers_Backend_Application
{
    const INDEXING_LIMIT = 100;

    protected $model = CustomerStream::class;

    public function delete($id)
    {
        $success = parent::delete($id);
        $this->get('dbal_connection')->executeQuery(
            'DELETE FROM s_customer_streams_mapping WHERE stream_id = :id',
            [':id' => $id]
        );

        return $success;
    }

    public function deleteCustomerAction()
    {
        $id = $this->Request()->get('id');

        if (!$id) {
            $this->View()->assign('success', false);
        } else {
            $this->deleteCustomer($id);
            $this->View()->assign('success', true);
        }
    }

    public function indexStreamAction()
    {
        $streamId = (int) $this->Request()->getParam('streamId');
        $total = (int) $this->Request()->getParam('total');

        $iteration = (int) $this->Request()->getParam('iteration', 1);
        $offset = ($iteration - 1) * self::INDEXING_LIMIT;

        /** @var StreamIndexer $indexer */
        $indexer = $this->get('customer_search.dbal.indexing.stream_indexer');

        /** @var \Shopware\Components\CustomerStream\CustomerStreamCriteriaFactory $factory */
        $factory = $this->get('shopware_customer_search.stream_criteria_factory');

        $criteria = $factory->createCriteria($streamId);

        $criteria->offset($offset)
            ->limit(self::INDEXING_LIMIT)
            ->setFetchCount(false);

        if ($criteria->getOffset() === 0) {
            $indexer->clearStreamIndex($streamId);
        }

        $indexer->populatePartial($streamId, $criteria);

        $handled = $offset + self::INDEXING_LIMIT;

        if ($handled >= $total) {
            $this->View()->assign([
                'success' => true,
                'finish' => true,
                'progress' => 1,
                'text' => 'Stream wurde aktualisiert',
            ]);

            return;
        }

        $this->View()->assign([
            'success' => true,
            'finish' => false,
            'text' => sprintf('Aktualisiere %s von %s Streamkunden', $handled, $total),
            'progress' => $handled / $total,
        ]);
    }

    public function getPartialCountAction()
    {
        $lastIndex = $this->getLastIndexTime();

        $query = $this->container->get('dbal_connection')->createQueryBuilder();
        $query->select(['COUNT(id)']);
        $query->from('s_user', 'customer');

        if ($lastIndex) {
            $query->where('customer.firstlogin >= :lastIndexTime');
            $query->setParameter(':lastIndexTime', $lastIndex);
        }

        $this->View()->assign([
            'total' => $query->execute()->fetch(PDO::FETCH_COLUMN),
            'lastIndexTime' => $lastIndex,
        ]);
    }

    public function buildSearchIndexAction()
    {
        $total = (int) $this->Request()->getParam('total');
        $iteration = (int) $this->Request()->getParam('iteration', 1);
        $offset = ($iteration - 1) * self::INDEXING_LIMIT;
        $handled = $offset + self::INDEXING_LIMIT;

        $indexer = Shopware()->Container()->get('customer_search.dbal.indexing.indexer');

        /** @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = $this->get('dbal_connection')->createQueryBuilder();
        $query->select('id');
        $query->from('s_user', 'user');
        $query->orderBy('id');

        $lastIndex = $this->Request()->getParam('lastIndexTime');

        //partial update for last x days
        if ($lastIndex) {
            $query->where('user.firstlogin >= :lastIndex');
            $query->setParameter(':lastIndex', $lastIndex);
        } else {
            $query->where('user.id > :lastId');
            $query->andWhere('user.id <= :maxId');
            $query->setParameter(':lastId', $offset);
            $query->setParameter(':maxId', $handled);

            if ($offset === 0) {
                $indexer->clearIndex();
            }
        }

        $query->setMaxResults(self::INDEXING_LIMIT);

        $ids = $query->execute()->fetchAll(PDO::FETCH_COLUMN);

        $indexer->populate($ids);

        if ($handled >= $total) {
            $this->View()->assign([
                'success' => true,
                'finish' => true,
                'progress' => 1,
                'text' => 'Kunden erfolgreich analyisiert',
            ]);

            return;
        }

        $this->View()->assign([
            'success' => true,
            'finish' => false,
            'text' => sprintf('Analysiere %s von %s Kunden', $handled, $total),
            'progress' => $handled / $total,
        ]);
    }

    public function getLastFullIndexTimeAction()
    {
        $time = $this->getLastIndexTime();
        $this->View()->assign('last_index_time', $time);
    }

    public function getCustomerCountAction()
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = $this->get('dbal_connection')->createQueryBuilder();
        $query->select('COUNT(id)');
        $query->from('s_user', 'user');

        $this->View()->assign([
            'total' => $query->execute()->fetch(PDO::FETCH_COLUMN),
        ]);
    }

    public function loadStreamAction()
    {
        $request = $this->Request();

        if ($request->has('conditions')) {
            $conditions = $request->getParam('conditions', '');
        } else {
            $stream = $this->getDetail($request->getParam('streamId'));
            $conditions = $stream['data']['conditions'];
        }

        $conditions = $conditions === null ? [] : json_decode($conditions, true);

        $criteria = $this->createCriteria($conditions);
        $criteria->offset((int) $request->getParam('start', 0));
        $criteria->limit((int) $request->getParam('limit', 50));

        $sortings = json_decode($request->getParam('sorting', []), true);

        if (!empty($sortings)) {
            $reflectionHelper = $this->container->get('shopware.logaware_reflection_helper');
            $sortings = $reflectionHelper->unserialize($sortings, '');

            foreach ($sortings as $sorting) {
                $criteria->addSorting($sorting);
            }
        }

        /** @var \Shopware\Bundle\CustomerSearchBundleDBAL\CustomerNumberSearch $numberSearch */
        $numberSearch = $this->get('customer_search.dbal.number_search');

        $result = $numberSearch->search($criteria);

        $streams = $this->fetchStreamsForCustomers($result->getIds());

        /** @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = $this->get('dbal_connection')->createQueryBuilder();
        $query->select('*');
        $query->from('s_customer_search_index', 'search_index');
        $query->where('search_index.id IN (:ids)');
        $query->setParameter(':ids', $result->getIds(), Connection::PARAM_INT_ARRAY);

        $dataRows = $query->execute()->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);

        $sorted = [];
        foreach ($result->getCustomers() as $row) {
            $id = $row->getId();

            $data = $dataRows[$id];
            $data['id'] = $id;

            $data['interests'] = json_decode($data['interests'], true);

            $data['streams'] = [];
            if (array_key_exists($row->getId(), $streams)) {
                $data['streams'] = $streams[$row->getId()];
            }

            $sorted[$id] = $data;
        }

        $this->View()->assign([
            'success' => true,
            'total' => $result->getTotal(),
            'data' => array_values($sorted),
        ]);
    }

    public function loadChartAction()
    {
        $date = (new \DateTime())->sub(new \DateInterval('P' . (int) 12 . 'M'));
        $now = new DateTime();

        $streamId = $this->Request()->getParam('streamId');

        $query = $this->createStreamAmountQuery($date, $streamId);

        $data = $query->execute()->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);
        $diff = $now->diff($date);
        $total = $diff->m + ($diff->y * 12);

        $chart = [];
        for ($i = 0; $i < $total; ++$i) {
            $month = $date->add(new DateInterval('P' . 1 . 'M'));
            $format = $month->format('Y/m');

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

        $this->View()->assign([
            'data' => array_values($chart),
        ]);
    }

    public function loadAmountPerStreamChartAction()
    {
        $streams = $this->container->get('dbal_connection')
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
        foreach ($streams as $name) {
            $default[$name] = 0;
        }

        $now = new DateTime();
        $diff = $now->diff($date);
        $total = $diff->m + ($diff->y * 12);

        $chart = [];
        for ($i = 0; $i < $total; ++$i) {
            $month = $date->add(new DateInterval('P' . 1 . 'M'));
            $format = $month->format('Y-m');

            $chart[$format] = array_merge(['yearMonth' => $format], $default);

            foreach ($streamAmount[$format] as $row) {
                $stream = $streams[$row['stream']];
                $chart[$format][$stream] += (float) $row['invoice_amount_sum'];
            }

            if (array_key_exists($format, $amount)) {
                $chart[$format]['unassigned'] = (float) $amount[$format];
            }
        }

        $this->View()->assign('data', array_values($chart));
    }

    /**
     * @param array $conditions
     *
     * @return Criteria
     */
    private function createCriteria(array $conditions)
    {
        $criteria = new Criteria();

        $conditions = $this->container->get('shopware.logaware_reflection_helper')->unserialize(
            $conditions,
            sprintf('Serialization error in customer stream')
        );

        foreach ($conditions as $condition) {
            $criteria->addCondition($condition);
        }

        return $criteria;
    }

    /**
     * @return false|string
     */
    private function getLastIndexTime()
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = $this->container->get('dbal_connection')->createQueryBuilder();
        $query->select('MIN(index_time)');
        $query->from('s_customer_search_index', 'search_index');

        return $query->execute()->fetch(PDO::FETCH_COLUMN);
    }

    private function fetchStreamsForCustomers($ids)
    {
        $query = $this->container->get('dbal_connection')->createQueryBuilder();
        $query->select(['customer_id', 'stream_id']);
        $query->from('s_customer_streams_mapping');
        $query->where('customer_id IN (:ids)');
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $mapping = $query->execute()->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

        if (empty($mapping)) {
            return [];
        }

        $streamIds = [];
        foreach ($mapping as $row) {
            $streamIds = array_merge($streamIds, $row);
        }
        $streamIds = array_keys(array_flip($streamIds));

        $query = $this->container->get('dbal_connection')->createQueryBuilder();
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
     * @param DateTime $date
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function createAmountPerMonthQuery(DateTime $date)
    {
        $format = "DATE_FORMAT(orders.ordertime,'%Y-%m')";

        /** @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = $this->get('dbal_connection')->createQueryBuilder();
        $query->select([
            $format,
            'ROUND(SUM(orders.invoice_amount), 2) as invoice_amount_sum',
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
     * @param $date
     * @param $streamId
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function createStreamAmountQuery($date, $streamId)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = $this->get('dbal_connection')->createQueryBuilder();
        $query->select(
            [
                "DATE_FORMAT(orders.ordertime, '%Y/%m')",
                "DATE_FORMAT(orders.ordertime, '%Y/%m') as yearMonth",
                'COUNT(DISTINCT orders.id) count_orders',
                'ROUND(SUM(orders.invoice_amount), 2) as invoice_amount_sum',
                'ROUND(AVG(orders.invoice_amount), 2) as invoice_amount_avg',
                'MIN(orders.invoice_amount) as invoice_amount_min',
                'MAX(orders.invoice_amount) as invoice_amount_max',
                'MIN(orders.ordertime) as first_order_time',
                'MAX(orders.ordertime) as last_order_time',
                'ROUND(AVG(details.price), 2) as product_avg',
            ]
        );
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
            $query->innerJoin(
                'orders',
                's_customer_streams_mapping',
                'stream',
                'stream.customer_id = orders.userID AND stream.stream_id = :streamId'
            );
            $query->setParameter(':streamId', $streamId);

            return $query;
        }

        return $query;
    }

    /**
     * @param int $id
     */
    private function deleteCustomer($id)
    {
        $customer = $this->container->get('models')->find(Customer::class, $id);

        $this->container->get('models')->remove($customer);
        $this->container->get('models')->flush();

        $this->get('dbal_connection')->executeQuery(
            'DELETE FROM s_customer_streams_mapping WHERE customer_id = :id',
            [':id' => $id]
        );

        $this->get('dbal_connection')->executeQuery(
            'DELETE FROM s_customer_search_index WHERE id = :id',
            [':id' => $id]
        );
    }
}
