<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

/**
 * Shopware standard search adapter
 *
 * @category  Shopware
 * @package   Shopware\Components\Search\Adapter
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Components_Search_Adapter_Default extends Shopware_Components_Search_Adapter_Abstract
{

    /**
     * @var Zend_Cache_Core
     */
    protected $cache;
    /**
     * The shopware database adapter.
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $database;

    /**
     * Factor for relevance if term and keyword are equal
     * @default 100
     * @var int
     */
    protected $configSearchExactMatchFactor = 100;

    /**
     * Factor for relevance if term could be find as part of keyword
     * @default 5
     * @var int
     */
    protected $configSearchMatchFactor = 5;

    /**
     * Factor if term and keyword are nearly equal
     * @default 50
     * @var int
     */
    protected $configSearchPatternMatchFactor = 50;

    /**
     * Factor if term is part of keyword
     * @default 25
     * @var int
     */
    protected $configSearchPartNameDistance = 25;

    /**
     *
     * Results that have relevance smaller then configSearchMinDistanceOnTop
     * will be cut from search results
     * @var int
     * @default 20
     */
    protected $configSearchMinDistanceOnTop = 20;

    /**
     * List of available price filters from search configuration
     *
     * @var array
     * @default 5|10|20|50|100|300|600|1000|1500|2500|3500|5000
     */
    protected $configSearchPriceFilter;

    /**
     * Items per Page selector values
     * @var string
     * @default 12|24|36|48
     */
    protected $configSearchSelectPerPage;

    /**
     * Default value for items per page
     * @var string
     * @default 12
     */
    protected $configSearchResultsPerPage;

    /**
     * Interval to update the search index
     * @var int
     * @default 86400
     */
    protected $configSearchCache;

    /**
     * @var string
     * @default ab,die,der,und,in,zu,den,das,nicht,von,sie,ist,des,sich,mit,dem,dass,er,es,ein,ich,auf,so,eine,auch,als,an,nach,wie,im,fÃ¼r,einen,um,werden,mehr,zum,aus,ihrem,style,oder,neue,spieler,kÃ¶nnen,wird,sind,ihre,einem,of,du,sind,einer,Ã¼ber,alle,neuen,bei,durch,kann,hat,nur,noch,zur,gegen,bis,aber,haben,vor,seine,ihren,jetzt,ihr,dir,etc,bzw,nach,deine,the,warum,machen,0,sowie,am
     */
    protected $configSearchBadWords;

    /**
     * Max keywords that should be considered in search
     * @var int
     */
    protected $configSearchMaxKeywordsPerSearch = 5;

    /**
     * Price filters to display in frontend
     * @var array
     */
    public $configPriceFilter = array(
        1 => array("start" => 0, "end" => 5),
        2 => array("start" => 5, "end" => 10),
        3 => array("start" => 10, "end" => 20),
        4 => array("start" => 20, "end" => 50),
        5 => array("start" => 50, "end" => 100),
        6 => array("start" => 100, "end" => 300),
        7 => array("start" => 300, "end" => 600),
        8 => array("start" => 600, "end" => 1000),
        9 => array("start" => 1000, "end" => 1500),
        10 => array("start" => 1500, "end" => 2500),
        11 => array("start" => 2500, "end" => 3500),
        12 => array("start" => 3500, "end" => 5000)
    );

    /**
     * List of all terms that occur in search request
     * @var array
     */
    protected $searchTerms = array();

    /**
     * List of all matching keywords determined by search terms
     * @var array
     */
    protected $matchingKeywords = array();


    /**
     * Id of shop main category to limit search results to
     * @var int
     */
    protected $requestRestrictSearchResultsToCategory;

    /**
     * Array with active search filters
     *  supplier => Id of supplier to limit search results to
     *  category => Id of category to limit search results to
     *  price =>
     * @var array
     */
    protected $requestFilter;

    /**
     * Numeric key to identify how search results should be sorted
     * @var int
     */
    protected $requestSortSearchResultsBy;

    /**
     * Current page of search results
     * @var int
     */
    protected $requestCurrentPage;

    /**
     * How many results should be displayed on each page
     * @var int
     */
    protected $requestResultsPerPage;

    /**
     * Id of main category of current shop object
     * @var int
     */
    protected $requestShopLanguageId;

    /**
     * Has the current shop object translations
     * @var boolean
     */
    protected $requestShopHasTranslations;

    /**
     * Zend Currency object
     * @var Zend_Currency
     */
    protected $requestShopCurrency;

    /**
     * Key (EK for example) of current active customer group
     * @var string
     */
    protected $requestShopCustomerGroup;

    /**
     * Percent discount that might by configured for customer group
     * @var float
     */
    protected $requestShopCustomerGroupDiscount;

    /**
     * Mode of customer group
     * false = has own prices
     * true = has only a percent debit set
     * @var boolean
     */
    protected $requestShopCustomerGroupMode;

    /**
     * Calculate with tax for the current customer group
     * @var boolean
     */
    protected $requestShopCustomerGroupTax;

    /**
     * Factor to convert default prices from database to another currency
     * @var float
     */
    protected $requestShopCurrencyFactor;

    /**
     * Id of current active customer group
     * @var int
     */
    protected $requestShopCustomerGroupId;

    /**
     * Is the current request is from suggest search
     * @var boolean
     */
    protected $requestSuggestSearch;


    /**
     * @var Shopware_Components_Search_Result_Default
     */
    protected $result;


    /**
     * Class constructor - expects reference to database object and configuration array
     * @param Zend_Db_Adapter_Abstract $database
     * @param array $configuration -> s_core_config
     */
    public function __construct(Zend_Db_Adapter_Abstract $database, Zend_Cache_Core $cache, Shopware_Components_Search_Result_Abstract $result, $configuration)
    {
        $this->database = $database;
        $this->cache = $cache;

        $this->result = $result;

        // todo@all Change to new configuration handler in next phase
        $this->initConfigurationDeprecated($configuration);
        // Do cache validation
        $this->validateCache($configuration);
    }

    /**
     * Get search result object
     * @return Shopware_Components_Search_Result_Abstract|Shopware_Components_Search_Result_Default
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Helper method to assign configuration values from database to local class
     * properties
     * @deprecated
     * @param array $oldConfiguration
     */
    protected function initConfigurationDeprecated($oldConfiguration)
    {
        $configurationItems = array(
            "sFUZZYSEARCHEXACTMATCHFACTOR" => 'configSearchExactMatchFactor',
            "sFUZZYSEARCHMATCHFACTOR" => 'configSearchMatchFactor',
            "sFUZZYSEARCHPATTERNMATCHFACTOR" => 'configSearchPatternMatchFactor',
            "sFUZZYSEARCHPARTNAMEDISTANCEN" => 'configSearchPartNameDistance',
            "sFUZZYSEARCHMINDISTANCENTOP" => 'configSearchMinDistanceOnTop',
            "sFUZZYSEARCHPRICEFILTER" => 'configSearchPriceFilter',
            "sFUZZYSEARCHSELECTPERPAGE" => 'configSearchSelectPerPage',
            "sFUZZYSEARCHRESULTSPERPAGE" => 'configSearchResultsPerPage',
            "sCACHESEARCH" => 'configSearchCache',
            "sBADWORDS" => 'configSearchBadWords'
        );

        foreach ($configurationItems as $arrayKeyName => $classPropertyName) {
            if (isset($oldConfiguration[$arrayKeyName])) {
                $this->$classPropertyName = $oldConfiguration[$arrayKeyName];
            }
        }
        $this->initConfigurationDeprecatedPriceFilter($oldConfiguration);
    }

    /**
     * Get price ranges
     * @return array
     */
    public function getPriceRanges()
    {
        if (empty($this->configSearchPriceFilter)) {
            return $this->configPriceFilter;
        }
        return $this->configSearchPriceFilter;
    }

    /**
     * Initiate local properties pricefilter and selectperpage with values from database
     * @deprecated
     * @param $oldConfiguration
     */
    protected function initConfigurationDeprecatedPriceFilter($oldConfiguration)
    {
        if (!empty($oldConfiguration["sFUZZYSEARCHSELECTPERPAGE"])) {
            $this->configSearchSelectPerPage = preg_split('/[^0-9]/', (string) $oldConfiguration["sFUZZYSEARCHSELECTPERPAGE"], -1, PREG_SPLIT_NO_EMPTY);
        }

        if (!empty($oldConfiguration["sFUZZYSEARCHPRICEFILTER"])) {
            $sPriceFilter = preg_split('/[^0-9]/', (string) $oldConfiguration["sFUZZYSEARCHPRICEFILTER"], -1, PREG_SPLIT_NO_EMPTY);
            $tmp = array();
            $last = 0;
            foreach ($sPriceFilter as $key => $price) {
                $tmp[$key + 1] = array('start' => $last, 'end' => $price);
                $last = $price;
            }
            $this->configSearchPriceFilter = $tmp;
        }
    }

    /**
     * Check if search index is valid anymore and rebuild if necessary
     * @param array $configuration
     */
    public function validateCache($configuration = array(), $force = false)
    {
        $interval = (empty($this->configSearchCache) || $this->configSearchCache < 360) ? 86400 : (int) $this->configSearchCache;
        $sql = '
            SELECT NOW() as current, cf.value as last,
            (SELECT 1 FROM s_search_index LIMIT 1) as not_force
            FROM s_core_config_elements ce, s_core_config_values cf
            WHERE ce.name = \'fuzzysearchlastupdate\'
            AND cf.element_id = ce.id AND cf.shop_id = 1
        ';
        $result = $this->database->fetchRow($sql);
        $last = !empty($result['last']) ? unserialize($result['last']) : null;

        $strategy = Shopware()->Config()->get('searchRefreshStrategy', 3);

        //search index refresh strategy is configured for "live refresh"?
        if ($strategy !== 3) {
            return;
        }

        if (empty($last) || empty($result['not_force']) || strtotime($last) < strtotime($result['current']) - $interval) {
            $this->buildSearchIndex();
        }
    }

    /**
     * Set search terms
     * @param array $terms
     */
    public function setTerms(array $terms)
    {
        $this->searchTerms = $terms;
    }

    /**
     * Get search terms
     * @return array
     */
    public function getTerms()
    {
        return $this->searchTerms;
    }

    /**
     * For a certain term get matching keywords from keyword index
     * @param string $term
     * @return array
     * <code>
     *  $results[] = array("relevance"=>$relevance,"term"=>$term,"keywordID"=>$keywordID,"keyword"=>$keyword);
     * </code>
     */
    public function searchMatchingKeywords($term)
    {

        $id = 'Shopware_Modules_Search_' . $term;
        $cache = $this->cache;

        if ($cache->test($id)) {
            return $cache->load($id);
        }

        $results = array();

        $sql = '
            SELECT `id` , `keyword`
            FROM `s_search_keywords`
            WHERE keyword LIKE CONCAT(\'%\',?,\'%\')
            OR keyword LIKE CONCAT(LEFT(?,2),\'%\')
        ';
        $result = $this->database->query($sql, array($term, $term));

        while ($keyword = $result->fetch()) {
            $keywordID = $keyword['id'];
            $keyword = $keyword['keyword'];

            if (strlen($term) < strlen($keyword)) {
                $term1 = $keyword;
                $term2 = $term;
            } else {
                $term2 = $keyword;
                $term1 = $term;
            }

            $relevance = 0;

            if ($term1 === $term2) { // Terms are similar
                $relevance = $this->configSearchExactMatchFactor;
            } elseif (strpos($term1, $term2) !== false) { // Check for sub term matching
                if (strlen($term1) < 4)
                    $relevance = $this->configSearchMatchFactor;
                elseif (strlen($term1) - strlen($term2) <= 1) //ipod === ipods
                    $relevance = $this->configSearchExactMatchFactor;
                elseif ((round(strlen($term2) / strlen($term1), 2) * 100) >= $this->configSearchPartNameDistance) //digital == digi
                    $relevance = $this->configSearchPatternMatchFactor;
            }

            if (!empty($relevance)) {
                $results[] = array("relevance" => $relevance, "term" => $term, "keywordID" => $keywordID, "keyword" => $keyword);
            }
        }

        $cache->save($results, $id, array('Shopware_Modules_Search'), $this->configSearchCache);

        return $results;
    }

    /**
     * Set list with all matching keywords
     * @param array $keywords
     */
    public function setMatchingKeywords(array $keywords)
    {
        $this->matchingKeywords = $keywords;
    }

    /**
     * Return a list with all matching keywords
     * @return array
     * <code>
     * [0] => array("relevance"=>$relevance,"term"=>$term,"keywordID"=>$keywordID,"keyword"=>$keyword);
     * </code>
     */
    public function getMatchingKeywords()
    {
        return $this->matchingKeywords;
    }


    /**
     * Split term string into terms array and find keywords that matching array items
     * @param string $term
     * @return void
     */
    public function searchFindKeywords($term)
    {
        // Set terms to search on, limit to configSearchMaxKeywordsPerSearch
        $term = array_slice($this->getKeywordsFromString($term), 0, $this->configSearchMaxKeywordsPerSearch);
        $this->setTerms($term);
        $termsToSearchOn = $this->getTerms();

        // If any term in search request
        if (!empty($termsToSearchOn)) {
            foreach ($termsToSearchOn as $searchTerm) {
                // Loop through terms and determinate matching keywords from database
                $matchingKeywords = $this->searchMatchingKeywords($searchTerm);
                // Update array with all matching keywords from this search request
                $this->setMatchingKeywords(array_merge($this->getMatchingKeywords(), $matchingKeywords));
            }
        }
    }

    /**
     * Add translation table as join in query
     * @param int $shopId
     * @return string
     */
    public function getSearchTranslationSql(int $shopId)
    {
        return '
                LEFT JOIN s_articles_translations AS at
                ON a.id=at.articleID
                AND at.languageID=' . (int) $shopId . '
               ';
    }

    /**
     * Select all matching keywords in sql query
     * @return array|string
     */
    public function getSearchKeywordSql()
    {
        $keywords = $this->getMatchingKeywords();

        $sql_keywords = array();
        foreach ($keywords as $match) {
            $sql_keywords[] = 'SELECT ' . $match['relevance'] . ' as relevance, ' . $this->database->quote($match['term']) . ' as term, ' . $match['keywordID'] . ' as keywordID';
        }
        $sql_keywords = implode(" UNION ALL ", $sql_keywords);
        return $sql_keywords;
    }

    /**
     * Get price column to use in this search request
     * Considers customer group configuration
     * @return string
     */
    public function getSearchPriceSql()
    {
        $sqlPriceRequestField = "
           (
               SELECT IFNULL(p.price,p2.price) as min_price
                FROM s_articles_details d

                   LEFT JOIN s_articles_prices p
                   ON p.articleDetailsID=d.id
                   AND p.pricegroup='" . $this->requestShopCustomerGroup . "'
                   AND p.to='beliebig'

                   LEFT JOIN s_articles_prices p2
                   ON p2.articledetailsID=d.id
                   AND p2.pricegroup='EK'
                   AND p2.to='beliebig'

               WHERE d.articleID=a.id
               ORDER BY min_price
               LIMIT 1
           )
       ";

        // If is a customer group without own dedicated prices, consider discount in query
        if ($this->requestShopCustomerGroupMode == true && !empty($this->requestShopCustomerGroupDiscount)) {
            $sqlPriceRequestField = $sqlPriceRequestField . '*(100-' . $this->requestShopCustomerGroupDiscount . ')/100';
        }
        // If any non default currency is active, possibly we need to convert prices
        if (!empty($this->requestShopCurrencyFactor) && $this->requestShopCurrencyFactor != 1) {
            $sqlPriceRequestField = $sqlPriceRequestField . '*' . $this->requestShopCurrencyFactor;
        }
        // If is the desired customer group is a net group
        if ($this->requestShopCustomerGroupTax == true) {
            $sqlPriceRequestField = "ROUND($sqlPriceRequestField*(100+t.tax)/100, 2)";
        }

        return $sqlPriceRequestField;
    }

    /**
     * Get all tables and columns that might be involved in this search request as an array
     * @return array
     */
    public function getSearchInvolvedTables()
    {
        static $tables;

        if (empty($tables)) {

            $tables = $this->database->fetchAll("
                SELECT STRAIGHT_JOIN
                    st.id as tableID,
                    st.table,
                    st.where,
                    st.referenz_table, st.foreign_key,
                    GROUP_CONCAT(sf.id SEPARATOR ', ') as fieldIDs,
                    GROUP_CONCAT(sf.field SEPARATOR ', ') as `fields`
                FROM s_search_fields sf FORCE INDEX (tableID)
                    INNER JOIN s_search_tables st
                        ON st.id = sf.tableID
                        AND sf.relevance != 0
                GROUP BY sf.tableID
           ");
        }
        return $tables;
    }

    /**
     * Get all tables and columns that might be involved in this search request as sql
     * @param array $tables
     * @param string $keywordSql
     * @return array|null
     */
    public function getSearchInvolvedTablesSql(array $tables, string $keywordSql)
    {
        $tablesSql = null;

        foreach ($tables as $table) {
            $sqlTable = "";

            if (!empty($table['referenz_table'])) {
                $sqlTable = 'JOIN ' . $table['referenz_table'] . ' st' . $table['tableID'] . "\n"
                    . 'ON si.elementID = st' . $table['tableID'] . '.' . $table['foreign_key'];
                $sqlArticleId = 'st' . $table['tableID'] . '.articleID';
            } elseif (!empty($table['foreign_key'])) {
                $sqlTable = 'JOIN s_articles st' . $table['tableID'] . "\n"
                    . 'ON si.elementID = st' . $table['tableID'] . '.' . $table['foreign_key'];
                $sqlArticleId = 'st' . $table['tableID'] . '.id';
            } else {
                $sqlArticleId = 'si.elementID';
            }
            $tablesSql[] = '
                SELECT ' . $sqlArticleId . ' as articleID, MAX(sf.relevance*sm.relevance) as relevance, sm.keywordID
                FROM (' . $keywordSql . ') sm
                JOIN s_search_index si
                ON sm.keywordID = si.keywordID
                JOIN s_search_fields sf
                ON si.fieldID=sf.id
                AND sf.relevance!=0
                AND sf.tableID = ' . $table['tableID'] . '

                ' . $sqlTable . '

                GROUP BY articleID, sm.term, sf.id
            ';
        }

        return $tablesSql;
    }

    /**
     * Count results and determinate max relevance for this search request
     * @param $sqlRelevanceField
     * @param $sqlFromStatement
     * @return array
     */
    public function getSearchCountResults($sqlRelevanceField, $sqlFromStatement)
    {
        $sql = '
        SELECT
            COUNT(DISTINCT a.id) as count, MAX(' . $sqlRelevanceField . ') as max_relevance

        ' . $sqlFromStatement . '

        INNER JOIN s_articles_categories_ro ac
            ON  ac.articleID  = a.id
            AND ac.categoryID = ?
        INNER JOIN s_categories c
            ON  c.id = ac.categoryID
            AND c.active = 1

        WHERE a.active=1
        ';
        return $this->database->fetchRow($sql, array($this->requestRestrictSearchResultsToCategory));
    }

    /**
     *
     * @param $involvedTablesSql
     * @return string
     */
    public function getSearchFromSql($involvedTablesSql)
    {
        return '
           FROM (
               SELECT srd.articleID, SUM(srd.relevance) as relevance
               FROM (
                   ' . $involvedTablesSql . '
               ) as srd
               GROUP BY srd.articleID
               ORDER BY relevance DESC
               LIMIT 5000
           ) sr

           JOIN s_articles a
           ON a.id = sr.articleID
       ';
    }

    /**
     * Assign search configuration to local class properties
     * @param array $configuration
     * @throws Enlight_Exception
     */
    public function initSearchConfiguration(array $configuration)
    {
        foreach ($configuration as $key => $config) {
            $property = 'request' . ucfirst($key);
            if (property_exists($this, $property)) {
                $this->$property = $config;
            }
        }
    }

    /**
     * Add where conditions if configSearchMinDstanceOnTop is set or
     * if supplier filter is active
     * @param $max_relevance
     * @return string
     */
    public function getSearchFilterSql($max_relevance)
    {
        $sqlWhere = '';
        // Strip out search results that does not have much relevance
        if (!empty($this->configSearchMinDistanceOnTop)) {
            $minimumRelevance = $max_relevance / 100 * $this->configSearchMinDistanceOnTop;
            if (!empty($minimumRelevance))
                $sqlWhere .= ' AND relevance>=' . (int) $minimumRelevance;
        }

        // Filter search results for supplier
        if (!empty($this->requestFilter["supplier"])) {
            $sqlWhere .= ' AND a.supplierID=' . (int) $this->requestFilter["supplier"];
        }
        return $sqlWhere;
    }

    /**
     * Add having condition to search query, if price range filter is active
     * @return string
     */
    public function getSearchFilterPricesSql()
    {
        $sqlHaving = "";
        // Filter search results for price ranges
        if (!empty($this->requestFilter["price"])) {
            if (is_array($this->requestFilter["price"])) {
                $filterPrice = $this->requestFilter["price"];
            } else {
                if (empty($this->configSearchPriceFilter)) {
                    $filterPrice = $this->configPriceFilter[$this->requestFilter["price"]];
                } else {
                    $filterPrice = $this->configSearchPriceFilter[$this->requestFilter["price"]];
                }
            }

            $sqlHaving .= ' HAVING price>=' . (float) $filterPrice['start'];
            $sqlHaving .= ' AND price<' . (float) $filterPrice['end'];
        }
        return $sqlHaving;
    }

    /**
     * @param $sqlRelevanceField
     * @param $sqlPriceField
     * @param $sqlNameField
     * @param $sqlDescriptionField
     * @param $sqlFrom
     * @param $sqlCategoryFilter
     * @param $sqlTranslation
     * @param $sqlWhere
     * @param $sqlHaving
     * @return array
     * @throws Enlight_Exception
     */
    public function getSearchExecuteQuery($sqlRelevanceField, $sqlPriceField, $sqlNameField, $sqlDescriptionField, $sqlFrom, $sqlCategoryFilter, $sqlTranslation, $sqlWhere, $sqlHaving)
    {
        $sql = '
            SELECT
                a.id as `key`, a.id as articleID, ' . $sqlRelevanceField . ' as relevance, ' . $sqlPriceField . ' as price, a.supplierID,
                a.datum, d.sales as sales, ' . $sqlNameField . ' as name, ' . $sqlDescriptionField . ' as description, ai.img as image, ai.media_id as mediaId, ai.extension,
                IFNULL((SELECT CONCAT(ROUND(AVG(points), 2), \'|\', COUNT(*)) as votes FROM s_articles_vote WHERE active=1 AND articleID=a.id), \'0.00|0\') as vote

            ' . $sqlFrom . '

            JOIN s_articles_details d
            ON a.id=d.articleID
            AND d.kind=1

            INNER JOIN s_articles_categories_ro ac
                ON  ac.articleID  = a.id
                AND ac.categoryID = ' .$sqlCategoryFilter. '
            INNER JOIN s_categories c
                ON  c.id = ac.categoryID
                AND c.active = 1

            JOIN s_core_tax t
            ON a.taxID = t.id

            LEFT JOIN s_articles_img AS ai
            ON ai.articleID=a.id
            AND ai.article_detail_id IS NULL
            AND ai.main = 1

            ' . $sqlTranslation . '

            WHERE a.active=1

            AND (
                SELECT articleID
                FROM s_articles_avoid_customergroups
                WHERE articleID = a.id AND customergroupID = ' . $this->requestShopCustomerGroupId . '
            ) IS NULL

            ' . $sqlWhere . '

            ' . $sqlHaving . '

            ORDER BY (' . $sqlRelevanceField . ') DESC, a.id
        ';

        try {
            $result = $this->database->fetchAssoc($sql);
        } catch (PDOException $e) {
            throw new Enlight_Exception($e->getMessage());
        }
        return $result;
    }

    /**
     * standard search method
     * @param string $term
     * @param array $searchConfiguration
     * @throws Enlight_Exception
     * @internal param $example <code
     * Array (
     *       [term] => test
     *       [restrictSearchResultsToCategory] => 3
     *       [filter] => Array
     *           (
     *               [supplier] => 0
     *               [category] => 0
     *               [price] => 0
     *               [propertyGroup] =>
     *           )
     *      [sortSearchResultsBy] => 0
     *      [currentPage] => 0
     *      [resultsPerPage] => 12
     *      [searchTermForTemplate] => test
     *      [shopLanguageId] => 1
     *      [shopHasTranslations] =>
     *      [shopCurrency] => Shopware_Models_Currency Object
     *      [shopCustomerGroup] => EK
     *      [shopCustomerGroupDiscount] => 0
     *      [shopCurrencyFactor] => 1
     *      [shopCustomerGroupMode] => 0
     *      [shopCustomerGroupTax] = 1
     * )
     * </code>
     * @return \Shopware_Components_Search_Result_Abstract
     */
    public function search($term, array $searchConfiguration)
    {
        $this->initSearchConfiguration($searchConfiguration);

        // Find keywords matching term
        $this->searchFindKeywords($term);

        // Get sql part to support multilanguage environments
        $sqlTranslationTableStatement = null;
        $sqlNameField = 'a.name';
        $sqlDescriptionField = 'IF(TRIM(a.description)!=\'\',a.description,a.description_long)';

        // Shop has possibly translations
        if ($this->requestShopHasTranslations == true) {
            $sqlTranslationTableStatement = $this->getSearchTranslationSql($this->requestShopLanguageId);
            $sqlNameField = 'IF(at.name IS NULL OR at.name=\'\',a.name,at.name)';
            $sqlDescriptionField = 'IF(at.description_long IS NULL OR at.description_long=\'\',IF(TRIM(a.description)!=\'\',a.description,a.description_long),IF(TRIM(at.description)=\'\',at.description_long,at.description))';
        }

        // Get sql part for selecting keywords in search query
        $sqlKeywords = $this->getSearchKeywordSql();

        // Get sql part to calculate the correct price in result
        $sqlPriceField = $this->getSearchPriceSql();

        // Get all tables and relationships that should be considered in search
        $involvedTables = $this->getSearchInvolvedTables();

        // Check if basic dependencies are resolved
        if (empty($involvedTables) || empty($sqlKeywords) || empty($term)) {
            return false;
        }

        // Build from query that considers all related tables and relationships
        $involvedTablesSql = $this->getSearchInvolvedTablesSql($involvedTables, $sqlKeywords);
        $involvedTablesSql = implode(' UNION ALL ', $involvedTablesSql);
        $sqlFromStatement = $this->getSearchFromSql($involvedTablesSql);

        // Define field to sort by default
        $sqlRelevanceField = 'sr.relevance+IF(a.topseller=1,50,0)+IF(a.datum >= DATE_SUB(NOW(),INTERVAL 7 DAY),25,0)';

        // Do query to determinate count of unfiltered search results
        $searchResults = $this->getSearchCountResults($sqlRelevanceField, $sqlFromStatement);
        $searchResultsCount = $searchResults['count'];

        // If no results, return false
        if (empty($searchResultsCount)) {
            return false;
        }

        // Add where conditions for minimum relevances and supplier filter
        $sqlWhere = $this->getSearchFilterSql($searchResults["max_relevance"]);

        // Add having conditions for price filters
        $sqlHaving = $this->getSearchFilterPricesSql();

        // Filter search results for category

        if (!empty($this->requestFilter["category"])) {
            $this->getResult()->setCurrentCategoryFilter((int) $this->requestFilter["category"]);
        } else {
            $this->getResult()->setCurrentCategoryFilter((int) $this->requestRestrictSearchResultsToCategory);
        }

        // Build final sql query and execute
        $searchResultsFinal = $this->getSearchExecuteQuery(
            $sqlRelevanceField,
            $sqlPriceField,
            $sqlNameField,
            $sqlDescriptionField,
            $sqlFromStatement,
            $this->getResult()->getCurrentCategoryFilter(),
            $sqlTranslationTableStatement,
            $sqlWhere,
            $sqlHaving
        );

        $traceSearch = Shopware()->Config()->get('traceSearch', true);
        if (empty($this->requestSuggestSearch) && $traceSearch) {
            $sql = '
              INSERT INTO s_statistics_search (datum, searchterm, results)
                VALUES (NOW(), ?, ?)
            ';
            Shopware()->Db()->query($sql, array(
                $term,
                empty($searchResultsFinal) ? 0 : count($searchResultsFinal)
            ));
        }

        // If no results return false
        if (empty($searchResultsFinal)) {
            return false;
        }

        // Read all affected categories from result
        $this->getCountCategoryFilters($searchResultsFinal);

        // Empty method to modifiy search results in own search adapters
        $this->executeCustomFilters($searchResultsFinal);

        $this->getCountSupplierPriceFilters($searchResultsFinal);

        // Set count of results to result object
        $this->getResult()->setResultCount(count($searchResultsFinal));


        if (!empty($this->requestSortSearchResultsBy)) {
            $sortedResult = $this->sortResults($searchResultsFinal, $this->requestSortSearchResultsBy);
            if ($sortedResult !== false) {
                $searchResultsFinal = $sortedResult;
            }
        }
        $searchResultsFinal = array_splice(
            $searchResultsFinal,
            ($this->requestCurrentPage -1) * $searchConfiguration['resultsPerPage'],
            $searchConfiguration['resultsPerPage']
        );

        if ($this->requestSuggestSearch == true) {
            $this->getResult()->setResult(array_values($searchResultsFinal));
        } else {
            // Set results to class property
            $this->getResult()->setResult($searchResultsFinal);
        }

        return $this->getResult();
    }

    /**
     * Empty method that allows to integrate own filters into search adapter
     * @param $searchResult
     */
    public function executeCustomFilters($searchResult)
    {
    }


    /**
     * Get all categories that are affected in search result and count search results
     * in each category
     * @param $searchResults
     * @return array
     * @throws Enlight_Exception
     */
    public function getCountCategoryFilters($searchResults)
    {
        $sql = '
            SELECT c.*, COUNT(DISTINCT ac.articleID) as count

            FROM s_categories c
                INNER JOIN s_articles_categories_ro ac
                    ON ac.categoryID = c.id
            WHERE c.parent=' . $this->getResult()->getCurrentCategoryFilter() . '
            AND c.active = 1
            AND ac.articleID  IN (' . implode(',', array_keys($searchResults)) . ')
            GROUP BY c.id
            ORDER BY count DESC, c.description
        ';
        try {
            $result = $this->database->fetchAll($sql);
        } catch (PDOException $e) {
            throw new Enlight_Exception($e->getMessage());
        }


        // Loop through affected categories as long as ...
        if (!empty($result) && count($result) === 1) {
            $this->getResult()->setCurrentCategoryFilter($result[0]['id']);
            return $this->getCountCategoryFilters($searchResults);
        }

        $this->getResult()->setAffectedCategories($result);

        return $result;
    }

    /**
     * Get all price ranges that are affected in current search result
     * @param $searchResult
     * @throws Enlight_Exception
     */
    public function getCountSupplierPriceFilters($searchResult)
    {
        $searchResultPriceFilters = array();
        $filterPrice = empty($this->configSearchPriceFilter) ? $this->configPriceFilter : $this->configSearchPriceFilter;

        foreach ($filterPrice as $key => $filter) {
            foreach ($searchResult as $article) {
                if ($article['price'] >= $filter['start'] && $article['price'] < $filter['end']) {
                    if (isset($searchResultPriceFilters[$key]))
                        $searchResultPriceFilters[$key]++;
                    else
                        $searchResultPriceFilters[$key] = 1;
                }
            }
        }

        // Set all price ranges that are occurring in search result
        $this->getResult()->setAffectedPriceRanges($searchResultPriceFilters);

        $sql = '
            SELECT s.id as `key`, s.*, COUNT(*) as count
            FROM s_articles a, s_articles_supplier s
            WHERE a.supplierID=s.id
            AND a.id IN (' . implode(',', array_keys($searchResult)) . ')
            GROUP BY s.id
            ORDER BY count DESC, s.name
        ';

        $suppliers = array();
        try {
            $result = $this->database->fetchAll($sql);
            if (is_array($result)) {
                foreach ($result as $row) {
                    $suppliers[$row["key"]] = $row;
                }
            }
        } catch (PDOException $e) {
            throw new Enlight_Exception($e->getMessage());
        }

        $this->getResult()->setAffectedSuppliers($suppliers);
    }


    /**
     * Sort search results locally
     * @param $searchResult
     * @param $sortBy
     *          1 = datum
     *          2 = sales
     *          3 = price
     *          4 = name
     *          7 = vote
     * @return array|bool
     */
    public function sortResults($searchResult, $sortBy)
    {
        switch ($sortBy) {
            case 1:
                $field = "datum";
                break;
            case 2:
                $field = "sales";
                break;
            case 3:
            case 4:
                $field = "price";
                break;
            case 5:
                $field = "name";
                break;
            case 7:
                $field = "vote";
                break;
            default:
                return false;
        }

        $result = $searchResult;

        $orderValues = array();
        foreach ($result as $articleID => $article) {
            $orderValues[$articleID] = $article[$field];
        }

        switch ($sortBy) {
            case 1:
            case 2:
            case 4:
            case 6:
            default:
                arsort($orderValues);
                break;
            case 5:
                natsort($orderValues);
                break;
            case 7:
                natsort($orderValues);
                $orderValues = array_reverse($orderValues, true);
                break;
            case 3:
                asort($orderValues);
                break;
        }
        $temporaryArticles = array();
        foreach (array_keys($orderValues) as $articleID) {
            $temporaryArticles[$articleID] = $result[$articleID];
        }
        return $temporaryArticles;
    }

    /**
     * Rebuild shopware search index
     * Loop through all tables / columns that should be considered in search
     * Write keywords to desired table
     * @throws Enlight_Exception
     */
    public function buildSearchIndex()
    {
        @ini_set("memory_limit", "512M");
        @set_time_limit(0);

        // Set time of last cache rebuild
        $sql = '
            SET @parent = (SELECT id FROM s_core_config_elements WHERE name = \'fuzzysearchlastupdate\');
            DELETE FROM `s_core_config_values` WHERE element_id = @parent;
            INSERT INTO `s_core_config_values` (`element_id`, `shop_id`, `value`) VALUES
            (@parent, 1, CONCAT(\'s:\', LENGTH(NOW()), \':"\', NOW(), \'";\'));
        ';
        $this->database->exec($sql);

        // Truncate search index table
        $this->database->query('TRUNCATE TABLE `s_search_index`');

        // Get a list of all tables and columns in this tables that should be processed by search
        /**
         * Example return:
         * tableID | table      | where | referenz_table | fieldIDs | fields            | foreign_key
         * 1       | s_articles | NULL  | NULL           | 3,4      | name, keywords    | NULL
         * 2       | s_categories | NULL | s_articles_categories | 1,2 | metakeywords, description | categoryID
         */
        $tables = $this->getSearchInvolvedTables();

        if (!empty($tables)) {
            foreach ($tables as $table) {
                // Set primary key
                $table['elementID'] = empty($table['foreign_key']) && $table['table'] != 's_articles' ? 'articleID' : 'id';
                // Build sql query to fetch values from this table
                $sql = 'SELECT ' . $table['elementID'] . ' as id, ' . $table['fields'] . ' FROM ' . $table['table'];
                // If any where condition is set, add to query
                if (!empty($table['where'])) $sql .= 'WHERE ' . $table['where'];

                // Get all fields & values from current table

                $getTableKeywords = $this->database->fetchAll($sql);

                // If no result, return
                if (empty($getTableKeywords)) {
                    continue;
                }

                // Build array from columns fieldIDs and fields
                $fields = array_combine(explode(', ', $table["fieldIDs"]), explode(', ', $table["fields"]));
                $keywords = array();
                $sql_index = array();

                // Go through every row of result
                foreach ($getTableKeywords as $currentRow => $row) {
                    // Go through every column of result
                    foreach ($fields as $fieldID => $field) {
                        // Split string from column into keywords
                        $field_keywords = $this->getKeywordsFromString($row[$field]);
                        if (empty($field_keywords)) {
                            continue;
                        }

                        foreach ($field_keywords as &$keyword) {
                            $keyword = $this->database->quote($keyword);
                            $keywords[] = $keyword;
                        }

                        // SQL-queries to fill s_search_index
                        $sql_index[] = 'SELECT sk.id as keywordID, ' . $row['id'] . ' as elementID, ' . $fieldID . ' as fieldID '
                            . 'FROM s_search_keywords sk '
                            . 'WHERE sk.keyword IN (' . implode(', ', $field_keywords) . ')';
                    }

                    // If no new keywords were found, proceed with next table
                    if (empty($keywords)) {
                        continue;
                    }

                    // If last row or more then 5000 keywords fetched, write results to index
                    if ($currentRow == count($getTableKeywords) - 1 || count($keywords) > 5000) {
                        $keywords = array_unique($keywords); // Remove duplicates
                        $sql_keywords = 'INSERT IGNORE INTO `s_search_keywords` (`keyword`) VALUES';
                        $sql_keywords .= ' (' . implode('), (', $keywords) . ')';

                        // Insert Keywords
                        try {
                            $this->database->query($sql_keywords);
                        } catch (PDOException $e) {
                            throw new Enlight_Exception($e->getMessage());
                        }

                        $keywords = array();

                        // Update index
                        try {
                            $sql_index = implode("\n\nUNION ALL\n\n", $sql_index);
                            $sql_index = "INSERT IGNORE INTO s_search_index (keywordID, elementID, fieldID)\n\n" . $sql_index;
                            $this->database->query($sql_index);
                            $sql_index = array();
                        } catch (PDOException $e) {
                            throw new Enlight_Exception($e->getMessage());
                        }
                    }
                }
            }
        }
        // Cleanup index from invalid entries
        $this->cleanupIndex();
        // Cleanup keywords from invalid entries
        $this->cleanupKeywords();
    }

    /**
     * Parse a string / search term into a keyword array
     * @param string $string
     * @return array
     */
    public function getKeywordsFromString($string)
    {
        $string = strtolower(html_entity_decode($string));

        $substitution = array(
            "ä" => "a", "Ä" => "a", "ö" => "o", "Ö" => "o", "ü" => "u", "Ü" => "u", "ß" => "ss", "\" " => " zoll ",
            "`" => "", "´" => "", "'" => "", "-" => ""
        );

        // Remove not required chars from string
        $string = str_replace(array_keys($substitution), array_values($substitution), $string);
        $string = trim(preg_replace("/[^a-z0-9]/", " ", $string));

        // Parse string into array
        $wordsTmp = preg_split('/ /', $string, -1, PREG_SPLIT_NO_EMPTY);

        if (count($wordsTmp)) $words = array_unique($wordsTmp);
        elseif (!empty($string)) $words = array($string);
        else return array();

        // Check if any keyword is on blacklist
        $words = $this->filterBadWordsFromString($words);
        sort($words);


        return $words;
    }

    /**
     * Check if a keyword is on blacklist or not
     * @param string $word
     * @return bool
     */
    public function filterBadWordFromString($word)
    {
        static $badWords;

        if (!isset($badWords)) $badWords = preg_split("#[\s,;]+#msi", $this->configSearchBadWords, -1, PREG_SPLIT_NO_EMPTY);

        if (in_array((string) $word, $badWords)) return false;
        return true;
    }


    /**
     * Filter out bad keywords before starting search
     * @param array $words
     * @return array|bool
     */
    public function filterBadWordsFromString(array $words)
    {
        if (!count($words) || !is_array($words)) return false;

        $result = array();

        foreach ($words as $word) {
            if ($this->filterBadWordFromString($word)) $result[] = $word;
        }

        return $result;
    }

    /**
     * Cleanup shopware search index
     */
    public function cleanupIndex()
    {
        $tables = $this->getSearchInvolvedTables();

        $sql_join = '';
        foreach ($tables as $table) {
            if (empty($table["foreign_key"])) continue;
            if (empty($table['referenz_table'])) $table['referenz_table'] = 's_articles';
            $sql_join .= "
                LEFT JOIN {$table['referenz_table']} t{$table['tableID']}
                ON si.elementID=t{$table['tableID']}.{$table['foreign_key']}
                AND si.fieldID IN ({$table['fieldIDs']})
            ";
        }

        $sql = "
            SELECT STRAIGHT_JOIN
                   keywordID, fieldID, sk.keyword
            FROM `s_search_index` si

            INNER JOIN s_search_keywords sk
            ON si.keywordID=sk.id

            $sql_join

            GROUP BY keywordID, fieldID
            HAVING COUNT(*) > (SELECT COUNT(*)*0.9 FROM `s_articles`)
        ";

        $collectToDelete = $this->database->fetchAll($sql);
        foreach ($collectToDelete as $delete) {
            $sql = '
                DELETE FROM s_search_index
                WHERE keywordID=? AND fieldID=?
            ';
            $this->database->query($sql, array($delete['keywordID'], $delete['fieldID']));
        }
    }

    /**
     * Cleanups search keywords in the database.
     */
    public function cleanupKeywords()
    {
        $sql = '
            DELETE sk FROM `s_search_keywords` sk
            LEFT JOIN s_search_index si
            ON sk.id=si.keywordID
            WHERE si.keywordID IS NULL
        ';
        $this->database->query($sql);
    }
}
