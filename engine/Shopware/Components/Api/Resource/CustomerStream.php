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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\CustomerSearchBundle\Condition\AssignedToStreamCondition;
use Shopware\Bundle\CustomerSearchBundle\CustomerNumberSearchInterface;
use Shopware\Bundle\CustomerSearchBundleDBAL\Indexing\SearchIndexer;
use Shopware\Bundle\ESIndexingBundle\Console\ProgressHelperInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\CustomerStream\CustomerStreamCriteriaFactoryInterface;
use Shopware\Components\CustomerStream\StreamIndexerInterface;
use Shopware\Components\LogawareReflectionHelper;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\CustomerStream\CustomerStream as CustomerStreamEntity;
use Shopware\Models\CustomerStream\CustomerStreamRepository;

class CustomerStream extends Resource
{
    /**
     * @var ModelManager
     */
    protected $manager;

    /**
     * @var LogawareReflectionHelper
     */
    private $reflectionHelper;

    /**
     * @var CustomerNumberSearchInterface
     */
    private $customerNumberSearch;

    /**
     * @var CustomerStreamRepository
     */
    private $streamRepository;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var SearchIndexer
     */
    private $searchIndexer;

    /**
     * @var StreamIndexerInterface
     */
    private $streamIndexer;

    /**
     * @var CustomerStreamCriteriaFactoryInterface
     */
    private $criteriaFactory;

    /**
     * @param LogawareReflectionHelper               $reflectionHelper
     * @param CustomerNumberSearchInterface          $customerNumberSearch
     * @param CustomerStreamRepository               $streamRepository
     * @param ModelManager                           $manager
     * @param Connection                             $connection
     * @param SearchIndexer                          $searchIndexer
     * @param StreamIndexerInterface                 $streamIndexer
     * @param CustomerStreamCriteriaFactoryInterface $criteriaFactory
     */
    public function __construct(
        LogawareReflectionHelper $reflectionHelper,
        CustomerNumberSearchInterface $customerNumberSearch,
        CustomerStreamRepository $streamRepository,
        ModelManager $manager,
        Connection $connection,
        SearchIndexer $searchIndexer,
        StreamIndexerInterface $streamIndexer,
        CustomerStreamCriteriaFactoryInterface $criteriaFactory
    ) {
        $this->reflectionHelper = $reflectionHelper;
        $this->customerNumberSearch = $customerNumberSearch;
        $this->streamRepository = $streamRepository;
        $this->manager = $manager;
        $this->connection = $connection;
        $this->searchIndexer = $searchIndexer;
        $this->streamIndexer = $streamIndexer;
        $this->criteriaFactory = $criteriaFactory;
    }

    public function getOne($id = null, $offset = 0, $limit = null, $conditions, $sortings)
    {
        $this->checkPrivilege('read');

        $criteria = new Criteria();

        $conditions = $this->getConditions($id, $conditions);

        foreach ($conditions as $condition) {
            $criteria->addCondition($condition);
        }
        $sortings = json_decode($sortings, true);
        if (!empty($sortings)) {
            $sortings = $this->reflectionHelper->unserialize($sortings, '');
            foreach ($sortings as $sorting) {
                $criteria->addSorting($sorting);
            }
        }

        $criteria->offset((int) $offset);
        $criteria->limit($limit);

        return $this->customerNumberSearch->search($criteria);
    }

    public function getList($offset = 0, $limit = 25, array $criteria = [], array $orderBy = [])
    {
        $this->checkPrivilege('read');

        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(['stream', 'attribute']);
        $builder->from(CustomerStreamEntity::class, 'stream');
        $builder->leftJoin('stream.attribute', 'attribute');
        $builder->setAlias('stream');
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

        $ids = array_column($data, 'id');
        if (empty($ids)) {
            return $data;
        }

        $counts = $this->streamRepository->fetchStreamsCustomerCount($ids);

        foreach ($data as &$row) {
            $id = (int) $row['id'];
            if (!array_key_exists($id, $counts)) {
                $row['customer_count'] = 0;
                $row['newsletter_count'] = 0;
            } else {
                $row = array_merge($row, $counts[$id]);
            }

            $row['freezeUp'] = $this->updateFreezeUp($id, $row['freezeUp']);
        }

        return ['data' => $data, 'total' => $total];
    }

    public function create(array $data, $index = false)
    {
        $this->checkPrivilege('save');

        $stream = new CustomerStreamEntity();
        $stream->fromArray($data);

        $violations = $this->getManager()->validate($stream);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->getManager()->persist($stream);
        $this->getManager()->flush($stream);

        if ($index) {
            $this->indexStream($stream);
        }

        if (array_key_exists('customers', $data) && $stream->getType() === CustomerStreamEntity::TYPE_STATIC) {
            $this->insertCustomers($data['customers'], $stream->getId());
        }

        return $stream;
    }

    public function update($id, array $data, $index = false)
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

        $violations = $this->getManager()->validate($stream);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        if ($stream->getType() === CustomerStreamEntity::TYPE_DYNAMIC && $index) {
            $this->indexStream($stream);
        }

        if (array_key_exists('customers', $data) && $stream->getType() === CustomerStreamEntity::TYPE_STATIC) {
            $this->insertCustomers($data['customers'], $stream->getId());
        }

        $this->getManager()->flush($stream);

        return $stream;
    }

    public function delete($id)
    {
        $stream = $this->manager->find(CustomerStreamEntity::class, $id);

        $this->manager->remove($stream);
        $this->manager->flush($stream);

        $this->connection->executeQuery(
            'DELETE FROM s_customer_streams_mapping WHERE stream_id = :id',
            [':id' => $id]
        );
    }

    public function buildSearchIndex($lastId, $full)
    {
        $ids = $this->streamRepository->fetchSearchIndexIds($lastId, $full);

        if (!empty($ids)) {
            $this->connection->executeUpdate(
                'DELETE FROM s_customer_search_index WHERE id IN (:ids)',
                [':ids' => $ids],
                [':ids' => Connection::PARAM_INT_ARRAY]
            );
        }

        $this->searchIndexer->populate($ids);
    }

    public function cleanupIndexSearchIndex()
    {
        $this->searchIndexer->cleanupIndex();
    }

    public function indexStream(CustomerStreamEntity $stream, $offset = null, $limit = null)
    {
        $now = new \DateTime();
        if ($stream->getFreezeUp() < $now) {
            $stream->setFreezeUp(null);
            $this->manager->flush($stream);
        }

        if ($stream->getFreezeUp() !== null) {
            return;
        }
        if ($stream->getType() === CustomerStreamEntity::TYPE_STATIC) {
            return;
        }

        $criteria = $this->criteriaFactory->createCriteria($stream->getId());

        $criteria->setFetchCount(false);

        if ($offset !== null) {
            $criteria->offset($offset);
        }
        if ($limit !== null) {
            $criteria->limit($limit);
        }

        if ($criteria->getOffset() === 0) {
            $this->streamIndexer->clearStreamIndex($stream->getId());
        }

        $this->streamIndexer->populatePartial($stream->getId(), $criteria);
    }

    private function getConditions($streamId, $conditions = [])
    {
        if (!empty($conditions)) {
            return $this->reflectionHelper->unserialize(
                json_decode($conditions, true),
                'Serialization error in Customer Stream'
            );
        }

        if (!$streamId) {
            return [];
        }
        $stream = $this->manager->find(CustomerStreamEntity::class, $streamId);

        switch ($stream->getType()) {
            case CustomerStreamEntity::TYPE_DYNAMIC:
                return $this->reflectionHelper->unserialize(
                    json_decode($stream->getConditions(), true),
                    'Serialization error in Customer Stream'
                );

            case CustomerStreamEntity::TYPE_STATIC:
                return [new AssignedToStreamCondition($streamId)];
        }

        return [];
    }

    /**
     * @param array $customerIds
     * @param int   $streamId
     */
    private function insertCustomers(array $customerIds, $streamId)
    {
        $connection = $this->connection;

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

    /**
     * @param int         $id
     * @param string|null $freezeUp
     *
     * @return string|null
     */
    private function updateFreezeUp($id, $freezeUp)
    {
        if (!$freezeUp) {
            return $freezeUp;
        }

        $now = new \DateTime();

        if ($freezeUp >= $now) {
            return $freezeUp;
        }
        $this->connection->executeUpdate(
            'UPDATE s_customer_streams SET freeze_up = NULL WHERE id = :id',
            [':id' => $id]
        );

        return null;
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
