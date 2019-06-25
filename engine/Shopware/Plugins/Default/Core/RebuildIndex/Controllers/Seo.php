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

class Shopware_Controllers_Backend_Seo extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @deprecated in 5.6, will be private in the future
     *
     * Helper function to get the new seo index component with auto completion
     *
     * @return Shopware_Components_SeoIndex
     */
    public function SeoIndex()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.7.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return Shopware()->Container()->get('seoindex');
    }

    /**
     * @deprecated in 5.6, will be private in the future
     *
     * Helper function to get the sRewriteTable class with auto completion.
     *
     * @return sRewriteTable
     */
    public function RewriteTable()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.7.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return Shopware()->Modules()->RewriteTable();
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

    /**
     * Assigns the amount of the seo links which to build for this shop
     * Bind for: Shopware_Controllers_Seo_filterCounts
     */
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

        $counts = [
            'category' => $category,
            'article' => $article,
            'blog' => $blog,
            'emotion' => $emotion,
            'static' => $static,
            'content' => $content,
            'supplier' => $supplier,
            'contentType' => $this->SeoIndex()->countContentTypes(),
        ];

        $counts = $this->get('events')->filter(
            'Shopware_Controllers_Seo_filterCounts',
            $counts,
            ['shopId' => $shopId]
        );

        $this->View()->assign([
            'success' => true,
            'data' => ['counts' => $counts],
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
        $context = $this->get('shopware_storefront.context_service')->createShopContext($shopId);
        $this->RewriteTable()->sCreateRewriteTableBlog($offset, $limit, $context);

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
        $seoSupplierConfig = Shopware()->Config()->get('sSEOSUPPLIER');
        if ($seoSupplierConfig === null || $seoSupplierConfig === false) {
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
        $this->SeoIndex()->registerShop($shopId);
        $context = $this->get('shopware_storefront.context_service')->createShopContext($shopId);

        // Make sure a template is available
        $this->RewriteTable()->baseSetup();
        $this->RewriteTable()->createManufacturerUrls($context, $offset, $limit);

        $this->View()->assign([
            'success' => true,
        ]);
    }

    public function seoContentTypeAction(): void
    {
        @set_time_limit(1200);
        $shopId = (int) $this->Request()->getParam('shopId', 1);

        // Create shop
        $this->SeoIndex()->registerShop($shopId);
        $context = $this->get('shopware_storefront.context_service')->createShopContext($shopId);

        // Make sure a template is available
        $this->RewriteTable()->baseSetup();
        $this->RewriteTable()->createContentTypeUrls($context);

        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * Helper function which creates the seo urls for the
     * passed shop id. The offset and limit parameter are used
     * to update only an offset of article urls.
     *
     * @param int                       $offset
     * @param int                       $limit
     * @param Shopware\Models\Shop\Shop $shop
     */
    protected function seoArticle($offset, $limit, $shop)
    {
        $this->RewriteTable()->baseSetup();

        $template = Shopware()->Template();
        $data = $template->createData();
        $data->assign('sConfig', Shopware()->Config());
        $data->assign('sRouter', $this->RewriteTable());
        $data->assign('sCategoryStart', $shop->getCategory()->getId());

        $sql = $this->RewriteTable()->getSeoArticleQuery();
        $sql = Shopware()->Db()->limit($sql, $limit, $offset);

        $shopFallbackId = ($shop->getFallback() instanceof \Shopware\Models\Shop\Shop) ? $shop->getFallback()->getId() : null;

        $articles = Shopware()->Db()->fetchAll($sql, [
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
                'string:' . Shopware()->Config()->get('sRouterArticleTemplate'),
                $data
            );
            $path = $this->RewriteTable()->sCleanupPath($path);

            $org_path = 'sViewport=detail&sArticle=' . $article['id'];
            $this->RewriteTable()->sInsertUrl($org_path, $path);
        }
    }
}
