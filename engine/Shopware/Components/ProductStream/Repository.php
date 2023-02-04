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

namespace Shopware\Components\ProductStream;

use Doctrine\DBAL\Connection;
use PDO;
use Shopware\Bundle\SearchBundle\Condition\ProductIdCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Components\LogawareReflectionHelper;
use Shopware\Models\ProductStream\ProductStream;

class Repository implements RepositoryInterface
{
    private Connection $conn;

    private LogawareReflectionHelper $reflector;

    public function __construct(Connection $conn, LogawareReflectionHelper $reflector)
    {
        $this->conn = $conn;
        $this->reflector = $reflector;
    }

    public function prepareCriteria(Criteria $criteria, $productStreamId)
    {
        $productStream = $this->getStreamById($productStreamId);

        if ((int) $productStream['type'] === ProductStream::TYPE_CONDITION) {
            $this->prepareConditionStream($productStream, $criteria);

            return;
        }

        if ((int) $productStream['type'] === ProductStream::TYPE_SELECTION) {
            $this->prepareSelectionStream($productStream, $criteria);
        }
    }

    /**
     * @param array $serializedConditions
     *
     * @return object[]
     */
    public function unserialize($serializedConditions)
    {
        return $this->reflector->unserialize($serializedConditions, 'Serialization error in Product stream');
    }

    private function prepareConditionStream(array $productStream, Criteria $criteria): void
    {
        $this->assignConditions($productStream, $criteria);

        $sortings = $criteria->getSortings();
        if (empty($sortings)) {
            $this->assignSortings($productStream, $criteria);
        }
    }

    private function prepareSelectionStream(array $productStream, Criteria $criteria): void
    {
        $productIds = $this->getProductIds((int) $productStream['id']);
        $criteria->addBaseCondition(new ProductIdCondition($productIds));

        $sortings = $criteria->getSortings();
        if (empty($sortings)) {
            $this->assignSortings($productStream, $criteria);
        }
    }

    /**
     * @return int[]
     */
    private function getProductIds(int $productStreamId): array
    {
        $query = $this->conn->createQueryBuilder();
        $query->select('article_id')
            ->from('s_product_streams_selection')
            ->where('stream_id = :productStreamId')
            ->setParameter(':productStreamId', $productStreamId);

        return $query->execute()->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getStreamById(int $productStreamId): array
    {
        $stream = $this->conn->fetchAssociative(
            'SELECT streams.*, customSorting.sortings as customSortings
             FROM s_product_streams streams
             LEFT JOIN s_search_custom_sorting customSorting
                 ON customSorting.id = streams.sorting_id
             WHERE streams.id = :productStreamId
             LIMIT 1',
            ['productStreamId' => $productStreamId]
        );

        if (!\is_array($stream)) {
            return [];
        }

        return $stream;
    }

    private function assignSortings(array $productStream, Criteria $criteria): void
    {
        $sorting = $productStream['sorting'];
        if (!empty($productStream['customSortings'])) {
            $sorting = $productStream['customSortings'];
        }

        $serializedSortings = json_decode($sorting, true);

        /** @var SortingInterface[] $sortings */
        $sortings = $this->unserialize($serializedSortings);
        foreach ($sortings as $sorting) {
            $criteria->addSorting($sorting);
        }
    }

    private function assignConditions(array $productStream, Criteria $criteria): void
    {
        $serializedConditions = json_decode($productStream['conditions'], true);
        /** @var ConditionInterface[] $conditions */
        $conditions = $this->unserialize($serializedConditions);
        foreach ($conditions as $condition) {
            $criteria->addBaseCondition($condition);
        }
    }
}
