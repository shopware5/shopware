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

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\SearchBundle;
use Shopware\Bundle\StoreFrontBundle;

/**
 * Deprecated Shopware Class that handle articles
 *
 * @category  Shopware
 * @package   Shopware\Core\Class
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class sArticles
{
    /**
     * Pointer to sSystem object
     *
     * @var sSystem
     */
    public $sSYSTEM;
    /**
     * Array of already loaded promotions
     *
     * @var array
     */
    public $sCachePromotions = array();

    /**
     * @var \Shopware\Models\Category\Category
     */
    public $category;

    /**
     * @var int
     */
    public $categoryId;

    /**
     * @var int
     */
    public $translationId;

    /**
     * @var int
     */
    public $customerGroupId;

    /**
     * @var \Shopware\Models\Article\Repository
     */
    protected $articleRepository = null;

    /**
     * @var \Shopware\Models\Media\Repository
     */
    protected $mediaRepository = null;

    /**
     * Constant for the alphanumeric sort configuration of the category filters
     */
    const FILTERS_SORT_ALPHANUMERIC = 0;

    /**
     * Constant for the numeric sort configuration of the category filters
     */
    const FILTERS_SORT_NUMERIC = 1;

    /**
     * Constant for the article count sort configuration of the category filters
     */
    const FILTERS_SORT_ARTICLE_COUNT = 2;

    /**
     * Constant for the positon sort configuration of the category filters
     */
    const FILTERS_SORT_POSITION = 3;

    /**
     * @var StoreFrontBundle\Service\ContextServiceInterface
     */
    private $contextService;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * @var StoreFrontBundle\Service\ListProductServiceInterface
     */
    private $listProductService;

    /**
     * @var StoreFrontBundle\Service\ProductServiceInterface
     */
    private $productService;

    /**
     * @var StoreFrontBundle\Service\VoteServiceInterface
     */
    private $voteService;

    /**
     * @var StoreFrontBundle\Service\ConfiguratorServiceInterface
     */
    private $configuratorService;

    /**
     * @var StoreFrontBundle\Service\PropertyServiceInterface
     */
    private $propertyService;

    /**
     * @var SearchBundle\ProductSearch
     */
    private $searchService;

    /**
     * @var Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    /**
     * @var \Shopware\Components\Compatibility\LegacyStructConverter
     */
    private $legacyStructConverter;

    /**
     * @var \Shopware\Components\Compatibility\LegacyEventManager
     */
    private $legacyEventManager;


    public function __construct(
        \Shopware\Models\Category\Category $category = null,
        $translationId = null,
        $customerGroupId = null,
        StoreFrontBundle\Service\ContextServiceInterface $contextService = null,
        Shopware_Components_Config $config = null,
        StoreFrontBundle\Service\ListProductServiceInterface $listProductService = null,
        StoreFrontBundle\Service\ProductServiceInterface $productService = null,
        StoreFrontBundle\Service\VoteServiceInterface $voteService = null,
        StoreFrontBundle\Service\ConfiguratorServiceInterface $configuratorService = null,
        StoreFrontBundle\Service\PropertyServiceInterface $propertyService = null,
        SearchBundle\ProductSearchInterface $searchService = null,
        Enlight_Event_EventManager $eventManager = null,
        Enlight_Components_Db_Adapter_Pdo_Mysql $db = null,
        \Shopware\Components\Compatibility\LegacyStructConverter $legacyStructConverter,
        \Shopware\Components\Compatibility\LegacyEventManager $legacyEventManager
    ) {
        $this->category = ($category) ?: Shopware()->Shop()->getCategory();
        $this->categoryId = $this->category->getId();

        $this->translationId = ($translationId)  ?: (!Shopware()->Shop()->getDefault() ? Shopware()->Shop()->getId() : null);
        $this->customerGroupId = $customerGroupId ?: ((int) Shopware()->Modules()->System()->sSYSTEM->sUSERGROUPDATA['id']);

        $this->contextService = $contextService;
        $this->config = $config;
        $this->listProductService = $listProductService;
        $this->productService = $productService;
        $this->voteService = $voteService;
        $this->configuratorService = $configuratorService;
        $this->propertyService = $propertyService;
        $this->searchService = $searchService;
        $this->eventManager = $eventManager;
        $this->db = $db;

        $this->legacyEventManager = $legacyEventManager;
        $this->legacyStructConverter = $legacyStructConverter;

        if ($this->contextService == null) {
            $this->contextService = Shopware()->Container()->get('context_service');
        }

        if ($this->config == null) {
            $this->config = Shopware()->Container()->get('config');
        }

        if ($this->listProductService == null) {
            $this->listProductService = Shopware()->Container()->get('list_product_service');
        }

        if ($this->productService == null) {
            $this->productService = Shopware()->Container()->get('product_service');
        }

        if ($this->voteService == null) {
            $this->voteService = Shopware()->Container()->get('vote_service');
        }

        if ($this->configuratorService == null) {
            $this->configuratorService = Shopware()->Container()->get('configurator_service');
        }

        if ($this->propertyService == null) {
            $this->propertyService = Shopware()->Container()->get('property_service');
        }

        if ($this->searchService == null) {
            $this->searchService = Shopware()->Container()->get('product_search');
        }

        if ($this->db == null) {
            $this->db = Shopware()->Container()->get('db');
        }

        if ($this->eventManager == null) {
            $this->eventManager = Shopware()->Container()->get('events');
        }

        if ($this->legacyStructConverter == null) {
            $this->legacyStructConverter = Shopware()->Container()->get('legacy_struct_converter');
        }

        if ($this->legacyEventManager == null) {
            $this->legacyEventManager = Shopware()->Container()->get('legacy_event_manager');
        }
    }

    /**
     * Helper function to get access to the media repository.
     * @return \Shopware\Models\Media\Repository
     */
    private function getMediaRepository()
    {
        if ($this->mediaRepository === null) {
            $this->mediaRepository = Shopware()->Models()->getRepository('Shopware\Models\Media\Media');
        }
        return $this->mediaRepository;
    }

    /**
     * Helper function to get access to the article repository.
     * @return \Shopware\Models\Article\Repository
     */
    private function getArticleRepository()
    {
        if ($this->articleRepository === null) {
            $this->articleRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Article');
        }
        return $this->articleRepository;
    }

    /**
     * Delete articles from comparision chart
     * @param int $article Unique article id - refers to s_articles.id
     */
    public function sDeleteComparison($article)
    {
        $article = (int) $article;
        if ($article) {
            $checkForArticle = $this->sSYSTEM->sDB_CONNECTION->Execute("
            DELETE FROM s_order_comparisons WHERE sessionID=? AND articleID=?
            ", array($this->sSYSTEM->sSESSION_ID, $article));
        }
    }

    /**
     * Delete all articles from comparision chart
     */
    public function sDeleteComparisons()
    {
        $sql = "
          DELETE FROM s_order_comparisons WHERE sessionID=?
        ";
        $checkForArticle = $this->sSYSTEM->sDB_CONNECTION->Execute($sql, array($this->sSYSTEM->sSESSION_ID));
    }

    /**
     * Insert articles in comparision chart
     * @param int $article s_articles.id
     * @return bool true/false
     */
    public function sAddComparison($article)
    {
        $article = (int) $article;
        if ($article) {
            // Check if this article is already noted
            $checkForArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow("
            SELECT id FROM s_order_comparisons WHERE sessionID=? AND articleID=?
            ", array($this->sSYSTEM->sSESSION_ID, $article));
            // Check if max. numbers of articles for one comparison-session is reached
            $checkNumberArticles = $this->sSYSTEM->sDB_CONNECTION->GetRow("
            SELECT COUNT(id) AS countArticles FROM s_order_comparisons WHERE sessionID=?
            ", array($this->sSYSTEM->sSESSION_ID));

            if ($checkNumberArticles["countArticles"] >= $this->sSYSTEM->sCONFIG["sMAXCOMPARISONS"]) {
                return "max_reached";
            }

            //
            if (!$checkForArticle["id"]) {
                $articleName = $this->sSYSTEM->sDB_CONNECTION->GetOne("
                SELECT s_articles.name AS articleName FROM s_articles WHERE
                id = ?
                ", array($article));

                if (!$articleName) return false;

                $sql = "
                INSERT INTO s_order_comparisons (sessionID, userID, articlename, articleID, datum)
                VALUES (?,?,?,?,now())
                ";


                $queryNewPrice = $this->sSYSTEM->sDB_CONNECTION->Execute($sql, array(
                    $this->sSYSTEM->sSESSION_ID,
                    empty($this->sSYSTEM->_SESSION["sUserId"]) ? 0 : $this->sSYSTEM->_SESSION["sUserId"],
                    $articleName,
                    $article
                ));

                if (!$queryNewPrice) {
                    throw new Enlight_Exception("sArticles##sAddComparison##01: Error in SQL-query");
                }
            }
            return true;
        }
    }

    /**
     * Get all articles from comparision chart
     * @return array Associative array with all articles or empty array
     */
    public function sGetComparisons()
    {

        if (!$this->sSYSTEM->sSESSION_ID) return array();

        // Get all comparisons for this user
        $checkForArticle = $this->sSYSTEM->sDB_CONNECTION->GetAll("
            SELECT * FROM s_order_comparisons WHERE sessionID=?
            ", array($this->sSYSTEM->sSESSION_ID));

        if (count($checkForArticle)) {
            foreach ($checkForArticle as $k => $article) {
                $checkForArticle[$k]["articlename"] = stripslashes($article["articlename"]);
                $checkForArticle[$k] = $this->sGetTranslation($article, $article["articleID"], "article", $this->sSYSTEM->sLanguage);
                if (!empty($checkForArticle[$k]["articleName"])) $checkForArticle[$k]["articlename"] = $checkForArticle[$k]["articleName"];
            }


            return $checkForArticle;
        } else {
            return array();
        }
    }

    /**
     * Get all articles and a table of their properties as an array
     * @return array Associative array with all articles or empty array
     */
    public function sGetComparisonList()
    {
        $articles = array();
        if (!$this->sSYSTEM->sSESSION_ID) return array();

        // Get all comparisons for this user
        $checkForArticle = $this->sSYSTEM->sDB_CONNECTION->GetAll("
            SELECT * FROM s_order_comparisons WHERE sessionID=?
            ", array($this->sSYSTEM->sSESSION_ID));

        if (count($checkForArticle)) {
            foreach ($checkForArticle as $article) {
                if ($article["articleID"]) {
                    $data = $this->sGetPromotionById("fix", 0, (int) $article["articleID"]);
                    $articles[] = $data;
                }
            }
            $properties = $this->sGetComparisonProperties($articles);
            $articles = $this->sFillUpComparisonArticles($properties, $articles);

            return array("articles" => $articles, "properties" => $properties);
        } else {
            return array();
        }
    }

    /**
     * Returns all filterable properties depending on the given articles
     *
     * @param array $articles
     * @return array
     */
    public function sGetComparisonProperties($articles)
    {
        $properties = array();
        foreach ($articles as $article) {
            //get all properties in the right order
            $sql = "SELECT options.id, options.name
                    FROM s_filter_options as options
                    LEFT JOIN s_filter_relations as relations ON relations.optionId = options.id
                    LEFT JOIN s_filter as filter ON filter.id = relations.groupID
                    WHERE relations.groupID = ?
                    AND filter.comparable = 1
                    ORDER BY relations.position ASC";
            $articleProperties = Shopware()->Db()->fetchPairs($sql, array($article["filtergroupID"]));

            foreach ($articleProperties as $articlePropertyKey => $articleProperty) {
                if (!in_array($articlePropertyKey, array_keys($properties))) {
                    //the key is not part of the array so add it to the end
                    $properties[$articlePropertyKey] = $articleProperty;
                }
            }
        }
        return $properties;
    }

    /**
     * fills the article properties with the values and fills up empty values
     *
     * @param array $properties
     * @param array $articles
     * @return array
     */
    public function sFillUpComparisonArticles($properties, $articles)
    {
        foreach ($articles as $articleKey => $article) {
            $articleProperties = array();
            foreach ($properties as $propertyKey => $property) {
                if (in_array($propertyKey, array_keys($article["sProperties"]))) {
                    $articleProperties[$propertyKey] = $article["sProperties"][$propertyKey];
                } else {
                    $articleProperties[$propertyKey] = null;
                }
            }
            $articles[$articleKey]["sProperties"] = $articleProperties;
        }

        return $articles;
    }

    /**
     * Get all properties from one article, filtered by one filter group
     *
     * @param int $articleId - s_articles.id
     * @param int $filterGroupId id of the property group (s_filter_groups)
     * @return array
     */
    public function sGetArticleProperties($articleId, $filterGroupId)
    {
        $articleId = (int) $articleId;
        $filterGroupId = (int) $filterGroupId;
        $language = $this->translationId;

        $sql = "
            SELECT
                fv.optionID AS id,
                fo.id AS optionID,
                fo.name AS name,
                f.id AS groupID,
                f.name AS groupName,
                fv.value AS value,
                fv.id AS valueID,
                st.objectdata AS nameTranslation,
                st2.objectdata AS groupNameTranslation,
                st3.objectdata AS valueTranslation
            FROM s_filter_articles fa

            JOIN s_filter_values fv
            ON fv.id=fa.valueID

            JOIN s_filter f
            ON f.id=?

            JOIN s_filter_relations fr
            ON fr.groupID=f.id

            JOIN s_filter_options fo
            ON fo.id=fr.optionID
            AND fo.id=fv.optionID

            LEFT JOIN s_core_translations AS st
            ON st.objecttype='propertyoption'
            AND st.objectkey=fv.optionID
            AND st.objectlanguage=?

            LEFT JOIN s_core_translations AS st2
            ON st2.objecttype='propertygroup'
            AND st2.objectkey=f.id
            AND st2.objectlanguage=?

            LEFT JOIN s_core_translations AS st3
            ON st3.objecttype='propertyvalue'
            AND st3.objectkey=fv.id
            AND st3.objectlanguage='$language'

            WHERE fa.articleID=?

            ORDER BY
              fr.position ASC,
              IF(f.sortmode=1, TRIM(REPLACE(fv.value,',','.'))+0, 0),
              IF(f.sortmode=3, fv.position, 0),
              fv.value
        ";

        $getProperties = Shopware()->Db()->fetchAll($sql, array(
            $filterGroupId,
            $language,
            $language,
            $articleId
        ));

        if (!empty($language)) {
            foreach ($getProperties as $propertyKey => $propertyValue) {
                if (!empty($propertyValue['nameTranslation'])) {
                    $translation = unserialize($propertyValue['nameTranslation']);
                    $getProperties[$propertyKey]['name'] = $translation['optionName'];
                }
                if (!empty($propertyValue['groupNameTranslation'])) {
                    $translation = unserialize($propertyValue['groupNameTranslation']);
                    $getProperties[$propertyKey]['groupName'] = $translation['groupName'];
                }
                if (!empty($propertyValue['valueTranslation'])) {
                    $translation = unserialize($propertyValue['valueTranslation']);
                    $getProperties[$propertyKey]['value'] = $translation['optionValue'];
                }
            }
        }

        $result = array();
        foreach ($getProperties as $property) {
            if (!isset($result[$property['optionID']])) {
                $property['values'] = array($property['value']);
                $result[$property['optionID']] = $property;
            } else {
                $result[$property['optionID']]['value'] .= ', ' . $property['value'];
                $result[$property['optionID']]['values'][] = $property['value'];
            }
        }

        return $result;
    }

    /**
     * Get the average rating from one article
     * @param int $article - s_articles.id
     * @return array
     */
    public function sGetArticlesAverangeVote($article)
    {
        $sql = "
            SELECT AVG(points) AS averange,
                   COUNT(articleID) as number
            FROM s_articles_vote
            WHERE articleID=?
            AND active=1
            GROUP BY articleID
        ";

        $article = (int) $article;
        $getArticles = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql, array($article), "article_$article");

        if (empty($getArticles["averange"])) {
            $getArticles["averange"] = "0.00";
        } else {
            $getArticles["averange"] = round($getArticles["averange"]*2);
        }
        if (empty($getArticles["number"])) $getArticles["number"] = "0";

        return array("averange" => $getArticles["averange"], "count" => $getArticles["number"]);
    }

    /**
     * Save a new article comment / voting
     * Reads several values directly from _POST
     * @param int $article - s_articles.id
     * @return null
     */
    public function sSaveComment($article)
    {
        // Permit Injects

        $this->sSYSTEM->_POST["sVoteName"] = strip_tags($this->sSYSTEM->_POST["sVoteName"]);
        $this->sSYSTEM->_POST["sVoteSummary"] = strip_tags($this->sSYSTEM->_POST["sVoteSummary"]);
        $this->sSYSTEM->_POST["sVoteComment"] = strip_tags($this->sSYSTEM->_POST["sVoteComment"]);
        $this->sSYSTEM->_POST["sVoteStars"] = doubleval($this->sSYSTEM->_POST["sVoteStars"]);

        if ($this->sSYSTEM->_POST["sVoteStars"] < 1 || $this->sSYSTEM->_POST["sVoteStars"] > 10) {
            $this->sSYSTEM->_POST["sVoteStars"] = 0;
        }

        $this->sSYSTEM->_POST["sVoteStars"] /= 2;

        $datum = date("Y-m-d H:i:s");

        if ($this->sSYSTEM->sCONFIG['sVOTEUNLOCK']) {
            $active = 0;
        } else {
            $active = 1;
        }

        $sBADWORDS = "#sex|porn|viagra|url\=|src\=|link\=#i";
        if (preg_match($sBADWORDS, $this->sSYSTEM->_POST["sVoteComment"])) {
            return false;
        }

        if (!empty($this->sSYSTEM->_SESSION['sArticleCommentInserts'][$article])) {
            $sql = '
                DELETE FROM s_articles_vote WHERE id=?
            ';
            $this->sSYSTEM->sDB_CONNECTION->Execute($sql, array(
                $this->sSYSTEM->_SESSION['sArticleCommentInserts'][$article]
            ));
        }

        $sql = '
            INSERT INTO s_articles_vote (articleID, name, headline, comment, points, datum, active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ';
        $insertComment = $this->sSYSTEM->sDB_CONNECTION->Execute($sql, array(
            $article,
            $this->sSYSTEM->_POST["sVoteName"],
            $this->sSYSTEM->_POST["sVoteSummary"],
            $this->sSYSTEM->_POST["sVoteComment"],
            $this->sSYSTEM->_POST["sVoteStars"],
            $datum,
            $active
        ));
        if (empty($insertComment)) {
            throw new Enlight_Exception("sSaveComment #00: Could not save comment");
        }

        $insertId = $this->sSYSTEM->sDB_CONNECTION->Insert_ID();
        if (!isset($this->sSYSTEM->_SESSION['sArticleCommentInserts'])) {
            $this->sSYSTEM->_SESSION['sArticleCommentInserts'] = new ArrayObject();
        }
        $this->sSYSTEM->_SESSION['sArticleCommentInserts'][$article] = $insertId;

        $this->sSYSTEM->_POST = array();
    }

    /**
     * Read all article comments / votings
     * @param int $article - s_articles.id
     * @return array
     */
    public function sGetArticlesVotes($article)
    {
        $article = (int) $article;

        $getArticles = $this->sSYSTEM->sDB_CONNECTION->GetAll("
        SELECT *
        FROM s_articles_vote FORCE INDEX (get_articles_votes)
        WHERE articleID = ?
        AND active = 1
        ORDER BY datum DESC
        ", array($article));
        foreach ($getArticles as $articleKey => $articleValue) {
            $getArticles[$articleKey]["comment"] = str_replace("\\n", "", $getArticles[$articleKey]["comment"]);
            $getArticles[$articleKey]["comment"] = str_replace("\\r", "", $getArticles[$articleKey]["comment"]);

            $getArticles[$articleKey]["comment"] = stripslashes($getArticles[$articleKey]["comment"]); //nl2br($getArticles[$articleKey]["comment"]);
        }
        return $getArticles;
    }

    /**
     * Get id from all articles, that belongs to a specific supplier
     * @param int $supplierID Supplier id (s_articles.supplierID)
     * @return array
     */
    public function sGetArticlesBySupplier($supplierID = null)
    {
        if (!empty($supplierID)) $this->sSYSTEM->_GET['sSearch'] = $supplierID;
        if (!$this->sSYSTEM->_GET['sSearch']) return;
        $sSearch = intval($this->sSYSTEM->_GET['sSearch']);

        $getArticles = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHESEARCH'], "
        SELECT id FROM s_articles WHERE supplierID=? AND active=1 ORDER BY topseller DESC
        ", array($sSearch));

        return $getArticles;
    }

    /**
     * Get articles by name
     * @param string $orderBy Sort
     * @param int $category Filter by category id
     * @param string $mode
     * @param string $search searchterm
     * @return array
     */
    public function sGetArticlesByName($orderBy = "a.topseller DESC", $category = 0, $mode = "", $search = "")
    {
        if (empty($search) && !empty($this->sSYSTEM->_GET['sSearch'])) {
            $search = $this->sSYSTEM->_GET['sSearch'];
        }
        if (empty($search) && empty($mode)) {
            return false;
        }

        if ($mode == "supplier") {
            $search = intval($search);
        }

        $sCategory = $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] ? $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"] : $this->sSYSTEM->sCONFIG["sCATEGORYPARENT"];
        if (!empty($category)) $sCategory = $category;
        $sSearch = trim(stripslashes(html_entity_decode($search)));


        if (strlen($sSearch) < (int) $this->sSYSTEM->sCONFIG["sMINSEARCHLENGHT"] && empty($mode)) {
            return false;
        }

        $sPage = !empty($this->sSYSTEM->_GET['sPage']) ? (int) $this->sSYSTEM->_GET['sPage'] : 1;

        if ($this->sSYSTEM->_GET['sPerPage']) {
            $this->sSYSTEM->_SESSION['sPerPage'] = (int) $this->sSYSTEM->_GET['sPerPage'];
        }
        if ($this->sSYSTEM->_POST['sPerPage']) {
            $this->sSYSTEM->_SESSION['sPerPage'] = (int) $this->sSYSTEM->_POST['sPerPage'];
        }

        if ($this->sSYSTEM->_SESSION['sPerPage']) {
            $this->sSYSTEM->_GET['sPerPage'] = $this->sSYSTEM->_SESSION['sPerPage'];
        }

        if ($this->sSYSTEM->_GET['sPerPage']) {
            $this->sSYSTEM->sCONFIG['sARTICLESPERPAGE'] = $this->sSYSTEM->_GET['sPerPage'];
        }

        $sLimitStart = ($sPage - 1) * $this->sSYSTEM->sCONFIG['sARTICLESPERPAGE'];
        $sLimitEnd = $this->sSYSTEM->_GET['sPerPage'] ? $this->sSYSTEM->_GET['sPerPage'] : $this->sSYSTEM->sCONFIG['sARTICLESPERPAGE'];

        $sql_add_where = "";
        $sql_search_fields = "";
        $ret = array();

        if (empty($mode)) {
            $sSearch = explode(' ', $sSearch);

            if (!empty($this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["id"])) {
                foreach ($sSearch as $search) {
                    $search = $this->sSYSTEM->sDB_CONNECTION->qstr("%$search%");
                    $sql_add_where .= "
                            OR (
                                '{$this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["id"]}'=t.languageID
                                AND (t.name LIKE $search OR t.keywords LIKE $search)
                            )
                    ";
                }
                $sql_add_join = "
                    LEFT JOIN s_articles_translations AS t
                    ON a.id=t.articleID
                ";
            }

            $sqlFields = array("s.name", "a.name", "a.keywords", "d.ordernumber");
            $sql_search_fields .= " OR (";
            foreach ($sSearch as $sqlSearch) {
                $sql_search[] = $this->sSYSTEM->sDB_CONNECTION->qstr("%$sqlSearch%");
            }

            foreach ($sql_search as $term) {
                $sql_search_fields .= " (";
                foreach ($sqlFields as $field) {
                    $sql_search_fields .= "$field LIKE $term OR ";
                }
                $sql_search_fields .= " 1 != 1) AND ";
            }
            $sql_search_fields .= "1 = 1 ) ";

        } elseif ($mode == "supplier") {
            $sql_search_fields = "OR a.supplierID = $search";

        } elseif ($mode == "newest") {
            $sql_search_fields = "OR 1 = 1";
        }

        $sql = "
            SELECT SQL_CALC_FOUND_ROWS DISTINCT
                    a.id as id
            FROM s_categories c, s_categories c2,
                 s_articles_categories_ro ac

            JOIN s_articles AS a
                INNER JOIN s_articles_categories_ro ac
                    ON  ac.articleID = a.id
                    AND ac.categoryID = $sCategory
                INNER JOIN s_categories c
                    ON  c.id = ac.categoryID
                    AND c.active = 1

            JOIN s_articles_details AS d
            ON d.id=a.main_detail_id

            LEFT JOIN s_articles_avoid_customergroups ag
            ON ag.articleID = a.id
            AND ag.customergroupID={$this->customerGroupId}

            LEFT JOIN s_articles_supplier s
            ON s.id=a.supplierID

            $sql_add_join

            WHERE a.mode = 0
            AND a.active=1
            AND ag.articleID IS NULL
            AND (
                0
                $sql_search_fields
                $sql_add_where
            )
            ORDER BY $orderBy
            LIMIT $sLimitStart,$sLimitEnd
        ";

        $ret["sArticles"] = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll(
            $this->sSYSTEM->sCONFIG['sCACHESEARCH'],
            $sql, false,
            'search_' . $mode
        );
        $sCountArticles = $this->sSYSTEM->sDB_CONNECTION->CacheGetFoundRows();

        $numberPages = ceil($sCountArticles / $sLimitEnd);

        // Max-Value for pages (in configuration, default: 12)
        if (!empty($this->sSYSTEM->sCONFIG['sMAXPAGES']) && $this->sSYSTEM->sCONFIG['sMAXPAGES'] < $numberPages) {
            $numberPages = $this->sSYSTEM->sCONFIG['sMAXPAGES'];
        }

        // Make Array with page-structure to render in template
        $pages = array();

        $this->sSYSTEM->_GET["sSearch"] = urlencode(urldecode($this->sSYSTEM->_GET["sSearch"]));
        if ($numberPages > 1) {
            for ($i = 1; $i <= $numberPages; $i++) {
                if ($i == $sPage) {
                    $pages["numbers"][$i]["markup"] = true;
                } else {
                    $pages["numbers"][$i]["markup"] = false;
                }
                $pages["numbers"][$i]["value"] = $i;
                $pages["numbers"][$i]["link"] = $this->sSYSTEM->sCONFIG['sBASEFILE'] . Shopware()->Modules()->Core()->sBuildLink(array("sPage" => $i), false);
                $pages["numbers"][$i]["link"] = str_replace("+", " ", $pages["numbers"][$i]["link"]);

            }
            // Previous page
            if ($sPage != 1) {
                $pages["previous"] = $this->sSYSTEM->sCONFIG['sBASEFILE'] . Shopware()->Modules()->Core()->sBuildLink(array("sPage" => $sPage - 1), false);
            } else {
                $pages["previous"] = null;
            }
            // Next page
            if ($sPage != $numberPages) {
                $pages["next"] = $this->sSYSTEM->sCONFIG['sBASEFILE'] . Shopware()->Modules()->Core()->sBuildLink(array("sPage" => $sPage + 1), false);
            } else {
                $pages["next"] = null;
            }
            // First page
            $pages["first"] = $this->sSYSTEM->sCONFIG['sBASEFILE'] . Shopware()->Modules()->Core()->sBuildLink(array("sPage" => 1), false);
        }

        if (!empty($this->sSYSTEM->sCONFIG['sNUMBERARTICLESTOSHOW'])) {
            $this->sSYSTEM->sExtractor[] = "sPerPage";
            // Load possible values from config
            $arrayArticlesToShow = explode("|", $this->sSYSTEM->sCONFIG['sNUMBERARTICLESTOSHOW']);

            // Iterate through values and building array for smarty
            foreach ($arrayArticlesToShow as $articlesToShowKey => $articlesToShowValue) {
                // Delete previous data
                $arrayArticlesToShow[$articlesToShowKey] = array();
                // Setting value
                $arrayArticlesToShow[$articlesToShowKey]["value"] = $articlesToShowValue;
                // Setting markup for currently chosen value
                if ($articlesToShowValue == $sLimitEnd) {
                    $arrayArticlesToShow[$articlesToShowKey]["markup"] = true;
                } else {
                    $arrayArticlesToShow[$articlesToShowKey]["markup"] = false;
                }
                // Building link
                $arrayArticlesToShow[$articlesToShowKey]["link"] = $this->sSYSTEM->sCONFIG['sBASEFILE'] . Shopware()->Modules()->Core()->sBuildLink(array("sPerPage" => $articlesToShowValue), false) . "";
                //echo $arrayArticlesToShow[$articlesToShowKey]["link"]."<br />";
            } // -- for every possible value
        } // -- Building array
        $ret['sPages'] = $pages;
        $ret['sPerPage'] = $arrayArticlesToShow;
        $ret['sNumberArticles'] = $sCountArticles;
        $ret['sNumberPages'] = $numberPages;

        return $ret;
    }

    /**
     * @return bool
     */
    private function isHttpCacheActive()
    {
        $httpCache = Shopware()->Plugins()->Core()->HttpCache();
        if (!$httpCache instanceof Shopware_Components_Plugin_Bootstrap) {
            return false;
        }

        /**@var $plugin \Shopware\Models\Plugin\Plugin */
        $plugin = Shopware()->Models()->find('\Shopware\Models\Plugin\Plugin', $httpCache->getId());

        return $plugin->getActive() && $plugin->getInstalled();
    }

    /**
     * Get all articles from a specific category
     *
     * @param int $categoryId category id
     * @return array
     */
    public function sGetArticlesByCategory($categoryId = null)
    {
        if (Enlight()->Events()->notifyUntil('Shopware_Modules_Articles_sGetArticlesByCategory_Start', array(
                'subject' => $this,
                'id'      => $categoryId
            ))) {
            return false;
        }

        $result = $this->getListing($categoryId);

        $result = $this->legacyEventManager->fireArticlesByCategoryEvents($result, $categoryId, $this);

        return $result;
    }


    /**
     * Get supplier by id
     *
     * Uses the new Supplier Manager
     *
     * TestCase: /_tests/Shopware/Tests/Modules/Articles/SuppliersTest.php
     *
     * @param int $id - s_articles_supplier.id
     * @return array
     */
    public function sGetSupplierById($id)
    {
        $id = (int) $id;
        $categoryId = (int) $this->sSYSTEM->_GET['sCategory'];

        $supplierRepository = Shopware()->Models()->getRepository(
            'Shopware\Models\Article\Supplier'
        );
        $supplier = $supplierRepository->find($id);
        if (!is_object($supplier)) {
            return array();
        }
        $supplier = Shopware()->Models()->toArray($supplier);
        if (!Shopware()->Shop()->getDefault()) {
            $supplier = $this->sGetTranslation($supplier, $supplier['id'], 'supplier');
        }
        $supplier['link'] = $this->sSYSTEM->sCONFIG['sBASEFILE'];
        $supplier['link'] .= '?sViewport=cat&sCategory=' . $categoryId . '&sPage=1&sSupplier=0';

        return $supplier;
    }

    /**
     * Helper function which checks the configuration if the article count
     * should be displayed for each filter value.
     *
     * @return bool
     */
    protected function displayFilterArticleCount()
    {
        return Shopware()->Config()->get('displayFilterArticleCount', true);
    }

    /**
     * Helper function to add the already activated filter values.
     * This function adds an inner join condition for each passed value.
     * The join will be set on the s_filter_articles table. The table alias
     * for each passed value is "filterArticles" + ValueID.
     * The function expects the s_articles table with the alias "articles" to
     * join the s_filter_articles over the articleID column.
     *
     * @param $builder QueryBuilder
     * @param $activeFilters
     * @return QueryBuilder
     */
    protected function addActiveFilterCondition(QueryBuilder $builder, $activeFilters)
    {
        foreach ($activeFilters as $valueId) {
            if ($valueId <= 0) {
                continue;
            }
            $alias = 'filterArticles' . $valueId;
            $builder->innerJoin('articles', 's_filter_articles', $alias, $alias . '.articleID = articles.id AND ' . $alias . '.valueID = ' . (int) $valueId);
        }
        return $builder;
    }

    /**
     * Helper function which creates a sql statement
     * to select all filters with their associated options and values.
     * This query is used to select all category filters.
     *
     * The query contains the following joins/aliases:
     *  - s_filter_values           => filterValues (FROM Table)
     *  - s_filter_articles         => filterArticles
     *  - s_articles_categories_ro  => articleCategories
     *  - s_articles                => articles
     *  - s_filter_options          => filterOptions
     *  - s_filter_relations        => filterRelations
     *  - s_filter                  => filters
     *  - s_articles_attributes     => attributes
     *  - s_articles_avoid_customergroups     => avoidGroups
     *
     * If the parameter $activeFilters isn't empty, the query builder
     * use a group by condition for the filterValues.id (s_filter_values.id).
     * This condition is required to select the assigned article count
     * faster.
     *
     * In case that the parameter $activeFilters is empty, the query builder
     * use a sub query to select the article count for each filter value.
     * Additional the query builder use a DISTINCT condition to prevent duplicate
     * items, which creates over the different N:M Associations like
     * s_filter_values : s_filter_articles or s_filter_articles : s_articles_categories_ro
     *
     * The query builder contains two parameters which has to be set from outside:
     *  -   :categoryId         => ID of the current category to select the category articles
     *  -   :customerGroupId    => ID of the current customer group to prevent avoided customer groups of an article.
     *
     * To set this parameter you can use the "$builder->setParameters" or "$builder->setParameter" function:
     * <php>
     * $builder->setParameters(array(
     *      ':categoryId'       => $categoryId
     *      ':customerGroupId'  => $customerGroupId
     * ));
     * </php>
     *
     * Shopware Events:
     *  -   Shopware_Modules_Articles_GetFilterQuery
     *
     * @param null $activeFilters
     * @return QueryBuilder
     */
    protected function getFilterQuery($activeFilters = null)
    {
        /**@var $builder QueryBuilder */
        $builder = Shopware()->Models()->getDBALQueryBuilder();

        $builder->select(array(
            'filterValues.optionID    as id',
            'filterValues.optionID    as optionID',
            'filterOptions.name       as optionName',
            'filterRelations.position as optionPosition',
            'filterValues.id          as valueID',
            'filterValues.value       as optionValue',
            'filterValues.position    as valuePosition',
            'filterRelations.groupID  as groupID',
            'filters.name             as groupName'
        ));

        $builder = $this->addArticleCountSelect($builder);

        //use as base table the s_filter_values
        $builder->from('s_filter_values', 'filterValues');

        //join the s_filter_articles to get add an additional join condition for the category articles.
        $builder->innerJoin(
            'filterValues',
            's_filter_articles',
            'filterArticles',
            'filterArticles.valueID = filterValues.id'
        );

        //join the s_articles_categories_ro to get only the filter configuration for the current category articles
        $builder->innerJoin(
            'filterArticles',
            's_articles_categories_ro',
            'articleCategories',
            "articleCategories.articleID = filterArticles.articleID AND articleCategories.categoryID = :categoryId"
        );

        //at least we add the condition to select only the active articles
        $builder->innerJoin(
            'filterArticles',
            's_articles',
            'articles',
            'articles.id = filterArticles.articleID AND articles.active = 1 AND articles.id = articleCategories.articleID'
        );

        //to get the filter option name, it is required to join the s_filter_options. The options can be configured with
        //an filterable flag.
        $builder->innerJoin(
            'filterValues',
            's_filter_options',
            'filterOptions',
            'filterValues.optionID = filterOptions.id AND filterOptions.filterable = 1'
        );

        //the filter relations table contains the data which filter options is assigned to which filter group.
        $builder->innerJoin(
            'filterOptions',
            's_filter_relations',
            'filterRelations',
            'filterRelations.groupID = articles.filtergroupID AND filterRelations.optionID = filterOptions.id'
        );

        //now we can select the s_filter to get the group name.
        $builder->innerJoin(
            'filterRelations',
            's_filter',
            'filters',
            'filters.id = filterRelations.groupID'
        );

        //at least we add the s_articles_avoid_customergroups and s_articles_attributes to prevent
        //inconsistent article selections.
        $builder->leftJoin(
            'articles',
            's_articles_avoid_customergroups',
            'avoidGroups',
            "avoidGroups.articleID = articles.id AND avoidGroups.customergroupID = :customerGroupId"
        );

        $builder->innerJoin(
            'articles',
            's_articles_attributes',
            'attributes',
            'articles.id = attributes.articleID'
        );

        $builder->andWhere('avoidGroups.articleID IS NULL');

        $builder = Shopware()->Events()->filter(
            'Shopware_Modules_Articles_GetFilterQuery',
            $builder,
            array(
                'subject' => $this,
                'activeFilters' => $activeFilters
            )
        );

        return $builder;
    }

    /**
     * Helper function to add the article count select for the filter queries.
     *
     * @param $builder QueryBuilder
     * @return QueryBuilder
     */
    protected function addArticleCountSelect(QueryBuilder $builder)
    {
        $builder->groupBy('filterValues.id');

        if (!$this->displayFilterArticleCount()) {
            return $builder;
        }

        $builder->addSelect('COUNT(DISTINCT articles.id) as articleCount');

        return $builder;
    }

    /**
     * Helper function to add the translation join and select condition
     * for the article filters. This function expects the following aliases:
     *  - s_filter         = filters
     *  - s_filter_values  = filterValues
     *  - s_filter_options = filterOptions
     *
     * @param $builder QueryBuilder
     * @param $translationId
     * @return QueryBuilder
     */
    protected function addFilterTranslation(QueryBuilder $builder, $translationId)
    {
        $builder->addSelect(array(
            'valueTranslation.objectdata AS valueTranslation',
            'optionTranslation.objectdata AS optionNameTranslation',
            'groupTranslation.objectdata AS groupNameTranslation'
        ));

        $builder->leftJoin(
            'filterValues',
            's_core_translations',
            'valueTranslation',
            "valueTranslation.objecttype = :valueType
             AND valueTranslation.objectkey = filterValues.id
             AND valueTranslation.objectlanguage = :translationId"
        );

        $builder->leftJoin(
            'filterOptions',
            's_core_translations',
            'optionTranslation',
            "optionTranslation.objecttype = :optionType
             AND optionTranslation.objectkey = filterOptions.id
             AND optionTranslation.objectlanguage = :translationId"
        );

        $builder->leftJoin(
            'filters',
            's_core_translations',
            'groupTranslation',
            "groupTranslation.objecttype = :groupType
             AND groupTranslation.objectkey = filters.id
             AND groupTranslation.objectlanguage = :translationId"
        );

        $builder->setParameter(':translationId', $translationId);
        $builder->setParameter(':groupType', 'propertygroup');
        $builder->setParameter(':optionType', 'propertyoption');
        $builder->setParameter(':valueType', 'propertyvalue');

        return $builder;
    }

    /**
     * This function returns all category filters in the correct sort order.
     * The returned array contains all filter groups, filter options and filter values
     * which configured for the category articles of the passed category id.
     *
     * The $activeFilters parameter can contains the already activated filter value
     * ids as flat array.
     * The already activated filter values will be added as join condition from the s_articles
     * on the s_filter_articles with the corresponding value id.
     *
     * Notice: If the system contains many filter values, it is required to increase the
     * max join configuration parameter of the sql server.
     *
     * The absolute max join limit of a 5.* mysql server is set to 61 join tables!
     *
     * Shopware Events:
     *  - Shopware_Modules_Article_GetCategoryFilters
     *
     * @param $categoryId
     * @param null $activeFilters
     * @return array
     */
    public function getCategoryFilters($categoryId, $activeFilters = null)
    {
        $builder = $this->getFilterQuery($activeFilters);
        $builder = $this->addActiveFilterCondition($builder, $activeFilters);
        $sortMode = $this->getFilterSortMode($categoryId, $this->customerGroupId, $activeFilters);

        $builder->addOrderBy('filterRelations.position');
        $builder->addOrderBy('filterOptions.name');

        switch ($sortMode) {
            case self::FILTERS_SORT_ALPHANUMERIC:
                $builder->addOrderBy('filterValues.value');
                break;

            case self::FILTERS_SORT_NUMERIC:
                $builder->addOrderBy('filterValues.value_numeric');
                break;

            case self::FILTERS_SORT_ARTICLE_COUNT:
                if ($this->displayFilterArticleCount()) {
                    $builder->addOrderBy('articleCount', 'DESC');
                } else {
                    $builder->addOrderBy('filterValues.position');
                }
                break;

            case self::FILTERS_SORT_POSITION:
            default:
                $builder->addOrderBy('filterValues.position');
                break;
        }
        $builder->addOrderBy('filterValues.id');

        if ($this->translationId !== null) {
            $builder = $this->addFilterTranslation($builder, $this->translationId);
        }

        $builder->setParameter(':customerGroupId', (int) $this->customerGroupId);
        $builder->setParameter(':categoryId', (int) $categoryId);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $builder->execute();

        $filters = $statement->fetchAll(PDO::FETCH_ASSOC);

        $filters = Shopware()->Events()->filter(
            'Shopware_Modules_Article_GetCategoryFilters',
            $filters,
            array(
                'subject' => $this,
                'category' => $categoryId,
                'activeFilters' => $activeFilters
            )
        );

        return $filters;
    }

    /**
     * Helper function to get the sort mode condition for the passed category id.
     * This function selects all filter group ids of the assigned category articles for the
     * passed category id.
     * In case that more than one filter group is assigned, the function returns
     * the config sort mode for filters.
     * If only one filter group id founded, the function returns the sort mode for this
     * filter group.
     *
     * Shopware Events:
     *  -   Shopware_Modules_Article_GetFilterSortMode
     *
     * @param $categoryId
     * @param $customerGroupId
     * @param null $activeFilters
     * @return int|null
     */
    protected function getFilterSortMode($categoryId, $customerGroupId, $activeFilters = null)
    {
        $builder = Shopware()->Models()->getDBALQueryBuilder();
        $builder->select(array('DISTINCT articles.filtergroupID', 'filters.sortmode'));

        $builder->from('s_articles', 'articles');
        $builder->innerJoin(
            'articles',
            's_articles_categories_ro',
            'articleCategories',
            "articleCategories.articleID = articles.id AND articleCategories.categoryID = :categoryId"
        );
        $builder->leftJoin(
            'articles',
            's_articles_avoid_customergroups',
            'avoidGroups',
            "avoidGroups.articleID = articles.id AND avoidGroups.customergroupID = :customerGroupId"
        );
        $builder->innerJoin(
            'articles',
            's_articles_attributes',
            'attributes',
            'articles.id = attributes.articleID'
        );
        $builder->innerJoin(
            'articles',
            's_filter',
            'filters',
            'filters.id = articles.filtergroupID'
        );

        $builder->where('articles.active = 1');
        $builder->andWhere('articles.filtergroupID IS NOT NULL');
        $builder->andWhere('avoidGroups.articleID IS NULL');

        $builder = $this->addActiveFilterCondition($builder, $activeFilters);

        $builder->setParameter('customerGroupId', $customerGroupId);
        $builder->setParameter('categoryId', $categoryId);

        /**@var $statement Doctrine\DBAL\Driver\Statement*/
        $statement = $builder->execute();

        $filterIds = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (count($filterIds) > 1) {
            $sortMode = Shopware()->Config()->get('defaultFilterSort', self::FILTERS_SORT_POSITION);
        } elseif (count($filterIds) === 1) {
            $sortMode = $filterIds[0]['sortmode'];
        } else {
            $sortMode = self::FILTERS_SORT_POSITION;
        }

        $sortMode = Shopware()->Events()->filter(
            'Shopware_Modules_Article_GetFilterSortMode',
            $sortMode,
            array(
                'subject' => $this,
                'category' => $categoryId,
                'activeFilters' => $activeFilters
            )
        );
        return $sortMode;
    }

    public function sGetCategoryProperties($categoryId = null, $supplierId = null, $activeFilters = null)
    {
        if ($categoryId === null
            && !empty($this->sSYSTEM->_GET["sCategory"])
        ) {
            $categoryId = $this->sSYSTEM->_GET["sCategory"];
        }
        if ($activeFilters === null
            && !empty($this->sSYSTEM->_GET["sFilterProperties"])
        ) {
            $activeFilters = preg_split('/\|/', $this->sSYSTEM->_GET["sFilterProperties"], -1, PREG_SPLIT_NO_EMPTY);
        }

        $categoryId = (int) $categoryId;
        $supplierId = (int) $supplierId;
        $activeFilters = (array) $activeFilters;

        if ($categoryId != Shopware()->Shop()->getCategory()->getId()) {
            $getProperties = $this->getCategoryFilters($categoryId, $activeFilters, $supplierId);
        } else {
            $getProperties = array();
        }


        if (!empty($this->sSYSTEM->_GET["sViewport"]) && $this->sSYSTEM->_GET["sViewport"] == 'supplier' && $supplierId) {
            $baseLink = $this->sSYSTEM->sCONFIG['sBASEFILE']
                . '?sViewport=supplier&sSupplier=' . $supplierId;
            if ($categoryId !== Shopware()->Shop()->getCategory()->getId()) {
                $baseLink .= '&sCategory=' . $categoryId;
            }
        } else {
            $baseLink = $this->sSYSTEM->sCONFIG['sBASEFILE']
                . '?sViewport=cat&sCategory=' . $categoryId . '&sPage=1';
        }

        $propertyArray = array();

        $propertyValues = array();

        foreach ($getProperties as $property) {
            if (!empty($property['optionNameTranslation'])) {
                $translation = unserialize($property['optionNameTranslation']);
                $property['optionName'] = $translation['optionName'];
            }
            if (!empty($property['groupNameTranslation'])) {
                $translation = unserialize($property['groupNameTranslation']);
                $property['groupName'] = $translation['groupName'];
            }
            if (!empty($property['valueTranslation'])) {
                $translation = unserialize($property['valueTranslation']);
                $property['optionValue'] = $translation['optionValue'];
            }

            $propertyValues[$property['optionID']][] = $property['valueID'];
            $filters = $activeFilters;
            $filters[] = $property['valueID'];
            $link = $baseLink . '&sFilterProperties=' . implode('|', $filters);

            if (empty($lastOptionId) || $lastOptionId != $property['optionID']) {
                //only set the default remove link once per option group like color
                $removeLink = $baseLink . '&sFilterProperties=0';
            }
            $lastOptionId = $property['optionID'];

            if (!empty($activeFilters)
                && in_array($property['valueID'], $activeFilters)
            ) {
                $filters = array_diff($activeFilters, $propertyValues[$property['optionID']]);
                if (empty($filters)) {
                    $filters[] = '0';
                }
                $removeLink = $baseLink . '&sFilterProperties=' . implode('|', $filters);
                $optionValueActive = true;
            } else {
                $optionValueActive = false;
            }

            if (!empty($propertyArray['filterOptions']['optionsOnly'][$property['optionName']]['properties']['active'])) {
                $optionGroupActive = true;
            } else {
                $optionGroupActive = $optionValueActive;
            }
            $propertyArray['filterOptions']['optionsOnly']
            [$property['optionName']]['properties'] = array(
                'active' => $optionGroupActive,
                'linkRemoveProperty' => $removeLink,
                'group' => $property['groupName']
            );

            $propertyArray['filterOptions']['optionsOnly']
            [$property['optionName']]['values']
            [$property['optionValue']] = array(
                'name' => $property['optionName'],
                'value' => $property['optionValue'],
                'valueTranslation' => null,
                'count' => $property['articleCount'],
                'group' => $property['groupName'],
                'optionID' => $property['id'],
                'link' => $link,
                'filter' => $filters,
                'active' => $optionValueActive
            );

            $propertyArray['filterOptions']['grouped']
            [$property['groupName']]['options']
            [$property['optionName']]
            [$property['optionValue']] = array(
                'name' => $property['optionName'],
                'value' => $property['optionValue'],
                'count' => $property['articleCount'],
                'group' => $property['groupName'],
                'optionID' => $property['id']
            );
            $propertyArray["filterOptions"]["grouped"][$property["groupName"]]["default"]["linkSelect"] = $this->sSYSTEM->sCONFIG['sBASEFILE'].Shopware()->Modules()->Core()->sBuildLink(array("sFilterGroup"=>$property["groupName"],false));
        }

        return $propertyArray;
    }

    /**
     * Get all available suppliers from a specific category
     * @param int $id - category id
     * @return array
     */
    public function sGetAffectedSuppliers($id = null, $limit = null)
    {
        $id = empty($id) ? (int) $this->sSYSTEM->_GET["sCategory"] : (int) $id;
        $configLimit = $this->sSYSTEM->sCONFIG['sMAXSUPPLIERSCATEGORY'] ? $this->sSYSTEM->sCONFIG['sMAXSUPPLIERSCATEGORY'] : 30;
        $limit = empty($limit) ? $configLimit : (int) $limit;

        $sql = "
            SELECT s.id AS id, COUNT(DISTINCT a.id) AS countSuppliers, s.name AS name, s.img AS image
            FROM s_articles a
                INNER JOIN s_articles_categories_ro ac
                    ON  ac.articleID = a.id
                    AND ac.categoryID = ?
                INNER JOIN s_categories c
                    ON  c.id = ac.categoryID
                    AND c.active = 1

            JOIN s_articles_supplier s
            ON s.id=a.supplierID

            LEFT JOIN s_articles_avoid_customergroups ag
            ON ag.articleID=a.id
            AND ag.customergroupID={$this->customerGroupId}

            WHERE ag.articleID IS NULL
            AND a.active = 1

            GROUP BY s.id
            ORDER BY s.name ASC
            LIMIT 0, $limit
        ";
        $getSupplier = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHESUPPLIER'], $sql, array(
            $id
        ));

        foreach ($getSupplier as $supplierKey => $supplierValue) {
            if (!Shopware()->Shop()->getDefault()) {
                $getSupplier[$supplierKey] = $this->sGetTranslation($supplierValue, $supplierValue['id'], 'supplier');
            }
            if ($supplierValue["image"]) {
                $getSupplier[$supplierKey]["image"] = $supplierValue["image"];
            }


            if ($id !== Shopware()->Shop()->getCategory()->getId()) {
                $query = array(
                    'sViewport' => 'cat',
                    'sCategory' => $id,
                    'sPage' => 1,
                    'sSupplier' => $supplierValue["id"]
                );
            } else {
                $query = array(
                    'sViewport' => 'supplier',
                    'sSupplier' => $supplierValue["id"]
                );
            }

            $getSupplier[$supplierKey]["link"] = Shopware()->Router()->assemble($query);
        }

        return $getSupplier;
    }

    /**
     * Article price calucation
     * @param double $price
     * @param double $tax
     * @param array $article article data as an array
     * @return double $price formated price
     */
    public function sCalculatingPrice($price, $tax, $taxId = 0, $article = array())
    {
        if (empty($taxId)) {
            throw new Enlight_Exception("Empty taxID in sCalculatingPrice");
        }

        $price = (float) $price;

        // Support tax rate defined by certain conditions
        $getTaxByConditions = $this->getTaxRateByConditions($taxId);
        if ($getTaxByConditions === false) $tax = (float) $tax; else $tax = (float) $getTaxByConditions;

        // Calculate global discount
        if ($this->sSYSTEM->sUSERGROUPDATA["mode"] && $this->sSYSTEM->sUSERGROUPDATA["discount"]) {
            $price = $price - ($price / 100 * $this->sSYSTEM->sUSERGROUPDATA["discount"]);
        }
        if ($this->sSYSTEM->sCurrency["factor"]) {
            $price = $price * floatval($this->sSYSTEM->sCurrency["factor"]);
        }

        // Condition Output-Netto AND NOT overwrite by customer-group
        // OR Output-Netto NOT SET AND tax-settings provided by customer-group
        if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])) {
            $price = $this->sFormatPrice($price);
        } else {
            //if ($price > 100)         die(round($price * (100 + $tax) / 100, 5));
            $price = $this->sFormatPrice(round($price * (100 + $tax) / 100, 3));
        }
        return $price;
    }

    /**
     * @param $taxId
     * @return mixed
     */
    public function getTaxRateByConditions($taxId)
    {
        static $result = array();
        if (!empty($result[$taxId])) {
            return $result[$taxId];
        }

        $sql = "
        SELECT id,tax FROM s_core_tax_rules WHERE
            active = 1 AND groupID = ?
        AND
            (areaID = ? OR areaID IS NULL)
        AND
            (countryID = ? OR countryID IS NULL)
        AND
            (stateID = ? OR stateID IS NULL)
        AND
            (customer_groupID = ? OR customer_groupID = 0 OR customer_groupID IS NULL)
        ORDER BY customer_groupID DESC, areaID DESC, countryID DESC, stateID DESC
        LIMIT 1
        ";

        $areaId = Shopware()->Session()->sArea;
        $countryId = Shopware()->Session()->sCountry;
        $stateId = Shopware()->Session()->sState;
        $customerGroupId = $this->sSYSTEM->sUSERGROUPDATA["id"];

        $parameters = array($taxId,$areaId,$countryId,$stateId,$customerGroupId);

        $getTax = Shopware()->Db()->fetchRow($sql,$parameters);

        if (empty($getTax["id"])) {
            $getTax["tax"] = Shopware()->Db()->fetchOne("SELECT tax FROM s_core_tax WHERE id = ?",array($taxId));
        }

        $result[$taxId] = $getTax["tax"];

        /*$params = (Shopware()->Db()->getProfiler()->getLastQueryProfile()->getQueryParams());
        $query = (Shopware()->Db()->getProfiler()->getLastQueryProfile()->getQuery());
        foreach ($params as $par) {
            $query = preg_replace('/\\?/', "'" . $par . "'", $query, 1);
        }*/
        return $result[$taxId];
    }

    /**
     * Article price calucation unformated return
     * @param double $price
     * @param double $tax
     * @param bool $considerTax
     * @param bool $donotround
     * @param array $article article data as an array
     * @param bool $ignoreCurrency
     * @return double $price  price unformated
     */
    public function sCalculatingPriceNum($price, $tax, $doNotRound = false, $ignoreTax = false, $taxId = 0, $ignoreCurrency = false, $article = array())
    {
        if (empty($taxId)) {
            throw new Enlight_Exception ("Empty tax id in sCalculatingPriceNum");
        }
        // Calculating global discount
        if ($this->sSYSTEM->sUSERGROUPDATA["mode"] && $this->sSYSTEM->sUSERGROUPDATA["discount"]) {
            $price = $price - ($price / 100 * $this->sSYSTEM->sUSERGROUPDATA["discount"]);
        }

        // Support tax rate defined by certain conditions
        $getTaxByConditions = $this->getTaxRateByConditions($taxId);
        if ($getTaxByConditions===false) $tax = (float) $tax; else $tax = (float) $getTaxByConditions;

        if (!empty($this->sSYSTEM->sCurrency["factor"]) && $ignoreCurrency == false) {
            $price = floatval($price) * floatval($this->sSYSTEM->sCurrency["factor"]);
        }

        if ($ignoreTax == true)  return round($price,2);

        // Show brutto or netto ?
        // Condition Output-Netto AND NOT overwrite by customer-group
        // OR Output-Netto NOT SET AND tax-settings provided by customer-group
        if ($doNotRound == true) {
            if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])) {

            } else {
                $price = $price * (100 + $tax) / 100;
            }
        } else {
            if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])) {
                $price = round($price, 3);
            } else {
                $price = round($price * (100 + $tax) / 100, 3);
            }
        }

        return $price;

    }

    /**
     * Get article topsellers for a specific category
     * @param $category int category id
     * @return array
     */
    public function sGetArticleCharts($category = null)
    {
        $sLimitChart = $this->sSYSTEM->sCONFIG['sCHARTRANGE'];
        if (empty($sLimitChart)) {
            $sLimitChart = 20;
        }
        if (!empty($category)) {
            $category = (int) $category;
        } elseif (!empty($this->sSYSTEM->_GET['sCategory'])) {
            $category = (int) $this->sSYSTEM->_GET['sCategory'];
        } else {
            $category = $this->categoryId;
        }

        $sql = "
            SELECT STRAIGHT_JOIN DISTINCT
              a.id AS articleID,
              s.sales AS quantity
            FROM s_articles_top_seller_ro s
            INNER JOIN s_articles_categories_ro ac
              ON  ac.articleID = s.article_id
              AND ac.categoryID = :categoryId
            INNER JOIN s_categories c
              ON  ac.categoryID = c.id
              AND c.active = 1
            INNER JOIN s_articles a
              ON  a.id = s.article_id
              AND a.active = 1

            LEFT JOIN s_articles_avoid_customergroups ag
              ON ag.articleID=a.id
              AND ag.customergroupID = :customerGroupId

            INNER JOIN s_articles_details d
              ON d.id = a.main_detail_id
              AND d.active = 1

            INNER JOIN s_articles_attributes at
              ON at.articleID=a.id

            INNER JOIN s_core_tax t
              ON t.id = a.taxID

            WHERE ag.articleID IS NULL
            ORDER BY s.sales DESC, s.article_id DESC
            LIMIT $sLimitChart
        ";


        $queryChart = Shopware()->Db()->fetchAssoc($sql, array(
            'categoryId'      => $category,
            'customerGroupId' => $this->customerGroupId
        ));

        $articles = array();
        if (!empty($queryChart)) {
            foreach ($queryChart as $articleID => $quantity) {
                $article = $this->sGetPromotionById('fix', 0, (int) $articleID);
                if (!empty($article["articleID"])) {
                    $article['quantity'] = $quantity;
                    $articles[] = $article;
                }
            }
        }

        Enlight()->Events()->notify(
            'Shopware_Modules_Articles_GetArticleCharts',
            array('subject' => $this, 'category' => $category, 'articles' => $articles)
        );

        return $articles;
    }

    /**
     * Check if an article has instant download
     * @param int $id s_articles.id
     * @param int $detailsID s_articles_details.id
     * @param bool $realtime deprecated
     * @return bool
     */
    public function sCheckIfEsd($id, $detailsID, $realtime = false)
    {
        // Check if this article is esd-only (check in variants, too -> later)

        if ($detailsID) {
            $sqlGetEsd = "
            SELECT id, serials FROM s_articles_esd WHERE articleID=$id
            AND articledetailsID=$detailsID
            ";
        } else {
            $sqlGetEsd = "
            SELECT id, serials FROM s_articles_esd WHERE articleID=$id
            ";
        }

        $getEsd = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($realtime == true ? 0 : $this->sSYSTEM->sCONFIG['sCACHEARTICLE'], $sqlGetEsd);
        if (!empty($getEsd["id"])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Read the id from all articles that are in the same category as the article specified by parameter (For article navigation in top of detailpage)
     *
     * @param int $article s_articles.id
     * @param null $categoryId
     * @return array
     */
    public function sGetAllArticlesInCategory($article, $categoryId = null)
    {
        $article = (int) $article;

        if ($categoryId === null) {
            $categoryId = (int) $this->sSYSTEM->_GET['sCategory'];
        }

        if (empty($categoryId)) {
            return;
        }

        if (!$this->isHttpCacheActive() && isset($this->sSYSTEM->_SESSION['sCategoryConfig' . $categoryId])) {
            $sCategoryConfig = $this->sSYSTEM->_SESSION['sCategoryConfig' . $categoryId];
        } else {
            $sCategoryConfig = array();
        }

        // Order List by
        if (isset($this->sSYSTEM->_POST['sSort'])) {
            $sCategoryConfig['sSort'] = (int) $this->sSYSTEM->_POST['sSort'];
        } elseif (!empty($this->sSYSTEM->_GET['sSort'])) {
            $sCategoryConfig['sSort'] = (int) $this->sSYSTEM->_GET['sSort'];
        }
        if (!empty($sCategoryConfig['sSort'])) {
            $this->sSYSTEM->_POST['sSort'] = $sCategoryConfig['sSort'];
        }

        $joinImpressions = '';
        switch ($this->sSYSTEM->_POST['sSort']) {
            case 1:
                $orderBy = "a.datum DESC, a.changetime DESC, a.id DESC";
                break;
            case 2:
                $orderBy = "aDetails.sales DESC, sai.impressions DESC, aDetails.articleID DESC";
                $joinImpressions = "LEFT JOIN s_statistics_article_impression sai ON a.id = sai.articleId";
                break;
            case 3:
                $orderBy = "price ASC, a.id";
                break;
            case 4:
                $orderBy = "price DESC, a.id DESC";
                break;
            case 5:
                $orderBy = "articleName ASC, a.id";
                break;
            case 6:
                $orderBy = "articleName DESC, a.id DESC";
                break;
            default:
                $orderBy = $this->sSYSTEM->sCONFIG['sORDERBYDEFAULT'] . ', a.id DESC';
        }

        if (strpos($orderBy, 'price') !== false) {
            $select_price = "
                (
                    SELECT IFNULL(p.price, p2.price) as min_price
                    FROM s_articles_details d

                    LEFT JOIN s_articles_prices p
                    ON p.articleDetailsID=d.id
                    AND p.pricegroup='{$this->sSYSTEM->sUSERGROUP}'
                    AND p.to='beliebig'

                    LEFT JOIN s_articles_prices p2
                    ON p2.articledetailsID=d.id
                    AND p2.pricegroup='EK'
                    AND p2.to='beliebig'

                    WHERE d.articleID=a.id

                    ORDER BY min_price
                    LIMIT 1
                ) * ((100 - IFNULL(cd.discount, 0)) / 100)
            ";
            $join_price = "
                LEFT JOIN s_core_customergroups cg
                ON cg.groupkey = '{$this->sSYSTEM->sUSERGROUP}'

                LEFT JOIN s_core_pricegroups_discounts cd
                ON a.pricegroupActive=1
                AND cd.groupID=a.pricegroupID
                AND cd.customergroupID=cg.id
                AND cd.discountstart=(
                    SELECT MAX(discountstart)
                    FROM s_core_pricegroups_discounts
                    WHERE groupID=a.pricegroupID
                    AND customergroupID=cg.id
                )
            ";
        } else {
            $select_price = '0';
            $join_price = '';
        }

        $sql = "
            SELECT a.id, name AS articleName,
                $select_price as price

            FROM s_articles a
                INNER JOIN s_articles_categories_ro ac
                    ON  ac.articleID = a.id
                    AND ac.categoryID = $categoryId
                INNER JOIN s_categories c
                    ON  c.id = ac.categoryID
                    AND c.active = 1

            JOIN s_articles_details AS aDetails
            ON aDetails.articleID=a.id AND aDetails.kind=1

            $joinImpressions

            JOIN s_articles_attributes AS aAttributes
            ON aAttributes.articledetailsID = aDetails.id

            LEFT JOIN s_articles_avoid_customergroups ag
            ON ag.articleID=ac.articleID
            AND ag.customergroupID={$this->customerGroupId}

            $join_price

            WHERE a.active=1
            AND ag.articleID IS NULL

            GROUP BY a.id
            ORDER BY $orderBy
        ";

        $getAllArticles = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll(
            $this->sSYSTEM->sCONFIG['sCACHECATEGORY'], $sql,
            false, "category_" . $categoryId
        );

        // Get articles position and previous, next article
        if (!empty($getAllArticles))
            foreach ($getAllArticles as $allArticlesKey => $allArticlesValue) {
                if ($allArticlesValue["id"] == $article) {
                    if ($getAllArticles[$allArticlesKey - 1]["id"]) {
                        // Previous article
                        $sNavigation["sPrevious"]["id"] = $getAllArticles[$allArticlesKey - 1]["id"];
                        $sNavigation["sPrevious"]["link"] = $this->sSYSTEM->sCONFIG['sBASEFILE'] . "?sViewport=detail&sDetails=" . $sNavigation["sPrevious"]["id"] . "&sCategory=" . $categoryId;
                        $sNavigation["sPrevious"]["name"] = $getAllArticles[$allArticlesKey - 1]["articleName"];

                    }
                    if ($getAllArticles[$allArticlesKey + 1]["id"]) {
                        // Next article
                        $sNavigation["sNext"]["id"] = $getAllArticles[$allArticlesKey + 1]["id"];
                        $sNavigation["sNext"]["link"] = $this->sSYSTEM->sCONFIG['sBASEFILE'] . "?sViewport=detail&sDetails=" . $sNavigation["sNext"]["id"] . "&sCategory=" . $categoryId;
                        $sNavigation["sNext"]["name"] = $getAllArticles[$allArticlesKey + 1]["articleName"];
                    }
                    $sNavigation["sCurrent"]["position"] = $allArticlesKey+1;
                    $sNavigation["sCurrent"]["count"] = count($getAllArticles);
                    $sNavigation["sCurrent"]["sCategory"] = $this->sSYSTEM->_GET["sCategory"];
                    $sNavigation["sCurrent"]["sCategoryLink"] = $this->sSYSTEM->sCONFIG['sBASEFILE'] . "?sViewport=cat&sCategory=" . $categoryId;
                    $getCategoryName = $this->sSYSTEM->sMODULES["sCategories"]->sGetCategoryContent($categoryId);
                    $sNavigation["sCurrent"]["sCategoryName"] = $getCategoryName["description"];
                }
            }

        return $sNavigation;
    }

    /**
     * Get translations for multidimensional groups and options for a certain article
     * @param int $id - s_articles.id
     * @return array
     */
    public function sGetArticleConfigTranslation($id)
    {
        if (empty($this->translationId)) {
            return array();
        }
        $sql = 'SELECT objectdata FROM s_core_translations WHERE objecttype=? AND objectkey=? AND objectlanguage=?';
        $data = array('configuratorgroup', $id, $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]);
        $getGroupTranslations = $this->sSYSTEM->sDB_CONNECTION->CacheGetOne($this->sSYSTEM->sCONFIG['sCACHEARTICLE'], $sql, $data);
        $getGroupTranslations = unserialize($getGroupTranslations);

        $sql = 'SELECT objectdata FROM s_core_translations WHERE objecttype=? AND objectkey=? AND objectlanguage=?';
        $data = array('configuratoroption', $id, $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"]);
        $getOptionTranslations = $this->sSYSTEM->sDB_CONNECTION->CacheGetOne($this->sSYSTEM->sCONFIG['sCACHEARTICLE'], $sql, $data);
        $getOptionTranslations = unserialize($getOptionTranslations);

        return array("options" => $getOptionTranslations, "groups" => $getGroupTranslations);

    }

    /**
     * Checks if a certain article is multidimensional configurable
     * @param int $id s_articles.id
     * @param bool $realtime deprecated
     * @return bool
     */
    public function sCheckIfConfig($id, $realtime = false)
    {
        $articleId = (int) $id;
        $sql = "SELECT (configurator_set_id IS NOT NULL) as isConfigurator FROM s_articles WHERE id = $articleId";
        $isConfigurator = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($realtime == true ? 0 : $this->sSYSTEM->sCONFIG['sCACHEARTICLE'], $sql, false, "article_$id");

        if (!empty($isConfigurator) && $isConfigurator[0]['isConfigurator'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Read the unit types from a certain article
     * @param int $id s_articles.id
     * @return array
     */
    public function sGetUnit($id)
    {
        static $cache = array();
        if (isset($cache[$id])) {
            return $cache[$id];
        }
        $unit = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHEARTICLE'], "
          SELECT unit, description FROM s_core_units WHERE id=?
        ", array($id));

        if (!empty($unit) && !Shopware()->Shop()->get('skipbackend')) {
            $sql = "SELECT objectdata FROM s_core_translations WHERE objecttype='config_units' AND objectlanguage=" . Shopware()->Shop()->getId();
            $translation = $this->sSYSTEM->sDB_CONNECTION->CacheGetOne($this->sSYSTEM->sCONFIG['sCACHEARTICLE'], $sql);
            if (!empty($translation)) {
                $translation = unserialize($translation);
            }
            if (!empty($translation[$id])) {
                $unit = array_merge($unit, $translation[$id]);
            }
        }
        return $cache[$id] = $unit;
    }

    /**
     * Get discounts and discount table for a certain article
     * @param string $customergroup id of customergroup key
     * @param string $groupID customer group id
     * @param float $listprice default price
     * @param int $quantity
     * @param bool $doMatrix Return array with all block prices
     * @param array $articleData current article
     * @param bool $ignore deprecated
     * @return array|float|null
     */
    public function sGetPricegroupDiscount($customergroup, $groupID, $listprice, $quantity, $doMatrix = true, $articleData = array(), $ignore = false)
    {
        $getBlockPricings = array();
        $laststart = null;
        $divPercent = null;

        if (!empty($this->sSYSTEM->sUSERGROUPDATA["groupkey"])) {
            $customergroup = $this->sSYSTEM->sUSERGROUPDATA["groupkey"];

        }
        if (!$customergroup || !$groupID) return false;

        $sql = "
        SELECT s_core_pricegroups_discounts.discount AS discount,discountstart
        FROM
            s_core_pricegroups_discounts,
            s_core_customergroups AS scc
        WHERE
            groupID=$groupID AND customergroupID = scc.id
        AND
            scc.groupkey = ?
        GROUP BY discount
        ORDER BY discountstart ASC
        ";

        $getGroups = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHEARTICLE'], $sql, array($customergroup));

        if (count($getGroups)) {
            foreach ($getGroups as $group) {
                $priceMatrix[$group["discountstart"]] = array("percent" => $group["discount"]);
                if (!empty($group["discount"])) $discountsFounds = true;
            }

            if (empty($discountsFounds)) {
                if (empty($doMatrix)) {

                    return $listprice;
                } else {
                    return;
                }
            }

            if (!empty($doMatrix) && count($priceMatrix) == 1) {
                return;
            }

            if (empty($doMatrix)) {
                // Getting price rule matching to quantity
                foreach ($priceMatrix as $start => $percent) {
                    if ($start <= $quantity) {
                        $matchingPercent = $percent["percent"];
                    }
                }

                if ($matchingPercent) {
                    //echo "Percent discount via pricegroup $groupID - $matchingPercent Discount\n";
                    return ($listprice / 100 * (100 - $matchingPercent));
                }
            } else {
                $i = 0;
                // Building price-ranges
                foreach ($priceMatrix as $start => $percent) {
                    $to = $start - 1;
                    if ($laststart && $to) $priceMatrix[$laststart]["to"] = $to;
                    $laststart = $start;
                }

                foreach ($priceMatrix as $start => $percent) {

                    $getBlockPricings[$i]["from"] = $start;
                    $getBlockPricings[$i]["to"] = $percent["to"];
                    if ($i == 0 && $ignore) {

                        $getBlockPricings[$i]["price"] = $this->sCalculatingPrice(($listprice / 100 * (100)), $articleData["tax"], $articleData["taxID"], $articleData);
                        $divPercent = $percent["percent"];
                    } else {
                        if ($ignore) $percent["percent"] -= $divPercent;
                        $getBlockPricings[$i]["price"] = $this->sCalculatingPrice(($listprice / 100 * (100 - $percent["percent"])), $articleData["tax"], $articleData["taxID"], $articleData);
                    }
                    $i++;

                }


                return $getBlockPricings;
            }
        }
        if (!empty($doMatrix)) {

            return;
        } else {

            return $listprice;
        }
    }

    /**
     * Get the cheapest price for a certain article
     * @param int $article id
     * @param int $group customer group id
     * @param int $pricegroup pricegroup id
     * @param bool $usepricegroups consider pricegroups
     * @return float cheapest price or null
     */
    public function sGetCheapestPrice($article, $group, $pricegroup, $usepricegroups, $realtime = false, $returnArrayIfConfigurator = false, $checkLiveshopping = false)
    {
        if ($group != $this->sSYSTEM->sUSERGROUP) {
            $fetchGroup = $group;
        } else {
            $fetchGroup = $this->sSYSTEM->sUSERGROUP;
        }

        if (empty($usepricegroups)) {
            $sql = "
            SELECT price FROM s_articles_prices, s_articles_details WHERE
            s_articles_details.id=s_articles_prices.articledetailsID AND
            pricegroup=?
            AND s_articles_details.articleID=?
            GROUP BY ROUND(price,2)
            ORDER BY price ASC
            LIMIT 2
        ";
        } else {
            $sql = "
            SELECT price FROM s_articles_details
            LEFT JOIN
            s_articles_prices ON s_articles_details.id=s_articles_prices.articledetailsID AND
            pricegroup=? AND s_articles_prices.from = '1'
            WHERE
            s_articles_details.articleID=?
            GROUP BY ROUND(price,2)
            ORDER BY price ASC
            LIMIT 2
            ";
        }

        $queryCheapestPrice = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($realtime == true ? 0 : $this->sSYSTEM->sCONFIG['sCACHEPRICES'], $sql, array(
            $fetchGroup, $article
        ), "article_$article");

        if (count($queryCheapestPrice) > 1) {
            $cheapestPrice = $queryCheapestPrice[0]["price"];
            if (empty($cheapestPrice)) {
                // No Price for this customer-group fetch defaultprice
                $sql = "
                SELECT price FROM s_articles_details
                LEFT JOIN s_articles_prices
                  ON s_articles_details.id=s_articles_prices.articledetailsID
                  AND pricegroup='EK'
                  AND s_articles_prices.from = '1'
                WHERE
                  s_articles_details.articleID=$article
                GROUP BY ROUND(price,2)
                ORDER BY price ASC
                LIMIT 2
                ";

                $queryCheapestPrice = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($realtime == true ? 0 : $this->sSYSTEM->sCONFIG['sCACHEPRICES'], $sql, false, "article_$article");
                if (count($queryCheapestPrice) > 1) {
                    $cheapestPrice = $queryCheapestPrice[0]["price"];
                } else {
                    $cheapestPrice = 0;
                    $basePrice = $queryCheapestPrice[0]["price"];
                }
            }
            $foundPrice = true;
        } else {
            $cheapestPrice = 0;
            $basePrice = $queryCheapestPrice[0]["price"];
        }

        $sql = "
        SELECT s_core_pricegroups_discounts.discount AS discount,discountstart
        FROM
            s_core_pricegroups_discounts,
            s_core_customergroups AS scc
        WHERE
            groupID=? AND customergroupID = scc.id
        AND
            scc.groupkey = ?
        GROUP BY discount
        ORDER BY discountstart ASC
        ";

        $getGroups = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($this->sSYSTEM->sCONFIG['sCACHEARTICLE'], $sql, array($pricegroup, $this->sSYSTEM->sUSERGROUP));

        //if there are no discounts for this customergroup don't show "ab:"
        if (empty($getGroups)) {
            return $cheapestPrice;
        }


        // Updated / Fixed 28.10.2008 - STH
        if (!empty($usepricegroups)) {

            if (!empty($cheapestPrice)) {

                $basePrice = $cheapestPrice;
            } else {
                $foundPrice = true;
            }

            $returnPrice = $this->sGetPricegroupDiscount(
                $this->sSYSTEM->sUSERGROUP,
                $pricegroup,
                $basePrice,
                99999,
                false
            );

            if (!empty($returnPrice) && $foundPrice) {


                $cheapestPrice = $returnPrice;
            } elseif (!empty($foundPrice) && $returnPrice == 0.00) {

                $cheapestPrice = "0.00";
            } else {

                $cheapestPrice = "0";
            }
        }

        if (isset($queryCheapestPrice[0]["count"]) && $queryCheapestPrice[0]["count"] > 1 && empty($queryCheapestPrice[1]["price"]) && !empty($returnArrayIfConfigurator)) {
            return (array($cheapestPrice, $queryCheapestPrice[0]["count"]));
        }

        return $cheapestPrice;
    }

    /**
     * returns the cheapest variant for the baseprice calculation
     *
     * @since 4.1.4
     * @param $article
     * @param $priceGroup
     * @param $priceGroupId
     * @return mixed
     */
    public function getCheapestVariant($article, $priceGroup, $priceGroupId)
    {
        if (empty($priceGroupId)) {
            $sql = "
                SELECT * FROM s_articles_prices, s_articles_details WHERE
                s_articles_details.id=s_articles_prices.articledetailsID AND
                pricegroup=?
                AND s_articles_details.articleID=?
                GROUP BY ROUND(price,2)
                ORDER BY price ASC
                LIMIT 2
            ";
        } else {
            $sql = "
                SELECT * FROM s_articles_details
                LEFT JOIN
                s_articles_prices ON s_articles_details.id=s_articles_prices.articledetailsID AND
                pricegroup=? AND s_articles_prices.from = '1'
                WHERE
                s_articles_details.articleID=?
                GROUP BY ROUND(price,2)
                ORDER BY price ASC
                LIMIT 2
            ";
        }

        $variantData = Shopware()->Db()->fetchRow($sql, array($priceGroup, $article));
        return $variantData;
    }

    /**
     * Get one article with all available data
     * @param int $id article id
     * @param null $sCategoryID
     * @param null $number
     * @param array $selection
     * @return array
     */
    public function sGetArticleById($id = 0, $sCategoryID = null, $number = null, array $selection = null)
    {
        /**
         * Validates the passed configuration array for the configurator selection
         */
        $configuration = $this->getCurrentConfiguration($selection);

        /**
         * Checks which product id should be used.
         * If an order number passed, the id of the order number should be used.
         */
        $productId = $this->getCurrentProductId($id, $number);

        /**
         * Checks which product number should be loaded. If a configuration passed.
         */
        $productNumber = $this->getCurrentProductNumber(
            $productId,
            $number,
            $configuration
        );

        $type = $this->getConfiguratorType($productId);

        /**
         * Check if a variant should be loaded. And load the configuration for the variant for pre selection.
         *
         * Requires the following scenario:
         * 1. $number has to be set (without a number we can't load a configuration)
         * 2. $number is equals to $productNumber (if the order number is invalid or inactive fallback to main variant)
         * 3. $configuration is empty (Customer hasn't not set an own configuration)
         */
        if ($number && $number == $productNumber && empty($configuration) || $type == 0) {
            $configuration = $this->getConfigurationByNumber($productNumber);
        }

        $categoryId = (int) $sCategoryID;
        if (empty($categoryId) || $categoryId == Shopware()->Shop()->getId()) {
            $categoryId = Shopware()->Modules()->Categories()->sGetCategoryIdByArticleId($id);
        }

        $product = $this->getProduct(
            $productNumber,
            $categoryId,
            $configuration
        );

        if ($product) {
            $product = $this->legacyEventManager->fireArticleByIdEvents($product, $this);
        }

        return $product;
    }

    private function getConfiguratorType($productId)
    {
        return $this->db->fetchOne(
            'SELECT type
             FROM s_article_configurator_sets configuratorSet
              INNER JOIN s_articles product
                ON product.configurator_set_id = configuratorSet.id
             WHERE product.id = ?',
            array($productId)
        );
    }

    /**
     * calculates the reference price with the base price data
     *
     * @since 4.1.4
     * @param $price | the final price which will be shown
     * @param float $purchaseUnit
     * @param float $referenceUnit
     * @return float
     */
    public function calculateReferencePrice($price, $purchaseUnit, $referenceUnit)
    {
        $purchaseUnit = (float) $purchaseUnit;
        $referenceUnit = (float) $referenceUnit;

        $price = floatval(str_replace(",", ".", $price));

        return $price / $purchaseUnit * $referenceUnit;
    }

    /**
     * calculates the cheapest base price data
     *
     * @since 4.1.4
     * @param $price | the final price which will be shown
     * @param int $articleId
     * @param string $priceGroup
     * @param int $priceGroupId
     * @return array
     */
    public function calculateCheapestBasePriceData($price, $articleId, $priceGroup, $priceGroupId)
    {
        $returnData = array();
        $cheapestVariantData = $this->getCheapestVariant($articleId, $priceGroup, $priceGroupId);

        if (!$cheapestVariantData["purchaseunit"] || empty($cheapestVariantData["referenceunit"])) {
            // stop the calculation because no unit data is set
            return null;
        }

        $returnData["purchaseunit"] = (float) $cheapestVariantData["purchaseunit"];
        $returnData["referenceunit"] = (float) $cheapestVariantData["referenceunit"];
        $returnData["packunit"] = $cheapestVariantData["packunit"];
        // Read unit if set
        if ($cheapestVariantData["unitID"]) {
            $returnData["sUnit"] = $this->sGetUnit($cheapestVariantData["unitID"]);
        }
        $returnData["referenceprice"] = $this->calculateReferencePrice(
            $price,
            $returnData["purchaseunit"],
            $returnData["referenceunit"]
        );

        return $returnData;
    }

    /**
     * Helper function to check the configuration for the article detail page navigation arrows.
     */
    private function showArticleNavigation()
    {
        return !(Shopware()->Config()->get('disableArticleNavigation'));
    }

    /**
     * Helper function to check the filter configuration for article detail pages.
     * Checks the configuration parameter displayFiltersOnDetailPage.
     * This config can be set over the performance module.
     *
     *
     * @return boolean
     */
    protected function displayFiltersOnArticleDetailPage()
    {
        return Shopware()->Config()->get('displayFiltersOnDetailPage', true);
    }

    /**
     * Formats article prices
     * @param float $price
     * @return float price
     */
    public function sFormatPrice($price)
    {
        $price = str_replace(",", ".", $price);
        $price = $this->sRound($price);
        $price = str_replace(".", ",", $price); // Replaces points with commas
        $commaPos = strpos($price, ",");
        if ($commaPos) {

            $part = substr($price, $commaPos + 1, strlen($price) - $commaPos);
            switch (strlen($part)) {
                case 1:
                    $price .= "0";
                    break;
                case 2:
                    break;
            }
        } else {
            if (!$price) {
                $price = "0";
            } else {
                $price .= ",00";
            }
        }

        return $price;
    }

    /**
     * Round article price
     *
     * @param float $moneyFloat price
     * @return float price
     */
    public function sRound($moneyfloat = null)
    {
        $money_str = explode(".", $moneyfloat);
        if (empty($money_str[1])) $money_str[1] = 0;
        $money_str[1] = substr($money_str[1], 0, 3); // convert to rounded (to the nearest thousandth) string

        $money_str = $money_str[0] . "." . $money_str[1];

        return round($money_str, 2);
    }

    public function sGetProductByOrdernumber($ordernumber)
    {
        if (Enlight()->Events()->notifyUntil('Shopware_Modules_Articles_sGetProductByOrdernumber_Start', array('subject' => $this, 'value' => $ordernumber))) {
            return false;
        }

        $markNew = (int) $this->sSYSTEM->sCONFIG['sMARKASNEW'];
        $markTop = (int) $this->sSYSTEM->sCONFIG['sMARKASTOPSELLER'];
        // Used in emotion widget to fetch only articles that have an image assigned

        $sql = "
            SELECT
                a.id as articleID, d.id AS articleDetailsID, d.kind,
                d.ordernumber, datum, sales, topseller as highlight,
                a.description, a.description_long,
                s.name AS supplierName, s.img AS supplierImg,
                a.name AS articleName, a.taxID,
                IFNULL(p.price,p2.price) as price,
                IF(p.pseudoprice, p.pseudoprice, p2.pseudoprice) as pseudoprice, tax,
                attr1,attr2,attr3,attr4,attr5,attr6,attr7,attr8,attr9,attr10,
                attr11,attr12,attr13,attr14,attr15,attr16,attr17,attr18,attr19,attr20,
                instock, weight, a.shippingtime,
                IFNULL(p.pricegroup, IFNULL(p2.pricegroup, 'EK')) as pricegroup,
                pricegroupID, pricegroupActive, filtergroupID,
                d.purchaseunit, d.referenceunit,
                d.unitID, laststock, additionaltext,
                (a.configurator_set_id IS NOT NULL) as sConfigurator,
                IFNULL((SELECT 1 FROM s_articles_esd WHERE articleID=a.id LIMIT 1), 0) as esd,
                IFNULL((SELECT CONCAT(AVG(points),'|',COUNT(*)) as votes FROM s_articles_vote WHERE active=1 AND articleID=a.id),'0.00|00') as sVoteAverange,
                IF(DATE_SUB(CURDATE(), INTERVAL $markNew DAY) <= a.datum, 1, 0) as newArticle,
                IF(d.sales>=$markTop, 1, 0) as topseller,
                IF(d.releasedate > CURDATE(), 1, 0) as sUpcoming,
                IF(d.releasedate > CURDATE(), d.releasedate, '') as sReleasedate,
                (SELECT 1 FROM s_articles_details WHERE articleID=a.id AND kind!=1 LIMIT 1) as sVariantArticle
            FROM s_articles a

            JOIN s_articles_details d
            ON d.articleID=a.id

            JOIN s_articles_attributes at
            ON at.articledetailsID=d.id

            JOIN s_core_tax t
            ON t.id=a.taxID

            LEFT JOIN s_articles_supplier s
            ON s.id=a.supplierID

            LEFT JOIN s_articles_prices p
            ON p.articleDetailsID=d.id
            AND p.pricegroup=?
            AND p.`from`='1'

            LEFT JOIN s_articles_prices p2
            ON p2.articleDetailsID=d.id
            AND p2.pricegroup='EK'
            AND p2.`from`='1'

            WHERE d.ordernumber=?
            AND a.active=1
            LIMIT 1
        ";

        $sql = Enlight()->Events()->filter(
            'Shopware_Modules_Articles_sGetProductByOrdernumber_FilterSql', $sql,
            array('subject' => $this, 'value' => $ordernumber)
        );

        $getPromotionResult = Shopware()->Db()->fetchRow($sql, array($this->sSYSTEM->sUSERGROUP, $ordernumber));

        if (empty($getPromotionResult)) {
            return false;
        }

        $getPromotionResult = $this->sGetTranslation(
            $getPromotionResult, $getPromotionResult["articleID"], 'article', $this->sSYSTEM->sLanguage
        );

        if ($getPromotionResult['kind'] != 1) {
            $getPromotionResult = $this->sGetTranslation(
                $getPromotionResult, $getPromotionResult['articleDetailsID'], 'variant', $this->sSYSTEM->sLanguage
            );
        }

        // Load article properties (Missing support for multilanguage)
        if ($getPromotionResult["filtergroupID"]) {
            $getPromotionResult["sProperties"] = $this->sGetArticleProperties(
                $getPromotionResult["articleID"],
                $getPromotionResult["filtergroupID"]
            );
        }


        // Formating prices
        $getPromotionResult["price"] = $this->sCalculatingPrice($getPromotionResult["price"], $getPromotionResult["tax"], $getPromotionResult["taxID"], $getPromotionResult);

        if (!empty($getPromotionResult["unitID"])) {
            $getPromotionResult["sUnit"] = $this->sGetUnit($getPromotionResult["unitID"]);
        }

        if ($getPromotionResult["pseudoprice"]) {
            $getPromotionResult["pseudoprice"] = $this->sCalculatingPrice($getPromotionResult["pseudoprice"], $getPromotionResult["tax"], $getPromotionResult["taxID"], $getPromotionResult);
            $discPseudo = str_replace(",", ".", $getPromotionResult["pseudoprice"]);
            $discPrice = str_replace(",", ".", $getPromotionResult["price"]);
            $discount = round(($discPrice / $discPseudo * 100) - 100, 2) * -1;
            $getPromotionResult["pseudopricePercent"] = array("int" => round($discount, 0), "float" => $discount);
        }

        // Calculating price for reference-unit
        if ($getPromotionResult["purchaseunit"] > 0 && $getPromotionResult["referenceunit"]) {
            $getPromotionResult["purchaseunit"] = (float) $getPromotionResult["purchaseunit"];
            $getPromotionResult["referenceunit"] = (float) $getPromotionResult["referenceunit"];

            $getPromotionResult["referenceprice"] = $this->calculateReferencePrice(
                $getPromotionResult["price"],
                $getPromotionResult["purchaseunit"],
                $getPromotionResult["referenceunit"]
            );
        }

        // Strip tags from descriptions
        $getPromotionResult["articleName"] = $this->sOptimizeText($getPromotionResult["articleName"]);

        if (Shopware()->Config()->get('useShortDescriptionInListing')) {
            $getPromotionResult["description_long"] = strlen($getPromotionResult["description"]) > 5 ? $getPromotionResult["description"] : $this->sOptimizeText($getPromotionResult["description_long"]);
        }

        $getPromotionResult['sVoteAverange'] = explode('|', $getPromotionResult['sVoteAverange']);
        $getPromotionResult['sVoteAverange'] = array(
            'averange' => round($getPromotionResult['sVoteAverange'][0], 2),
            'count' => round($getPromotionResult['sVoteAverange'][1]),
        );
        $getPromotionResult["image"] = $this->sGetArticlePictures($getPromotionResult["articleID"], true, 0, "", false, false);

        $getPromotionResult["linkBasket"] = $this->sSYSTEM->sCONFIG['sBASEFILE'] . "?sViewport=basket&sAdd=" . $getPromotionResult["ordernumber"];
        $getPromotionResult["linkDetails"] = $this->sSYSTEM->sCONFIG['sBASEFILE'] . "?sViewport=detail&sArticle=" . $getPromotionResult["articleID"];
        if (!empty($category) && $category != $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"]) {
            $getPromotionResult["linkDetails"] .= "&sCategory=$category";
        }

        $getPromotionResult = Enlight()->Events()->filter(
            'Shopware_Modules_Articles_sGetProductByOrdernumber_FilterResult',
            $getPromotionResult,
            array('subject' => $this, 'value' => $ordernumber)
        );

        return $getPromotionResult;
    }

    /**
     * Get basic article data in various modes (firmly definied by id, random, top,new)
     * @param string $mode Modus (fix, random, top, new)
     * @param int $category filter by category
     * @param int $value article id / ordernumber for firmly definied articles
     * @param bool $withImage
     * @return array
     */
    public function sGetPromotionById($mode, $category = 0, $value = 0, $withImage = false)
    {
        $notifyUntil = $this->eventManager->notifyUntil(
            'Shopware_Modules_Articles_GetPromotionById_Start',
            array(
                'subject' => $this,
                'mode' => $mode,
                'category' => $category,
                'value' => $value
            )
        );

        if ($notifyUntil) {
            return false;
        }

        $value = $this->getPromotionNumberByMode($mode, $category, $value, $withImage);

        if (!$value) {
            return false;
        }

        $result = $this->getPromotion($category, $value);

        if (!$result) {
            return false;
        }

        $result = $this->legacyEventManager->firePromotionByIdEvents(
            $result,
            $category,
            $this
        );

        return $result;
    }

    private function getPromotionNumberByMode($mode, $category, $value, $withImage)
    {
        switch ($mode) {
            case 'top':
            case 'new':
            case 'random':
                $value = $this->getRandomArticle($mode, $category, $value, $withImage);
                break;
            case "gfx":
            case "image":
                return $this->getGfxData($mode, $category, $value);
            case "premium":
                $value = $this->getPremiumArticle($mode, $category, $value);
                break;
            case "fix":
                break;
        }

        if (!$value) {
            return false;
        }

        $number = Shopware()->Db()->fetchOne(
            "SELECT ordernumber
             FROM s_articles_details
                INNER JOIN s_articles
                  ON s_articles.main_detail_id = s_articles_details.id
             WHERE articleID = ?",
            array($value)
        );

        if ($number) {
            $value = $number;
        }

        return $value;
    }

    private function getPremiumArticle($mode, $category = 0, $value = 0)
    {
        $value = $this->sSYSTEM->sDB_CONNECTION->qstr($value);

        $sql = "
                SELECT a.active AS active, a.id as articleID, ordernumber,datum,sales, topseller,
                a.description AS description,description_long, aSupplier.name AS supplierName,
                aSupplier.img AS supplierImg, a.name AS articleName
                FROM
                s_articles AS a,
                s_articles_supplier AS aSupplier,
                s_articles_details AS d
                WHERE aSupplier.id=a.supplierID
                AND d.articleID=a.id
                AND d.kind=1
                AND a.id=$value
            ";

        $sql = Enlight()->Events()->filter('Shopware_Modules_Articles_GetPromotionById_FilterSqlPremium', $sql, array(
            'subject' => $this, 'mode' => $mode, 'category' => $category, 'value' => $value
        ));

        $data = Shopware()->Db()->fetchRow($sql);

        return $data['ordernumber'];
    }

    private function getRandomArticle($mode, $category = 0, $value = 0, $withImage = false)
    {
        $category = (int) $category;
        $categoryJoin = "";

        if (!empty($category)) {
            $categoryJoin = "
                INNER JOIN s_articles_categories_ro ac
                    ON  ac.articleID  = a.id
                    AND ac.categoryID = $category
                INNER JOIN s_categories c
                    ON  c.id = ac.categoryID
                    AND c.active = 1
            ";
        }

        $withImageJoin = "";
        if ($withImage) {
            $withImageJoin = "
                JOIN s_articles_img ai
                ON ai.articleID=a.id
                AND ai.main=1
                AND ai.article_detail_id IS NULL
            ";
        }

        if ($mode == 'top') {
            $promotionTime = !empty($this->sSYSTEM->sCONFIG['sPROMOTIONTIME']) ? (int) $this->sSYSTEM->sCONFIG['sPROMOTIONTIME'] : 30;
            $now = Shopware()->Db()->quote(date('Y-m-d H:00:00'));
            $sql = "
                SELECT od.articleID
                FROM s_order as o, s_order_details od, s_articles a $withImageJoin

                $categoryJoin

                LEFT JOIN s_articles_avoid_customergroups ag
                ON ag.articleID=a.id
                AND ag.customergroupID={$this->customerGroupId}
                WHERE o.ordertime > DATE_SUB($now, INTERVAL $promotionTime DAY)
                AND o.id=od.orderID
                AND od.modus=0 AND od.articleID=a.id
                AND a.active=1
                AND ag.articleID IS NULL
                GROUP BY od.articleID
                ORDER BY COUNT(od.articleID) DESC
                LIMIT 100
            ";
        } else {
            $sql = "
                SELECT a.id as articleID
                FROM  s_articles a $withImageJoin
                $categoryJoin
                LEFT JOIN s_articles_avoid_customergroups ag
                ON ag.articleID=a.id
                AND ag.customergroupID={$this->customerGroupId}
                WHERE a.active=1 AND a.mode=0
                AND ag.articleID IS NULL
                ORDER BY a.datum DESC
                LIMIT 100
            ";
        }
        $sql = Enlight()->Events()->filter(
            'Shopware_Modules_Articles_GetPromotionById_FilterSqlRandom',
            $sql,
            array('subject' => $this, 'mode' => $mode, 'category' => $category, 'value' => $value)
        );
        $articleIDs = Shopware()->Db()->fetchCol($sql);

        if ($mode == 'random') {
            $value = array_rand($articleIDs);
            $value = $articleIDs[$value];
        } else {
            $value = current($articleIDs);
        }

        return $value;
    }

    private function getGfxData($mode, $category, $value)
    {
        $rs = array(
            'mode' => 'gfx',
            'img'  => $value["img"] ? $this->sSYSTEM->sPathBanner . $value["img"] : $this->sSYSTEM->sPathBanner . $value["image"],
            'link' => $value["link"],
            'linkTarget' => $value["link_target"] ? $value["link_target"] : $value["target"],
            'description' => $value['description']
        );

        return Enlight()->Events()->filter(
            'Shopware_Modules_Articles_GetPromotionById_FilterGfx',
            $rs,
            array(
                'subject' => $this,
                'mode' => $mode,
                'category' => $category,
                'value' => $value
            )
        );
    }

    /**
     * Optimize text, strip html tags etc.
     * @param string $text
     * @return string $text
     */
    public function sOptimizeText($text)
    {
        $text = html_entity_decode($text, ENT_NOQUOTES, 'UTF-8');
        $text = preg_replace('!<[^>]*?>!u', ' ', $text);
        $text = preg_replace('/\s\s+/u', ' ', $text);
        $text = trim($text);
        return $text;
    }

    /**
     * Internal helper function to convert the image data from the database to the frontend structure.
     * @param $image
     * @param $articleAlbum \Shopware\Models\Media\Album
     * @return array
     */
    private function getDataOfArticleImage($image, $articleAlbum)
    {
        //initial the data array
        $imageData = array();

        if (empty($image["path"])) {
            return $imageData;
        }

        //first we get all thumbnail sizes of the article album
        $sizes = $articleAlbum->getSettings()->getThumbnailSize();

        //now we get the configured image and thumbnail dir.
        $imageDir = $this->sSYSTEM->sPathArticleImg;
        $thumbDir = $imageDir. 'thumbnail/';

        //if no extension is configured, shopware use jpg as default extension
        if (empty($image['extension'])) $image['extension'] = 'jpg';

        $imageData['src']['original'] = $imageDir . $image["path"] . "." . $image["extension"];
        $imageData["res"]["original"]["width"] = $image["width"];
        $imageData["res"]["original"]["height"] = $image["height"];
        $imageData["res"]["description"] = $image["description"];
        $imageData["position"] = $image["position"];
        $imageData["extension"] = $image["extension"];
        $imageData["main"] = $image["main"];
        $imageData["id"] = $image["id"];
        $imageData["parentId"] = $image["parentId"];

        // attributes as array as they come from non configurator aricles
        if (!empty($image['attribute'])) {
            unset($image['attribute']['id']);
            unset($image['attribute']['articleImageId']);
            $imageData['attribute'] = $image['attribute'];
        } else {
            $imageData['attribute'] = array();
        }

        // attributes as keys as they come from configurator articles
        if (!empty($image['attribute1'])) {
            $imageData['attribute']['attribute1'] = $image['attribute1'];
        }

        if (!empty($image['attribute2'])) {
            $imageData['attribute']['attribute2'] = $image['attribute2'];
        }

        if (!empty($image['attribute1'])) {
            $imageData['attribute']['attribute3'] = $image['attribute3'];
        }

        foreach ($sizes as $key => $size) {
            if (strpos($size, 'x')===0) {
                $size = $size.'x'.$size;
            }
            $imageData["src"][$key] = $thumbDir . $image['path'] . '_'. $size .'.'. $image['extension'];
        }

        $translation = $this->sGetTranslation(array(), $imageData['id'], "articleimage");

        if (!empty($translation)) {
            if (!empty($translation['description'])) {
                $imageData["res"]["description"] = $translation['description'];
            }
        }

        return $imageData;
    }

    /**
     * Internal helper function to get the cover image of an article.
     * If the orderNumber parameter is set, the function checks first
     * if an variant image configured. If this is the case, this
     * image will be used as cover image. Otherwise the function calls the
     * getArticleMainCover function which returns the absolute main image
     *
     * @param $articleId
     * @param $orderNumber
     * @param $articleAlbum
     * @return array
     */
    public function getArticleCover($articleId, $orderNumber, $articleAlbum)
    {
        if (!empty($orderNumber)) {
            //check for specify variant images. For example:
            //if the user is on a detail page of a shoe and select the color "red"
            //we have to check if the current variant has an own configured picture for a red shoe.
            //the query selects orders the result at first by the image main flag, at second for the position.
            $cover = $this->getArticleRepository()
                ->getVariantImagesByArticleNumberQuery($orderNumber, 0, 1)
                ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        }

        //if we have found a configured article image which has the same options like the passed article order number
        //we have to return this one.
        if (!empty($cover)) {
            return $this->getDataOfArticleImage($cover, $articleAlbum);
        }

        //if we haven't found and variant image we have to select the first image which has no configuration.
        //the query orders the result at first by the image main flag, at second by the position.
        $cover = $this->getArticleRepository()
            ->getArticleCoverImageQuery($articleId)
            ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        if (!empty($cover)) {
            return $this->getDataOfArticleImage($cover, $articleAlbum);
        }

        //if no variant or normal article image is found we will return the main image of the article even if this image has a variant restriction
        return $this->getArticleMainCover($articleId, $articleAlbum);
    }

    /**
     * Returns the the absolute main article image
     * This method returns the main cover depending on the main flag no matter if any variant restriction is set
     *
     * @param $articleId
     * @param $articleAlbum
     * @return array
     */
    public function getArticleMainCover($articleId, $articleAlbum)
    {
        $cover = $this->getArticleRepository()->getArticleFallbackCoverQuery($articleId)->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        return $this->getDataOfArticleImage($cover, $articleAlbum);
    }

    /**
     * Wrapper method to specialize the sGetArticlePictures method for the listing images
     *
     * @param $articleId
     * @param bool $forceMainImage | if true this will return the main image no matter which variant restriction is set
     * @return array
     */
    public function getArticleListingCover($articleId, $forceMainImage = false)
    {
        return $this->sGetArticlePictures($articleId, true, 0, null, null, null, $forceMainImage);
    }

    /**
     * Get all pictures from a certain article
     * @param        $sArticleID
     * @param bool $onlyCover
     * @param int $pictureSize | unused variable
     * @param string $ordernumber
     * @param bool $allImages | unused variable
     * @param bool $realtime | unused variable
     * @param bool $forceMainImage | will return the main image no matter which variant restriction is set
     * @return array
     */
    public function sGetArticlePictures($sArticleID, $onlyCover = true, $pictureSize = 0, $ordernumber = null, $allImages = false, $realtime = false, $forceMainImage = false)
    {
        static $articleAlbum;
        if ($articleAlbum === null) {
            //now we search for the default article album of the media manager, this album contains the thumbnail configuration.
            /**@var $model \Shopware\Models\Media\Album*/
            $articleAlbum = $this->getMediaRepository()
                ->getAlbumWithSettingsQuery(-1)
                ->getOneOrNullResult();
        }

        //first we convert the passed article id into an integer to prevent sql injections
        $articleId = (int) $sArticleID;

        Enlight()->Events()->notify('Shopware_Modules_Articles_GetArticlePictures_Start', array('subject' => $this, 'id' => $articleId));

        //first we get the article cover
        if ($forceMainImage) {
            $cover = $this->getArticleMainCover($articleId, $articleAlbum);
        } else {
            $cover = $this->getArticleCover($articleId, $ordernumber, $articleAlbum);
        }


        if ($onlyCover) {
            $cover = Enlight()->Events()->filter('Shopware_Modules_Articles_GetArticlePictures_FilterResult', $cover, array('subject' => $this, 'id' => $articleId));
            return $cover;
        }

        //now we select all article images of the passed article id.
        $articleImages = $this->getArticleRepository()
            ->getArticleImagesQuery($articleId)
            ->getArrayResult();

        //if an order number passed to the function, we have to select the configured variant images
        $variantImages = array();
        if (!empty($ordernumber)) {
            $variantImages = $this->getArticleRepository()
                ->getVariantImagesByArticleNumberQuery($ordernumber)
                ->getArrayResult();
        }
        //we have to collect the already added image ids, otherwise the images
        //would be displayed multiple times.
        $addedImages = array($cover['id']);
        $images = array();

        //first we add all variant images, this images has a higher priority as the normal article images
        foreach ($variantImages as $variantImage) {

            //if the image wasn't added already, we can add the image
            if (!in_array($variantImage['id'], $addedImages)) {

                //first we have to convert the image data, to resolve the image path and get the thumbnail configuration
                $image = $this->getDataOfArticleImage($variantImage, $articleAlbum);

                //after the data was converted we add the image to the result array and add the id to the addedImages array
                $images[] = $image;
                $addedImages[] = $variantImage['id'];
            }
        }

        //after the variant images added, we can add the normal images, this images has a lower priority as the variant images
        foreach ($articleImages as $articleImage) {
            //add only normal images without any configuration
            //if the image wasn't added already, we can add the image
            if (!in_array($articleImage['id'], $addedImages)) {

                //first we have to convert the image data, to resolve the image path and get the thumbnail configuration
                $image = $this->getDataOfArticleImage($articleImage, $articleAlbum);

                //after the data was converted we add the image to the result array and add the id to the addedImages array
                $images[] = $image;
                $addedImages[] = $articleImage['id'];
            }
        }

        $images = Enlight()->Events()->filter('Shopware_Modules_Articles_GetArticlePictures_FilterResult', $images, array('subject' => $this, 'id' => $articleId));

        return $images;
    }

    /**
     * Get article id by ordernumber
     * @param string $ordernumber
     * @return int $id or false
     */
    public function sGetArticleIdByOrderNumber($ordernumber)
    {
        $checkForArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow("
        SELECT articleID AS id FROM s_articles_details WHERE ordernumber=?
        ", array($ordernumber));

        if ($checkForArticle["id"]) {
            return $checkForArticle["id"];
        } else {
            return false;
        }
    }

    /**
     * Get name from a certain article by ordernumber
     * @param string $ordernumber
     * @param bool $returnAll return only name or additional data, too
     * @return string or array
     */
    public function sGetArticleNameByOrderNumber($ordernumber, $returnAll = false)
    {
        $checkForArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow("
            SELECT s_articles.id,s_articles_details.id AS did, s_articles.name AS articleName, additionaltext FROM s_articles_details, s_articles WHERE
            ordernumber=?
            AND s_articles.id=s_articles_details.articleID
        ", array($ordernumber));

        if (!empty($checkForArticle)) {
            $checkForArticle = $this->sGetTranslation($checkForArticle, $checkForArticle["id"], "article");
            if ($returnAll) {
                $checkForArticle = $this->sGetTranslation($checkForArticle, $checkForArticle["did"], "variant");
                return $checkForArticle;
            } else {

                return $checkForArticle["articleName"];
            }
        } else {
            return false;
        }
    }

    /**
     * Get article name by s_articles.id
     * @param int $articleId
     * @return string name
     */
    public function sGetArticleNameByArticleId($articleId, $returnAll = false)
    {
        $ordernumber = $this->sSYSTEM->sDB_CONNECTION->GetOne("
            SELECT ordernumber FROM s_articles_details WHERE kind=1 AND articleID=?
        ", array($articleId));
        return $this->sGetArticleNameByOrderNumber($ordernumber, $returnAll);
    }

    /**
     * Get article taxrate by id
     * @param int $id article id
     * @return float tax or false
     */
    public function sGetArticleTaxById($id)
    {
        $checkForArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow("
        SELECT s_core_tax.tax AS tax FROM s_core_tax, s_articles WHERE s_articles.id=? AND
        s_articles.taxID = s_core_tax.id
        ", array($id));

        if ($checkForArticle["tax"]) {
            return $checkForArticle["tax"];
        } else {
            return false;
        }
    }

    /**
     * Get recently viewed products
     *
     * @param int $currentArticle current article
     * @return array
     */
    public function sGetLastArticles($currentArticle = null)
    {
        if (!empty($this->sSYSTEM->_SESSION['sUserId'])) {
            $updateArticles = $this->sSYSTEM->sDB_CONNECTION->Execute('
                UPDATE s_emarketing_lastarticles
                SET userID=?
                WHERE sessionID=?
            ', array(
                $this->sSYSTEM->_SESSION['sUserId'],
                $this->sSYSTEM->sSESSION_ID
            ));
        }

        $numberOfArticles = (int) $this->sSYSTEM->sCONFIG['sLASTARTICLESTOSHOW'];


        $categoryJoin = "
            INNER JOIN s_articles_categories_ro ac
                ON  ac.articleID = l.articleID
                AND ac.categoryID = sc.category_id
            INNER JOIN s_categories c
                ON  c.id = ac.categoryID
                AND c.active = 1
        ";

        $sql = "
            SELECT img, l.name, l.articleID
            FROM s_emarketing_lastarticles l

            LEFT JOIN s_core_shops sc ON sc.id=l.shopID

            $categoryJoin

            WHERE l.sessionID=?
            AND l.articleID!=?
            AND l.shopID=?

            GROUP BY l.articleID
            ORDER BY time DESC
            LIMIT {$numberOfArticles}
        ";


        $queryArticles = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql, array(
            $this->sSYSTEM->sSESSION_ID,
            (int) $currentArticle,
            $this->sSYSTEM->sLanguage
        ));

        foreach ($queryArticles as $articleKey => $articleValue) {
            $queryArticles[$articleKey]['linkDetails'] = $this->sSYSTEM->sCONFIG['sBASEFILE'] . '?sViewport=detail&sArticle=' . $articleValue['articleID'];

            if (preg_match('/443/', $_SERVER['SERVER_PORT'])) {
                $queryArticles[$articleKey]['img'] = str_replace('http://', 'https://', $queryArticles[$articleKey]['img']);
            }
        }
        return $queryArticles;
    }

    /**
     * Get list of all promotions from a certain category
     * @param int $category category id
     * @return array
     */
    public function sGetPromotions($category)
    {
        $category = intval($category);

        $sToday = date("Y-m-d");
        $sql = "
            SELECT category,mode, TRIM(ordernumber) as ordernumber, link, description, link_target, img
            FROM s_emarketing_promotions
            WHERE category=$category AND ((TO_DAYS(valid_from) <= TO_DAYS('$sToday') AND
            TO_DAYS(valid_to) >= TO_DAYS('$sToday')) OR
            (valid_from='0000-00-00' AND valid_to='0000-00-00')) ORDER BY position ASC
        ";
        $sql = Enlight()->Events()->filter('Shopware_Modules_Articles_GetPromotions_FilterSQL', $sql, array('subject' => $this, 'category' => $category));

        $getAffectedPromitions = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);

        // Clearing cache
        unset($this->sCachePromotions);
        if (count($getAffectedPromitions)) {
            foreach ($getAffectedPromitions as $promotion) {
                switch ($promotion["mode"]) {
                    case "random":
                        $promotion = $this->sGetPromotionById("random", $category);
                        if (count($promotion) > 1) $promote[] = $promotion;
                        break;
                    case "fix":
                        $promotion = $this->sGetPromotionById("fix", 0, $promotion["ordernumber"]);
                        if (count($promotion) > 1) $promote[] = $promotion;
                        break;
                    case "new":
                        $promotion = $this->sGetPromotionById("new", $category);
                        if (count($promotion) > 1) $promote[] = $promotion;
                        break;
                    case "top":
                        $promotion = $this->sGetPromotionById("top", $category);
                        if (count($promotion) > 1) $promote[] = $promotion;
                        break;
                    case "gfx":
                        $promotion = $this->sGetPromotionById("gfx", $category, $promotion);
                        if (count($promotion) > 1) $promote[] = $promotion;
                        break;
                    case "livefix":
                        break;
                    case "liverand":
                        break;
                    case "liverandcat":
                        break;
                } // end switch

            } // end foreach

            $promote = Enlight()->Events()->filter('Shopware_Modules_Articles_GetPromotions_FilterResult', $promote, array('subject' => $this, 'category' => $category));

            return $promote;
        } // end if
    } // end function

    /**
     * Read translation for one or more articles
     * @param $data
     * @param $ids
     * @param $object
     * @param $language
     * @return array
     */
    public function sGetTranslations($data, $object)
    {
        if (Shopware()->Shop()->get('skipbackend') || empty($data)) {
            return $data;
        }
        $language = Shopware()->Shop()->getId();
        $fallback = Shopware()->Shop()->get('fallback');
        $cacheTime = Shopware()->Config()->get('cacheTranslations');
        $ids = Shopware()->Db()->quote(array_keys($data));

        switch ($object) {
            case 'article':
                $map = array(
                    'txtshortdescription' => 'description',
                    'txtlangbeschreibung' => 'description_long',
                    'txtArtikel' => 'articleName',
                    'txtzusatztxt' => 'additionaltext',
                    'txtkeywords' => 'keywords',
                    'txtpackunit' => 'packunit'
                );
                break;
            case 'configuratorgroup':
                $map = array(
                    'description' => 'groupdescription',
                    'name' => 'groupname',
                );
                break;
            default:
                return $data;
        }

        $object = Shopware()->Db()->quote($object);

        $sql = '';
        if (!empty($fallback)) {
            $sql .= "
                SELECT s.objectdata, s.objectkey
                FROM s_core_translations s
                WHERE
                    s.objecttype = $object
                AND
                    s.objectkey IN ($ids)
                AND
                    s.objectlanguage = '$fallback'
            UNION ALL
            ";
        }
        $sql .= "
            SELECT s.objectdata, s.objectkey
            FROM s_core_translations s
            WHERE
                s.objecttype = $object
            AND
                s.objectkey IN ($ids)
            AND
                s.objectlanguage = '$language'
        ";

        $translations = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($cacheTime, $sql);

        if (empty($translations)) {
            return $data;
        }

        foreach ($translations as $translation) {
            $article = (int) $translation['objectkey'];
            $object = unserialize($translation['objectdata']);
            foreach ($object as $translateKey => $value) {
                if (isset($map[$translateKey])) {
                    $key = $map[$translateKey];
                } else {
                    $key = $translateKey;
                }
                if (!empty($value) && array_key_exists($key, $data[$article])) {
                    $data[$article][$key] = $value;
                }
            }
        }

        return $data;
    }

    /**
     * Get translation for an object (article / variant / link / download / supplier)
     * @param $data
     * @param $id
     * @param $object
     * @param $language
     * @return array
     */
    public function sGetTranslation($data, $id, $object, $language = null)
    {
        if (Shopware()->Shop()->get('skipbackend')) {
            return $data;
        }
        $id = (int) $id;
        $language = Shopware()->Shop()->getId();
        $fallback = Shopware()->Shop()->get('fallback');
        $cacheTime = Shopware()->Config()->get('cacheTranslations');

        switch ($object) {
            case 'article':
                $map = array(
                    'txtshortdescription' => 'description',
                    'txtlangbeschreibung' => 'description_long',
                    'txtArtikel' => 'articleName',
                    'txtzusatztxt' => 'additionaltext',
                    'txtkeywords' => 'keywords',
                    'txtpackunit' => 'packunit'
                );
                break;
            case 'variant':
                $map = array('txtzusatztxt' => 'additionaltext', 'txtpackunit' => 'packunit');
                break;
            case 'link':
                $map = array('linkname' => 'description');
                break;
            case 'download':
                $map = array('downloadname' => 'description');
                break;
            case 'configuratoroption':
                $map = array(
                    'name' => 'optionname',
                );
                break;
            case 'configuratorgroup':
                $map = array(
                    'description' => 'groupdescription',
                    'name' => 'groupname',
                );
                break;
            case 'supplier':
                $map = array(
                    'meta_title' => 'title',
                    'description' => 'description',
                );
                break;
        }

        $sql = "
            SELECT objectdata FROM s_core_translations
            WHERE objecttype = '$object'
            AND objectkey = ?
            AND objectlanguage = '$language'
        ";
        $objectData = $this->sSYSTEM->sDB_CONNECTION->CacheGetOne(
            $cacheTime, $sql, array($id)
        );
        if (!empty($objectData)) {
            $objectData = unserialize($objectData);
        } else {
            $objectData = array();
        }
        if (!empty($fallback)) {
            $sql = "
                SELECT objectdata FROM s_core_translations
                WHERE objecttype = '$object'
                AND objectkey = $id
                AND objectlanguage = '$fallback'
            ";
            $objectFallback = $this->sSYSTEM->sDB_CONNECTION->CacheGetOne(
                $cacheTime, $sql
            );
            if (!empty($objectFallback)) {
                $objectFallback = unserialize($objectFallback);
                $objectData = array_merge($objectFallback, $objectData);
            }
        }
        if (!empty($objectData)) {
            foreach ($objectData as $translateKey => $value) {
                if (isset($map[$translateKey])) {
                    $key = $map[$translateKey];
                } else {
                    $key = $translateKey;
                }
                $data[$key] = $value;
            }
        }
        return $data;
    }

    /**
     * Get array of images from a certain configurator combination
     * @param array $sArticle Associative array with all article data
     * @param string $sCombination Currencly active combination
     * @return array
     */
    public function sGetConfiguratorImage($sArticle, $sCombination = "")
    {
        if (!empty($sArticle["sConfigurator"]) || !empty($sCombination)) {

            $foundImage = false;
            $configuratorImages = false;
            $mainKey = 0;

            if (empty($sCombination)) {
                $sArticle["image"]["description"] = $sArticle["image"]["res"]["description"];
                $sArticle["image"]["relations"] = $sArticle["image"]["res"]["relations"];
                foreach ($sArticle["sConfigurator"] as $key => $group) {
                    foreach ($group["values"] as $key2 => $option) {

                        $groupVal = $group["groupnameOrig"] ? $group["groupnameOrig"] : $group["groupname"];
                        $groupVal = str_replace("/", "", $groupVal);
                        $groupVal = str_replace(" ", "", $groupVal);
                        $optionVal = $option["optionnameOrig"] ? $option["optionnameOrig"] : $option["optionname"];
                        $optionVal = str_replace("/", "", $optionVal);
                        $optionVal = str_replace(" ", "", $optionVal);
                        if (!empty($option["selected"])) {
                            $referenceImages[strtolower($groupVal . ":" . str_replace(" ", "", $optionVal))] = true;
                        }
                    }
                }
                foreach (array_merge($sArticle["images"], array(count($sArticle["images"]) => $sArticle["image"])) as $value) {
                    if (preg_match("/(.*){(.*)}/", $value["relations"])) {
                        $configuratorImages = true;

                        break;
                    }
                }
            } else {
                $referenceImages = array_flip(explode("$$", $sCombination));
                foreach ($referenceImages as $key => $v) {
                    $keyNew = str_replace("/", "", $key);
                    unset($referenceImages[$key]);
                    $referenceImages[$keyNew] = $v;
                }
                $sArticle = array("images" => $sArticle, "image" => array());
                foreach ($sArticle["images"] as $k => $value) {
                    if (preg_match("/(.*){(.*)}/", $value["relations"])) {
                        $configuratorImages = true;


                    }
                    if ($value["main"] == 1) {
                        $mainKey = $k;
                    }
                }
                if (empty($configuratorImages)) {

                    return $sArticle["images"][$mainKey];
                }

            }


            if (!empty($configuratorImages)) {

                $sArticle["images"] = array_merge($sArticle["images"], array(count($sArticle["images"]) => $sArticle["image"]));

                unset($sArticle["image"]);

                $debug = false;

                foreach ($sArticle["images"] as $imageKey => $image) {
                    if (empty($image["src"]["original"]) || empty($image["relations"])) {
                        continue;
                    }
                    $string = $image["relations"];
                    // Parsing string
                    $stringParsed = array();

                    preg_match("/(.*){(.*)}/", $string, $stringParsed);

                    $relation = $stringParsed[1];
                    $available = explode("/", $stringParsed[2]);

                    if (!@count($available)) $available = array(0 => $stringParsed[2]);

                    $imageFailedCheck = array();

                    foreach ($available as $checkKey => $checkCombination) {
                        $getCombination = explode(":", $checkCombination);
                        $group = $getCombination[0];
                        $option = $getCombination[1];

                        if (isset($referenceImages[strtolower($checkCombination)])) {

                            $imageFailedCheck[] = true;

                        }
                    }
                    if (count($imageFailedCheck) && count($imageFailedCheck) >= 1 && count($available) >= 1 && $relation == "||") { // ODER Verknï¿½pfunbg
                        if (!empty($debug)) echo $string . " matching combination\n";
                        $sArticle["images"][$imageKey]["relations"] = "";
                        $positions[$image["position"]] = $imageKey;
                    } elseif (count($imageFailedCheck) == count($available) && $relation == "&") { // UND VERKNï¿½PFUNG
                        $sArticle["images"][$imageKey]["relations"] = "";
                        $positions[$image["position"]] = $imageKey;
                    } else {
                        if (!empty($debug)) echo $string . " doesnt match combination\n";
                        unset($sArticle["images"][$imageKey]);
                    }
                }
                ksort($positions);
                $posKeys = array_keys($positions);

                $sArticle["image"] = $sArticle["images"][$positions[$posKeys[0]]];
                unset($sArticle["images"][$positions[$posKeys[0]]]);

                if (!empty($sCombination)) {
                    return $sArticle["image"];
                }

            } else {

            }
        }

        if (!empty($sArticle["images"])) {
            foreach ($sArticle["images"] as $key => $image) {
                if ($image["relations"] == "&{}" || $image["relations"] == "||{}") {
                    $sArticle["images"][$key]["relations"] = "";
                }
            }
        }
        return $sArticle;
    }

    /**
     * Returns a minified product which can be used for listings,
     * sliders or emotions.
     *
     * @param $category
     * @param $number
     * @return array|bool
     */
    private function getPromotion($category, $number)
    {
        $context = $this->contextService->get();

        $product = $this->listProductService->get(
            $number,
            $context
        );

        if (!$product) {
            return false;
        }

        $promotion = $this->legacyStructConverter->convertListProductStruct($product);
        if (!empty($category) && $category != $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"]) {
            $promotion["linkDetails"] .= "&sCategory=$category";
        }

        /**@var $average StoreFrontBundle\Struct\Product\VoteAverage */
        $average = $this->voteService->getAverage(
            $product,
            $context
        );

        if ($average && $average->getCount()) {
            $promotion['sVoteAverange'] = $this->legacyStructConverter->convertVoteAverageStruct($average);
        }

        //check if the product has an configured property set which stored in s_filter.
        //the mini product doesn't contains this data so we have to load this lazy.
        if (!$product->hasProperties()) {
            return $promotion;
        }

        $propertySet = $this->propertyService->get(
            $product,
            $context
        );

        if (!$propertySet) {
            return $promotion;
        }

        $properties = $this->legacyStructConverter->convertPropertySetStruct($propertySet);
        $promotion['sProperties'] = $this->legacyStructConverter->getFlatPropertyArray($properties);

        return $promotion;
    }

    /**
     * Returns a listing of products. Used for the backward compatibility category listings.
     * This function calls the new shopware core and converts the result to the old listing structure.
     *
     * @param $categoryId
     * @return array
     */
    private function getListing($categoryId)
    {
        $context = $this->contextService->get();

        $config = $this->loadCategoryConfig($categoryId);

        $criteria = $this->getListingCriteria(
            $categoryId,
            $config,
            $context
        );

        $searchResult = $this->searchService->search(
            $criteria,
            $context
        );

        $articles = array();

        $averages = $this->voteService->getAverages(
            $searchResult->getProducts(),
            $context
        );

        /**@var $product StoreFrontBundle\Struct\ListProduct */
        foreach ($searchResult->getProducts() as $product) {
            $article = $this->legacyStructConverter->convertListProductStruct($product);

            if (!empty($categoryId) && $categoryId != $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"]) {
                $article["linkDetails"] .= "&sCategory=$categoryId";
            }

            if (array_key_exists($product->getNumber(), $averages)) {
                $article['sVoteAverange'] = $this->legacyStructConverter->convertVoteAverageStruct(
                    $averages[$product->getNumber()]
                );
            }

            if ($this->config->get('useShortDescriptionInListing') && strlen($article['description']) > 5) {
                $article["description_long"] = $article['description'];
            }
            $article['description_long'] = $this->sOptimizeText($article['description_long']);

            $articles[] = $article;
        }

        $result = array();
        foreach ($searchResult->getFacets() as $facet) {
            switch (true) {
                case ($facet instanceof SearchBundle\Facet\PropertyFacet):
                    $properties = $this->getFacetProperties($facet, $config);
                    $result = array_merge($result, $properties);
                    break;

                case ($facet instanceof SearchBundle\Facet\ManufacturerFacet):
                    $suppliers = $this->getFacetManufacturers($facet, $config);
                    $result['sSupplierInfo'] = $this->getActiveListingSupplier($suppliers, $config);
                    $result['sSuppliers'] = array_values($suppliers);
                    break;

                case ($facet instanceof SearchBundle\Facet\PriceFacet):
                    $result['priceFacet'] = $this->getPriceFacet($facet, $config);
                    break;

                case ($facet instanceof SearchBundle\Facet\ShippingFreeFacet):
                    $result['shippingFreeFacet'] = $this->getShippingFreeFacet($facet, $config);
                    break;

                case ($facet instanceof SearchBundle\Facet\ImmediateDeliveryFacet):
                    $result['immediateDeliveryFacet'] = $this->getImmediateDeliveryFacet($facet, $config);
                    break;
                default:
                    $result['facets'][$facet->getName()] = $facet;
            }
        }

        $result = array_merge(
            $result,
            array(
                'sArticles' => $articles,
                'sNumberArticles' => $searchResult->getTotalCount(),
                'sPages' => $this->createListingPageLinks($searchResult->getTotalCount(), $config),
                'sPerPage' => $this->createListingPerPageLinks($config),
                'sPage' => $config['sPage']
            )
        );

        $shortParameters = $this->getShortParameters();
        $params = $this->getListingLinkParameters($config);

        $params = $this->replaceParameters(
            $params,
            $shortParameters
        );
        ksort($params);

        $result['categoryParams'] = $params;
        $result['shortParameters'] = $shortParameters;

        $result['sNumberPages'] = count($result['sPages']['numbers']);

        if ($config['sTemplate']) {
            $result['sTemplate'] = $config['sTemplate'];
        }

        if ($config['sSort']) {
            $result['sSort'] = $config['sSort'];
        }

        return $result;
    }

    /**
     * Helper function which loads a full product struct and converts the product struct
     * to the shopware 3 array structure.
     *
     * @param $number
     * @param $categoryId
     * @param array $selection
     * @return array
     */
    private function getProduct($number, $categoryId, array $selection)
    {
        $context = $this->contextService->get();
        $product = $this->productService->get(
            $number,
            $context
        );

        if (!$product) {
            return array();
        }

        $data = $this->legacyStructConverter->convertProductStruct($product, $categoryId);

        $relatedArticles = array();
        foreach($data['sRelatedArticles'] as $related) {
            $related = $this->legacyEventManager->firePromotionByIdEvents($related, null, $this);
            if ($related) {
                $relatedArticles[] = $related;
            }
        }
        $data['sRelatedArticles'] = $relatedArticles;

        $similarArticles = array();
        foreach($data['sSimilarArticles'] as $similar) {
            $similar = $this->legacyEventManager->firePromotionByIdEvents($similar, null, $this);
            if ($similar) {
                $similarArticles[] = $similar;
            }
        }
        $data['sSimilarArticles'] = $similarArticles;

        $data['categoryID'] = $categoryId;

        $average = $this->voteService->getAverage($product, $context);

        if ($average instanceof StoreFrontBundle\Struct\Product\VoteAverage) {
            $data['sVoteAverange'] = $this->legacyStructConverter->convertVoteAverageStruct($average);
        }

        $configurator = $this->configuratorService->getProductConfigurator(
            $product,
            $context,
            $selection
        );

        $convertedConfigurator = $this->legacyStructConverter->convertConfiguratorStruct($product, $configurator);

        $data = array_merge($data, $convertedConfigurator);

        $data = array_merge($data, $this->getLinksOfProduct($product, $categoryId));

        $data["articleName"] = $this->sOptimizeText($data["articleName"]);
        $data["description_long"] = htmlspecialchars_decode($data["description_long"]);

        $data["sDescriptionKeywords"] = $this->getDescriptionKeywords(
            $data["description_long"]
        );

        if ($this->showArticleNavigation()) {
            $data["sNavigation"] = $this->sGetAllArticlesInCategory($product->getId());
        }

        return $data;
    }

    /**
     * @param $categoryId
     * @param array $config
     * @param StoreFrontBundle\Struct\Context $context
     * @return SearchBundle\Criteria
     */
    private function getListingCriteria($categoryId, array $config, StoreFrontBundle\Struct\Context $context)
    {
        $criteria = new SearchBundle\Criteria();

        $criteria->addCategoryCondition(array($categoryId));

        $criteria->addCustomerGroupCondition(
            array($context->getCurrentCustomerGroup()->getId())
        );

        $criteria->limit($config['sPerPage']);

        $criteria->offset(($config['sPage'] - 1) * $config['sPerPage']);

        if (!empty($config['sFilterProperties'])) {
            $criteria->addPropertyCondition(
                explode('|', $config['sFilterProperties'])
            );
        }

        if ($config['shippingFree']) {
            $criteria->addShippingFreeCondition();
        }

        if ($config['immediateDelivery']) {
            $criteria->addImmediateDeliveryCondition();
        }

        if (!empty($config['sSupplier'])) {
            $criteria->addManufacturerCondition(
                array($config['sSupplier'])
            );
        }

        if ($config['priceMax'] || $config['priceMin']) {
            $criteria->addPriceCondition(
                (float) $config['priceMin'],
                (float) $config['priceMax']
            );
        }

        switch ($config['sSort']) {
            case 1:
                $criteria->sortByReleaseDate(SearchBundle\SortingInterface::SORT_DESC);
                break;
            case 2:
                $criteria->sortByPopularity(SearchBundle\SortingInterface::SORT_DESC);
                break;
            case 3:
                $criteria->sortByCheapestPrice();
                break;
            case 4:
                $criteria->sortByHighestPrice();
                break;
            case 5:
                $criteria->sortByProductName();
                break;
            case 6:
                $criteria->sortByProductName(SearchBundle\SortingInterface::SORT_DESC);
                break;
            default:
                $criteria->sortByReleaseDate(SearchBundle\SortingInterface::SORT_DESC);
        }

        $criteria->addPriceFacet()
            ->addShippingFreeFacet()
            ->addImmediateDeliveryFacet()
            ->addManufacturerFacet();

        if ($this->config->get('displayFiltersInListings', true)) {
            $criteria->addPropertyFacet();
        }

        return $this->eventManager->filter('Shopware_Listing_Filter_Criteria', $criteria, array(
            'categoryConfig' => $config,
            'context' => $context
        ));
    }

    private function getImmediateDeliveryFacet(SearchBundle\Facet\ImmediateDeliveryFacet $facet, $config)
    {
        $params = $this->getListingLinkParameters($config);
        $params['immediateDelivery'] = 1;
        $link = $this->buildListingLink($params);

        unset($params['immediateDelivery']);

        return array(
            'active' => ($config['immediateDelivery']),
            'removeLink' => $this->buildListingLink($params),
            'link' => $link,
            'total' => $facet->getTotal()
        );
    }

    private function getShippingFreeFacet(SearchBundle\Facet\ShippingFreeFacet $facet, $config)
    {
        $params = $this->getListingLinkParameters($config);
        $params['shippingFree'] = 1;
        $link = $this->buildListingLink($params);

        unset($params['shippingFree']);

        return array(
            'active' => ($config['shippingFree']),
            'removeLink' => $this->buildListingLink($params),
            'link' => $link,
            'total' => $facet->getTotal()
        );
    }

    private function getPriceFacet(SearchBundle\Facet\PriceFacet $facet, $config)
    {
        $params = $this->getListingLinkParameters($config);
        unset($params['priceMin']);
        unset($params['priceMax']);

        return array(
            'active' => (isset($config['priceMax']) && $config['priceMax'] > 0),
            'removeLink' => $this->buildListingLink($params),
            'range' => array(
                'min' => $facet->getMinPrice(),
                'max' => $facet->getMaxPrice()
            )
        );
    }

    private function getFacetManufacturers(
        SearchBundle\Facet\ManufacturerFacet $facet,
        array $config
    ) {
        $manufacturers = $facet->getManufacturers();

        $data = array();
        $params = $this->getListingLinkParameters($config);

        $filteredManufacturer = null;
        foreach ($manufacturers as $struct) {

            $params = array_merge($params, array('sSupplier' => $struct->getId()));

            $manufacturer = $this->legacyStructConverter->convertManufacturerStruct($struct);
            $manufacturer['supplier_link'] = $manufacturer['link'];
            $manufacturer['link'] = $this->buildListingLink($params);

            $attribute = $struct->getAttribute('facet');
            $manufacturer['countSuppliers'] = $attribute->get('total');

            $data[$struct->getId()] = $manufacturer;
        }
        $limit = 30;
        if ($this->config->get('maxSuppliersCategory')) {
            $limit = (int) $this->config->get('maxSuppliersCategory');
        }

        return array_slice($data, 0, $limit);
    }

    private function getActiveListingSupplier($suppliers, $config)
    {
        if (!$config['sSupplier']) {
            return array();
        }

        $activeSupplier = array();
        foreach ($suppliers as $supplier) {
            if ($supplier['id'] == $config['sSupplier']) {
                $activeSupplier = $supplier;
            }
        }

        $params = $this->getListingLinkParameters($config);

        unset($params['sSupplier']);

        $activeSupplier['link'] = $this->buildListingLink($params);

        return $activeSupplier;
    }

    private function getFacetProperties(
        SearchBundle\Facet\PropertyFacet $facet,
        array $config
    ) {

        $properties = $facet->getProperties();

        $data = array();
        foreach ($properties as $propertySet) {
            $data[] = $this->legacyStructConverter->convertPropertySetStruct(
                $propertySet,
                $properties,
                $config
            );
        }

        $filteredOptions = explode('|', $config['sFilterProperties']);
        $params = $this->getListingLinkParameters($config);

        $grouped = array();
        $flat = array();

        foreach ($data as &$set) {
            $activeSetGroups = array();

            $groups = array();
            foreach ($set['groups'] as &$group) {

                $activeGroupOptions = array();
                $options = array();

                foreach ($group['options'] as &$option) {
                    $currentFilters = array_merge(
                        $filteredOptions,
                        array($option['id'])
                    );

                    $params = array_merge(
                        $params,
                        array(
                            'sFilterProperties' => implode('|', $currentFilters)
                        )
                    );

                    $option['link'] = $this->buildListingLink($params);

                    $option['active'] = $option['attributes']['facet']['active'];

                    $option['total'] = $option['attributes']['facet']['total'];

                    if ($option['active']) {
                        $activeGroupOptions[] = $option['id'];
                    }

                    //legacy convert
                    $options[$option['name']] = array(
                        'name' => $group['name'],
                        'value' => $option['name'],
                        'count' => $option['total'],
                        'group' => $set['name'],
                        'optionID' => $group['id'],
                        'link' => $option['link'],
                        'active' => $option['active']
                    );
                }

                $group['active'] = (bool) (!empty($activeGroupOptions));

                if ($group['active']) {
                    $activeSetGroups[] = $group['id'];

                    $removeOptions = array_diff($filteredOptions, $activeGroupOptions);

                    $params = array_merge($params, array(
                        'sFilterProperties' => implode('|', $removeOptions)
                    ));

                    $group['removeLink'] = $this->buildListingLink($params);
                }

                $set['active'] = (bool) (!empty($activeSetGroups));

                //legacy convert
                $groups[$group['name']] = $options;
                $flat[$group['name']] = array(
                    'properties' => array(
                        'active' => $group['active'],
                        'group' => $set['name'],
                        'linkRemoveProperty' => $group['removeLink']
                    ),
                    'values' => $options
                );
            }

            //legacy convert
            $params = $this->getListingLinkParameters($config);
            unset($params['sFilterProperties']);
            $params['sFilterGroup'] = $set['name'];
            $grouped[$set['name']] = array(
                'options' => $groups,
                'default' => array(
                    'linkSelect' => $this->buildListingLink($params)
                )
            );
        }

        $result = array(
            'sPropertiesOptionsOnly' => $flat,
            'sPropertiesGrouped' => $grouped
        );

        return $result;
    }

    /**
     * Helper function which builds the listing links with all required parameters.
     *
     * @param $params
     * @return string
     */
    private function buildListingLink($params)
    {
        return $this->sSYSTEM->sCONFIG['sBASEFILE'] .
        Shopware()->Modules()->Core()->sBuildLink($params);
    }

    /**
     * Helper function which returns all category listing configurations
     * which are required for the listing links like "add filter", "next page", ...
     *
     * @param $config
     * @return array
     */
    private function getListingLinkParameters($config)
    {
        $params = array();
        $shopwareConfig = Shopware()->Config();

        $default = 1;
        if ($config['sSort'] && $config['sSort'] != $default) {
            $params['sSort'] = $config['sSort'];
        }

        if ($config['sFilterProperties']) {
            $params['sFilterProperties'] = $config['sFilterProperties'];
        }
        if ($config['sSupplier']) {
            $params['sSupplier'] = $config['sSupplier'];
        }

        $default = $shopwareConfig->get('articlesPerPage');
        if ($config['sPerPage'] && $config['sPerPage'] != $default) {
            $params['sPerPage'] = $config['sPerPage'];
        }

        if ($config['priceMin']) {
            $params['priceMin'] = $config['priceMin'];
        }

        if ($config['priceMax']) {
            $params['priceMax'] = $config['priceMax'];
        }

        if ($config['sTemplate']) {
            $params['sTemplate'] = $config['sTemplate'];
        }

        if ($config['shippingFree']) {
            $params['shippingFree'] = $config['shippingFree'];
        }

        if ($config['immediateDelivery']) {
            $params['immediateDelivery'] = $config['immediateDelivery'];
        }

        return $this->eventManager->filter('Shopware_Listing_Filter_Listing_Link_Parameters', $params, array(
            'config' => $config
        ));
    }

    /**
     * Generates the template array for the different page sizes of a listing.
     *
     * Returns an array for each configured pages size of the settings backend module.
     *
     * The sizes are stored in the configuration field sNumberArticlesToShow.
     *
     * Each size array contains a field "value" with the page size,
     * a field "markup" if the size is currently selected and a field
     * "link" which contains a link to change the page size.
     *
     * @param $config
     * @return array
     */
    private function createListingPerPageLinks($config)
    {
        $pageSizes = explode("|", $this->config->get('numberArticlesToShow'));

        $sizes = array();

        $params = $this->getListingLinkParameters($config);

        $currentSize = $config['sPerPage'];

        foreach ($pageSizes as $size) {
            $params = array_merge($params, array('sPerPage' => $size));

            $sizeData = array(
                'markup' => (int) ($size == $currentSize),
                'value' => $size,
                'link' => $this->buildListingLink($params)
            );

            $sizes[] = $sizeData;
        }
        return $sizes;
    }

    /**
     * Generates the template array for the different listing pages.
     *
     * Returns an array for each available listing page.
     * The listing page count can be limit over the shopware configuration field "sMaxPages"
     *
     * Each page array contains a field "value" with the page number,
     * a field "markup" if the page is currently selected and a field
     * "link" which contains a link to change the page.
     *
     * @param $totalCount
     * @param $config
     * @return array
     */
    private function createListingPageLinks($totalCount, $config)
    {
        $currentPage = $config['sPage'];

        $count = ceil($totalCount / $config['sPerPage']);

        if ((int) $this->config->get('maxPages') > 0 && (int) $this->config->get('maxPages') < $count) {
            $count = (int) $this->config->get('maxPages');
        }

        $params = $this->getListingLinkParameters($config);

        $pages = array();
        $nextIndex = 1;
        $previousIndex = 0;

        for ($i = 1; $i <= $count; $i++) {
            $params = array_merge($params, array('sPage' => $i));

            $page = array(
                'markup' => (int) ($i == $currentPage),
                'value' => $i,
                'link' => $this->buildListingLink($params)
            );

            if ($currentPage == $i) {
                $nextIndex = $i + 1;
                $previousIndex = $i - 1;
            }

            $pages[$i] = $page;
        }

        return array(
            'numbers' => $pages,
            'previous' => $pages[$previousIndex]['link'],
            'next' => $pages[$nextIndex]['link']
        );
    }

    /**
     * Loads the listing configuration for the passed category id.
     *
     * @param $categoryId
     * @return array
     */
    private function loadCategoryConfig($categoryId)
    {
        $setup = array(
            'sSort' => 0,
            'sPerPage' => (int) $this->config->get('articlesPerPage'),
            'sSupplier' => null,
            'sFilterProperties' => null,
            'sTemplate' => null,
            'priceMin' => null,
            'priceMax' => null,
            'shippingFree' => false,
            'immediateDelivery' => false,
            'sPage' => 1
        );

        $setup = $this->eventManager->filter('Shopware_Listing_Filter_Config_Setup', $setup, array(
            'categoryId' => $categoryId
        ));

        $config = array();
        foreach($setup as $key => $default) {
            $config[$key] = $this->getConfigParameter($key, $default);
        }

        if (!empty($config['sFilterProperties'])) {
            $filters = explode('|', $config['sFilterProperties']);
            if ($filters[0] == 0) {
                unset($filters[0]);
            }
            $config['sFilterProperties'] = implode('|', $filters);
        }

        Shopware()->Session()->offsetSet('sCategoryConfig' . $categoryId, $config);

        return $config;
    }

    /**
     * Helper function which checks the different sources for the category config.
     *
     * @param $parameter
     * @param $default
     * @return null
     */
    private function getConfigParameter($parameter, $default)
    {
        $value = null;

        $get = $this->sSYSTEM->_GET;
        $post = $this->sSYSTEM->_POST;

        // Order List by
        if (isset($post[$parameter])) {
            $value = $post[$parameter];

        } elseif (isset($get[$parameter])) {
            $value = $get[$parameter];

        } else {
            $value = $default;
        }

        return $value;
    }

    private function getShortParameters()
    {
        $config = $this->config->get('seoQueryAlias');
        $config = explode(',', $config);

        $params = array();
        foreach ($config as $alias) {
            $alias = explode('=', $alias);

            $key = trim($alias[0]);
            $value = trim($alias[1]);

            $params[$key] = $value;
        }
        return $params;
    }

    private function replaceParameters($params, $shortParameters)
    {
        foreach ($shortParameters as $key => $value) {
            if (array_key_exists($key, $params)) {
                $params[$value] = $params[$key];
                unset($params[$key]);
            }
        }

        return $params;
    }

    /**
     * Creates different links for the product like `add to basket`, `add to note`, `view detail page`, ...
     *
     * @param StoreFrontBundle\Struct\ListProduct $product
     * @param null $categoryId
     * @return array
     */
    private function getLinksOfProduct(StoreFrontBundle\Struct\ListProduct $product, $categoryId = null)
    {
        $baseFile = $this->config->get('baseFile');

        $detail = $baseFile . "?sViewport=detail&sArticle=" . $product->getId();
        if ($categoryId) {
            $detail .= '&sCategory=' . $categoryId;
        }
        $rewrite = Shopware()->Modules()->Core()->sRewriteLink($detail, $product->getName());

        $basket = $baseFile . "?sViewport=basket&sAdd=" . $product->getNumber();
        $note = $baseFile . "?sViewport=note&sAdd=" . $product->getNumber();
        $friend = $baseFile . "?sViewport=tellafriend&sDetails=" . $product->getId();
        $pdf = $baseFile . "?sViewport=detail&sDetails=" . $product->getId() . "&sLanguage=" . $this->sSYSTEM->sLanguage . "&sPDF=1";

        return array(
            'linkBasket' => $basket,
            'linkDetails' => $detail,
            'linkDetailsRewrited' => $rewrite,
            'linkNote' => $note,
            'linkTellAFriend' => $friend,
            'linkPDF' => $pdf,
        );
    }

    private function getDescriptionKeywords($longDescription)
    {
        //sDescriptionKeywords
        $string = (strip_tags(html_entity_decode($longDescription, null, 'UTF-8')));
        $string = str_replace(',', '', $string);
        $words = preg_split('/ /', $string, -1, PREG_SPLIT_NO_EMPTY);
        $badwords = explode(",", $this->config->get('badwords'));
        $words = array_diff($words, $badwords);
        $words = array_count_values($words);
        foreach (array_keys($words) as $word) {
            if (strlen($word) < 2) {
                unset($words[$word]);
            }
        }
        arsort($words);

        return htmlspecialchars(
            implode(", ", array_slice(array_keys($words), 0, 20)),
            ENT_QUOTES,
            'UTF-8',
            false
        );
    }

    /**
     * Returns a single order number for the passed product configuration selection.
     * Used for the product detail page.
     *
     * @param $productId
     * @param array $selection
     * @return mixed
     */
    private function getNumberBySelection($productId, array $selection)
    {
        $query = Shopware()->Models()->getDBALQueryBuilder();
        $query->select(array('variant.ordernumber'))
            ->from('s_articles_details', 'variant')
            ->where('variant.articleID = :productId')
            ->andWhere('variant.active = 1')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->setParameter(':productId', $productId);

        foreach ($selection as $optionId) {
            $alias = 'option_' . (int) $optionId;

            $query->innerJoin(
                'variant',
                's_article_configurator_option_relations',
                $alias,
                'variant.id = ' . $alias . '.article_id
                 AND ' . $alias . '.option_id = :' . $alias
            );
            $query->setParameter(':' . $alias, (int) $optionId);
        }

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * Helper function which checks the different parameters which has to
     * be considered on the product detail page.
     *
     * The passed $selection has the highest priority. This property contains
     * the customer selection of the configuration options.
     * If the selection isn't empty, the function returns the first possible
     * variant number for the selection.
     *
     * The passed $number parameter has the second priority. If an order number
     * passed to the product detail page, the detail page should be loaded
     * directly for the specify product variation.
     *
     * At least the passed $id parameter is used to get the order number
     * of the main variation.
     *
     * @param $id
     * @param $number
     * @param $selection
     * @return mixed|string
     */
    private function getCurrentProductNumber($id, $number, $selection)
    {
        $selected = null;
        if (!empty($selection)) {
            $selected = $this->getNumberBySelection($id, $selection);
        }

        if ($selected) {
            return $selected;
        }

        $query = Shopware()->Models()->getDBALQueryBuilder();
        $query->select(array('variant.ordernumber'));
        $query->from('s_articles_details', 'variant');

        $query->innerJoin(
            'variant',
            's_articles',
            'product',
            'product.id = variant.articleID
             AND (product.laststock * variant.instock) >= (product.laststock * variant.minpurchase)
             AND variant.active = 1'
        );

        if ($number !== null) {
            $query->where('variant.ordernumber = :number')
                ->setParameter(':number', $number);

            $statement = $query->execute();
            $selected = $statement->fetch(\PDO::FETCH_COLUMN);
        }

        if ($selected) {
            return $selected;
        }

        $query->where('variant.id = product.main_detail_id')
            ->andWhere('product.id = :number')
            ->setParameter(':number', $id);

        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * Helper function which used to get the configuration selection of
     * the passed product number.
     * The result array contains a simple array which elements are indexed by
     * the configurator group id and the value contains the configurator option id.
     *
     * This function is required to load different product variations on the product
     * detail page via order number.
     *
     * @param $number
     * @return array
     */
    private function getConfigurationByNumber($number)
    {
        $query = Shopware()->Models()->getDBALQueryBuilder();
        $query->select(array('groups.id', 'options.id'))
            ->from('s_article_configurator_option_relations', 'configuration');

        $query->innerJoin(
            'configuration',
            's_article_configurator_options',
            'options',
            'options.id = configuration.option_id'
        );

        $query->innerJoin(
            'options',
            's_article_configurator_groups',
            'groups',
            'groups.id = options.group_id'
        );

        $query->innerJoin(
            'configuration',
            's_articles_details',
            'variant',
            'variant.id = configuration.article_id
             AND variant.ordernumber = :number'
        );

        $query->setParameter(':number', $number);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * Helper function which checks which product id should be used.
     * If only a product number passed to the product detail page,
     * the function returns the associated product id for the product variation.
     *
     * The product id is required for following selections.
     *
     * @param $id
     * @param null $number
     * @return string
     */
    private function getCurrentProductId($id, $number = null)
    {
        if ($number == null) {
            return $id;
        }

        return $this->db->fetchOne(
            "SELECT articleID FROM s_articles_details WHERE ordernumber = ?",
            array($number)
        );
    }

    /**
     * Helper function which checks the passed $selection parameter for empty.
     * If this is the case the function implements the fallback on the legacy
     * _POST access to get the configuration selection directly of the request object.
     *
     * Additionally the function removes empty array elements.
     * Array elements of the configuration selection can be empty, if the user resets the
     * different group selections.
     *
     * @param $selection
     * @return mixed
     */
    private function getCurrentConfiguration($selection)
    {
        if (empty($selection) && Shopware()->Front() && Shopware()->Front()->Request()) {
            $selection = Shopware()->Front()->Request()->getParam('group');
        }

        foreach ($selection as $groupId => $optionId) {
            if (!$groupId || !$optionId) {
                unset($selection[$groupId]);
            }
        }

        return $selection;
    }
}
