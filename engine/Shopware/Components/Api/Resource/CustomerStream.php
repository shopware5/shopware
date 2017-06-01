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

namespace Shopware\Components\Api\Resource;

use Shopware\Bundle\ESIndexingBundle\Console\ProgressHelperInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Models\CustomerStream\CustomerStream as CustomerStreamEntity;

class CustomerStream extends Resource
{
    public function getOne($id, $offset = 0, $limit = null)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ParameterMissingException();
        }

        $stream = $this->getManager()->find(CustomerStreamEntity::class, $id);

        if (!$stream) {
            throw new NotFoundException("Customer Stream with id $id not found");
        }

        $criteria = new Criteria();
        $conditions = $this->container->get('shopware.logaware_reflection_helper')->unserialize(
            $stream->getConditions(),
            sprintf('Serialization error in Customer Stream')
        );

        foreach ($conditions as $condition) {
            $criteria->addCondition($condition);
        }

        $criteria->offset((int) $offset);
        $criteria->limit($limit);

        return $this->container->get('customer_search.dbal.number_search')->search($criteria);
    }

    public function getList($offset = 0, $limit = 25, array $criteria = [], array $orderBy = [])
    {
        $this->checkPrivilege('read');

        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(['stream', 'attribute']);
        $builder->from(CustomerStreamEntity::class, 'stream');
        $builder->leftJoin('stream.attribute', 'attribute');
        $builder->setFirstResult($offset);
        $builder->setMaxResults($limit);

        if (!empty($criteria)) {
            $builder->addFilter($criteria);
        }
        if (!empty($orderBy)) {
            $builder->addOrderBy($orderBy);
        }

        $query = $builder->getQuery();
        $query->setHydrationMode($this->getResultMode());
        $paginator = $this->getManager()->createPaginator($query);
        $total = $paginator->count();
        $data = $paginator->getIterator()->getArrayCopy();

        return ['data' => $data, 'total' => $total];
    }

    public function create(array $data)
    {
        $this->checkPrivilege('save');

        $stream = new CustomerStreamEntity();
        $stream->fromArray($data);

        $this->getManager()->persist($stream);
        $this->getManager()->flush($stream);

        $this->indexStream($data, $stream);

        return $stream;
    }

    public function update($id, array $data)
    {
        $this->checkPrivilege('save');

        if (empty($id)) {
            throw new ParameterMissingException();
        }

        $stream = $this->getManager()->find(CustomerStreamEntity::class, $id);

        if (!$stream) {
            throw new NotFoundException("Customer Stream with id $id not found");
        }

        $stream->fromArray($data);

        $this->indexStream($data, $stream);

        $this->getManager()->flush($stream);

        return $stream;
    }

    /**
     * @param array                $data
     * @param CustomerStreamEntity $stream
     */
    protected function indexStream(array $data, CustomerStreamEntity $stream)
    {
        if ($stream->getType() === CustomerStreamEntity::TYPE_DYNAMIC) {
            $indexer = $this->container->get('shopware.customer_stream.stream_indexer');
            $indexer->populate($stream->getId(), new ApiProgressHelper());

            return;
        }

        if (array_key_exists('customers', $data) && $stream->getType() === CustomerStreamEntity::TYPE_STATIC) {
            $this->insertCustomers($data['customers'], $stream->getId());
        }
    }

    /**
     * @param array $customerIds
     * @param int   $streamId
     */
    private function insertCustomers(array $customerIds, $streamId)
    {
        $connection = $this->container->get('dbal_connection');

        $connection->transactional(function () use ($connection, $customerIds, $streamId) {
            $connection->executeUpdate(
                'DELETE FROM s_customer_streams_mapping WHERE stream_id = :streamId',
                [':streamId' => (int) $streamId]
            );

            $insert = $connection->prepare('INSERT INTO s_customer_streams_mapping (stream_id, customer_id) VALUES (:streamId, :customerId)');
            $customerIds = array_keys(array_flip($customerIds));

            foreach ($customerIds as $customerId) {
                $insert->execute([
                    ':streamId' => (int) $streamId,
                    ':customerId' => (int) $customerId,
                ]);
            }
        });
    }
}

class ApiProgressHelper implements ProgressHelperInterface
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
