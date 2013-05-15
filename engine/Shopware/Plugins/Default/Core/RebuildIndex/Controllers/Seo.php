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
 */

/**
 * @category  Shopware
 * @package   Shopware\Plugins\RebuildIndex\Controllers\Backend
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_Seo extends Shopware_Controllers_Backend_ExtJs
{
    /**
    * @var Shopware\Components\Model\ModelManager
    */
    protected $manager;

    /**
    * @var Shopware\Models\Category\Repository
    */
    protected $categoryRepository;

    /**
    * @var Shopware\Models\Blog\Repository
    */
    protected $blogRepository;

    /**
    * @var Shopware\Models\Category\Category
    */
    protected $baseCategory;

    /**
    * Class constructor.
    */
    public function init()
    {
        $this->manager = Shopware()->Models();
        $this->categoryRepository = $this->manager->getRepository('Shopware\Models\Category\Category');
        $this->blogRepository = $this->manager->getRepository('Shopware\Models\Blog\Blog');
    }

    public function getCategoryRepository()
    {
        return $this->categoryRepository;
    }

    public function getBlogRepository()
    {
        return $this->blogRepository;
    }

    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Clean up seo links. remove links of non-existing categories, articles...
     */
    public function initSeoAction()
    {
        $shopId = (int) $this->Request()->getParam('shop', 1);

        // Create shop
        Shopware()->SeoIndex()->registerShop($shopId);

        Shopware()->Modules()->RewriteTable()->baseSetup();
        Shopware()->Modules()->RewriteTable()->sCreateRewriteTableCleanup();


        $this->View()->assign(array(
            'success' => true
        ));
    }

    /**
     * After the SEO links where rewritten, clear the cache
     */
    public function finishSeoAction()
    {
        Shopware()->SeoIndex()->clearRouterRewriteCache();
    }

    /**
     * Static seo links will be create within one request
     */
    public function staticCountAction()
    {
        $this->View()->assign(array(
            'success' => true,
            'data' => array('count' => 1)
        ));
    }

    /**
     * Create static seo links
     */
    public function seoStaticAction()
    {
        $shopId = (int) $this->Request()->getParam('shop', 1);

        // Create shop
        Shopware()->SeoIndex()->registerShop($shopId);

        Shopware()->Modules()->RewriteTable()->baseSetup();
        Shopware()->Modules()->RewriteTable()->sCreateRewriteTableStatic();

        $this->View()->assign(array(
            'success' => true
        ));
    }

    /**
     * Returns the number of categories available
     */
    public function categoryCountAction()
    {
        $count = Shopware()->SeoIndex()->countCategories(1);

        $this->View()->assign(array(
            'success' => true,
            'data' => array('count' => $count)
        ));

    }

    /**
     * Creates seo links for categories
     */
    public function seoCategoryAction()
    {
        $offset = $this->Request()->getParam('offset');
        $limit = $this->Request()->getParam('limit');
        $shopId = (int) $this->Request()->getParam('shop', 1);

        // Create shop
        $shop = Shopware()->SeoIndex()->registerShop($shopId);

        Shopware()->Modules()->RewriteTable()->baseSetup();
        Shopware()->Modules()->RewriteTable()->sCreateRewriteTableCategories($offset, $limit);

        $this->View()->assign(array(
            'success' => true
        ));
    }

    /**
     * Count number of blogCategories to create links for
     */
    public function blogCountAction()
    {
        $shopId = (int) $this->Request()->getParam('shop', 1);

        $count = Shopware()->SeoIndex()->countBlogs($shopId);

        $this->View()->assign(array(
            'success' => true,
            'data' => array('count' => $count)
        ));
    }

    /**
     * Create blog SEO links
     */
    public function seoBlogAction()
    {
        $offset = $this->Request()->getParam('offset');
        $limit = $this->Request()->getParam('limit');
        $shopId = (int) $this->Request()->getParam('shop', 1);

        // Create shop
        $shop = Shopware()->SeoIndex()->registerShop($shopId);

        Shopware()->Modules()->RewriteTable()->baseSetup();
        Shopware()->Modules()->RewriteTable()->sCreateRewriteTableBlog($offset, $limit);

        $this->View()->assign(array(
            'success' => true
        ));
    }

    /**
     * Count number of articles which will be updated
     */
    public function articleCountAction()
    {
        $shopId = (int) $this->Request()->getParam('shop', 1);

        $count = Shopware()->SeoIndex()->countArticles($shopId);

        $this->View()->assign(array(
            'success' => true,
            'data' => array('count' => $count)
        ));
    }

    /**
     * Create SEO urls for articles
     */
    public function seoArticleAction()
    {
        $offset = $this->Request()->getParam('offset');
        $limit = $this->Request()->getParam('limit');
        $shopId = (int) $this->Request()->getParam('shop', 1);

        // Create shop
        $shop = Shopware()->SeoIndex()->registerShop($shopId);

        list($cachedTime, $elementId, $shopId) = Shopware()->SeoIndex()->getCachedTime();

        Shopware()->Modules()->RewriteTable()->baseSetup();

        $currentTime = Shopware()->Db()->fetchOne('SELECT ?', array(new Zend_Date()));
        Shopware()->SeoIndex()->setCachedTime($currentTime, $elementId, $shopId);

        $resultTime = Shopware()->Modules()->RewriteTable()->sCreateRewriteTableArticles($cachedTime, $limit);
        if ($resultTime === $cachedTime) {
            $resultTime = $currentTime;
        }
        if($resultTime !== $currentTime) {
            Shopware()->SeoIndex()->setCachedTime($resultTime, $elementId, $shopId);
        }

        $this->View()->assign(array(
            'success' => true
        ));
    }

    /**
     * Count number of emotions which will be updated
     */
    public function emotionCountAction()
    {
        $shopId = (int) $this->Request()->getParam('shop', 1);

        $count = Shopware()->SeoIndex()->countEmotions($shopId);

        $this->View()->assign(array(
            'success' => true,
            'data' => array('count' => $count)
        ));
    }

    /**
     * Create SEO urls for emotion landing pages
     */
    public function seoEmotionAction()
    {
        $offset = $this->Request()->getParam('offset');
        $limit = $this->Request()->getParam('limit');
        $shopId = (int) $this->Request()->getParam('shop', 1);

        // Create shop
        $shop = Shopware()->SeoIndex()->registerShop($shopId);

        // Make sure a template is available
        Shopware()->Modules()->RewriteTable()->baseSetup();

        Shopware()->Modules()->RewriteTable()->sCreateRewriteTableCampaigns($offset, $limit);

        $this->View()->assign(array(
            'success' => true
        ));
    }

    /**
     * Count number of content items
     */
    public function contentCountAction()
    {
        $shopId = (int) $this->Request()->getParam('shop', 1);

        // SEO link generation is only needed once - so we return 0 for anything but the default shop
        if ($shopId > 1) {
            $this->View()->assign(array(
                'success' => true,
                'data' => array('count' => 0)
            ));
            return;
        }

        $count = Shopware()->SeoIndex()->countContent();

        $this->View()->assign(array(
            'success' => true,
            'data' => array('count' => $count)
        ));
    }

    /**
     * Create SEO links for CMS/tickets
     */
    public function seoContentAction()
    {
        $offset = $this->Request()->getParam('offset');
        $limit = $this->Request()->getParam('limit');
        $shopId = (int) $this->Request()->getParam('shop', 1);

        // Create shop
        $shop = Shopware()->SeoIndex()->registerShop($shopId);

        // Make sure a template is available
        Shopware()->Modules()->RewriteTable()->baseSetup();

        Shopware()->Modules()->RewriteTable()->sCreateRewriteTableContent($offset, $limit);

        $this->View()->assign(array(
            'success' => true
        ));
    }

}

