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
     * @return
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

        if ($this->doSearch($term) == true) {

            $this->View()->sSearchRequest = array("sSearch" => $term);
            $this->View()->sSearchResults = array("sResults" => $this->getResults(), "sArticlesCount" => $this->getCountResults());
        }
    }

    /**
     * Search for $term with shopware default search object
     * @param $term
     * @return bool Successfully ?
     */
    public function doSearch($term)
    {
        $adapter = Enlight()->Events()->filter('Shopware_Controllers_Frontend_Search_SelectAdapter',null);
        if (empty($adapter)){
            $adapter = new Shopware_Components_Search_Adapter_Default(Shopware()->Db(), Shopware()->Cache(), new Shopware_Components_Search_Result_Default(), Shopware()->Config());
        }
        $search = new Shopware_Components_Search($adapter);
        $searchResults = $search->search($term, $this->getConfig());

        if ($searchResults !== false) {
            $resultCount = $searchResults->getResultCount();
            $resultArticles = $searchResults->getResult();
        } else {
            return false;
        }

        $basePath = $this->Request()->getScheme() . '://' . $this->Request()->getHttpHost() . $this->Request()->getBasePath();

        if (!empty($resultArticles)) {
            foreach ($resultArticles as &$result) {
                if (empty($result['type'])) $result['type'] = 'article';
                if (!empty($result["mediaId"])) {
                    /**@var $mediaModel \Shopware\Models\Media\Media*/
                    $mediaModel = Shopware()->Models()->find('Shopware\Models\Media\Media', $result["mediaId"]);
                    if ($mediaModel != null) {
                        $result["thumbNails"] = array_values($mediaModel->getThumbnails());
                        //deprecated just for the downward compatibility use the thumbNail Array instead
                        $result["image"] = $result["thumbNails"][1];
                    }
                }
                $result['link'] = $this->Front()->Router()->assemble(array('controller' => 'detail', 'sArticle' => $result['articleID'], 'title' => $result['name']));
            }
        }

        // Set result & count of result
        $this->setResults($resultArticles);
        $this->setCountResults($resultCount);
        return true;
    }

    /**
     * Get search configuration
     * @return array
     */
    protected function getConfig()
    {
        $config = array(
            "suggestSearch" => true,
            "currentPage" => 1,
            "restrictSearchResultsToCategory" => Shopware()->Shop()->get('parentID'),
            "resultsPerPage" => empty(Shopware()->Config()->MaxLiveSearchResults) ? 6 : (int)Shopware()->Config()->MaxLiveSearchResults
        );

        $config["sPerPage"] = $config["resultsPerPage"];

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
     * Set results
     * @param array $results
     */
    protected function setResults(array $results)
    {
        $this->_results = $results;
    }

    /**
     * Get results
     * @return array
     */
    protected function getResults()
    {
        return $this->_results;
    }

    /**
     * Set count of results
     * @param int $count
     */
    protected function setCountResults(int $count)
    {
        $this->_countResults = $count;
    }

    /**
     * Get count of results
     * @return int
     */
    protected function getCountResults()
    {
        return $this->_countResults;
    }
}
