<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Components_Search_Adapter_Fuzzy
 * @subpackage Adapter
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stefan Hamann
 * @author     Heiner Lohaus
 * @author     $Author$
 */
class Shopware_Components_Search_Adapter_Fuzzy extends Shopware_Components_Search_Adapter_Default
{
    /**
     * @var int
     */
    public $limitMatchingKeywords = 8;
    /**
     * @var int
     */
    public $limitSimilarSearchRequests = 8;


    /**
     * Load property groups / options / values depending from search result
     * @param $searchResults
     */
    public function executeCustomFilters($searchResults){
        $this->getCountPropertyFilters($searchResults);

        if (!empty($this->requestFilter["propertyOption"])){
            $this->getCountPropertyOptions($searchResults);
        }
    }

    /**
     * Get similar search terms & similar search requests
     */
    public function getSimilarSearchTerms()
    {
        $terms = $this->getTerms();
        if (empty($terms)){
            return;
        }

        $keywords = $this->getMatchingKeywords();
        $keywords = array_slice($keywords, 0, $this->limitMatchingKeywords);
        $terms = $this->getTerms();
        $fullTerm = $this->database->quote(implode(" ",$terms));

        // Do you mean - spelling
        $keywordsResult = array();
        foreach ($keywords as $keyword) {
            if($keyword['keyword'] === $keyword['term']) {
                continue;
            }
            $position = array_search($keyword['term'], $terms);
            if($position === false) {
                continue;
            }
            $newTerm = $terms;
            $newTerm[$position] = $keyword['keyword'];
            $newTerm = implode(' ', $newTerm);
            $keywordsResult[] = array(
                'keyword' => $newTerm
            );
        }
        $this->getResult()->setResultMatchingKeywords($keywordsResult);

        // Similar search requests
        $similarTerms = $terms;
        foreach ($keywords as $keyword) {
            $similarTerms[] = $keyword['keyword'];
        }
        $similarTerms = array_unique($similarTerms);
        $sql = array();
        foreach ($similarTerms as $term){
            $term = $this->database->quote($term);
            $sql[] = "
                SELECT
                  s.searchterm,
                  COUNT(*) as relevance,
                  MAX(s.datum) as `date`,
                  (SELECT results FROM s_statistics_search WHERE searchterm=s.searchterm AND id=MAX(s.id)) as results
                FROM s_statistics_search s
                WHERE s.searchterm NOT LIKE {$fullTerm}
                AND (
                    s.searchterm LIKE {$term}
                    OR s.searchterm LIKE CONCAT({$term}, ' %')
                    OR s.searchterm LIKE CONCAT('% ', {$term})
                    OR s.searchterm LIKE CONCAT('% ', {$term}, ' %')
                )
                AND results > 0
                GROUP BY s.searchterm
            ";
        }
        $sql = '(' . implode(') UNION ALL (', $sql) . ')';
        $sql .= "
            ORDER BY relevance DESC, results DESC
            LIMIT {$this->limitSimilarSearchRequests}
        ";
        $result = $this->database->fetchAssoc($sql);
        $this->getResult()->setResultMatchingSearchRequests($result);
    }

    public function search($term, array $searchConfiguration){
        $result = parent::search($term, $searchConfiguration);
        $this->getSimilarSearchTerms();
        return $result;
    }
    /**
     * Modify sql search query to consider property filters
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

        // Sample-format
        // http://sth.test.shopware.in/trunk/search/index/sSearch/test/sPerPage/12/sFilter_propertygroup/1_2
        // propertyGroup_propertyValue1_propertyValue2_propertyValue3 etc.

        if (!empty($this->requestFilter["propertyGroup"])){
            $filterByProperty = explode('_', $this->requestFilter["propertyGroup"]);

            // Get filter group id
            $filterByPropertyId = $this->requestFilter["propertyOption"] = (int) $filterByProperty[0];
            $sqlPropertyWhere = "";

            // If set filter option/value id
            if(count($filterByProperty)>1) {
                $filterByPropertyOption = array_slice($filterByProperty,1);#

                foreach ($filterByPropertyOption as &$option){
                    $option = intval($option);
                }

                $sql = '
                        SELECT fv.id, fv.optionID, fr.groupID, fv.value
                        FROM s_filter f, s_filter_values fv, s_filter_options fo, s_filter_relations fr
                        WHERE fr.groupID=f.id
                        AND fr.optionID = fo.id
                        AND f.id=1
                        AND fv.id IN ('.implode(",",$filterByPropertyOption).')
                        AND fo.id=fv.optionID
                        AND fo.filterable=1
                ';

                // Get selected options
                $resultSelectedOptions = $this->database->fetchAll($sql,array($filterByPropertyId));

                // Build sql-query to filter search result
                if (!empty($resultSelectedOptions)){
                    $sqlPropertySelect = "";
                    $resultSelectedOptionsTemp = array();
                    foreach ($resultSelectedOptions as $filter) {
                        $resultSelectedOptionsTemp[$filter["optionID"]] = $filter;
                            $sqlPropertySelect .= "
                            INNER JOIN s_filter_articles fv{$filter['id']}
                            ON fv{$filter['id']}.articleID = a.id
                            AND fv{$filter['id']}.valueID = ".$this->database->quote($filter['id'])."
                            ";
                    }

                    $this->requestFilter["propertySelectedOptions"] = $resultSelectedOptionsTemp;
                }
            }

            $sqlPropertyWhere .= ' AND a.filtergroupID='.intval($filterByPropertyId);
        }


        // Sample: AND a.filtergroupID=1
        $sqlWhere .= $sqlPropertyWhere;

        /**
         * Sample:
         * (For every selected option/value pair)
         * INNER JOIN s_filter_articles fv2 ON fv2.articleID = a.id AND fv2.valueID = '2' INNER JOIN s_filter_articles fv3 ON fv3.articleID = a.id AND fv3.valueID = '3'
         */
        $sqlFrom .= $sqlPropertySelect;


        return parent::getSearchExecuteQuery($sqlRelevanceField, $sqlPriceField, $sqlNameField, $sqlDescriptionField, $sqlFrom, $sqlCategoryFilter, $sqlTranslation, $sqlWhere, $sqlHaving);
    }

    /**
     * Get property options & values for the current search request
     * Write result to search result object
     * @param $searchResults
     */
    public function getCountPropertyOptions($searchResults){
        $sql = "
               SELECT fv.id as valueID, fo.id as optionID, fv.value as name, COUNT(*) as count,
               st4.objectdata AS valueTranslation
               FROM s_filter f, s_filter_values fv
               LEFT JOIN s_core_translations AS st4 ON st4.objecttype='propertyvalue' AND st4.objectkey=fv.id AND st4.objectlanguage=?,
               s_filter_articles fArticles,s_filter_options fo, s_filter_relations fr
               WHERE f.id=?
               AND fo.id=fv.optionID
               AND fo.filterable=1
               AND fArticles.valueID = fv.id
               AND fArticles.articleID IN (".implode(",",array_keys($searchResults)).")
               AND fr.optionID=fo.id
               AND fr.groupID=f.id
               GROUP BY fv.optionID, fv.value
               ORDER BY fr.position, IF(f.sortmode=1, TRIM(REPLACE(fv.value,',','.'))+0, 0), IF(f.sortmode=2, COUNT(*) , 0) DESC, fv.value
       ";

        $result = $this->database->fetchAll($sql,array($this->requestShopLanguageId,$this->requestFilter["propertyOption"]));
        if ($result!==false){
            foreach ($result as &$row){
                if (!empty($row["valueTranslation"])){
                    $translation = unserialize($row["valueTranslation"]);
                    $row["name"] = $translation["optionValue"];
                }
                $resultValues[$row["optionID"]][$row["valueID"]] = $row;
            }

            $sql = "
                   SELECT fo.id, fo.id as optionID, fo.name,st.objectdata AS optionNameTranslation
                   FROM s_filter_options fo
                   LEFT JOIN s_core_translations AS st ON st.objecttype='propertyoption' AND st.objectkey=fo.id AND st.objectlanguage=?,
                   s_filter_relations fr
                   WHERE fo.id IN (".implode(",",array_keys($resultValues)).")
                   AND fr.groupID=?
                   AND fo.filterable=1
                   AND fr.optionID=fo.id
                   ORDER BY fr.position, fo.name
           ";
            $resultOptions = $this->database->fetchAll($sql, array($this->requestShopLanguageId, $this->requestFilter["propertyOption"]));

            foreach ($resultOptions as $optionID => $option) {

                if (!empty($option["optionNameTranslation"])) {
                    $translation = unserialize($option["optionNameTranslation"]);
                    $option["name"] = $translation["optionName"];
                }
                $option["selected"] = isset($this->requestFilter["propertySelectedOptions"][$option["id"]]['id']) ? $this->requestFilter["propertySelectedOptions"][$option["id"]]['id'] : false;
                $resultOptionsTemp[$option["id"]] = $option;
            }
            $resultOptions = $resultOptionsTemp;
        }


        $this->getResult()->setAffectedPropertyOptions($resultOptions);
        $this->getResult()->setAffectedPropertyValues($resultValues);
    }

    /**
     * Get Property Filter Groups
     * @param $searchResults
     * @return array
     * @throws Enlight_Exception
     */
    public function getCountPropertyFilters($searchResults)
    {
        $articleIds = $this->database->quote(array_keys($searchResults));
        $sql = "
           SELECT f.id, f.id as filerID, f.name, COUNT(DISTINCT fa.articleID) as count, st2.objectdata AS groupNameTranslation
           FROM s_filter_articles fa, s_filter_values fv, s_filter_options fo, s_filter_relations fr, s_filter f

           LEFT JOIN s_core_translations AS st2
           ON st2.objecttype='propertygroup'
           AND st2.objectkey=f.id AND st2.objectlanguage=?

           WHERE fr.groupID=f.id
           AND fr.optionID = fv.optionID
           AND fo.id=fv.optionID
           AND fo.filterable=1
           AND fa.valueID = fv.id
           AND fa.articleID IN ($articleIds)
           GROUP BY f.id
           ORDER BY f.position, f.name
       ";

        try {
            $result = $this->database->fetchAll($sql, array($this->requestShopLanguageId));
        } catch (PDOException $e) {
            throw new Enlight_Exception($e->getMessage());
        }

        foreach ($result as &$value) {
            if (!empty($value["groupNameTranslation"])) {
                $translation = unserialize($value["groupNameTranslation"]);
                if (!empty($translation["groupName"])) {
                    $value["name"] = $translation["groupName"];
                }
            }
        }

        $this->getResult()->setAffectedProperties($result);

        return $result;
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

        $this->database->query("
          UPDATE `s_search_keywords` SET `soundex` = IF(SOUNDEX(`keyword`)='', NULL, SOUNDEX(`keyword`))
        ");
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
        $fuzzySearchDistance = Shopware()->Config()->get('fuzzySearchDistance');

        if (($keywords = $cache->load($id)) !== false) {
            return $keywords;
        }

        $keywords = array();

        $sql = '
            SELECT `id` , `keyword`
            FROM `s_search_keywords`
            WHERE
            (
                soundex IS NOT NULL
                AND SOUNDEX(?)
                AND soundex LIKE CONCAT(LEFT(SOUNDEX(?), 4), \'%\')
            )
            OR keyword LIKE CONCAT(\'%\', ?, \'%\')
            OR keyword LIKE CONCAT(LEFT(?, 2),\'%\')
        ';
        $result = $this->database->query($sql, array($term, $term,$term,$term));

        while ($keyword = $result->fetch()) {
            $keywordID = $keyword['id'];
            $keyword = $keyword['keyword'];

            if (strlen($term) < strlen($keyword)) {
                $term1 = $keyword;
                $term2 = $term;
            }
            else {
                $term2 = $keyword;
                $term1 = $term;
            }

            $relevance = 0;

            if ($term1 === $term2) { // Terms are similar
                $relevance = $this->configSearchExactMatchFactor;
            } elseif (strpos($term1, $term2) !== false) { // Check for sub term matching
                if (strlen($term1) < 4) {
                    $relevance = $this->configSearchMatchFactor;
                } elseif (strlen($term1) - strlen($term2) <= 1) { //ipod === ipods
                    $relevance = $this->configSearchExactMatchFactor;
                } elseif ((round(strlen($term2) / strlen($term1), 2) * 100) >= $this->configSearchPartNameDistance) { //digital == digi
                    $relevance = $this->configSearchPatternMatchFactor;
                }
            } elseif (round(1-levenshtein($term, $keyword)/strlen($term1),2) * 100 >= $fuzzySearchDistance) { //ipod = ipop
                $relevance = $this->configSearchMatchFactor;
            }
            if (!empty($relevance)) {
                $keywords[] = array(
                    "relevance" => $relevance, "term" => $term,
                    "keywordID" => $keywordID, "keyword" => $keyword
                );
                $sort[] = $relevance;
            }
        }

        array_multisort($sort, SORT_NUMERIC, SORT_DESC, $keywords);

        $cache->save($keywords, $id, array('Shopware_Modules_Search'), $this->configSearchCache);

        return $keywords;
    }
}