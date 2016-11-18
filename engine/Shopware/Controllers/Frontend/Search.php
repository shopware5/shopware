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
use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\SearchBundle\SearchTermPreProcessorInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

/**
 * @category  Shopware
 * @package   Shopware\Controllers\Frontend
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
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
        return $this->forward("defaultSearch");
    }

    /**
     * Default search
     */
    public function defaultSearchAction()
    {
        if (!$this->Request()->has('sSort')) {
            $this->Request()->setParam('sSort', 7);
        }

        $term = $this->getSearchTerm();

        // Check if we have a one to one match for order number, then redirect
        $location = $this->searchFuzzyCheck($term);
        if (!empty($location)) {
            return $this->redirect($location);
        }

        $this->View()->loadTemplate('frontend/search/fuzzy.tpl');

        $minLengthSearchTerm = $this->get('config')->get('minSearchLenght');
        if (strlen($term) < (int) $minLengthSearchTerm) {
            return;
        }

        /**@var $context ShopContextInterface */
        $context = $this->get('shopware_storefront.context_service')->getShopContext();

        $criteria = Shopware()->Container()->get('shopware_search.store_front_criteria_factory')
            ->createSearchCriteria($this->Request(), $context);

        /**@var $result ProductSearchResult */
        $result = $this->get('shopware_search.product_search')->search($criteria, $context);
        $articles = $this->convertProducts($result);

        if ($this->get('config')->get('traceSearch', true)) {
            $this->get('shopware_searchdbal.search_term_logger')->logResult(
                $criteria,
                $result,
                $context->getShop()
            );
        }

        $pageCounts = $this->get('config')->get('fuzzySearchSelectPerPage');

        $request = $this->Request()->getParams();
        $request['sSearchOrginal'] = $term;

        /** @var $mapper \Shopware\Components\QueryAliasMapper */
        $mapper = $this->get('query_alias_mapper');

        $this->View()->assign([
            'term' => $term,
            'criteria' => $criteria,
            'facets' => $result->getFacets(),
            'sPage' => $this->Request()->getParam('sPage', 1),
            'sSort' => $this->Request()->getParam('sSort', 7),
            'sTemplate' => $this->Request()->getParam('sTemplate'),
            'sPerPage' => array_values(explode("|", $pageCounts)),
            'sRequests' => $request,
            'shortParameters' => $mapper->getQueryAliases(),
            'pageSizes' => array_values(explode("|", $pageCounts)),
            'ajaxCountUrlParams' => [],
            'sSearchResults' => [
                'sArticles' => $articles,
                'sArticlesCount' => $result->getTotalCount()
            ],
            'productBoxLayout' => $this->get('config')->get('searchProductBoxLayout')
        ]);
    }

    /**
     * @param ProductSearchResult $result
     * @return array
     */
    private function convertProducts(ProductSearchResult $result)
    {
        $articles = [];
        foreach ($result->getProducts() as $product) {
            $article = $this->get('legacy_struct_converter')->convertListProductStruct($product);

            $articles[] = $article;
        }

        if (empty($articles)) {
            return null;
        }

        return $articles;
    }

    /**
     * @return string
     */
    private function getSearchTerm()
    {
        $term = $this->Request()->getParam('sSearch', '');

        /** @var SearchTermPreProcessorInterface $processor */
        $processor = $this->get('shopware_search.search_term_pre_processor');

        return $processor->process($term);
    }

    /**
     * Search product by order number
     *
     * @param string $search
     * @return string
     */
    protected function searchFuzzyCheck($search)
    {
        /** @var Shopware_Components_Config $config */
        $config = $this->get('config');
        if (!$config->get('activateNumberSearch')) {
            return false;
        }

        $minSearch = empty($config->sMINSEARCHLENGHT) ? 2 : (int) $config->sMINSEARCHLENGHT;
        $number = null;
        if (!empty($search) && strlen($search) >= $minSearch) {
            $sql = '
                SELECT DISTINCT articleID, ordernumber, s_articles.configurator_set_id
                FROM s_articles_details
                  INNER JOIN s_articles
                   ON s_articles.id = s_articles_details.articleID
                WHERE ordernumber = ?
                GROUP BY articleID
                LIMIT 2
            ';
            $articles = $this->get('db')->fetchAll($sql, [$search]);
            if ($articles[0]['configurator_set_id']) {
                $number = $articles[0]['ordernumber'];
            }

            $articles = array_column($articles, 'articleID');

            if (empty($articles)) {
                $sql = "
                    SELECT DISTINCT articleID
                    FROM s_articles_details
                    WHERE ordernumber = ?
                    OR ? LIKE CONCAT(ordernumber, '%')
                    GROUP BY articleID
                    LIMIT 2
                ";
                $articles = $this->get('db')->fetchCol($sql, [$search, $search]);
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

            $articles = $this->get('db')->fetchCol($sql, [
                $this->get('shop')->get('parentID'),
                $articles[0]
            ]);
        }
        if (!empty($articles) && count($articles) == 1) {
            $assembleParams = [
                'sViewport' => 'detail',
                'sArticle' => $articles[0],
            ];
            if ($number) {
                $assembleParams['number'] = $number;
            }

            $partner = $this->Request()->getParam('partner', $this->Request()->getParam('sPartner'));
            if (!empty($partner)) {
                $assembleParams['sPartner'] = $partner;
            }

            return $this->get('router')->assemble($assembleParams);
        }
    }
}
