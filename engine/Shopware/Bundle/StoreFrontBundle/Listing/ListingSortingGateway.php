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

namespace Shopware\Bundle\StoreFrontBundle\Listing;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Common\FieldHelper;
use Shopware\Bundle\StoreFrontBundle\Context\TranslationContext;

class ListingSortingGateway
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Common\FieldHelper
     */
    private $fieldHelper;

    /**
     * @var ListingHydrator
     */
    private $hydrator;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @param Connection                                           $connection
     * @param \Shopware\Bundle\StoreFrontBundle\Common\FieldHelper $fieldHelper
     * @param ListingHydrator                                      $hydrator
     * @param \Shopware_Components_Config                          $config
     */
    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        ListingHydrator $hydrator,
        \Shopware_Components_Config $config
    ) {
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
        $this->hydrator = $hydrator;
        $this->config = $config;
    }

    /**
     * @param int[]              $ids
     * @param TranslationContext $context
     *
     * @return ListingSorting[] indexed by id, sorted by provided id array
     */
    public function getList(array $ids, TranslationContext $context)
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
     * @param int[]              $categoryIds
     * @param TranslationContext $context
     *
     * @return array[] indexed by category id, sorted by category mapping or position
     */
    public function getSortingsOfCategories(array $categoryIds, TranslationContext $context)
    {
        $mapping = $this->getCategoryMapping($categoryIds);

        $sortings = $this->getList(
            array_merge(...array_values($mapping)),
            $context
        );

        $categorySortings = [];
        foreach ($mapping as $categoryId => $sortingIds) {
            $categorySortings[$categoryId] = $this->getAndSortElementsByIds(
                $sortingIds,
                $sortings
            );
        }

        return $categorySortings;
    }

    /**
     * @param TranslationContext $context
     *
     * @return ListingSorting[]
     */
    public function getAllCategorySortings(TranslationContext $context)
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
     * @param TranslationContext $context
     *
     * @return QueryBuilder
     */
    private function createQuery(TranslationContext $context)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select($this->fieldHelper->getCustomSortingFields());
        $query->from('s_search_custom_sorting', 'customSorting');
        $query->andWhere('customSorting.active = 1');
        $this->fieldHelper->addCustomSortingTranslation($query, $context);

        return $query;
    }

    /**
     * @param array $data
     *
     * @return ListingSorting[]
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
     * @param int[]            $sortingIds
     * @param ListingSorting[] $sortings
     *
     * @return ListingSorting[] indexed by id
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
