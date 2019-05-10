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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Gateway\CustomSortingGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\CustomListingHydrator;
use Shopware\Bundle\StoreFrontBundle\Struct\Search\CustomSorting;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class CustomSortingGateway implements CustomSortingGatewayInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var CustomListingHydrator
     */
    private $hydrator;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        CustomListingHydrator $hydrator,
        \Shopware_Components_Config $config
    ) {
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
        $this->hydrator = $hydrator;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $ids, ShopContextInterface $context)
    {
        $ids = array_keys(array_flip($ids));
        $query = $this->createQuery($context);
        $query->andWhere('customSorting.id IN (:ids)');
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $sortings = $this->hydrate(
            $query->execute()->fetchAll(\PDO::FETCH_ASSOC)
        );

        return $this->getAndSortElementsByIds($ids, $sortings);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortingsOfCategories(array $categoryIds, ShopContextInterface $context)
    {
        $mapping = $this->getCategoryMapping($categoryIds);

        /** @var int[] $ids */
        $ids = array_merge(...array_values($mapping));

        $sortings = $this->getList(
            $ids,
            $context
        );

        $categorySortings = [];

        /** @var int $categoryId */
        /** @var int[] $sortingIds */
        foreach ($mapping as $categoryId => $sortingIds) {
            $categorySortings[$categoryId] = $this->getAndSortElementsByIds(
                $sortingIds,
                $sortings
            );
        }

        return $categorySortings;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllCategorySortings(ShopContextInterface $context)
    {
        return $this->getList($this->getAllCategorySortingIds(), $context);
    }

    /**
     * Returns an array with all sorting ids which enabled for category listings
     *
     * @return int[]
     */
    private function getAllCategorySortingIds()
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('id');
        $query->from('s_search_custom_sorting', 'customSorting');
        $query->andWhere('customSorting.display_in_categories = 1');
        $query->addOrderBy('customSorting.position', 'ASC');
        $ids = $query->execute()->fetchAll(\PDO::FETCH_COLUMN);

        $default = $this->config->get('defaultListingSorting', 1);

        return array_unique(array_merge([$default], $ids));
    }

    /**
     * Returns the base query to select the custom sorting data.
     *
     * @return QueryBuilder
     */
    private function createQuery(ShopContextInterface $context)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select($this->fieldHelper->getCustomSortingFields());
        $query->from('s_search_custom_sorting', 'customSorting');
        $query->andWhere('customSorting.active = 1');
        $this->fieldHelper->addCustomSortingTranslation($query, $context);

        return $query;
    }

    /**
     * @return CustomSorting[]
     */
    private function hydrate(array $data)
    {
        $sortings = [];
        foreach ($data as $row) {
            $id = (int) $row['__customSorting_id'];
            $sortings[$id] = $this->hydrator->hydrateSorting($row);
        }

        return $sortings;
    }

    /**
     * @param int[]           $sortingIds
     * @param CustomSorting[] $sortings
     *
     * @return CustomSorting[] indexed by id
     */
    private function getAndSortElementsByIds(array $sortingIds, array $sortings)
    {
        $filtered = [];
        foreach ($sortingIds as $sortingId) {
            if (isset($sortings[$sortingId])) {
                $filtered[$sortingId] = $sortings[$sortingId];
            }
        }

        return $filtered;
    }

    /**
     * @param int[] $categoryIds
     *
     * @return string[] indexed by id
     */
    private function getCategoryMapping(array $categoryIds)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['id', 'sorting_ids'])
            ->from('s_categories', 'categories')
            ->where('categories.id IN (:ids)')
            ->setParameter(':ids', $categoryIds, Connection::PARAM_INT_ARRAY);

        $mapping = $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);

        $allSortingIds = [];
        $hasEmpty = count(array_filter($mapping)) !== count($mapping);
        if ($hasEmpty) {
            $allSortingIds = $this->getAllCategorySortingIds();
        }

        return array_map(
            function ($ids) use ($allSortingIds) {
                $ids = array_filter(explode('|', $ids));

                if (!empty($ids)) {
                    return $ids;
                }

                return $allSortingIds;
            },
            $mapping
        );
    }
}
