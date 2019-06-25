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

use Shopware\Bundle\SearchBundleDBAL\SearchTerm\SearchIndexerInterface;
use Shopware\Models\Shop\Shop;

class Shopware_Plugins_Core_RebuildIndex_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Refresh the data only manuel.
     */
    const STRATEGY_MANUAL = 1;

    /**
     * Refresh the data over a cron job.
     */
    const STRATEGY_CRON_JOB = 2;

    /**
     * Refresh the data after access the specified core function
     */
    const STRATEGY_LIVE = 3;

    /**
     * Returns capabilities so the plugin is default not installable and hidden in the plugin manager
     */
    public function getCapabilities()
    {
        return [
            'install' => false,
            'enable' => false,
            'update' => true,
        ];
    }

    /**
     * Returns the top seller name
     */
    public function getLabel()
    {
        return 'Shopware Such- und SEO-Index';
    }

    /**
     * Current plugin version
     */
    public function getVersion()
    {
        return '1.0.0';
    }

    /**
     * Returns the meta information about the plugin.
     * Keep in mind that the plugin description is located
     * in the info.txt.
     *
     * @return array
     */
    public function getInfo()
    {
        return [
            'version' => $this->getVersion(),
            'label' => $this->getLabel(),
            'link' => 'http://www.shopware.de/',
        ];
    }

    /**
     * Helper function to get access on the sRewriteTable component.
     *
     * @return sRewriteTable
     */
    public function RewriteTable()
    {
        return Shopware()->Modules()->RewriteTable();
    }

    /**
     * Helper function to get access on the SeoIndex component.
     *
     * @return Shopware_Components_SeoIndex
     */
    public function SeoIndex()
    {
        return Shopware()->Container()->get('seoindex');
    }

    /**
     * The install function creates the plugin configuration
     * and subscribes all required events for this plugin
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeSeoIndexEvents();
        $this->subscribeSearchIndexEvents();

        return true;
    }

    /**
     * Event listener function of the search index rebuild cron job.
     *
     * @return bool
     */
    public function onRefreshSeoIndex(Enlight_Event_EventArgs $arguments)
    {
        $strategy = Shopware()->Config()->get('seoRefreshStrategy', self::STRATEGY_LIVE);

        if ($strategy !== self::STRATEGY_CRON_JOB) {
            return true;
        }

        $shops = Shopware()->Db()->fetchCol('SELECT id FROM s_core_shops WHERE active = 1');

        $currentTime = new DateTime();

        $this->SeoIndex()->registerShop($shops[0]);
        $this->RewriteTable()->sCreateRewriteTableCleanup();

        foreach ($shops as $shopId) {
            /** @var \Shopware\Models\Shop\Repository $repository */
            $repository = Shopware()->Models()->getRepository(Shop::class);
            $shop = $repository->getActiveById($shopId);
            if ($shop === null) {
                throw new Exception('No valid shop id passed');
            }

            $this->get('shopware.components.shop_registration_service')->registerShop($shop);
            Shopware()->Modules()->Categories()->baseId = $shop->getCategory()->getId();

            list($cachedTime, $elementId, $shopId) = $this->SeoIndex()->getCachedTime();
            $this->SeoIndex()->setCachedTime($currentTime->format('Y-m-d h:m:i'), $elementId, $shopId);

            $this->RewriteTable()->baseSetup();

            $limit = 10000;
            $lastId = null;
            $lastUpdateVal = '0000-00-00 00:00:00';

            do {
                $lastUpdateVal = $this->RewriteTable()->sCreateRewriteTableArticles($lastUpdateVal, $limit);
                $lastId = $this->RewriteTable()->getRewriteArticleslastId();
            } while ($lastId !== null);

            $this->SeoIndex()->setCachedTime($currentTime->format('Y-m-d h:m:i'), $elementId, $shopId);

            $context = $this->get('shopware_storefront.context_service')->createShopContext($shopId);

            $this->RewriteTable()->sCreateRewriteTableCategories();
            $this->RewriteTable()->sCreateRewriteTableCampaigns();
            $this->RewriteTable()->sCreateRewriteTableContent();
            $this->RewriteTable()->sCreateRewriteTableBlog(null, null, $context);
            $this->RewriteTable()->createManufacturerUrls($context);
            $this->RewriteTable()->sCreateRewriteTableStatic();
            $this->RewriteTable()->createContentTypeUrls($context);

            Shopware()->Events()->notify(
                'Shopware_CronJob_RefreshSeoIndex_CreateRewriteTable',
                [
                    'shopContext' => $context,
                    'cachedTime' => $currentTime,
                ]
            );
        }

        return true;
    }

    /**
     * Event listener function of the search index rebuild cron job.
     *
     * @return bool
     */
    public function refreshSearchIndex(Enlight_Event_EventArgs $arguments)
    {
        $strategy = Shopware()->Config()->get('searchRefreshStrategy', self::STRATEGY_LIVE);

        if ($strategy !== self::STRATEGY_CRON_JOB) {
            return true;
        }

        /* @var SearchIndexerInterface $indexer */
        $indexer = $this->get('shopware_searchdbal.search_indexer');
        $indexer->build();

        return true;
    }

    /**
     * This replaces the old event from the routerRewrite plugin
     *
     * The refreshSeoIndex method will only be called, if "live" mode is enabled. Else the process will be
     * triggered via plugin or manually
     */
    public function onAfterSendResponse(Enlight_Controller_EventArgs $args)
    {
        $request = $args->getRequest();

        if ($request->getModuleName() !== 'frontend') {
            return;
        }

        if (!Shopware()->Container()->initialized('shop')) {
            return;
        }

        /**
         * If 'live' mode is configured, pass the request to the SeoIndex component and handle it as in SW < 4.1.0.
         */
        $refreshStrategy = $this->Application()->Config()->get('seoRefreshStrategy');

        if ($refreshStrategy !== self::STRATEGY_LIVE) {
            return;
        }
        $this->SeoIndex()->refreshSeoIndex();
    }

    /**
     * Event listener function of the Enlight_Controller_Dispatcher_ControllerPath_Backend_Seo
     * event. This event is fired when shopware trying to access the plugin SEO controller.
     *
     * @return string
     */
    public function getSeoBackendController(Enlight_Event_EventArgs $arguments)
    {
        return $this->Path() . 'Controllers/Seo.php';
    }

    /**
     * Event listener function of the Enlight_Controller_Dispatcher_ControllerPath_Backend_SearchIndex
     * event. This event is fired when shopware trying to access the plugin SearchIndex controller.
     *
     * @return string
     */
    public function getSearchIndexBackendController(Enlight_Event_EventArgs $arguments)
    {
        return $this->Path() . 'Controllers/SearchIndex.php';
    }

    /**
     * Event listener function of the Enlight_Controller_Dispatcher_ControllerPath_Backend_SimilarShown
     * event. This event is fired when shopware trying to access the plugin AlsoBought controller.
     *
     * @return string
     */
    public function getAlsoBoughtBackendController(Enlight_Event_EventArgs $arguments)
    {
        return $this->Path() . 'Controllers/AlsoBought.php';
    }

    /**
     * Plugin event listener function which is fired
     * when the also bought resource has to be initialed.
     *
     * @return Shopware_Components_SeoIndex
     */
    public function initSeoIndexResource()
    {
        $this->Application()->Loader()->registerNamespace(
            'Shopware_Components',
            $this->Path() . 'Components/'
        );

        return Enlight_Class::Instance('Shopware_Components_SeoIndex');
    }

    /**
     * Registers all required events for the similar shown articles function.
     */
    protected function subscribeSearchIndexEvents()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_SearchIndex',
            'getSearchIndexBackendController'
        );

        $this->createCronJob('Refresh search index', 'RefreshSearchIndex', 86400, true);
        $this->subscribeEvent('Shopware_CronJob_RefreshSearchIndex', 'refreshSearchIndex');
    }

    /**
     * Registers all required events for the also bought articles function.
     */
    protected function subscribeSeoIndexEvents()
    {
        $this->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Backend_Seo', 'getSeoBackendController');

        $this->subscribeEvent('Enlight_Bootstrap_InitResource_SeoIndex', 'initSeoIndexResource');
        $this->subscribeEvent('Enlight_Controller_Front_DispatchLoopShutdown', 'onAfterSendResponse');

        $this->createCronJob('Refresh seo index', 'RefreshSeoIndex', 86400, true);
        $this->subscribeEvent('Shopware_CronJob_RefreshSeoIndex', 'onRefreshSeoIndex');
    }
}
