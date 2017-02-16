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

use Shopware\Bundle\CustomerSearchBundle\Criteria;
use Shopware\Bundle\CustomerSearchBundle\CustomerNumberRow;
use Shopware\Bundle\CustomerSearchBundle\CustomerStream\CustomerStreamCriteriaFactory;
use Shopware\Bundle\CustomerSearchBundle\CustomerStream\StreamIndexer;
use Shopware\Components\ReflectionHelper;
use Shopware\Models\Customer\CustomerStream;

class Shopware_Controllers_Backend_CustomerStream extends Shopware_Controllers_Backend_Application
{
    const INDEXING_LIMIT = 100;

    protected $model = CustomerStream::class;

    public function indexStreamAction()
    {
        $streamId = (int) $this->Request()->getParam('streamId');
        $total = (int) $this->Request()->getParam('total');

        $iteration = (int) $this->Request()->getParam('iteration', 1);
        $offset = ($iteration - 1) * self::INDEXING_LIMIT;

        /** @var StreamIndexer $indexer */
        $indexer = $this->get('shopware_customer_search.stream_indexer');

        /** @var CustomerStreamCriteriaFactory $factory */
        $factory = $this->get('shopware_customer_search.stream_criteria_factory');

        $criteria = $factory->createCriteria($streamId);

        $criteria->offset($offset)
            ->limit(self::INDEXING_LIMIT)
            ->setFetchTotal(false);

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
                'text' => 'All customers indexed',
            ]);

            return;
        }

        $this->View()->assign([
            'success' => true,
            'finish' => false,
            'text' => sprintf('Indexing %s of %s customers', $handled, $total),
            'progress' => $handled / $total,
        ]);
    }

    public function buildSearchIndexAction()
    {
        $total = (int) $this->Request()->getParam('total');
        $iteration = (int) $this->Request()->getParam('iteration', 1);
        $offset = ($iteration - 1) * self::INDEXING_LIMIT;

        /** @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = $this->get('dbal_connection')->createQueryBuilder();
        $query->select('id');
        $query->from('s_user', 'user');
        $query->where('user.id > :lastId');
        $query->setParameter(':lastId', $offset);
        $query->orderBy('id');
        $query->setMaxResults(self::INDEXING_LIMIT);

        $ids = $query->execute()->fetchAll(PDO::FETCH_COLUMN);

        $indexer = Shopware()->Container()->get('shopware_bundle_customer_search.customer_stream.search_indexer');
        if ($offset === 0) {
            $indexer->clearIndex();
        }
        $indexer->populate($ids);

        $handled = $offset + self::INDEXING_LIMIT;

        if ($handled >= $total) {
            $this->View()->assign([
                'success' => true,
                'finish' => true,
                'progress' => 1,
                'text' => 'All customers indexed',
            ]);

            return;
        }

        $this->View()->assign([
            'success' => true,
            'finish' => false,
            'text' => sprintf('Indexing %s of %s customers', $handled, $total),
            'progress' => $handled / $total,
        ]);
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
            $stream = $this->getDetail(
                $request->getParam('streamId')
            );
            $conditions = $stream['data']['conditions'];
        }

        $offset = (int) $request->getParam('start', 0);
        $limit = (int) $request->getParam('limit', 50);

        if ($conditions !== null) {
            $conditions = json_decode($conditions, true);
        } else {
            $conditions = [];
        }

        $criteria = $this->createCriteria($conditions);

        $criteria->offset($offset);
        $criteria->limit($limit);

        /** @var \Shopware\Bundle\CustomerSearchBundle\CustomerNumberSearch $numberSearch */
        $numberSearch = $this->get('shopware_customer_search.customer_number_search');

        $result = $numberSearch->search($criteria);

        $data = array_map(function (CustomerNumberRow $row) {
            $data = $row->getAttribute('search')->toArray();
            $data['interests'] = json_decode($data['interests'], true);
            $data['newest_interests'] = json_decode($data['newest_interests'], true);

            return $data;
        }, $result->getRows());

        $this->View()->assign([
            'success' => true,
            'total' => $result->getTotal(),
            'data' => array_values($data),
        ]);
    }

    public function loadChartAction()
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $query */
        $query = $this->get('dbal_connection')->createQueryBuilder();
        $query->select([
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
        ]);

        $date = (new \DateTime())->sub(new \DateInterval('P' . (int) 12 . 'M'));
        $now = new DateTime();

        $query->from('s_order', 'orders');
        $query->innerJoin('orders', 's_order_details', 'details', 'details.orderID = orders.id AND details.modus = 0');
        $query->andWhere('orders.status != :cancelStatus');
        $query->andWhere('orders.ordernumber IS NOT NULL');
        $query->andWhere('orders.ordertime >= :orderTime');
        $query->andWhere('orders.ordernumber != 0');
        $query->setParameter(':cancelStatus', -1);
        $query->setParameter(':orderTime', $date->format('Y-m'));
        $query->groupBy("DATE_FORMAT(orders.ordertime,'%Y-%m')");

        if ($streamId = $this->Request()->getParam('streamId')) {
            $query->innerJoin('orders', 's_customer_streams_mapping', 'stream', 'stream.customer_id = orders.userID AND stream.stream_id = :streamId');
            $query->setParameter(':streamId', $streamId);
        }

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

    /**
     * @param array $conditions
     *
     * @return Criteria
     */
    private function createCriteria(array $conditions)
    {
        $criteria = new Criteria();

        $reflector = new ReflectionHelper();

        foreach ($conditions as $className => $arguments) {
            $className = explode('|', $className);
            $className = $className[0];
            /** @var \Shopware\Bundle\SearchBundle\ConditionInterface $condition */
            $condition = $reflector->createInstanceFromNamedArguments($className, $arguments);
            $criteria->addCondition($condition);
        }

        return $criteria;
    }
}
