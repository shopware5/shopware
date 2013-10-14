<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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
 * @category  Shopware
 * @package   Shopware\Controllers\Frontend
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_Search extends Enlight_Controller_Action
{
    /**
     * Index action method
     *
     * @return void
     */
    public function indexAction()
    {
        if ($this->Request()->sSearchMode == "supplier") {
            return $this->forward("supplierSearch");
        }
        return $this->forward("defaultSearch");
    }

    /**
     * Method that is used for "search other articles from this vendor"
     * @deprecated Please use the Listing controller index action. The listing function expects the supplier id
     * in the request parameter sSupplier.
     */
    public function supplierSearchAction()
    {
        $search = $this->Request()->sSearch;

        $variables = Shopware()->Modules()->Articles()->sGetArticlesByName('a.name ASC', '', 'supplier', $search);
        $search = $this->Request()->sSearchText;


        foreach ($variables['sPerPage'] as $perPageKey => &$perPage) {
            $perPage['link'] = str_replace('sPage=' . $this->Request()->sPage, 'sPage=1', $perPage['link']);
        }

        $searchResults = $variables['sArticles'];

        foreach ($searchResults as $searchResult) {
            if (is_array($searchResult)) {
                $searchResult = $searchResult['id'];
            }
            $article = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, (int)$searchResult);
            if (!empty($article['articleID'])) {
                $articles[] = $article;
            }
        }

        $this->View()->loadTemplate('frontend/search/index.tpl');
        $this->View()->sSearchResults = $articles;
        $this->View()->sSearchResultsNum = empty($variables['sNumberArticles']) ? count($articles) : $variables['sNumberArticles'];
        $this->View()->sSearchTerm = $search;
        $this->View()->sPages = $variables['sPages'];
        $this->View()->sPerPage = $variables['sPerPage'];
        $this->View()->sNumberPages = $variables['sNumberPages'];
        $this->View()->sPage = $this->Request()->sPage;
    }

    /**
     * Get search configuration based on shop properties
     * @param string $term
     * @return array
     */
    public function getSearchConfiguration($term)
    {
        $config = array();
        $config["term"] = $config["sSearch"] = $term;
        $config['restrictSearchResultsToCategory'] = Shopware()->Shop()->get('parentID');
        $config['filter']['supplier'] = $config['sFilter']['supplier'] = (int)$this->Request()->sFilter_supplier;
        $config['filter']['category'] = $config['sFilter']['category'] = (int)$this->Request()->sFilter_category;
        $config['filter']['price'] = $config['sFilter']['price'] = (int)$this->Request()->sFilter_price;
        $config['filter']['propertyGroup'] = $this->Request()->sFilter_propertygroup;
        $config['filter']['propertygroup'] = $config['filter']['propertyGroup'];
        $config['sFilter']['propertygroup']= $config['filter']['propertyGroup'];

        $config['sortSearchResultsBy'] = $config["sSort"] = (int)$this->Request()->sSort;
        $config['sortSearchResultsByDirection'] = (int)$this->Request()->sOrder;

        if (!empty($this->Request()->sPage)) {
            $config['currentPage'] = (int)$this->Request()->sPage;
        } else {
            $config['currentPage'] = 1;
        }

        if (!empty($this->Request()->sPerPage)) {
            $config['resultsPerPage'] = (int)$this->Request()->sPerPage;
        } elseif (!empty(Shopware()->Config()->sFUZZYSEARCHRESULTSPERPAGE)) {
            $config['resultsPerPage'] = (int)Shopware()->Config()->sFUZZYSEARCHRESULTSPERPAGE;
        } else {
            $config['resultsPerPage'] = 8;
        }

        $config["sPerPage"] = $config["resultsPerPage"];

        $config['sSearchOrginal'] = $config['term'];
        $config['sSearchOrginal'] = htmlspecialchars($config['sSearchOrginal']);

        $config["shopLanguageId"] = Shopware()->Shop()->getId();
        $config["shopHasTranslations"] = Shopware()->Shop()->get('skipbackend') == true ? false : true;
        //$config["shopCurrency"] = Shopware()->Shop()->Currency();
        // todo@all Change Call to system class @deprecated
        $config["shopCustomerGroup"] = Shopware()->System()->sUSERGROUP;
        $config["shopCustomerGroupDiscount"] = Shopware()->System()->sUSERGROUPDATA["discount"];
        $config["shopCustomerGroupMode"] = Shopware()->System()->sUSERGROUPDATA["mode"];
        $config["shopCustomerGroupTax"] = Shopware()->System()->sUSERGROUPDATA["tax"];
        $config["shopCustomerGroupId"] = Shopware()->System()->sUSERGROUPDATA["id"];
        $config["shopCurrencyFactor"] = Shopware()->System()->sCurrency["factor"];

        return $config;
    }

    /**
     * Default search
     */
    public function defaultSearchAction()
    {
        $term = trim(strip_tags(htmlspecialchars_decode(stripslashes($this->Request()->sSearch))));
        //we have to strip the / otherwise broken urls would be created e.g. wrong pager urls
        $term = str_replace("/","",$term);

        // Load search configuration
        $config = $this->getSearchConfiguration($term);

        // Check if we have a one to one match for ordernumber, then redirect
        $location = $this->searchFuzzyCheck($term);
        if (!empty($location)) {
            return $this->redirect($location);
        }

        $this->View()->loadTemplate('frontend/search/fuzzy.tpl');

        // Prepare links for template
        $links = $this->searchDefaultPrepareLinks($config);

        $minLengthSearchTerm = Shopware()->Config()->sMINSEARCHLENGHT;

        // Check if search term met minimum length
        if (strlen($term) >= (int)$minLengthSearchTerm) {
            // Configure search adapter
            $adapter = Enlight()->Events()->filter('Shopware_Controllers_Frontend_Search_SelectAdapter',null);
            if (empty($adapter)){
                $adapter = new Shopware_Components_Search_Adapter_Default(Shopware()->Db(), Shopware()->Cache(), new Shopware_Components_Search_Result_Default(), Shopware()->Config());
            }

            $search = new Shopware_Components_Search($adapter);
            // Submit search request
            $searchResults = $search->search($term, $config);
            // Initiate variables
            $resultCount = 0;
            $resultArticles = array();
            $resultSuppliersAffected = array();
            $resultPriceRangesAffected = array();
            $resultCurrentCategory = array();

            // If search has results
            if ($searchResults !== false) {
                $resultCount = $searchResults->getResultCount();
                $resultArticles = $searchResults->getResult();
                $resultSuppliersAffected = $searchResults->getAffectedSuppliers();
                $resultPriceRangesAffected = $searchResults->getAffectedPriceRanges();
                $resultAffectedCategories = $searchResults->getAffectedCategories();
                $resultCurrentCategory = $searchResults->getCurrentCategoryFilter();
            }

            // Generate page array
            $sPages = $this->generatePagesResultArray($resultCount, $config['resultsPerPage'], $config["currentPage"]);

            // Get additional information for each search result
            $articles = array();
            foreach ($resultArticles as $article) {
                $article = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, (int)$article["articleID"]);
                if (!empty($article['articleID'])) {
                    $articles[] = $article;
                }
            }

            $resultSmartyArray = array(
                'sArticles' => $articles,
                'sArticlesCount' => $resultCount,
                'sSuppliers' => $resultSuppliersAffected,
                'sPrices' => $resultPriceRangesAffected,
                'sCategories' => $resultAffectedCategories,
                'sLastCategory' => $resultCurrentCategory
            );


            // Assign result to template
            $this->View()->sRequests = $config;
            $this->View()->sSearchResults = $resultSmartyArray;
            $this->View()->sPerPage = array_values(explode("|", Shopware()->Config()->sFUZZYSEARCHSELECTPERPAGE));
            $this->View()->sLinks = $links;
            $this->View()->sPages = $sPages;
            $this->View()->sPriceFilter = $search->getAdapter()->getPriceRanges();

            Enlight()->Events()->notify('Shopware_Controllers_Frontend_Search_ModifySearchResult',array("subject" => $this,"search"=>$search,"result"=>$searchResults));

            $this->View()->sCategoriesTree = $this->getCategoryTree(
                $resultCurrentCategory, $config['restrictSearchResultsToCategory']
            );
        }
    }

    /**
     * Generate array with pages for template
     * @param $resultCount int Count of search results
     * @param $resultsPerPage int How many products per page
     * @param $currentPage int Current page offset
     * @return array
     */
    public function generatePagesResultArray($resultCount, $resultsPerPage, $currentPage)
    {

        $numberPages = ceil($resultCount / $resultsPerPage);
        if ($numberPages > 1) {
            for ($i = 1; $i <= $numberPages; $i++) {
                $sPages['pages'][$i] = $i;
            }
            // Previous page
            if ($currentPage != 1) {
                $sPages["before"] = $currentPage - 1;
            } else {
                $sPages["before"] = null;
            }
            // Next page
            if ($currentPage != $numberPages) {
                $sPages["next"] = $currentPage +1;
            } else {
                $sPages["next"] = null;
            }
        }
        return $sPages;
    }

    /**
     * Returns a category tree
     *
     * @param int $id
     * @param int $mainId
     * @return array
     */
    protected function getCategoryTree($id, $mainId)
    {
        $sql = '
			SELECT
				`id` ,
				`description`,
				`parent`
			FROM `s_categories`
			WHERE `id`=?
		';
        $cat = Shopware()->Db()->fetchRow($sql, array($id));
        if (empty($cat['id']) || $id == $cat['parent'] || $id == $mainId) {
            return array();
        } else {
            $cats = $this->getCategoryTree($cat['parent'], $mainId);
            $cats[$id] = $cat;
            return $cats;
        }
    }

    /**
     * Prepare fuzzy search links
     *
     * @param array $config
     * @return array
     */
    protected function searchDefaultPrepareLinks(array $config)
    {
        $links = array();

        $links['sLink'] = Shopware()->Config()->BaseFile . '?sViewport=search';
        $links['sLink'] .= '&sSearch=' . urlencode($config['term']);
        $links['sSearch'] = $this->Front()->Router()->assemble(array('sViewport' => 'search'));

        $links['sPage'] = $links['sLink'];
        $links['sPerPage'] = $links['sLink'];
        $links['sSort'] = $links['sLink'];

        $links['sFilter']['category'] = $links['sLink'];
        $links['sFilter']['supplier'] = $links['sLink'];
        $links['sFilter']['price'] = $links['sLink'];
        $links['sFilter']['propertygroup'] = $links['sLink'];

        $filterTypes = array('supplier', 'category', 'price', 'propertygroup');

        foreach ($filterTypes as $filterType) {
            if (empty($config['filter'][$filterType])) {
                continue;
            }
            $links['sPage'] .= "&sFilter_$filterType=" . $config['filter'][$filterType];
            $links['sPerPage'] .= "&sFilter_$filterType=" . $config['filter'][$filterType];
            $links['sSort'] .= "&sFilter_$filterType=" . $config['filter'][$filterType];

            foreach ($filterTypes as $filterType2) {
                if ($filterType != $filterType2) {
                    $links['sFilter'][$filterType2] .= "&sFilter_$filterType=" . urlencode($config['filter'][$filterType]);
                }
            }
        }

        foreach (array('sortSearchResultsBy' => 'sSort', 'resultsPerPage' => 'sPerPage') as $property => $name) {
            if (!empty($config[$property])) {
                if ($name != 'sPage') {
                    $links['sPage'] .= "&$name=" . $config[$property];
                }
                if ($name != 'sPerPage') {
                    $links['sPerPage'] .= "&$name=" . $config[$property];
                }
                $links['sFilter']['__'] .= "&$name=" . $config[$property];
            }
        }

        foreach ($filterTypes as $filterType) {
            $links['sFilter'][$filterType] .= $links['sFilter']['__'];
        }

        $links['sSupplier'] = $links['sSort'];

        return $links;
    }


    /**
     * Search product by order number
     *
     * @param string $search
     * @return string
     */
    protected function searchFuzzyCheck($search)
    {
        $minSearch = empty(Shopware()->Config()->sMINSEARCHLENGHT) ? 2 : (int)Shopware()->Config()->sMINSEARCHLENGHT;
        if (!empty($search) && strlen($search) >= $minSearch) {
            $sql = '
                SELECT DISTINCT articleID
                FROM s_articles_details
                WHERE ordernumber = ?
                GROUP BY articleID
                LIMIT 2
			';
            $articles = Shopware()->Db()->fetchCol($sql, array($search));

            if (empty($articles)) {
                $sql = "
                    SELECT DISTINCT articleID
                    FROM s_articles_details
                    WHERE ordernumber = ?
                    OR ? LIKE CONCAT(ordernumber, '%')
                    GROUP BY articleID
                    LIMIT 2
				";
                $articles = Shopware()->Db()->fetchCol($sql, array($search, $search));
            }
        }
        if (!empty($articles) && count($articles) == 1) {
            $sql = '
                SELECT ac.articleID
                FROM  s_articles_categories_ro ac
                INNER JOIN s_categories c
                    ON  c.id = ac.categoryID
                    AND c.active = 1
                    AND c.id = ?
                WHERE ac.articleID = ?
                LIMIT 1
            ';
            $articles = Shopware()->Db()->fetchCol($sql, array(Shopware()->Shop()->get('parentID'), $articles[0]));
        }
        if (!empty($articles) && count($articles) == 1) {
            return $this->Front()->Router()->assemble(array('sViewport' => 'detail', 'sArticle' => $articles[0]));
        }
    }


}
