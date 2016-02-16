<?php

namespace Shopware\Components\Compatibility;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_Request_RequestHttp as Request;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Condition\SearchTermCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\FacetResult\RangeFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\TreeFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\TreeItem;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Models\Shop\Shop;

class LegacySearchSubscriber implements SubscriberInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            'Enlight_Controller_Action_PreDispatch_Frontend_Search' => array('convertSearchParameter', 0),
            'Enlight_Controller_Action_PostDispatch_Frontend_Search' => array('convertSearch', 100),
            'Enlight_Controller_Action_PostDispatch_Frontend_AjaxSearch' => ['convertAjaxSearch', 100]
        );
    }

    public function convertAjaxSearch(\Enlight_Event_EventArgs $args)
    {
        /**@var $shop Shop */
        $shop = $this->container->get('shop');
        if ($shop->getTemplate()->getVersion() >= 3) {
            return;
        }

        $data = $args->getSubject()->View()->getAssign();
        foreach ($data['sSearchResults']['sResults'] as &$article) {
            $article['thumbNails'] = $article['image']['src'];
            $article['image'] = $article['image']['src'][1];
        }
        $args->getSubject()->View()->assign($data);
    }

    public function convertSearchParameter(\Enlight_Controller_ActionEventArgs $args)
    {
        /**@var $shop Shop */
        $shop = $this->container->get('shop');
        if ($shop->getTemplate()->getVersion() >= 3) {
            return;
        }
        /**@var $controller \Shopware_Controllers_Frontend_Search*/
        $controller = $args->getSubject();
        $request = $controller->Request();

        if ($request->has('sFilter_price')) {
            $ranges = $this->getPriceRanges();
            $active = $ranges[$request->get('sFilter_price')];

            $request->setParam('priceMin', $active['start']);
            $request->setParam('priceMax', $active['end']);
        }

        if ($request->has('sFilter_category')) {
            $request->setParam('sCategory', $request->getParam('sFilter_category'));
        }

        if ($request->has('sFilter_supplier')) {
            $request->setParam('sSupplier', $request->getParam('sFilter_supplier'));
        }
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function convertSearch(\Enlight_Controller_ActionEventArgs $args)
    {
        /**@var $shop Shop */
        $shop = $this->container->get('shop');
        if ($shop->getTemplate()->getVersion() >= 3) {
            return;
        }
        /**@var $controller \Shopware_Controllers_Frontend_Search*/
        $controller = $args->getSubject();
        $request    = $controller->Request();

        $assign = $controller->View()->getAssign();

        if (!isset($assign['facets'])) {
            return;
        }

        $facets = $assign['facets'];

        /**@var $criteria Criteria*/
        $criteria = $assign['criteria'];

        $activeCategoryId = $this->getActiveCategoryId($shop, $criteria);

        $priceRanges = $this->getPriceRanges();
        $priceFacetValues = null;
        $manufacturerFacet = null;
        $categoryFacet = null;

        foreach ($facets as $facet) {
            if (!$facet instanceof FacetResultInterface) {
                continue;
            }

            if ($facet->getFacetName() == 'price') {
                $priceFacetValues = $this->getPriceRangeValues($priceRanges, $facet);
            } elseif ($facet->getFacetName() == 'category') {
                $categoryFacet = $this->convertCategoryFacet($facet, $activeCategoryId);
            } elseif ($facet->getFacetName() == 'manufacturer') {
                $manufacturerFacet = $this->convertManufacturerFacet($facet);
            }
        }

        $result = $assign['sSearchResults'];

        $result = array_merge($result, array(
            'sLastCategory' => $activeCategoryId,
            'sPrices'       => $priceFacetValues,
            'sSuppliers'    => $manufacturerFacet,
            'sCategories'   => $categoryFacet,
        ));

        $controller->View()->assign('sSearchResults', $result);
        $controller->View()->assign(array(
            'sPriceFilter'    => $priceRanges,
            'sLinks'          => $this->getSearchLinks($request, $criteria),
            'sPages'          => $this->getPages($criteria, $request->getParam('sPage', 1), $result['sArticlesCount']),
            'sPerPage'        => array_values(explode("|", $this->container->get('config')->get('fuzzySearchSelectPerPage'))),
            'sRequests'       => $this->getRequestValues($criteria, $request),
            'sCategoriesTree' => $this->getCategoryTree($activeCategoryId, $shop->getCategory()->getId()),
        ));
    }

    private function getRequestValues(Criteria $criteria, Request $request)
    {
        return array(
            'sSearchOrginal' => $criteria->getCondition('search')->getTerm(),
            'sSearch'        => $criteria->getCondition('search')->getTerm(),
            'sPage'          => $request->getParam('sPage', 1),
            'sSort'          => $request->getParam('sSort'),
            'sPerPage'       => $request->getParam('sPage', 12),
            'sFilter'        => array(
                'category'   => $request->getParam('sFilter_category', null),
                'supplier'   => $request->getParam('sFilter_supplier', null),
                'price'      => $request->getParam('sFilter_price', null),
                'property'   => $request->getParam('sFilter_property', null),
            )
        );
    }

    private function getPages(Criteria $criteria, $currentPage, $totalCount)
    {
        if ($criteria->getLimit() != 0) {
            $numberPages = ceil($totalCount / $criteria->getLimit());
        } else {
            $numberPages = 0;
        }

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

    private function getSearchLinks(Request $request, Criteria $criteria)
    {
        $filters = array(
            'sSort',
            'sPerPage',
            'sFilter_supplier',
            'sFilter_category',
            'sFilter_price',
            'sFilter_propertygroup'
        );

        $activeFilters = array();
        foreach ($filters as $filter) {
            if (!$request->has($filter) || !$request->getParam($filter)) {
                continue;
            }
            $activeFilters[$filter] = $request->getParam($filter);
        }

        $activeFilters['sSearch'] = $criteria->getCondition('search')->getTerm();

        /**@var $searchTerm SearchTermCondition*/
        $searchTerm = $criteria->getCondition('search');
        $baseLink = $this->container->get('config')->get('baseFile') . '?sViewport=search&sSearch=' . urlencode($searchTerm->getTerm());

        $withoutSort     = $activeFilters;
        $withoutPerPage  = $activeFilters;
        $withoutCategory = $activeFilters;
        $withoutSupplier = $activeFilters;
        $withoutPrice    = $activeFilters;
        $withoutProperty = $activeFilters;

        unset($withoutSort['sSort']);
        unset($withoutPerPage['sPerPage']);
        unset($withoutCategory['sFilter_category']);
        unset($withoutSupplier['sFilter_supplier']);
        unset($withoutPrice['sFilter_price']);
        unset($withoutProperty['sFilter_propertygroup']);

        $links = array(
            'sLink'         => $baseLink,
            'sSearch'       => $this->container->get('router')->assemble(array('sViewport' => 'search')),

            'sPage'         => $baseLink . '&' . http_build_query($activeFilters, "", "&"),
            'sSort'         => $baseLink . '&' . http_build_query($withoutSort, "", "&"),
            'sPerPage'      => $baseLink . '&' . http_build_query($withoutPerPage, "", "&"),

            'sFilter'       => array(
                'category'  => $baseLink . '&' . http_build_query($withoutCategory, "", "&"),
                'supplier'  => $baseLink . '&' . http_build_query($withoutSupplier, "", "&"),
                'price'     => $baseLink . '&' . http_build_query($withoutPrice, "", "&"),
                'property'  => $baseLink . '&' . http_build_query($withoutProperty, "", "&"),
            )
        );
        return $links;
    }

    /**
     * @param Shop $shop
     * @param Criteria $criteria
     * @return int
     */
    private function getActiveCategoryId(Shop $shop, Criteria $criteria)
    {
        /**@var $condition CategoryCondition*/
        $category = $shop->getCategory()->getId();
        if ($condition = $criteria->getCondition('category')) {
            $category = $condition->getCategoryIds()[0];
        }
        return $category;
    }


    private function getPriceRangeValues($ranges, RangeFacetResult $facet)
    {
        $result = array();
        foreach ($ranges as $index => $range) {
            $start = $range['start'];
            $end   = $range['end'];

            if (($start >= $facet->getMin() && $start <= $facet->getMax()) ||
                ($end >= $facet->getMin() && $end <= $facet->getMax())) {
                $result[$index] = 1;
            }
        }

        return $result;
    }

    private function convertManufacturerFacet(ValueListFacetResult $facetResult)
    {
        $manufacturers = array();

        foreach ($facetResult->getValues() as $value) {
            $manufacturer = array(
                'id' => $value->getId(),
                'key' => $value->getId(),
                'name' => $value->getLabel(),
                'img' => null,
                'description' => null,
                'meta_title' => null,
                'count' => null
            );

            $manufacturers[$value->getId()] = $manufacturer;
        }

        return $manufacturers;
    }

    private function convertCategoryFacet(TreeFacetResult $facet, $activeId)
    {
        $values = $this->getLastValuesOfTree($facet->getValues());

        $result = array();

        foreach ($values as $value) {
            if ($value->getId() == $activeId) {
                continue;
            }
            $result[] = array(
                'id' => $value->getId(),
                'description' => $value->getLabel(),
                'count' => 1
            );
        }
        return $result;
    }

    private function getPriceRanges()
    {
        $filter = $this->container->get('config')->get('fuzzysearchpricefilter');
        $filter = explode('|', $filter);

        $result = array();
        $end = 0;
        foreach ($filter as $index => $value) {
            $start = $end;
            $end = $value;

            $result[$index+1] = array('start' => $start, 'end' => $end);
        }
        return $result;
    }

    /**
     * @param TreeItem[] $values
     * @return TreeItem[]
     */
    private function getLastValuesOfTree($values)
    {
        foreach ($values as $value) {
            if (count($value->getValues()) > 0) {
                return $this->getLastValuesOfTree($value->getValues());
            } else {
                return $values;
            }
        }
    }

    protected function getCategoryTree($id, $mainId)
    {
        $sql = '
            SELECT `id`, `description`, `parent`
            FROM `s_categories`
            WHERE `id` = ?
        ';
        $cat = $this->container->get('db')->fetchRow($sql, array($id));
        if (empty($cat['id']) || $id == $cat['parent'] || $id == $mainId) {
            return array();
        } else {
            $cats = $this->getCategoryTree($cat['parent'], $mainId);
            $cats[$id] = $cat;
            return $cats;
        }
    }
}
