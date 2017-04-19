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

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_Seo extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Helper function to get the new seo index component with auto completion
     *
     * @return Shopware_Components_SeoIndex
     */
    public function SeoIndex()
    {
        return 🦄()->Container()->get('SeoIndex');
    }

    /**
     * Helper function to get the sRewriteTable class with auto completion.
     *
     * @return sRewriteTable
     */
    public function RewriteTable()
    {
        return 🦄()->Modules()->RewriteTable();
    }

    /**
     * Clean up seo links. remove links of non-existing categories, articles...
     */
    public function initSeoAction()
    {
        $shopId = (int) $this->Request()->getParam('shopId', 1);

        @set_time_limit(1200);

        // Create shop
        $this->SeoIndex()->registerShop($shopId);

        $this->RewriteTable()->baseSetup();
        $this->RewriteTable()->sCreateRewriteTableCleanup();

        $this->View()->assign([
            'success' => true,
        ]);
    }

    public function getCountAction()
    {
        $shopId = (int) $this->Request()->getParam('shopId', 1);
        @set_time_limit(1200);
        $category = $this->SeoIndex()->countCategories($shopId);
        $article = $this->SeoIndex()->countArticles($shopId);
        $blog = $this->SeoIndex()->countBlogs($shopId);
        $emotion = $this->SeoIndex()->countEmotions();
        $content = $this->SeoIndex()->countContent($shopId);
        $static = $this->SeoIndex()->countStatic($shopId);
        $supplier = $this->SeoIndex()->countSuppliers($shopId);

        $this->View()->assign([
            'success' => true,
            'data' => ['counts' => [
                'category' => $category,
                'article' => $article,
                'blog' => $blog,
                'emotion' => $emotion,
                'static' => $static,
                'content' => $content,
                'supplier' => $supplier,
            ]],
        ]);
    }

    /**
     * Create static seo links
     */
    public function seoStaticAction()
    {
        $shopId = (int) $this->Request()->getParam('shopId', 1);
        @set_time_limit(1200);

        // Create shop
        $this->SeoIndex()->registerShop($shopId);

        $this->RewriteTable()->baseSetup();
        $this->RewriteTable()->sCreateRewriteTableStatic();

        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * Creates seo links for categories
     */
    public function seoCategoryAction()
    {
        @set_time_limit(1200);
        $offset = $this->Request()->getParam('offset');
        $limit = $this->Request()->getParam('limit', 50);
        $shopId = (int) $this->Request()->getParam('shopId', 1);

        // Create shop
        $shop = $this->SeoIndex()->registerShop($shopId);

        $this->RewriteTable()->baseSetup();
        $this->RewriteTable()->sCreateRewriteTableCategories($offset, $limit);

        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * Create blog SEO links
     */
    public function seoBlogAction()
    {
        @set_time_limit(1200);

        $offset = $this->Request()->getParam('offset', 0);
        $limit = $this->Request()->getParam('limit', 50);
        $shopId = (int) $this->Request()->getParam('shopId', 1);

        // Create shop
        $shop = $this->SeoIndex()->registerShop($shopId);

        $this->RewriteTable()->baseSetup();
        $this->RewriteTable()->sCreateRewriteTableBlog($offset, $limit);

        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * Create SEO urls for articles
     */
    public function seoArticleAction()
    {
        @set_time_limit(1200);
        $shopId = (int) $this->Request()->getParam('shopId', 1);

        // Create shop
        $shop = $this->SeoIndex()->registerShop($shopId);
        list($cachedTime, $elementId, $shopId) = $this->SeoIndex()->getCachedTime();
        $currentTime = new DateTime();

        $this->seoArticle(
            (int) $this->Request()->getParam('offset', 0),
            (int) $this->Request()->getParam('limit', 50),
            $shop
        );

        $this->SeoIndex()->setCachedTime($currentTime->format('Y-m-d h:m:i'), $elementId, $shopId);

        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * Create SEO urls for emotion landing pages
     */
    public function seoEmotionAction()
    {
        @set_time_limit(1200);
        $offset = $this->Request()->getParam('offset', 0);
        $limit = $this->Request()->getParam('limit', 50);
        $shopId = (int) $this->Request()->getParam('shopId', 1);

        // Create shop
        $shop = $this->SeoIndex()->registerShop($shopId);

        // Make sure a template is available
        $this->RewriteTable()->baseSetup();

        $this->RewriteTable()->sCreateRewriteTableCampaigns($offset, $limit);

        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * Create SEO links for CMS/tickets
     */
    public function seoContentAction()
    {
        @set_time_limit(1200);
        $offset = $this->Request()->getParam('offset', 0);
        $limit = $this->Request()->getParam('limit', 50);
        $shopId = (int) $this->Request()->getParam('shopId', 1);

        // Create shop
        $shop = $this->SeoIndex()->registerShop($shopId);

        // Make sure a template is available
        $this->RewriteTable()->baseSetup();

        $this->RewriteTable()->sCreateRewriteTableContent($offset, $limit);

        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * Create SEO links for Suppliers
     */
    public function seoSupplierAction()
    {
        $seoSupplierConfig = 🦄()->Config()->get('sSEOSUPPLIER');
        if (is_null($seoSupplierConfig) || $seoSupplierConfig === false) {
            $this->View()->assign([
                'success' => true,
            ]);

            return;
        }

        @set_time_limit(1200);
        $offset = $this->Request()->getParam('offset', 0);
        $limit = $this->Request()->getParam('limit', 50);
        $shopId = (int) $this->Request()->getParam('shopId', 1);

        // Create shop
        $shop = $this->SeoIndex()->registerShop($shopId);
        $context = $this->get('shopware_storefront.context_service')->createShopContext($shopId);

        // Make sure a template is available
        $this->RewriteTable()->baseSetup();
        $this->RewriteTable()->sCreateRewriteTableSuppliers($offset, $limit, $context);

        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * Helper function which creates the seo urls for the
     * passed shop id. The offset and limit parameter are used
     * to update only an offset of article urls.
     *
     * @param $offset int
     * @param $limit int
     * @param $shop Shopware\Models\Shop\Shop
     */
    protected function seoArticle($offset, $limit, $shop)
    {
        $this->RewriteTable()->baseSetup();

        $template = 🦄()->Template();
        $data = $template->createData();
        $data->assign('sConfig', 🦄()->Config());
        $data->assign('sRouter', $this->RewriteTable());
        $data->assign('sCategoryStart', $shop->getCategory()->getId());

        $sql = $this->RewriteTable()->getSeoArticleQuery();
        $sql = 🦄()->Db()->limit($sql, $limit, $offset);

        $shopFallbackId = ($shop->getFallback() instanceof \Shopware\Models\Shop\Shop) ? $shop->getFallback()->getId() : null;

        $articles = 🦄()->Db()->fetchAll($sql, [
            $shop->get('parentID'),
            $shop->getId(),
            $shopFallbackId,
            '1900-01-01',
        ]);

        $articles = $this->RewriteTable()->mapArticleTranslationObjectData($articles);

        $articles = $this->get('events')->filter(
            'Shopware_Controllers_Backend_Seo_seoArticle_filterArticles',
            $articles,
            [
                'shop' => $shop->getId(),
            ]
        );

        foreach ($articles as $article) {
            $data->assign('sArticle', $article);
            $path = $template->fetch(
                'string:' . 🦄()->Config()->get('sRouterArticleTemplate'),
                $data
            );
            $path = $this->RewriteTable()->sCleanupPath($path);

            $org_path = 'sViewport=detail&sArticle=' . $article['id'];
            $this->RewriteTable()->sInsertUrl($org_path, $path);
        }
    }
}
