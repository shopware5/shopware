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

namespace Shopware\Bundle\SearchBundleDBAL\SearchTerm;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\SearchBundleDBAL\KeywordFinderInterface;
use Shopware\Bundle\SearchBundleDBAL\SearchTermQueryBuilderInterface;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL\SearchTerm
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class SearchTermQueryBuilder implements SearchTermQueryBuilderInterface
{
    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var KeywordFinder
     */
    private $keywordFinder;

    /**
     * @var SearchIndexerInterface
     */
    private $searchIndexer;

    /**
     * @var TermHelperInterface
     */
    private $termHelper;

    /**
     * @param \Shopware_Components_Config $config
     * @param Connection $connection
     * @param KeywordFinderInterface $keywordFinder
     * @param SearchIndexerInterface $searchIndexer
     * @param TermHelperInterface $termHelper
     */
    public function __construct(
        \Shopware_Components_Config $config,
        Connection $connection,
        KeywordFinderInterface $keywordFinder,
        SearchIndexerInterface $searchIndexer,
        TermHelperInterface $termHelper
    ) {
        $this->config = $config;
        $this->connection = $connection;
        $this->keywordFinder = $keywordFinder;
        $this->searchIndexer = $searchIndexer;
        $this->termHelper = $termHelper;

        $this->searchIndexer->validate();
    }

    /**
     * @return string
     */
    private function getRelevanceSelection()
    {
        return 'sr.relevance
        + IF(a.topseller = 1, 50, 0)
        + IF(a.datum >= DATE_SUB(NOW(),INTERVAL 7 DAY), 25, 0)';
    }

    /**
     * Required table fields:
     *  - product_id : id of the product, used as join
     *
     * @param $term
     * @return QueryBuilder|null
     */
    public function buildQuery($term)
    {
        $keywords = $this->keywordFinder->getKeywordsOfTerm($term);

        if (empty($keywords)) {
            return null;
        }

        $tables = $this->getSearchTables();

        if (empty($tables)) {
            return null;
        }

        $query = $this->buildQueryFromKeywords($keywords, $tables);

        $this->addToleranceCondition($query);

        $query->select(
            [
                "a.id as product_id",
                "(" . $this->getRelevanceSelection() . ") as ranking"
            ]
        );

        $enableAndSearchLogic = $this->config->get('enableAndSearchLogic', false);
        if ($enableAndSearchLogic) {
            $this->addAndSearchLogic($query, $term, $keywords);
        }

        return $query;
    }

    /**
     * Generates a single query builder from the provided keywords array.
     *
     * @param Keyword[] $keywords
     * @param $tables
     * @return QueryBuilder
     */
    private function buildQueryFromKeywords($keywords, $tables)
    {
        $keywordSelection = [];
        foreach ($keywords as $match) {
            $keywordSelection[] = 'SELECT ' . $match->getRelevance() . ' as relevance, ' . $this->connection->quote($match->getTerm()) . ' as term, ' . $match->getId() . ' as keywordID';
        }
        $keywordSelection = implode("\n             UNION ALL ", $keywordSelection);

        $tablesSql = [];
        foreach ($tables as $table) {
            $query = $this->connection->createQueryBuilder();
            $alias = 'st' . $table['tableID'];

            $query->select(['MAX(sf.relevance * sm.relevance) as relevance', 'sm.keywordID']);
            $query->from('(' . $keywordSelection . ')', 'sm');
            $query->innerJoin('sm', 's_search_index', 'si', 'sm.keywordID = si.keywordID');
            $query->innerJoin('si', 's_search_fields', 'sf', 'si.fieldID = sf.id AND sf.relevance != 0 AND sf.tableID = ' . $table['tableID']);

            $query->groupBy('articleID')
                ->addGroupBy('sm.term')
                ->addGroupBy('sf.id');

            if (!empty($table['referenz_table'])) {
                $query->addSelect($alias . '.articleID as articleID');
                $query->innerJoin('si', $table['referenz_table'], $alias, 'si.elementID = ' . $alias . '.' . $table['foreign_key']);
            } elseif (!empty($table['foreign_key'])) {
                $query->addSelect($alias . '.id as articleID');
                $query->innerJoin('si', 's_articles', $alias, 'si.elementID = ' . $alias . '.' . $table['foreign_key']);
            } else {
                $query->addSelect('si.elementID as articleID');
            }

            $tablesSql[] = $query->getSQL();
        }

        $tablesSql = "\n" . implode("\n     UNION ALL\n", $tablesSql);

        $subQuery = $this->connection->createQueryBuilder();
        $subQuery->select(['srd.articleID', 'SUM(srd.relevance) as relevance']);
        $subQuery->from("(" . $tablesSql . ')', 'srd')
            ->groupBy('srd.articleID')
            ->setMaxResults(5000);

        $query = $this->connection->createQueryBuilder();
        $query->from("(" . $subQuery->getSQL() . ')', 'sr')
            ->innerJoin('sr', 's_articles', 'a', 'a.id = sr.articleID');

        return $query;
    }

    /**
     * Calculates the search tolerance and adds an where condition
     * to the query.
     *
     * @param QueryBuilder $query
     */
    private function addToleranceCondition(QueryBuilder $query)
    {
        $query->select("MAX(" . $this->getRelevanceSelection() . ")");

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $highestRanking = $statement->fetch(\PDO::FETCH_COLUMN);

        //calculates the tolerance limit
        $distance = $this->config->get('fuzzySearchMinDistancenTop', 20);

        if ($highestRanking && $distance) {
            $toleranceLimit = $highestRanking / 100 * $distance;

            $query->andWhere('(' . $this->getRelevanceSelection() . ') > ' . $toleranceLimit);
        }
    }

    /**
     * Get all tables and columns that might be involved in this search request as an array
     *
     * @return array
     */
    private function getSearchTables()
    {
        return $this->connection->fetchAll("
            SELECT STRAIGHT_JOIN
                st.id AS tableID,
                st.table,
                st.where,
                st.referenz_table, st.foreign_key,
                GROUP_CONCAT(sf.id SEPARATOR ', ') AS fieldIDs,
                GROUP_CONCAT(sf.field SEPARATOR ', ') AS `fields`
            FROM s_search_fields sf FORCE INDEX (tableID)
                INNER JOIN s_search_tables st
                    ON st.id = sf.tableID
                    AND sf.relevance != 0
            GROUP BY sf.tableID
       ");
    }

    /**
     * checks if the given result set matches all search terms
     *
     * @param QueryBuilder $query
     * @param string $term
     * @param Keyword[] $keywords
     */
    private function addAndSearchLogic($query, $term, $keywords)
    {
        $searchTerms = $this->termHelper->splitTerm($term);

        $searchTermMatchQueries = $this->createSearchTermMatchQueries($keywords, $searchTerms);

        $totalSearchTermMatchesQuery = $this->connection->createQueryBuilder();

        $totalSearchTermMatchesQuery->select('sum(matches)')
            ->from('(' . $searchTermMatchQueries . ')', 'termMatches')
            ->where('termMatches.elementID = product_id');

        $query->addSelect('(' . $totalSearchTermMatchesQuery->getSQL() . ') AS searchTermMatches');
        $query->having('searchTermMatches >= ' . count($searchTerms));
    }

    /**
     * creates the search term match query. makes sure that, for each search term at least one keyword matches
     *
     * @param Keyword[] $keywords
     * @param string[] $searchTerms
     * @return string
     */
    private function createSearchTermMatchQueries($keywords, $searchTerms)
    {
        $searchTermMatchQueries = [];
        foreach ($searchTerms as $searchTerm) {
            $searchKeywordIds = [];
            foreach ($keywords as $keyword) {
                if ($keyword->getTerm() == $searchTerm) {
                    $searchKeywordIds[] = $keyword->getId();
                }
            }

            $searchTermMatchQueries[] = $this->createSearchTermMatchQuery($searchKeywordIds);
        }

        $searchTermMatchQueries = "\n" . implode("\n     UNION ALL\n", $searchTermMatchQueries);

        return $searchTermMatchQueries;
    }

    /**
     * checks if at least one keyword matches to a search term
     *
     * @param int[] $searchKeywordIds
     * @return string
     */
    private function createSearchTermMatchQuery($searchKeywordIds)
    {
        //if there is no keyword for a search term we need this little hack to prevent an empty IN condition
        if (empty($searchKeywordIds)) {
            $searchKeywordIds[] = -1;
        }

        $searchTermMatchQuery = $this->connection->createQueryBuilder();
        $searchTermMatchQuery->select('DISTINCT 1 AS matches, elementID')
            ->from('s_search_index', 'search_index')
            ->innerJoin('search_index', 's_search_keywords', 'search_keywords', 'search_keywords.id = search_index.keywordID')
            ->where("search_keywords.id IN(" . implode(',', $searchKeywordIds) . ")");

        return $searchTermMatchQuery->getSQL();
    }
}
