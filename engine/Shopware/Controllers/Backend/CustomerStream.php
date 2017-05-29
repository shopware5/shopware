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
use Shopware\Models\Customer\CustomerStream;
use Shopware\Models\Customer\CustomerStreamRepository;

class Shopware_Controllers_Backend_CustomerStream extends Shopware_Controllers_Backend_Application
{
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

    public function getLastFullIndexTimeAction()
    {
        $date = $this->container->get('shopware.customer_stream.repository')
            ->getLastFillIndexDate();

        $this->View()->assign('last_index_time', $date);
    }

    public function indexStreamAction()
    {
        $streamId = (int) $this->Request()->getParam('streamId');
        $total = (int) $this->Request()->getParam('total');

        $iteration = (int) $this->Request()->getParam('iteration', 1);
        $offset = ($iteration - 1) * CustomerStreamRepository::INDEXING_LIMIT;

        /** @var StreamIndexer $indexer */
        $indexer = $this->get('shopware.customer_stream.stream_indexer');

        /** @var \Shopware\Components\CustomerStream\CustomerStreamCriteriaFactory $factory */
        $factory = $this->get('shopware.customer_stream.criteria_factory');

        $criteria = $factory->createCriteria($streamId);

        $criteria->offset($offset)
            ->limit(CustomerStreamRepository::INDEXING_LIMIT)
            ->setFetchCount(false);

        if ($criteria->getOffset() === 0) {
            $indexer->clearStreamIndex($streamId);
        }

        $indexer->populatePartial($streamId, $criteria);

        $handled = $offset + CustomerStreamRepository::INDEXING_LIMIT;

        $snippets = $this->container->get('snippets')->getNamespace('backend/customer/view/main');

        if ($handled >= $total) {
            $this->View()->assign([
                'success' => true,
                'finish' => true,
                'progress' => 1,
                'text' => $snippets->get('stream_refreshed'),
            ]);

            return;
        }

        $this->View()->assign([
            'success' => true,
            'finish' => false,
            'text' => sprintf($snippets->get('refresh_stream'), $handled, $total),
            'progress' => $handled / $total,
        ]);
    }

    public function getNotIndexedCountAction()
    {
        $count = $this->container->get('shopware.customer_stream.repository')->getNotIndexedCount();

        $this->View()->assign(['total' => $count]);
    }

    public function getCustomerCountAction()
    {
        $this->View()->assign([
            'total' => $this->container->get('shopware.customer_stream.repository')->getCustomerCount(),
        ]);
    }

    public function buildSearchIndexAction()
    {
        $total = (int) $this->Request()->getParam('total');
        $iteration = (int) $this->Request()->getParam('iteration', 1);
        $lastId = (int) $this->Request()->getParam('lastId');

        $offset = ($iteration - 1) * CustomerStreamRepository::INDEXING_LIMIT;
        $handled = $offset + CustomerStreamRepository::INDEXING_LIMIT;

        $indexer = $this->container->get('customer_search.dbal.indexing.indexer');

        $full = $this->Request()->getParam('full');

        $ids = $this->container->get('shopware.customer_stream.repository')
            ->fetchSearchIndexIds($lastId, $full);

        if (!empty($ids)) {
            $this->container->get('dbal_connection')->executeUpdate(
                'DELETE FROM s_customer_search_index WHERE id IN (:ids)',
                [':ids' => $ids],
                [':ids' => Connection::PARAM_INT_ARRAY]
            );
        }

        $indexer->populate($ids);

        $snippets = $this->container->get('snippets')->getNamespace('backend/customer/view/main');

        if ($handled >= $total) {
            $indexer->cleanupIndex();

            $this->View()->assign([
                'success' => true,
                'finish' => true,
                'progress' => 1,
                'text' => $snippets->get('customer_refreshed'),
            ]);

            return;
        }

        $this->View()->assign([
            'success' => true,
            'finish' => false,
            'params' => ['lastId' => (int) array_pop($ids)],
            'text' => sprintf($snippets->get('refresh_customer'), $handled, $total),
            'progress' => $handled / $total,
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

        $data = $this->container->get('shopware.customer_stream.repository')->fetchBackendListing($result->getIds());

        $this->View()->assign([
            'success' => true,
            'total' => $result->getTotal(),
            'data' => array_values($data),
        ]);
    }

    public function loadChartAction()
    {
        $chart = $this->container->get('shopware.customer_stream.repository')
            ->fetchCustomerAmount(
                $this->Request()->getParam('streamId'),
                (int) $this->Request()->getParam('months', 12)
            );

        $this->View()->assign([
            'data' => array_values($chart),
        ]);
    }

    public function loadAmountPerStreamChartAction()
    {
        $chart = $this->container->get('shopware.customer_stream.repository')
            ->fetchAmountPerStreamChart();

        $this->View()->assign('data', array_values($chart));
    }

    protected function initAcl()
    {
        $this->addAclPermission('read', 'list', 'You do not have sufficient rights to delete a customer.');
        $this->addAclPermission('read', 'detail', 'You do not have sufficient rights to delete a customer.');
        $this->addAclPermission('delete', 'delete', 'You do not have sufficient rights to delete a customer.');
        $this->addAclPermission('update', 'save', 'You do not have sufficient rights to update a customer.');
        $this->addAclPermission('create', 'save', 'You do not have sufficient rights to create a customer.');
        $this->addAclPermission('indexStream', 'stream_index', 'You do not have sufficient rights to index Customer Streams.');
        $this->addAclPermission('buildSearchIndex', 'search_index', 'You do not have sufficient rights to index customer search.');
        $this->addAclPermission('loadChart', 'charts', 'You do not have sufficient rights to load this data.');
        $this->addAclPermission('loadAmountPerStreamChart', 'charts', 'You do not have sufficient rights to load this data.');
    }

    protected function getList($offset, $limit, $sort = [], $filter = [], array $wholeParams = [])
    {
        $data = parent::getList($offset, $limit, $sort, $filter, $wholeParams);

        $ids = array_column($data['data'], 'id');
        if (empty($ids)) {
            return $data;
        }

        $counts = $this->container->get('shopware.customer_stream.repository')->fetchStreamsCustomerCount($ids);

        foreach ($data['data'] as &$row) {
            $id = (int) $row['id'];
            if (!array_key_exists($id, $counts)) {
                $row['customer_count'] = 0;
                $row['newsletter_count'] = 0;
            } else {
                $row = array_merge($row, $counts[$id]);
            }
        }

        return $data;
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
            sprintf('Serialization error in Customer Stream')
        );

        foreach ($conditions as $condition) {
            $criteria->addCondition($condition);
        }

        return $criteria;
    }
}
