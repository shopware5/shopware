<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

use Doctrine\DBAL\Connection;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Resource\CustomerStream as CustomerStreamApi;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\CustomerStream\CustomerStream;
use Shopware\Models\CustomerStream\CustomerStreamRepository;
use Shopware\Models\CustomerStream\CustomerStreamRepositoryInterface;

/**
 * @extends Shopware_Controllers_Backend_Application<CustomerStream>
 */
class Shopware_Controllers_Backend_CustomerStream extends Shopware_Controllers_Backend_Application
{
    protected $model = CustomerStream::class;

    public function delete($id)
    {
        try {
            $this->getApiResource()->delete((int) $id);
        } catch (NotFoundException $e) {
            return ['success' => false, 'error' => 'The passed id parameter exists no more.'];
        }

        return ['success' => true];
    }

    public function save($data)
    {
        $data['freezeUp'] = null;

        if ($data['freezeUpDate'] && $data['static']) {
            $date = new DateTime($data['freezeUpDate']);
            $time = '';

            if ($data['freezeUpTime']) {
                $time = new DateTime($data['freezeUpTime']);
                $time = $time->format(' H:i:s');
            }
            $data['freezeUp'] = new DateTime($date->format('Y-m-d') . $time);
        }

        if ($data['id']) {
            $entity = $this->getApiResource()->update((int) $data['id'], $data);
        } else {
            $entity = $this->getApiResource()->create($data);
        }
        $detail = $this->getDetail($entity->getId());

        return ['success' => true, 'data' => $detail['data']];
    }

    /**
     * @return void
     */
    public function getLastFullIndexTimeAction()
    {
        $date = $this->container->get(CustomerStreamRepositoryInterface::class)
            ->getLastFillIndexDate();

        $this->View()->assign('last_index_time', $date);
    }

    /**
     * @return void
     */
    public function indexStreamAction()
    {
        $streamId = (int) $this->Request()->getParam('streamId');
        $total = (int) $this->Request()->getParam('total');

        $snippets = $this->container->get('snippets')->getNamespace('backend/customer/view/main');
        $stream = $this->container->get(ModelManager::class)->find(CustomerStream::class, $streamId);
        if (!$stream instanceof CustomerStream) {
            $this->View()->assign('success', false);

            return;
        }

        if ($stream->getFreezeUp()) {
            $this->View()->assign([
                'success' => true,
                'finish' => true,
                'progress' => 1,
                'text' => $snippets->get('stream_refreshed'),
            ]);

            return;
        }

        $iteration = (int) $this->Request()->getParam('iteration', 1);
        $offset = ($iteration - 1) * CustomerStreamRepository::INDEXING_LIMIT;

        $this->getApiResource()->indexStream($stream, $offset, CustomerStreamRepository::INDEXING_LIMIT);

        $handled = $offset + CustomerStreamRepository::INDEXING_LIMIT;

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

    /**
     * @return void
     */
    public function getNotIndexedCountAction()
    {
        $count = $this->container->get(CustomerStreamRepositoryInterface::class)->getNotIndexedCount();

        $this->View()->assign(['total' => $count]);
    }

    /**
     * @return void
     */
    public function getCustomerCountAction()
    {
        $this->View()->assign([
            'total' => $this->container->get(CustomerStreamRepositoryInterface::class)->getCustomerCount(),
        ]);
    }

    /**
     * @return void
     */
    public function buildSearchIndexAction()
    {
        $total = (int) $this->Request()->getParam('total');
        $iteration = (int) $this->Request()->getParam('iteration', 1);
        $lastId = (int) $this->Request()->getParam('lastId');

        $offset = ($iteration - 1) * CustomerStreamRepository::INDEXING_LIMIT;
        $handled = $offset + CustomerStreamRepository::INDEXING_LIMIT;

        $full = $this->Request()->getParam('full');

        $ids = $this->getApiResource()->buildSearchIndex($lastId, $full);

        $snippets = $this->container->get('snippets')->getNamespace('backend/customer/view/main');

        if ($handled >= $total) {
            $this->getApiResource()->cleanupIndexSearchIndex();

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

    /**
     * @return void
     */
    public function loadStreamAction()
    {
        $request = $this->Request();

        $streamId = $request->getParam('streamId');
        if ($streamId !== null) {
            $streamId = (int) $streamId;
        }
        $result = $this->getApiResource()->getOne(
            $streamId,
            (int) $request->getParam('start', 0),
            (int) $request->getParam('limit', 50),
            $request->getParam('conditions', ''),
            $request->getParam('sorting', '')
        );

        $data = $this->container->get(CustomerStreamRepositoryInterface::class)->fetchBackendListing($result->getIds());

        $this->View()->assign([
            'success' => true,
            'total' => $result->getTotal(),
            'data' => array_values($data),
        ]);
    }

    /**
     * @return void
     */
    public function loadChartAction()
    {
        $chart = $this->container->get(CustomerStreamRepositoryInterface::class)
            ->fetchCustomerAmount(
                $this->Request()->getParam('streamId'),
                (int) $this->Request()->getParam('months', 12)
            );

        $this->View()->assign([
            'data' => array_values($chart),
        ]);
    }

    /**
     * @return void
     */
    public function addCustomerToStreamAction()
    {
        $streamId = (int) $this->Request()->getParam('streamId');
        $customerId = (int) $this->Request()->getParam('customerId');
        $connection = $this->container->get(Connection::class);

        try {
            $connection->executeUpdate(
                'INSERT INTO s_customer_streams_mapping (stream_id, customer_id) VALUES (:streamId, :customerId)',
                [':streamId' => $streamId, ':customerId' => $customerId]
            );
            $this->View()->assign('success', true);
        } catch (Exception $e) {
            $this->View()->assign('success', false);
        }
    }

    /**
     * @return void
     */
    public function removeCustomerFromStreamAction()
    {
        $streamId = (int) $this->Request()->getParam('streamId');
        $customerId = (int) $this->Request()->getParam('customerId');

        $connection = $this->container->get(Connection::class);

        $connection->executeUpdate(
            'DELETE FROM s_customer_streams_mapping WHERE stream_id = :streamId AND customer_id = :customerId',
            [':streamId' => $streamId, ':customerId' => $customerId]
        );

        $this->View()->assign('success', true);
    }

    /**
     * @return void
     */
    public function loadAmountPerStreamChartAction()
    {
        $chart = $this->container->get(CustomerStreamRepositoryInterface::class)
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
        $this->addAclPermission('indexStream', 'save', 'You do not have sufficient rights to index Customer Streams.');
        $this->addAclPermission('buildSearchIndex', 'search_index', 'You do not have sufficient rights to index customer search.');
        $this->addAclPermission('loadChart', 'charts', 'You do not have sufficient rights to load this data.');
        $this->addAclPermission('loadAmountPerStreamChart', 'charts', 'You do not have sufficient rights to load this data.');
    }

    protected function getList($offset, $limit, $sort = [], $filter = [], array $wholeParams = [])
    {
        $resource = $this->getApiResource();

        return $resource->getList($offset ?? 0, $limit ?? 25, $filter, $sort);
    }

    /**
     * @return CustomerStreamApi
     */
    protected function getApiResource()
    {
        return $this->container->get(CustomerStreamApi::class);
    }
}
