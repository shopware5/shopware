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

class Shopware_Plugins_Core_MarketingAggregate_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Refresh the marketing data only manuel.
     */
    const AGGREGATE_STRATEGY_MANUAL = 1;

    /**
     * Refresh the marketing data over a cron job.
     */
    const AGGREGATE_STRATEGY_CRON_JOB = 2;

    /**
     * Refresh the marketing data after access the specified core function
     */
    const AGGREGATE_STRATEGY_LIVE = 3;

    /**
     * Returns the capabilities for this plugin.
     * The top seller plugin can't be uninstalled or disabled.
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
        return 'Shopware Marketing Aggregat Funktionen';
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
     * Helper function to get access on the TopSeller component.
     *
     * @return Shopware_Components_TopSeller
     */
    public function TopSeller()
    {
        return Shopware()->Container()->get('topseller');
    }

    /**
     * Helper function to get access on the AlsoBought component.
     *
     * @return Shopware_Components_AlsoBought
     */
    public function AlsoBought()
    {
        return Shopware()->Container()->get('alsobought');
    }

    /**
     * Helper function to get access on the SimilarShown component.
     *
     * @return Shopware_Components_SimilarShown
     */
    public function SimilarShown()
    {
        return Shopware()->Container()->get('similarshown');
    }

    /**
     * The install function creates the plugin configuration
     * and subscribes all required events for this plugin
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeTopSellerEvents();
        $this->subscribeAlsoBoughtEvents();
        $this->subscribeSimilarShownEvents();

        return true;
    }

    /**
     * Event listener function of the Enlight_Controller_Dispatcher_ControllerPath_Backend_SimilarShown
     * event. This event is fired when shopware trying to access the plugin SimilarShown controller.
     *
     * @return string
     */
    public function getSimilarShownBackendController(Enlight_Event_EventArgs $arguments)
    {
        return $this->Path() . 'Controllers/SimilarShown.php';
    }

    /**
     * Plugin event listener function which is fired
     * when the similar shown resource has to be initialed.
     *
     * @return Shopware_Components_SimilarShown
     */
    public function initSimilarShownResource()
    {
        $this->Application()->Loader()->registerNamespace(
            'Shopware_Components',
            $this->Path() . 'Components/'
        );

        $similarShown = Enlight_Class::Instance('Shopware_Components_SimilarShown');
        Shopware()->Container()->set('similarshown', $similarShown);

        return $similarShown;
    }

    /**
     * Event listener function of the Shopware_Plugins_LastArticles_ResetLastArticles
     * event. This event is fired after the Shopware_Plugins_LastArticles plugin resets
     * the s_emarketing_lastarticles data for a validation time.
     * This listener is used to update the similar shown article data at the same time.
     */
    public function afterSimilarShownArticlesReset(Enlight_Event_EventArgs $arguments)
    {
        if (!($this->isSimilarShownActivated())) {
            return $arguments->getReturn();
        }

        $strategy = $this->Application()->Config()->get(
            'similarRefreshStrategy',
            self::AGGREGATE_STRATEGY_LIVE
        );

        if ($strategy !== self::AGGREGATE_STRATEGY_LIVE) {
            return $arguments->getReturn();
        }

        $this->SimilarShown()->resetSimilarShown(
            $this->SimilarShown()->getSimilarShownValidationTime()
        );

        return $arguments->getReturn();
    }

    /**
     * Event listener function of the Shopware_Modules_Articles_SetLastArticle event.
     * This event is fired after a user visit an article detail page.
     * This listener function is used to increment the counter value of
     * the s_articles_similar_shown_ro table.
     */
    public function beforeSetLastArticle(Enlight_Event_EventArgs $arguments)
    {
        if (Shopware()->Session()->Bot || !($this->isSimilarShownActivated())) {
            return $arguments->getReturn();
        }

        $articleId = $arguments->getArticle();

        $sql = 'SELECT COUNT(id)
                FROM s_emarketing_lastarticles
                WHERE sessionID = :sessionId
                AND   articleID = :articleId';

        $alreadyViewed = Shopware()->Db()->fetchOne($sql, [
            'sessionId' => Shopware()->Session()->get('sessionId'),
            'articleId' => $articleId,
        ]);

        if ($alreadyViewed > 0) {
            return $arguments->getReturn();
        }

        $sql = '
            SELECT
                articleID as articleId
            FROM s_emarketing_lastarticles
            WHERE sessionID = :sessionId
            AND   articleID != :articleId
        ';

        $articles = Shopware()->Db()->fetchCol($sql, [
            'sessionId' => Shopware()->Session()->get('sessionId'),
            'articleId' => $articleId,
        ]);

        foreach ($articles as $id) {
            $this->SimilarShown()->refreshSimilarShown($articleId, $id);
            $this->SimilarShown()->refreshSimilarShown($id, $articleId);
        }

        return $arguments->getReturn();
    }

    /**
     * Event listener function of the Shopware_CronJob_RefreshSimilarShown event.
     * This event is a configured cron job which is used to update the
     * elapsed similar shown article data.
     *
     * @return bool
     */
    public function refreshSimilarShown(Enlight_Event_EventArgs $arguments)
    {
        $strategy = $this->Application()->Config()->get(
            'similarRefreshStrategy',
            self::AGGREGATE_STRATEGY_LIVE
        );

        if ($strategy !== self::AGGREGATE_STRATEGY_CRON_JOB || !($this->isSimilarShownActivated())) {
            return true;
        }

        $this->SimilarShown()->resetSimilarShown();
        $this->SimilarShown()->initSimilarShown();

        return true;
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
     * @return Shopware_Components_AlsoBought
     */
    public function initAlsoBoughtResource()
    {
        $this->Application()->Loader()->registerNamespace(
            'Shopware_Components',
            $this->Path() . 'Components/'
        );

        $alsoBought = Enlight_Class::Instance('Shopware_Components_AlsoBought');
        Shopware()->Container()->set('alsobought', $alsoBought);

        return $alsoBought;
    }

    /**
     * Event listener function of the Shopware_Modules_Order_SaveOrder_ProcessDetails event.
     * This event is fired after a customer completed an order.
     * This function is used to add or increment the new also bought articles.
     */
    public function addNewAlsoBought(Enlight_Event_EventArgs $arguments)
    {
        if (Shopware()->Session()->Bot) {
            return $arguments->getReturn();
        }

        $variants = $arguments->getDetails();
        if (count($variants) <= 1) {
            return $arguments->getReturn();
        }
        $sql = '
            SELECT
                basket1.articleID as article_id,
                basket2.articleID as related_article_id
            FROM s_order_basket basket1
               INNER JOIN s_order_basket basket2
                  ON basket1.sessionID = basket2.sessionID
                  AND basket1.articleID != basket2.articleID
                  AND basket1.modus = 0
                  AND basket2.modus = 0
            WHERE basket1.sessionID = :sessionId
        ';
        $combinations = Shopware()->Db()->fetchAll($sql, [
            'sessionId' => Shopware()->Session()->get('sessionId'),
        ]);

        $this->AlsoBought()->refreshMultipleBoughtArticles($combinations);

        return $arguments->getReturn();
    }

    /**
     * Event listener function of the Enlight_Controller_Dispatcher_ControllerPath_Backend_TopSeller
     * event. This event is fired when shopware trying to access the plugin TopSeller controller.
     *
     * @return string
     */
    public function getTopSellerBackendController(Enlight_Event_EventArgs $arguments)
    {
        return $this->Path() . 'Controllers/TopSeller.php';
    }

    /**
     * Plugin event listener function which is fired
     * when the top seller resource has to be initialed.
     *
     * @return Shopware_Components_TopSeller
     */
    public function initTopSellerResource()
    {
        $this->Application()->Loader()->registerNamespace(
            'Shopware_Components',
            $this->Path() . 'Components/'
        );

        $topSeller = Enlight_Class::Instance('Shopware_Components_TopSeller');
        Shopware()->Container()->set('topseller', $topSeller);

        return $topSeller;
    }

    /**
     * Plugin event listener function which fired after a customer
     * has ordered articles in the store front.
     * This function is used to increment the sales count in the
     * s_articles_top_seller table.
     */
    public function incrementTopSeller(Enlight_Event_EventArgs $arguments)
    {
        if (Shopware()->Session()->Bot || !($this->isTopSellerActivated())) {
            return $arguments->getReturn();
        }

        $details = $arguments->getDetails();
        foreach ($details as $article) {
            if ($article['modus'] != 0 || empty($article['articleID'])) {
                continue;
            }

            $this->TopSeller()->incrementTopSeller(
                $article['articleID'],
                $article['quantity']
            );
        }

        return $arguments->getReturn();
    }

    /**
     * Event listener function of the top seller cron job.
     * This function is only called if the shop supports the shopware cron job
     * and the cron plugin is activated.
     */
    public function refreshTopSeller()
    {
        $strategy = $this->Application()->Config()->get(
            'topSellerRefreshStrategy',
            self::AGGREGATE_STRATEGY_LIVE
        );

        if ($strategy !== self::AGGREGATE_STRATEGY_CRON_JOB) {
            return true;
        }

        $this->TopSeller()->updateElapsedTopSeller();

        return true;
    }

    /**
     * Plugin event listener function which fired after the
     * top seller data selected.
     * This function is used in case that the shop owner configured the
     * live refresh of the article data.
     * The listener function registers an additional listener on the
     * after_send_response event.
     */
    public function afterTopSellerSelected(Enlight_Event_EventArgs $arguments)
    {
        if (Shopware()->Session()->Bot) {
            return $arguments->getReturn();
        }

        $strategy = $this->Application()->Config()->get(
            'topSellerRefreshStrategy',
            self::AGGREGATE_STRATEGY_LIVE
        );

        if ($strategy !== self::AGGREGATE_STRATEGY_LIVE || !($this->isTopSellerActivated())) {
            return $arguments->getReturn();
        }

        $this->TopSeller()->updateElapsedTopSeller(50);

        return $arguments->getReturn();
    }

    /**
     * Refresh the top seller data of a single article.
     */
    public function refreshArticle(Enlight_Event_EventArgs $arguments)
    {
        if (!$this->isTopSellerActivated()) {
            return;
        }

        /** @var \Shopware\Models\Article\Article $product */
        $product = $arguments->getEntity();
        if (!($product instanceof \Shopware\Models\Article\Article)) {
            return;
        }
        if (!($product->getId()) > 0) {
            return;
        }

        $this->TopSeller()->refreshTopSellerForArticleId(
            $product->getId()
        );
    }

    /**
     * Plugin event listener function which fired after
     * the response send.
     * This function is used in the case that the top seller configuration
     * is set to "live". That means that we have to refresh the top seller
     * after each access on the top seller core function.
     * This function refresh only a minimum stack of the top seller data
     * to prevent long server times.
     */
    public function afterSendResponseOnTopSeller(Enlight_Event_EventArgs $arguments)
    {
        $this->TopSeller()->updateElapsedTopSeller(50);
    }

    /**
     * Helper function to check if the similar shown
     * function is activated.
     */
    protected function isSimilarShownActivated()
    {
        return $this->Application()->Config()->get(
            'similarActive',
            true
        );
    }

    /**
     * Helper function to check if the top seller
     * function is activated.
     */
    protected function isTopSellerActivated()
    {
        return $this->Application()->Config()->get(
            'topSellerActive',
            true
        );
    }

    /**
     * Registers all required events for the similar shown articles function.
     */
    protected function subscribeSimilarShownEvents()
    {
        $this->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Backend_SimilarShown', 'getSimilarShownBackendController');
        $this->subscribeEvent('Enlight_Bootstrap_InitResource_SimilarShown', 'initSimilarShownResource');
        $this->subscribeEvent('Shopware_Plugins_LastArticles_ResetLastArticles', 'afterSimilarShownArticlesReset');
        $this->subscribeEvent('Shopware_Modules_Articles_Before_SetLastArticle', 'beforeSetLastArticle');

        $this->createCronJob('Similar shown article refresh', 'RefreshSimilarShown', 86400, true);
        $this->subscribeEvent('Shopware_CronJob_RefreshSimilarShown', 'refreshSimilarShown');
    }

    /**
     * Registers all required events for the also bought articles function.
     */
    protected function subscribeAlsoBoughtEvents()
    {
        $this->subscribeEvent('Shopware_Modules_Order_SaveOrder_ProcessDetails', 'addNewAlsoBought');
        $this->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Backend_AlsoBought', 'getAlsoBoughtBackendController');
        $this->subscribeEvent('Enlight_Bootstrap_InitResource_AlsoBought', 'initAlsoBoughtResource');
    }

    /**
     * Helper function to subscribe all events of this plugin.
     */
    protected function subscribeTopSellerEvents()
    {
        $this->subscribeEvent('Shopware_Modules_Order_SaveOrder_ProcessDetails', 'incrementTopSeller');
        $this->subscribeEvent('Shopware_Modules_Articles_GetArticleCharts', 'afterTopSellerSelected');
        $this->subscribeEvent('Enlight_Bootstrap_InitResource_TopSeller', 'initTopSellerResource');
        $this->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Backend_TopSeller', 'getTopSellerBackendController');

        $this->createCronJob('Topseller Refresh', 'RefreshTopSeller', 86400, true);
        $this->subscribeEvent('Shopware_CronJob_RefreshTopSeller', 'refreshTopSeller');
        $this->subscribeEvent('Shopware\Models\Article\Article::postUpdate', 'refreshArticle');
        $this->subscribeEvent('Shopware\Models\Article\Article::postPersist', 'refreshArticle');
    }
}
