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

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\SearchBundle\SearchTermPreProcessorInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

/**
 * Search controller for suggest search
 */
class Shopware_Controllers_Frontend_AjaxSearch extends Enlight_Controller_Action
{
    /**
     * Index action - get search term from request (sSearch) and start search
     */
    public function indexAction()
    {
        $this->View()->loadTemplate('frontend/search/ajax.tpl');

        /** @var SearchTermPreProcessorInterface $processor */
        $processor = $this->get('shopware_search.search_term_pre_processor');
        $term = $processor->process($this->Request()->getParam('sSearch'));

        if (!$term || strlen($term) < Shopware()->Config()->get('MinSearchLenght')) {
            return;
        }

        $this->setDefaultSorting();

        /** @var ShopContextInterface $context */
        $context = $this->get('shopware_storefront.context_service')->getShopContext();

        $criteria = $this->get('shopware_search.store_front_criteria_factory')
            ->createAjaxSearchCriteria($this->Request(), $context);

        $result = $this->search($term, $criteria, $context);

        if ($result->getTotalCount() > 0) {
            $products = $this->convertProducts($result);
            $this->View()->assign('searchResult', $result);
            $this->View()->assign('sSearchRequest', ['sSearch' => $term]);
            $this->View()->assign('sSearchResults', [
                'sResults' => $products,
                'sArticlesCount' => $result->getTotalCount(),
            ]);
        }
    }

    /**
     * @return array
     */
    private function convertProducts(ProductSearchResult $result)
    {
        $products = [];
        foreach ($result->getProducts() as $product) {
            $productArray = $this->get('legacy_struct_converter')->convertListProductStruct($product);

            $productArray['link'] = $this->Front()->Router()->assemble([
                'controller' => 'detail',
                'sArticle' => $product->getId(),
                'number' => $product->getNumber(),
                'title' => $product->getName(),
            ]);
            $productArray['name'] = $product->getName();
            $products[] = $productArray;
        }

        return $products;
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

    /**
     * @param string $term
     *
     * @return ProductSearchResult
     */
    private function search($term, Criteria $criteria, ShopContextInterface $context)
    {
        $result = null;

        // If the search for product numbers is active, do that first
        if ((int) $this->get('config')->get('activateNumberSearch') === 1) {
            // Check if search-term is a valid product-number
            /** @var ListProduct|null $directHit */
            $directHit = $this->get('shopware_storefront.list_product_service')
                ->get($term, $context);

            if ($directHit) {
                /** @var ProductSearchResult $result */
                $result = new ProductSearchResult([$directHit], 1, [], $criteria, $context);
            }
        }

        // If number search is inactive or didn't find anything, do a regular search
        if (!$result || $result->getTotalCount() === 0) {
            $result = $this->get('shopware_search.product_search')->search($criteria, $context);
        }

        return $result;
    }
}
