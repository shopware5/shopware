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

class Shopware_Controllers_Frontend_Search extends Enlight_Controller_Action
{
    /**
     * Index action method
     */
    public function indexAction()
    {
        return $this->forward('defaultSearch');
    }

    /**
     * Default search
     */
    public function defaultSearchAction()
    {
        $this->setDefaultSorting();

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

        /** @var ShopContextInterface $context */
        $context = $this->get('shopware_storefront.context_service')->getShopContext();

        $criteria = Shopware()->Container()->get('shopware_search.store_front_criteria_factory')
            ->createSearchCriteria($this->Request(), $context);

        /** @var ProductSearchResult $result */
        $result = $this->get('shopware_search.product_search')->search($criteria, $context);
        $products = $this->convertProducts($result);

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

        /** @var \Shopware\Components\QueryAliasMapper $mapper */
        $mapper = $this->get('query_alias_mapper');

        $service = Shopware()->Container()->get('shopware_storefront.custom_sorting_service');

        $sortingIds = $this->container->get('config')->get('searchSortings');
        $sortingIds = array_filter(explode('|', $sortingIds));
        $sortings = $service->getList($sortingIds, $context);

        $this->View()->assign([
            'term' => $term,
            'criteria' => $criteria,
            'facets' => $result->getFacets(),
            'sPage' => $this->Request()->getParam('sPage', 1),
            'sSort' => $this->Request()->getParam('sSort', 7),
            'sTemplate' => $this->Request()->getParam('sTemplate'),
            'sPerPage' => array_values(explode('|', $pageCounts)),
            'sRequests' => $request,
            'shortParameters' => $mapper->getQueryAliases(),
            'pageSizes' => array_values(explode('|', $pageCounts)),
            'ajaxCountUrlParams' => [],
            'sortings' => $sortings,
            'sSearchResults' => [
                'sArticles' => $products,
                'sArticlesCount' => $result->getTotalCount(),
            ],
            'productBoxLayout' => $this->get('config')->get('searchProductBoxLayout'),
        ]);
    }

    /**
     * Search product by order number
     *
     * @param string $search
     *
     * @return string|false
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
            $products = $this->get('db')->fetchAll($sql, [$search]);
            if ($products[0]['configurator_set_id']) {
                $number = $products[0]['ordernumber'];
            }

            $products = array_column($products, 'articleID');

            if (empty($products)) {
                $like_search = $search . '%';
                $sql = '
                    SELECT DISTINCT articleID
                    FROM s_articles_details
                    WHERE ordernumber = ?
                    OR ordernumber LIKE ?
                    GROUP BY articleID
                    LIMIT 2
                ';
                $products = $this->get('db')->fetchCol($sql, [$search, $like_search]);
            }
        }
        if (!empty($products) && count($products) == 1) {
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

            $products = $this->get('db')->fetchCol($sql, [
                $this->get('shop')->get('parentID'),
                $products[0],
            ]);
        }
        if (!empty($products) && count($products) == 1) {
            $assembleParams = [
                'sViewport' => 'detail',
                'sArticle' => $products[0],
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

    /**
     * @return array|null
     */
    private function convertProducts(ProductSearchResult $result)
    {
        $products = [];
        foreach ($result->getProducts() as $product) {
            $productArray = $this->get('legacy_struct_converter')->convertListProductStruct($product);

            $products[] = $productArray;
        }

        if (empty($products)) {
            return null;
        }

        return $products;
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

    private function setDefaultSorting()
    {
        if ($this->Request()->has('sSort')) {
            return;
        }

        $sortings = $this->container->get('config')->get('searchSortings');
        $sortings = array_filter(explode('|', $sortings));
        $this->Request()->setParam('sSort', array_shift($sortings));
    }
}
