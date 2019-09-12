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

class Shopware_Controllers_Widgets_Recommendation extends Enlight_Controller_Action
{
    /** @var Shopware_Components_Config */
    protected $config;

    /** @var sArticles */
    protected $articleModule;

    /** @var sMarketing */
    protected $marketingModule;

    /**
     * Pre dispatch method
     */
    public function preDispatch()
    {
        $this->Response()->setHeader('x-robots-tag', 'noindex');
    }

    public function init()
    {
        $this->config = Shopware()->Config();
        $this->articleModule = Shopware()->Modules()->Articles();
        $this->marketingModule = Shopware()->Modules()->Marketing();
    }

    /**
     * Show similar viewed articles
     */
    public function viewedAction()
    {
        $productId = (int) $this->Request()->getParam('articleId');
        $maxPages = (int) $this->config->get('similarViewedMaxPages', 10);
        $perPage = (int) $this->config->get('similarViewedPerPage', 4);

        $this->marketingModule->sBlacklist[] = $productId;
        $products = $this->marketingModule->sGetSimilaryShownArticles($productId, $maxPages * $perPage);

        $numbers = array_column($products, 'number');
        $result = $this->getPromotions($numbers);

        $this->View()->assign('maxPages', $maxPages);
        $this->View()->assign('perPage', $perPage);
        $this->View()->assign('viewedArticles', $result);
    }

    /**
     * Show also bought articles
     */
    public function boughtAction()
    {
        $productId = (int) $this->Request()->getParam('articleId');
        $maxPages = (int) $this->config->get('alsoBoughtMaxPages', 10);
        $perPage = (int) $this->config->get('alsoBoughtPerPage', 4);

        $this->marketingModule->sBlacklist[] = $productId;
        $product = $this->marketingModule->sGetAlsoBoughtArticles($productId, $maxPages * $perPage);

        $numbers = array_column($product, 'number');
        $result = $this->getPromotions($numbers);

        $this->View()->assign('maxPages', $maxPages);
        $this->View()->assign('perPage', $perPage);
        $this->View()->assign('boughtArticles', $result);
    }

    /**
     * @param string[] $numbers
     *
     * @return array[]
     */
    private function getPromotions($numbers)
    {
        if (empty($numbers)) {
            return [];
        }

        $context = $this->get('shopware_storefront.context_service')->getShopContext();
        $products = $this->get('shopware_storefront.list_product_service')
            ->getList($numbers, $context);

        return $this->get('legacy_struct_converter')->convertListProductStructList($products);
    }
}
