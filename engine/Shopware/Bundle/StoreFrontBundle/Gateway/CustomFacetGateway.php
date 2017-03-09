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

namespace Shopware\Bundle\StoreFrontBundle\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Gateway\Hydrator\CustomListingHydrator;
use Shopware\Bundle\StoreFrontBundle\Struct\Search\CustomFacet;
use Shopware\Bundle\StoreFrontBundle\Struct\TranslationContext;

class CustomFacetGateway
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
     * @param Connection            $connection
     * @param FieldHelper           $fieldHelper
     * @param CustomListingHydrator $hydrator
     */
    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        CustomListingHydrator $hydrator
    ) {
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
        $this->hydrator = $hydrator;
    }

    /**
     * @param int[]              $ids
     * @param TranslationContext $context
     *
     * @return CustomFacet[] indexed by id
     */
    public function getList(array $ids, TranslationContext $context)
    {
        $ids = array_keys(array_flip($ids));
        $query = $this->createQuery($context);
        $query->andWhere('customFacet.id IN (:ids)');
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $facets = $this->hydrate(
            $query->execute()->fetchAll(\PDO::FETCH_ASSOC)
        );

        return $this->getAndSortElementsByIds($ids, $facets);
    }

    /**
     * @param array              $categoryIds
     * @param TranslationContext $context
     *
     * @return array indexed by category id, each element contains a list of CustomFacet
     */
    public function getFacetsOfCategories(array $categoryIds, TranslationContext $context)
    {
        $mapping = $this->getCategoryMapping($categoryIds);

        $ids = array_merge(...array_values($mapping));

        if (empty($ids)) {
            return [];
        }
        $facets = $this->getList($ids, $context);

        $categoryFacets = [];
        foreach ($mapping as $categoryId => $facetIds) {
            $categoryFacets[$categoryId] = $this->getAndSortElementsByIds($facetIds, $facets);
        }

        return $categoryFacets;
    }

    /**
     * @param TranslationContext $context
     *
     * @return CustomFacet[]
     */
    public function getAllCategoryFacets(TranslationContext $context)
    {
        $query = $this->createQuery($context);
        $query->andWhere('customFacet.display_in_categories = 1');
        $query->orderBy('customFacet.position');

        return $this->hydrate(
            $query->execute()->fetchAll(\PDO::FETCH_ASSOC)
        );
    }

    /**
     * Returns an array with all facet ids which enabled for category listings
     *
     * @return int[]
     */
    private function getAllCategoryFacetIds()
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('id');
        $query->from('s_search_custom_facet', 'customFacet');
        $query->andWhere('customFacet.display_in_categories = 1');
        $query->addOrderBy('customFacet.position', 'ASC');

        return $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Returns the base query to select the custom facet data.
     *
     * @param TranslationContext $context
     *
     * @return QueryBuilder
     */
    private function createQuery(TranslationContext $context)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select($this->fieldHelper->getCustomFacetFields());
        $query->from('s_search_custom_facet', 'customFacet');
        $query->andWhere('customFacet.active = 1');
        $this->fieldHelper->addCustomFacetTranslation($query, $context);

        return $query;
    }

    /**
     * @param array $data
     *
     * @return CustomFacet[]
     */
    private function hydrate(array $data)
    {
        $facets = [];
        foreach ($data as $row) {
            $id = (int) $row['__customFacet_id'];
            $facets[$id] = $this->hydrator->hydrateFacet($row);
        }

        return $facets;
    }

    /**
     * @param int[]         $facetIds
     * @param CustomFacet[] $facets
     *
     * @return CustomFacet[] indexed by id
     */
    private function getAndSortElementsByIds(array $facetIds, array $facets)
    {
        $filtered = [];
        foreach ($facetIds as $facetId) {
            if (isset($facets[$facetId])) {
                $filtered[$facetId] = $facets[$facetId];
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
        $query->select(['id', 'facet_ids'])
            ->from('s_categories', 'categories')
            ->where('categories.id IN (:ids)')
            ->setParameter(':ids', $categoryIds, Connection::PARAM_INT_ARRAY);

        $mapping = $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);
        $allFacetIds = [];

        $hasEmpty = count(array_filter($mapping)) !== count($mapping);
        if ($hasEmpty) {
            $allFacetIds = $this->getAllCategoryFacetIds();
        }

        return array_map(
            function ($ids) use ($allFacetIds) {
                $ids = array_filter(explode('|', $ids));

                if (!empty($ids)) {
                    return $ids;
                }

                return $allFacetIds;
            },
            $mapping
        );
    }
}
