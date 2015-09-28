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

namespace Shopware\Components\Compatibility;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOStatement;
use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\SearchBundle\FacetResult\FacetResultGroup;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Models\Shop\Shop;

/**
 * @category  Shopware
 * @package   Shopware\Components\Compatibility
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class LegacyListingSubscriber implements SubscriberInterface
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
     * @param $service
     * @return mixed
     * @throws \Exception
     */
    private function get($service)
    {
        return $this->container->get($service);
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            'Enlight_Controller_Action_PostDispatch_Frontend_Listing' => array('convertListing', 0),
            'Enlight_Controller_Action_PreDispatch_Frontend_Listing' => array('redirectManufacturerListing', 0),
            'Legacy_Struct_Converter_Convert_Media' => 'convertMedia',
            'Enlight_Controller_Action_PostDispatch_Frontend_Blog' => ['convertBlogMedia', 200]
        );
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function convertBlogMedia(\Enlight_Controller_ActionEventArgs $args)
    {
        $controller = $args->getSubject();
        if ($controller->Request()->getActionName() !== 'detail') {
            return;
        }

        if (!$this->isLegacyTemplate()) {
            return;
        }

        $imageDir = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext()->getBaseUrl() . '/media/image/';
        $imageDir = str_replace('/media/image/', DIRECTORY_SEPARATOR, $imageDir);

        $data = $controller->View()->getAssign();
        foreach ($data['sArticle']['media'] as &$media) {
            foreach ($media['src'] as &$thumb) {
                $thumb = str_replace($imageDir, '', $thumb);
            }
            $media['thumbNails'] = $media['src'];
        }

        foreach ($data['sArticle']['preview']['src'] as &$thumb) {
            $thumb = str_replace($imageDir, '', $thumb);
        }
        $data['sArticle']['preview']['thumbNails'] = $data['sArticle']['preview']['src'];
        $controller->View()->assign($data);
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @return array
     * @throws \Exception
     */
    public function convertMedia(\Enlight_Event_EventArgs $args)
    {
        $data = $args->getReturn();

        if (!$this->isLegacyTemplate()) {
            return $data;
        }

        $data['src'] = array_column($data['thumbnails'], 'source');
        $data['src']['original'] = $data['source'];

        return $data;
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     * @throws \Exception
     */
    public function redirectManufacturerListing(\Enlight_Controller_ActionEventArgs $args)
    {
        $controller = $args->getSubject();

        if ($controller->Request()->getActionName() !== 'manufacturer') {
            return;
        }

        if (!$this->isLegacyTemplate()) {
            return;
        }

        $controller->forward('index');
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function convertListing(\Enlight_Controller_ActionEventArgs $args)
    {
        if (!$this->isLegacyTemplate()) {
            return;
        }

        /**@var $view \Enlight_View_Default */
        $view = $args->getSubject()->View();

        $totalCount = $view->getAssign('sNumberArticles');
        $shortParameters = $view->getAssign('shortParameters') ? : [];

        $params = $this->getCategoryConfig($args->getSubject()->Request());

        $view->assign('sPerPage', $this->createListingPerPageLinks($params));
        $view->assign('categoryParams', $this->getFilteredCategoryParams($shortParameters, $params));

        $pages = $this->createListingPageLinks($totalCount, $params);

        $view->assign('sPages', $pages);
        $view->assign('sNumberPages', count($pages['numbers']));

        $facets = $this->convertFacets($view->getAssign('facets'), $params);
        $view->assign($facets);
    }

    /**
     * @param \Enlight_Controller_Request_Request $request
     * @return array
     */
    private function getCategoryConfig(\Enlight_Controller_Request_Request $request)
    {
        return array(
            'sSort' => $request->getParam('sSort', 0),
            'sPage' => $request->getParam('sPage', 1),
            'sTemplate' => $request->getParam('sTemplate', null),
            'sPerPage' => $request->getParam('sPerPage', (int) $this->get('config')->get('articlesPerPage')),
            'sSupplier' => $request->getParam('sSupplier', null),
            'priceMin' => $request->getParam('priceMin', null),
            'priceMax' => $request->getParam('priceMax', null),
            'shippingFree' => $request->getParam('shippingFree', false),
            'sFilterProperties' => $request->getParam('sFilterProperties', array()),
            'immediateDelivery' => $request->getParam('immediateDelivery', false),
        );
    }

    /**
     * @param array $shortParameters
     * @param array $config
     * @return array
     */
    private function getFilteredCategoryParams(array $shortParameters, array $config)
    {
        $params = $this->getListingLinkParameters($config);

        foreach ($shortParameters as $key => $value) {
            if (array_key_exists($key, $params)) {
                $params[$value] = $params[$key];
                unset($params[$key]);
            }
        }

        ksort($params);

        return $params;
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
        $pageSizes = explode("|", $this->get('config')->get('numberArticlesToShow'));

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
     * Helper function which returns all category listing configurations
     * which are required for the listing links like "add filter", "next page", ...
     *
     * @param array $config
     * @return array
     */
    private function getListingLinkParameters(array $config)
    {
        $params = array();

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

        $default = $this->get('config')->get('articlesPerPage');
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

        return $params;
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
     * @param int $totalCount
     * @param $config
     * @return array
     */
    private function createListingPageLinks($totalCount, $config)
    {
        $currentPage = $config['sPage'];

        if ($config['sPerPage'] != 0) {
            $count = ceil($totalCount / $config['sPerPage']);
        } else {
            $count = 0;
        }

        if ((int) $this->get('config')->get('maxPages') > 0 && (int) $this->get('config')->get('maxPages') < $count) {
            $count = (int) $this->get('config')->get('maxPages');
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
     * Helper function which builds the listing links with all required parameters.
     *
     * @param $params
     * @return string
     */
    private function buildListingLink($params)
    {
        return $this->get('config')->get('baseFile') . Shopware()->Modules()->Core()->sBuildLink($params);
    }

    /**
     * @param FacetResultInterface[] $facets
     * @param array $params
     * @return array
     */
    private function convertFacets($facets, $params)
    {
        $result = array();
        $propertyFacets = array();

        foreach ($facets as $facet) {
            switch ($facet->getFacetName()) {
                case 'property':
                    $propertyFacets[] = $facet;
                    break;

                case 'manufacturer':
                    $suppliers = $this->getFacetManufacturers($facet, $params);
                    $result['sSupplierInfo'] = $this->getActiveListingSupplier($suppliers, $params);

                    if ($this->get('front')->Request()->getParam('action') != 'manufacturer') {
                        $result['sSuppliers'] = array_values($suppliers);
                    }
                    break;
            }
        }

        if ($propertyFacets) {
            $properties = $this->getFacetProperties($propertyFacets, $params);
            $result = array_merge($result, $properties);
        }

        return $result;
    }

    /**
     * @param FacetResultInterface|ValueListFacetResult $facet
     * @param array $config
     * @return array
     */
    private function getFacetManufacturers(FacetResultInterface $facet, array $config)
    {
        $items = $facet->getValues();

        $data = array();
        $params = $this->getListingLinkParameters($config);

        $filteredManufacturer = null;
        foreach ($items as $item) {
            $params = array_merge($params, array('sSupplier' => $item->getId()));

            $data[$item->getId()] = array(
                'id' => $item->getId(),
                'name' => $item->getLabel(),
                'link' => $this->buildListingLink($params)
            );
        }

        $ids = array_column($data, 'id');

        $query = $this->get('dbal_connection')->createQueryBuilder();
        $query->select(array('id', 'img', 'description'))
            ->from('s_articles_supplier', 'supplier')
            ->where('supplier.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement PDOStatement*/
        $statement = $query->execute();
        $supplierData = $statement->fetchAll();

        foreach ($supplierData as $row) {
            if (!isset($data[$row['id']])) {
                continue;
            }

            $data[$row['id']]['image'] = $row['img'];
            $data[$row['id']]['description'] = $row['description'];
        }

        $limit = 30;
        if ($this->get('config')->get('maxSuppliersCategory')) {
            $limit = (int) $this->get('config')->get('maxSuppliersCategory');
        }

        return array_slice($data, 0, $limit);
    }

    /**
     * @param array $suppliers
     * @param array $config
     * @return array
     *
     */
    private function getActiveListingSupplier(array $suppliers, array $config)
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

        $request = $this->get('front')->Request();

        // @deprecated since 5.1 will be removed in 5.2
        if ($activeSupplier['image']) {
            $mediaService = Shopware()->Container()->get('shopware_media.media_service');
            $activeSupplier['image'] = $mediaService->getUrl($activeSupplier['image']);
        }

        if ($request->getParam('action') == 'manufacturer') {
            $activeSupplier['link'] = null;
        } else {
            unset($params['sSupplier']);
            $activeSupplier['link'] = $this->buildListingLink($params);
        }
        return $activeSupplier;
    }

    /**
     * @param FacetResultInterface[]|FacetResultGroup[] $facets
     * @return array[]
     */
    private function convertPropertyValueList($facets)
    {
        $data = array();

        foreach ($facets as $facet) {
            $valueLists = $facet->getFacetResults();

            $set = array(
                'name' => $facet->getLabel(),
                'groups' => array(),
                'attributes' => $facet->getAttributes()
            );

            /**@var $propertyGroup ValueListFacetResult*/
            foreach ($valueLists as $propertyGroup) {
                $group = array(
                    'name' => $propertyGroup->getLabel(),
                    'options' => array(),
                    'attributes' => $propertyGroup->getAttributes()
                );

                foreach ($propertyGroup->getValues() as $propertyOption) {
                    $group['options'][] = array(
                        'id' => $propertyOption->getId(),
                        'name' => $propertyOption->getLabel(),
                        'attributes' => $propertyOption->getAttributes()
                    );
                }

                $set['groups'][] = $group;
            }

            $data[] = $set;
        }

        return $data;
    }

    /**
     * @param FacetResultInterface[]|FacetResultGroup[] $facets
     * @param array $config
     * @return array
     */
    private function getFacetProperties($facets, array $config)
    {
        $data = $this->convertPropertyValueList($facets);

        $filteredOptions = explode('|', $config['sFilterProperties']);
        if (!is_array($filteredOptions)) {
            $filteredOptions = array();
        }

        /** @var $mapper \Shopware\Components\QueryAliasMapper  */
        $mapper = Shopware()->Container()->get('query_alias_mapper');

        $params = $this->getListingLinkParameters($config);

        $grouped = array();
        $flat = array();

        foreach ($data as &$set) {
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

                    $option['active'] = in_array($option['id'], $filteredOptions);

                    $option['total'] = $option['attributes']['facet']['total'];

                    if ($option['active']) {
                        $activeGroupOptions[] = $option['id'];
                    }

                    //legacy convert
                    $options[$option['name']] = array(
                        'name' => $group['name'],
                        'value' => $option['name'],
                        'group' => $set['name'],
                        'link' => $option['link'],
                        'active' => $option['active']
                    );
                }

                $group['active'] = (bool) (!empty($activeGroupOptions));

                if ($group['active']) {
                    $removeOptions = array_diff($filteredOptions, $activeGroupOptions);

                    $filterParams['sFilterProperties'] = implode('|', $removeOptions);
                    if ($shortAlias = $mapper->getShortAlias('sFilterProperties')) {
                        $filterParams[$shortAlias] = implode('|', $removeOptions);
                    }

                    $params = array_merge(
                        $params,
                        $filterParams
                    );

                    $group['removeLink'] = $this->buildListingLink($params);
                }

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
     * @return bool
     */
    private function isLegacyTemplate()
    {
        /**@var $shop Shop */
        $shop = $this->container->get('shop');

        return $shop->getTemplate()->getVersion() < 3;
    }
}
