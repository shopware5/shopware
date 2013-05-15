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
 * @package   Shopware\Plugins\RebuildINdex
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
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
     * Returns the capabilities for this plugin.
     * The top seller plugin can't be uninstalled or disabled.
     */
    public function getCapabilities()
    {
        return array(
            'install' => true,
            'enable' => true,
            'update' => true
        );
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
        return "1.0.0";
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
        return array(
            'version'     => $this->getVersion(),
            'label'       => $this->getLabel(),
            'link'        => 'http://www.shopware.de/'
        );
    }

    /**
     * Helper function to get access on the SearchIndex component.
     *
     * @return Shopware_Components_AlsoBought
     */
    public function SearchIndex()
    {
        return Shopware()->SearchIndex();
    }

    /**
     * Helper function to get access on the SeoIndex component.
     *
     * @return Shopware_Components_SimilarShown
     */
    public function SeoIndex()
    {
        return Shopware()->SeoIndex();
    }

    /**
     * The install function creates the plugin configuration
     * and subscribes all required events for this plugin
     * @return bool
     */
    public function install()
    {
        $this->subscribeSeoIndexEvents();
        $this->subscribeSearchIndexEvents();

        return true;
    }

    /**
     * Registers all required events for the similar shown articles function.
     */
    protected function subscribeSearchIndexEvents()
    {
        $this->subscribeEvent('Enlight_Bootstrap_InitResource_SearchIndex', 'initSearchIndexResource');

//        $this->createCronJob('Refresh search index', 'RefreshSearchIndex', 86400, true);
//        $this->subscribeEvent('Shopware_CronJob_RefreshSimilarShown', 'refreshSimilarShown');
    }

    /**
     * Registers all required events for the also bought articles function.
     */
    protected function subscribeSeoIndexEvents()
    {
        $this->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Backend_Seo','getSeoBackendController');

        $this->subscribeEvent('Enlight_Bootstrap_InitResource_SeoIndex', 'initSeoIndexResource');
        $this->subscribeEvent('Enlight_Controller_Front_DispatchLoopShutdown', 'onAfterSendResponse');

        $this->createCronJob('Refresh seo index', 'RefreshSeoIndex', 86400, true);
        $this->subscribeEvent('Shopware_CronJob_RefreshSeoIndex', 'onRefreshSeoIndex');
    }

    /**
     * In the cronjob callback we just trigger the old update method which generates all SEO urls in one big query
     */
    public function onRefreshSeoIndex()
    {
        Shopware()->SeoIndex()->refreshSeoIndex();
    }

    /**
     * This replaces the old event from the routerRewrite plugin
     *
     * The refreshSeoIndex method will only be called, if "live" mode is enabled. Else the process will be
     * triggered via plugin or manually
     *
     * @param Enlight_Controller_EventArgs $args
     */
    public function onAfterSendResponse(Enlight_Controller_EventArgs $args)
    {
        $request = $args->getRequest();

        if ($request->getModuleName() != 'frontend') {
            return;
        }

        if (!Shopware()->Bootstrap()->issetResource('Shop')) {
            return;
        }

        /**
         * If 'live' mode is configured, pass the request to the SeoIndex component and handle it as in SW < 4.1.0.
         */
        $refreshStrategy = $this->Application()->Config()->get('seoRefreshStrategy');

        if ($refreshStrategy == self::STRATEGY_LIVE) {
            Shopware()->SeoIndex()->refreshSeoIndex();
        }

    }

    /**
     * Event listener function of the Enlight_Controller_Dispatcher_ControllerPath_Backend_SimilarShown
     * event. This event is fired when shopware trying to access the plugin SimilarShown controller.
     *
     * @param Enlight_Event_EventArgs $arguments
     * @return string
     */
    public function getSeoBackendController(Enlight_Event_EventArgs $arguments)
    {
        return $this->Path() . 'Controllers/Seo.php';
    }

    /**
     * Plugin event listener function which is fired
     * when the similar shown resource has to be initialed.
     * @return Shopware_Components_SimilarShown
     */
    public function initSearchIndexResource()
    {
        $this->Application()->Loader()->registerNamespace(
            'Shopware_Components',
            $this->Path() . 'Components/'
        );

        $searchIndex = Enlight_Class::Instance('Shopware_Components_SearchIndex');
        return $searchIndex;
    }


    /**
     * Event listener function of the Enlight_Controller_Dispatcher_ControllerPath_Backend_SimilarShown
     * event. This event is fired when shopware trying to access the plugin AlsoBought controller.
     *
     * @param Enlight_Event_EventArgs $arguments
     * @return string
     */
    public function getAlsoBoughtBackendController(Enlight_Event_EventArgs $arguments)
    {
        return $this->Path() . 'Controllers/AlsoBought.php';
    }

    /**
     * Plugin event listener function which is fired
     * when the also bought resource has to be initialed.
     * @return Shopware_Components_AlsoBought
     */
    public function initSeoIndexResource()
    {
        $this->Application()->Loader()->registerNamespace(
            'Shopware_Components',
            $this->Path() . 'Components/'
        );

        $seoIndex = Enlight_Class::Instance('Shopware_Components_SeoIndex');
        return $seoIndex;
    }

}