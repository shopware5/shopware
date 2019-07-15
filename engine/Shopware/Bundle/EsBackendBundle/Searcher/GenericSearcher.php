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

namespace Shopware\Bundle\EsBackendBundle\Searcher;

use Elasticsearch\Client;
use ONGR\ElasticsearchDSL\BuilderInterface;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\WildcardQuery;
use ONGR\ElasticsearchDSL\Search;
use ONGR\ElasticsearchDSL\Sort\FieldSort;
use Shopware\Bundle\AttributeBundle\Repository\SearchCriteria;
use Shopware\Bundle\AttributeBundle\Repository\Searcher\SearcherInterface;
use Shopware\Bundle\AttributeBundle\Repository\Searcher\SearcherResult;
use Shopware\Bundle\EsBackendBundle\IndexFactoryInterface;
use Shopware\Bundle\EsBackendBundle\SearchQueryBuilder;
use Shopware\Bundle\ESIndexingBundle\EsSearch;

class GenericSearcher implements SearcherInterface
{
    /**
     * @var SearcherInterface
     */
    protected $decorated;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $domainName;

    /**
     * @var SearchQueryBuilder
     */
    protected $searchQueryBuilder;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var string
     */
    private $esVersion;

    /**
     * @var IndexFactoryInterface
     */
    private $indexFactory;

    public function __construct(
        Client $client,
        SearcherInterface $decorated,
        SearchQueryBuilder $searchQueryBuilder,
        string $domainName,
        bool $enabled,
        string $esVersion,
        IndexFactoryInterface $indexFactory
    ) {
        $this->decorated = $decorated;
        $this->client = $client;
        $this->searchQueryBuilder = $searchQueryBuilder;
        $this->domainName = $domainName;
        $this->enabled = $enabled;
        $this->esVersion = $esVersion;
        $this->indexFactory = $indexFactory;
    }

    /**
     * @return SearcherResult
     */
    public function search(SearchCriteria $criteria)
    {
        if (!$this->enabled) {
            return $this->decorated->search($criteria);
        }

        $search = $this->buildSearchObject($criteria);

        $result = $this->fetch($search);

        return $this->iterate($result);
    }

    /**
     * @return EsSearch
     */
    protected function buildSearchObject(SearchCriteria $criteria)
    {
        $search = new EsSearch();

        if ($criteria->offset) {
            $search->setFrom($criteria->offset);
        }
        if ($criteria->limit) {
            $search->setSize($criteria->limit);
        }

        if ($criteria->term) {
            $search->addQuery($this->buildSearchQuery($criteria));
        }

        if (!empty($criteria->conditions)) {
            $this->addFilters($search, $criteria);
        }
        if (!empty($criteria->sortings)) {
            $this->addSortings($criteria, $search);
        }

        return $search;
    }

    protected function fetch(Search $search)
    {
        $arguments = [
            'index' => $this->indexFactory->createIndexConfiguration($this->domainName)->getAlias(),
            'type' => $this->domainName,
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

        return $this->client->search($arguments);
    }

    /**
     * @return SearcherResult
     */
    protected function iterate(array $result)
    {
        $hits = $result['hits']['hits'];

        $sources = array_column($hits, '_source');

        $ids = array_column($sources, $this->getIdentifierColumn());

        return new SearcherResult($ids, (int) $result['hits']['total']);
    }

    /**
     * @return string
     */
    protected function getIdentifierColumn()
    {
        return 'id';
    }

    /**
     * @return BuilderInterface
     */
    protected function buildSearchQuery(SearchCriteria $criteria)
    {
        return $this->searchQueryBuilder->buildQuery($this->getSearchFields(), $criteria->term);
    }

    /**
     * @return array
     */
    protected function getSearchFields()
    {
        return ['swag_all' => 1];
    }

    protected function addSortings(SearchCriteria $criteria, Search $search)
    {
        foreach ($criteria->sortings as $sorting) {
            $search->addSort(
                new FieldSort($sorting['property'], strtolower($sorting['direction']))
            );
        }
    }

    private function addFilters(Search $search, SearchCriteria $criteria)
    {
        $query = new BoolQuery();
        foreach ($criteria->conditions as $condition) {
            if ($condition['property'] === 'search') {
                $search->addQuery(
                    $this->searchQueryBuilder->buildQuery($this->getSearchFields(), $condition['value'])
                );
                continue;
            }

            $expression = $condition['expression'] ?: '=';

            switch (strtolower($expression)) {
                case 'in':
                    $value = $condition['value'];
                    if (!is_array($value)) {
                        $value = [$value];
                    }
                    $query->add(
                        new TermsQuery($condition['property'], $value),
                        BoolQuery::MUST
                    );
                    break;

                case '=':
                    $query->add(
                        new TermQuery($condition['property'], $condition['value']),
                        BoolQuery::MUST
                    );
                    break;

                case '>=':
                    $query->add(
                        new RangeQuery($condition['property'], [RangeQuery::GTE => $condition['value']])
                    );
                    break;

                case '<=':
                    $query->add(
                        new RangeQuery($condition['property'], [RangeQuery::LTE => $condition['value']])
                    );
                    break;

                case '<':
                    $query->add(
                        new RangeQuery($condition['property'], [RangeQuery::LT => $condition['value']])
                    );
                    break;

                case '>':
                    $query->add(
                        new RangeQuery($condition['property'], [RangeQuery::GT => $condition['value']])
                    );
                    break;

                case 'like':
                    $value = strtolower($condition['value']);
                    $query->add(
                        new WildcardQuery($condition['property'], '*' . $value . '*'),
                        BoolQuery::MUST
                    );
                    break;
            }
        }

        if (empty($query->getQueries())) {
            return;
        }

        $search->addQuery($query);
    }
}
