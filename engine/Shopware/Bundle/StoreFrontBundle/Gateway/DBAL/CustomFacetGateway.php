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
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\CustomFacetGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\CustomListingHydrator;
use Shopware\Bundle\StoreFrontBundle\Struct\Search\CustomFacet;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class CustomFacetGateway implements CustomFacetGatewayInterface
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
     * {@inheritdoc}
     */
    public function getList(array $ids, ShopContextInterface $context)
    {
        $ids = array_keys(array_flip($ids));
        $query = $this->createQuery($context);
        $query->andWhere('customFacet.id IN (:ids)');
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $facets = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

        $facets = $this->hydrate($facets);

        return $this->getAndSortElementsByIds($ids, $facets);
    }

    /**
     * {@inheritdoc}
     */
    public function getFacetsOfCategories(array $categoryIds, ShopContextInterface $context)
    {
        $mapping = $this->getCategoryMapping($categoryIds);

        /** @var int[] $ids */
        $ids = array_merge(...array_values($mapping));

        if (empty($ids)) {
            return [];
        }

        $facets = $this->getList($ids, $context);

        $categoryFacets = [];

        /** @var int $categoryId */
        /** @var int[] $facetIds */
        foreach ($mapping as $categoryId => $facetIds) {
            $categoryFacets[$categoryId] = $this->getAndSortElementsByIds($facetIds, $facets);
        }

        return $categoryFacets;
    }

    /**
     * @return CustomFacet[] indexed by id
     */
    public function getAllCategoryFacets(ShopContextInterface $context)
    {
        $query = $this->createQuery($context);
        $query->andWhere('customFacet.display_in_categories = 1');
        $query->orderBy('customFacet.position');

        return $this->hydrate(
            $query->execute()->fetchAll(PDO::FETCH_ASSOC)
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

        return $query->execute()->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Returns the base query to select the custom facet data.
     *
     * @return QueryBuilder
     */
    private function createQuery(ShopContextInterface $context)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select($this->fieldHelper->getCustomFacetFields());
        $query->from('s_search_custom_facet', 'customFacet');
        $query->andWhere('customFacet.active = 1');
        $this->fieldHelper->addCustomFacetTranslation($query, $context);

        return $query;
    }

    /**
     * @return CustomFacet[]
     */
    private function hydrate(array $data)
    {
        $streams = $this->fetchAssignedStreams($data);

        $facets = [];
        foreach ($data as $row) {
            $id = (int) $row['__customFacet_id'];

            $hydratedFacet = $this->hydrator->hydrateFacet($row, $streams);

            if ($hydratedFacet === null) {
                continue;
            }
            $facets[$id] = $hydratedFacet;
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

        $mapping = $query->execute()->fetchAll(PDO::FETCH_KEY_PAIR);
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

    /**
     * @return array
     */
    private function fetchAssignedStreams(array $data)
    {
        $streamIds = [];
        foreach ($data as $facet) {
            $config = json_decode($facet['__customFacet_facet'], true);
            $config = array_shift($config);
            if (array_key_exists('streamId', $config)) {
                $streamIds[] = $config['streamId'];
            }
        }

        if (empty($streamIds)) {
            return [];
        }

        $query = $this->connection->createQueryBuilder();
        $query->select([
            'streams.id',
            'streams.conditions',
            'GROUP_CONCAT(variant.ordernumber) as numbers',
        ]);
        $query->from('s_product_streams', 'streams');
        $query->leftJoin('streams', 's_product_streams_selection', 'articles', 'articles.stream_id = streams.id');
        $query->leftJoin('articles', 's_articles_details', 'variant', 'variant.articleID = articles.article_id AND variant.kind = 1');
        $query->where('streams.id IN (:ids)');
        $query->groupBy('streams.id');
        $query->setParameter(':ids', $streamIds, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);
    }
}
