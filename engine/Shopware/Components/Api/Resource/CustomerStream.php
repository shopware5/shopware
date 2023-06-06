<?php

declare(strict_types=1);
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

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query;
use Shopware\Bundle\CustomerSearchBundle\Condition\AssignedToStreamCondition;
use Shopware\Bundle\CustomerSearchBundle\CustomerNumberSearchInterface;
use Shopware\Bundle\CustomerSearchBundle\CustomerNumberSearchResult;
use Shopware\Bundle\CustomerSearchBundleDBAL\Indexing\SearchIndexerInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Components\Api\Exception\CustomValidationException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\PrivilegeException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\CustomerStream\CustomerStreamCriteriaFactoryInterface;
use Shopware\Components\CustomerStream\StreamIndexerInterface;
use Shopware\Components\LogawareReflectionHelper;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\CustomerStream\CustomerStream as CustomerStreamEntity;
use Shopware\Models\CustomerStream\CustomerStreamRepositoryInterface;

class CustomerStream extends Resource
{
    private LogawareReflectionHelper $reflectionHelper;

    private CustomerNumberSearchInterface $customerNumberSearch;

    private CustomerStreamRepositoryInterface $streamRepository;

    private Connection $connection;

    private SearchIndexerInterface $searchIndexer;

    private StreamIndexerInterface $streamIndexer;

    private CustomerStreamCriteriaFactoryInterface $criteriaFactory;

    public function __construct(
        LogawareReflectionHelper $reflectionHelper,
        CustomerNumberSearchInterface $customerNumberSearch,
        CustomerStreamRepositoryInterface $streamRepository,
        ModelManager $manager,
        Connection $connection,
        SearchIndexerInterface $searchIndexer,
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

    /**
     * @param int|null $id
     * @param int      $offset
     * @param int|null $limit
     * @param string   $conditions
     * @param string   $sortings
     *
     * @return CustomerNumberSearchResult
     */
    public function getOne($id, $offset = 0, $limit = 50, $conditions = '', $sortings = '')
    {
        $this->checkPrivilege('read');

        $criteria = new Criteria();

        $parsedConditions = $this->getConditions($id, $conditions);

        foreach ($parsedConditions as $condition) {
            $criteria->addCondition($condition);
        }
        $decodedSortings = json_decode($sortings, true);
        if (!empty($decodedSortings)) {
            $unserializedSortings = $this->reflectionHelper->unserialize($decodedSortings, '');

            foreach ($unserializedSortings as $sorting) {
                if (!$sorting instanceof SortingInterface) {
                    continue;
                }
                $criteria->addSorting($sorting);
            }
        }

        $criteria->offset((int) $offset);
        $criteria->limit($limit);

        return $this->customerNumberSearch->search($criteria);
    }

    /**
     * @param int                                                                                     $offset
     * @param int                                                                                     $limit
     * @param array<string, string>|array<array{property: string, value: mixed, expression?: string}> $criteria
     * @param array<array{property: string, direction: string}>                                       $orderBy
     *
     * @return array{success: true, data: array<array<string, mixed>>, total: int}
     */
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

        /** @var Query<array<string, mixed>> $query */
        $query = $builder->getQuery();
        $query->setHydrationMode(self::HYDRATE_ARRAY);
        $paginator = $this->getManager()->createPaginator($query);
        $total = $paginator->count();
        $data = iterator_to_array($paginator);

        $ids = array_column($data, 'id');
        if (empty($ids)) {
            return ['success' => true, 'data' => [], 'total' => 0];
        }

        $counts = $this->streamRepository->fetchStreamsCustomerCount($ids);

        foreach ($data as &$row) {
            $id = (int) $row['id'];
            if (!\array_key_exists($id, $counts)) {
                $row['customer_count'] = 0;
                $row['newsletter_count'] = 0;
            } else {
                $row = array_merge($row, $counts[$id]);
            }

            $result = $this->updateFrozenState($id, $row['freezeUp'], $row['conditions']);
            if ($result) {
                $row['freezeUp'] = $result['freezeUp'];
                $row['static'] = $result['static'];
            }
        }

        return ['success' => true, 'data' => $data, 'total' => $total];
    }

    /**
     * @param bool $index
     *
     * @throws CustomValidationException
     *
     * @return CustomerStreamEntity
     */
    public function create(array $data, $index = false)
    {
        $this->checkPrivilege('save');

        $data = $this->prepareData($data);

        $stream = new CustomerStreamEntity();
        $stream->fromArray($data);

        $violations = $this->getManager()->validate($stream);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->validateStream($stream);

        $this->getManager()->persist($stream);
        $this->getManager()->flush($stream);

        if ($index) {
            $this->indexStream($stream);
        }

        if (\array_key_exists('customers', $data) && $stream->isStatic()) {
            $this->insertCustomers($data['customers'], $stream->getId());
        }

        return $stream;
    }

    /**
     * @param int  $id
     * @param bool $index
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     * @throws ValidationException
     *
     * @return CustomerStreamEntity
     */
    public function update($id, array $data, $index = false)
    {
        $this->checkPrivilege('save');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $stream = $this->getManager()->find(CustomerStreamEntity::class, $id);

        if (!$stream instanceof CustomerStreamEntity) {
            throw new NotFoundException(sprintf('Customer Stream by id %d not found', $id));
        }

        $data = $this->prepareData($data);

        $stream->fromArray($data);

        $violations = $this->getManager()->validate($stream);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->validateStream($stream);

        if (!$stream->isStatic() && $index) {
            $this->indexStream($stream);
        }

        if (\array_key_exists('customers', $data) && $stream->isStatic()) {
            $this->insertCustomers($data['customers'], $stream->getId());
        }

        $this->getManager()->flush($stream);

        return $stream;
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        $stream = $this->manager->find(CustomerStreamEntity::class, $id);
        if (!$stream instanceof CustomerStreamEntity) {
            throw new NotFoundException(sprintf('Customer Stream by id %d not found', $id));
        }

        $this->manager->remove($stream);
        $this->manager->flush($stream);

        $this->connection->executeStatement(
            'DELETE FROM s_customer_streams_mapping WHERE stream_id = :id',
            [':id' => $id]
        );
    }

    /**
     * @param int  $lastId
     * @param bool $full
     *
     * @return int[]
     */
    public function buildSearchIndex($lastId, $full)
    {
        $this->checkPrivilege('search_index');

        $ids = $this->streamRepository->fetchSearchIndexIds($lastId, $full);

        if (!empty($ids)) {
            $this->connection->executeStatement(
                'DELETE FROM s_customer_search_index WHERE id IN (:ids)',
                [':ids' => $ids],
                [':ids' => Connection::PARAM_INT_ARRAY]
            );
        }

        $this->searchIndexer->populate($ids);

        return $ids;
    }

    /**
     * @return void
     */
    public function cleanupIndexSearchIndex()
    {
        $this->searchIndexer->cleanupIndex();
    }

    /**
     * @param int|null $offset
     * @param int|null $limit
     *
     * @throws PrivilegeException
     *
     * @return void
     */
    public function indexStream(CustomerStreamEntity $stream, $offset = null, $limit = null)
    {
        $this->checkPrivilege('save');

        $result = $this->updateFrozenState($stream->getId(), $stream->getFreezeUp(), $stream->getConditions());
        if ($result) {
            $stream->setStatic($result['static']);
            $stream->setFreezeUp($result['freezeUp']);
        }

        if ($stream->getFreezeUp() !== null || $stream->isStatic()) {
            return;
        }

        $criteria = $this->criteriaFactory->createCriteria($stream->getId());

        $criteria->setFetchCount(false);
        $criteria->offset((int) $offset);

        if ($limit !== null) {
            $criteria->limit((int) $limit);
        }

        if ($criteria->getOffset() === 0) {
            $this->streamIndexer->clearStreamIndex($stream->getId());
        }

        $this->streamIndexer->populatePartial($stream->getId(), $criteria);
    }

    /**
     * Returns true if frozen state has changed
     *
     * @param int         $streamId
     * @param string|null $conditions
     *
     * @return array|null
     */
    public function updateFrozenState($streamId, ?DateTimeInterface $freezeUp, $conditions)
    {
        $now = new DateTime();
        if (!$freezeUp || $freezeUp >= $now) {
            return null;
        }

        $conditions = json_decode((string) $conditions, true);
        $params = [
            'id' => (int) $streamId,
            'freezeUp' => null,
            'static' => empty($conditions),
        ];

        $this->manager->getConnection()->executeStatement(
            'UPDATE s_customer_streams SET static = :static, freeze_up = :freezeUp WHERE id = :id',
            $params
        );

        return $params;
    }

    private function getConditions(?int $streamId, ?string $conditions = null): array
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
        if (!$stream instanceof CustomerStreamEntity) {
            return [];
        }

        if ($stream->isStatic() || $stream->getFreezeUp()) {
            return [new AssignedToStreamCondition($streamId)];
        }

        return $this->reflectionHelper->unserialize(
            json_decode($stream->getConditions() ?? '', true),
            'Serialization error in Customer Stream'
        );
    }

    private function insertCustomers(array $customerIds, int $streamId): void
    {
        $connection = $this->connection;

        $connection->transactional(function () use ($connection, $customerIds, $streamId) {
            $connection->executeStatement(
                'DELETE FROM s_customer_streams_mapping WHERE stream_id = :streamId',
                [':streamId' => $streamId]
            );

            $insert = $connection->prepare('INSERT INTO s_customer_streams_mapping (stream_id, customer_id) VALUES (:streamId, :customerId)');
            $customerIds = array_keys(array_flip($customerIds));

            foreach ($customerIds as $customerId) {
                $insert->executeStatement([
                    ':streamId' => $streamId,
                    ':customerId' => (int) $customerId,
                ]);
            }
        });
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function prepareData(array $data): array
    {
        $conditions = json_decode($data['conditions'] ?? '', true);
        if (empty($conditions)) {
            $data['conditions'] = null;
        }

        return $data;
    }

    /**
     * @throws CustomValidationException
     */
    private function validateStream(CustomerStreamEntity $stream): void
    {
        if (!$stream->isStatic()) {
            if (!$stream->getConditions()) {
                throw new CustomValidationException('A dynamic stream has to have at least one condition');
            }

            if ($stream->getFreezeUp()) {
                throw new CustomValidationException('A dynamic stream can not have a freezeUp time');
            }
        }
    }
}
