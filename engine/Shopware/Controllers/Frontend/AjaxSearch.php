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
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;

/**
 * Search controller for suggest search
 */
class Shopware_Controllers_Frontend_AjaxSearch extends Enlight_Controller_Action
{
    /**
     * Array with search results
     * @var array
     */
    protected $_results = array();

    /**
     * Count of search results
     * @var int
     */
    protected $_countResults = 0;

    /**
     * Index action - get searchterm from request (sSearch) and start search
     * @return void
     */
    public function indexAction()
    {
        Enlight()->Plugins()->Controller()->Json()->setPadding();

        $this->View()->loadTemplate('frontend/search/ajax.tpl');

        $term = $this->Request()->getParam('sSearch');
        $term = trim(stripslashes(html_entity_decode($term)));

        if (!$term || strlen($term) < Shopware()->Config()->MinSearchLenght) {
            return false;
        }

        /**@var $context ProductContextInterface*/
        $context  = $this->get('shopware_storefront.context_service')->getProductContext();

        $criteria = $this->get('shopware_search.store_front_criteria_factory')
            ->createAjaxSearchCriteria($this->Request(), $context);

        /**@var $result ProductSearchResult*/
        $result = $this->get('shopware_search.product_search')->search($criteria, $context);

        if ($result->getTotalCount() > 0) {
            $articles = $this->convertProducts($result);
            $this->View()->searchResult = $result;
            $this->View()->sSearchRequest = array("sSearch" => $term);
            $this->View()->sSearchResults = array("sResults" => $articles, "sArticlesCount" => $result->getTotalCount());
        }
    }

    /**
     * @param ProductSearchResult $result
     * @return array
     */
    private function convertProducts(ProductSearchResult $result)
    {
        $articles = array();
        foreach ($result->getProducts() as $product) {
            $article = $this->get('legacy_struct_converter')->convertListProductStruct(
                $product
            );

            if ($article['sVoteAverange']['averange']) {
                $article['sVoteAverange']['averange'] /= 2;
            }

            $article['link'] = $this->Front()->Router()->assemble(array(
                'controller' => 'detail',
                'sArticle' => $product->getId(),
                'title' => $product->getName()
            ));
            $article['name'] = $product->getName();
            $articles[] = $article;
        }

        return $articles;
    }
}
