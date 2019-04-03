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

namespace Shopware\Bundle\SearchBundleES\FacetHandler;

use Doctrine\DBAL\Connection;
use Elasticsearch\Client;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\IdsQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Search;
use ONGR\ElasticsearchDSL\Sort\FieldSort;
use Shopware\Bundle\ESIndexingBundle\EsSearch;
use Shopware\Bundle\ESIndexingBundle\IndexFactoryInterface;
use Shopware\Bundle\ESIndexingBundle\Property\PropertyMapping;
use Shopware\Bundle\SearchBundle\Condition\PropertyCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\Facet\PropertyFacet;
use Shopware\Bundle\SearchBundle\FacetResult\FacetResultGroup;
use Shopware\Bundle\SearchBundle\FacetResult\MediaListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\MediaListItem;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\SearchBundleES\ResultHydratorInterface;
use Shopware\Bundle\SearchBundleES\StructHydrator;
use Shopware\Bundle\StoreFrontBundle\Struct\Property\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Property\Option;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;

class PropertyFacetHandler implements HandlerInterface, ResultHydratorInterface
{
    const AGGREGATION_SIZE = 5000;

    /**
     * @var QueryAliasMapper
     */
    private $queryAliasMapper;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var StructHydrator
     */
    private $hydrator;

    /**
     * @var IndexFactoryInterface
     */
    private $indexFactory;

    /**
     * @var string
     */
    private $esVersion;

    public function __construct(
        QueryAliasMapper $queryAliasMapper,
        Client $client,
        Connection $connection,
        StructHydrator $hydrator,
        IndexFactoryInterface $indexFactory,
        string $esVersion
    ) {
        $this->queryAliasMapper = $queryAliasMapper;
        $this->client = $client;
        $this->connection = $connection;
        $this->hydrator = $hydrator;
        $this->indexFactory = $indexFactory;
        $this->esVersion = $esVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return $criteriaPart instanceof PropertyFacet;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        $aggregation = new TermsAggregation('properties');
        $aggregation->setField('properties.id');
        $aggregation->addParameter('size', self::AGGREGATION_SIZE);
        $search->addAggregation($aggregation);
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(
        array $elasticResult,
        ProductNumberSearchResult $result,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        if (!isset($elasticResult['aggregations'])) {
            return;
        }
        if (!isset($elasticResult['aggregations']['properties'])) {
            return;
        }

        $data = $elasticResult['aggregations']['properties']['buckets'];
        $ids = array_column($data, 'key');

        if (empty($ids)) {
            return;
        }

        $groupIds = $this->getGroupIds($ids);

        $search = new EsSearch();
        $search->addQuery(new IdsQuery($groupIds), BoolQuery::FILTER);
        $search->addQuery(new TermQuery('filterable', true), BoolQuery::FILTER);
        $search->addSort(new FieldSort('name', 'asc'));
        $search->setFrom(0);
        $search->setSize(self::AGGREGATION_SIZE);

        $index = $this->indexFactory->createShopIndex($context->getShop(), PropertyMapping::TYPE);

        $arguments = [
            'index' => $index->getName(),
            'type' => PropertyMapping::TYPE,
            'body' => $search->toArray(),
        ];

        if (version_compare($this->esVersion, '7', '>=')) {
            $arguments = array_merge(
                $arguments,
                [
                    'rest_total_hits_as_int' => true,
                    'track_total_hits' => true,
                ]
            );
        }

        $data = $this->client->search(
            $arguments
        );

        $data = $data['hits']['hits'];

        $properties = $this->hydrateProperties($data, $ids);
        $actives = $this->getFilteredValues($criteria);
        $criteriaPart = $this->createCollectionResult($properties, $actives);
        $result->addFacet($criteriaPart);
    }

    /**
     * @param array[] $data
     * @param int[]   $optionIds
     *
     * @return Group[]
     */
    private function hydrateProperties($data, $optionIds)
    {
        $groups = [];
        foreach ($data as $row) {
            $group = $this->hydrator->createPropertyGroup($row['_source']);

            $options = array_filter($group->getOptions(), function (Option $option) use ($optionIds) {
                return in_array($option->getId(), $optionIds);
            });

            usort($options, function (Option $a, Option $b) {
                if ($a->getPosition() !== $b->getPosition()) {
                    return $a->getPosition() > $b->getPosition();
                }

                return $a->getName() > $b->getName();
            });

            $group->setOptions($options);
            $groups[] = $group;
        }

        return $groups;
    }

    /**
     * @param int[] $optionIds
     *
     * @return int[]
     */
    private function getGroupIds($optionIds)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('DISTINCT optionID')
            ->from('s_filter_values', 'options')
            ->where('options.id IN (:ids)')
            ->setParameter(':ids', $optionIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @param Group[] $groups
     * @param int[]   $actives
     *
     * @return FacetResultGroup
     */
    private function createCollectionResult(array $groups, $actives)
    {
        $results = [];

        if (!$fieldName = $this->queryAliasMapper->getShortAlias('sFilterProperties')) {
            $fieldName = 'sFilterProperties';
        }

        foreach ($groups as $group) {
            $items = [];
            $useMedia = false;
            $isActive = false;

            foreach ($group->getOptions() as $option) {
                $listItem = new MediaListItem(
                    $option->getId(),
                    $option->getName(),
                    in_array($option->getId(), $actives),
                    $option->getMedia(),
                    $option->getAttributes()
                );

                $isActive = ($isActive || $listItem->isActive());
                $useMedia = ($useMedia || $listItem->getMedia() !== null);
                $items[] = $listItem;
            }

            if ($useMedia) {
                $results[] = new MediaListFacetResult(
                    'property',
                    $isActive,
                    $group->getName(),
                    $items,
                    $fieldName,
                    $group->getAttributes()
                );
            } else {
                $results[] = new ValueListFacetResult(
                    'property',
                    $isActive,
                    $group->getName(),
                    $items,
                    $fieldName,
                    $group->getAttributes()
                );
            }
        }

        return new FacetResultGroup($results, null, 'property');
    }

    /**
     * @return array
     */
    private function getFilteredValues(Criteria $criteria)
    {
        $values = [];
        foreach ($criteria->getConditions() as $condition) {
            if ($condition instanceof PropertyCondition) {
                $values = array_merge($values, $condition->getValueIds());
            }
        }

        return $values;
    }
}
