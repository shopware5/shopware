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
 * Deprecated Shopware Class that handle newsletters
 */
class sNewsletter
{
    /**
     * @var sSystem
     */
    public $sSYSTEM;

    /**
     * Database connection which used for each database operation in this class.
     * Injected over the class constructor
     *
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    /**
     * Shopware configuration object which used for
     * each config access in this class.
     * Injected over the class constructor
     *
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * Class constructor.
     * Injects all dependencies which are required for this class.
     */
    public function __construct()
    {
        $this->db = Shopware()->Db();
        $this->config = Shopware()->Config();
    }

    /**
     * Gets article suggestions
     *
     * @param $id
     * @param int $userId
     * @return null|array
     */
    public function sCampaignsGetSuggestions($id, $userId = 0)
    {
        $sql = "
            SELECT value, description FROM s_campaigns_containers WHERE type='ctSuggest'
            AND promotionID = ?
        ";
        unset($this->sSYSTEM->sMODULES['sArticles']->sCachePromotions);
        unset($this->sSYSTEM->sMODULES['sMarketing']->sBlacklist);

        $getSuggestInfo = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($sql, null, array(intval($id)));
        if ($getSuggestInfo["value"] && $getSuggestInfo["description"]) {
            // Main information
            $sSuggestion["description"] = $getSuggestInfo["description"];
            $sSuggestion["value"] = $getSuggestInfo["value"];

            // Get personalized articles
            $limit = intval($sSuggestion["value"] / 2);
            if ($userId) {

                $selectLast = array_merge(
                    $this->getLastViewedArticles($userId, $limit),
                    $this->getLastBoughtArticles($userId, $limit)
                );

                $blacklist = array();

                foreach ($selectLast as $lastArticle) {
                    $category = $this->sSYSTEM->_GET["sCategory"] ? : 0;
                    $temp = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById("fix", $category, $lastArticle["articleID"]);
                    if ($temp["articleID"] && empty($blacklist[$temp["articleID"]])) {
                        $finalRecommendations[] = $temp;
                        $blacklist[$temp["articleID"]] = $temp["articleID"];
                    }
                }
            }

            $leftRecommendations = $sSuggestion["value"] - count($finalRecommendations);

            $randomize = array('new', 'top');
            $category = $this->sSYSTEM->_GET['sCategory'] ? : 0;

            while ($leftRecommendations > 0) {
                $article = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById($randomize[array_rand($randomize)], $category, '');
                if (!empty($article)) {
                    $leftRecommendations--;
                    $this->sSYSTEM->sMODULES['sArticles']->sCachePromotions[] = $article['articleID'];
                    $finalRecommendations[] = $article;
                }
            }

            $sSuggestion["data"] = $finalRecommendations;

            return $sSuggestion;
        }
        return null;
    }

    /**
     * Get last viewed articles
     *
     * @param $userId
     * @param $limit
     *
     * @return array
     */
    private function getLastViewedArticles($userId, $limit)
    {
        $sql = "
            SELECT DISTINCT articleID FROM s_emarketing_lastarticles WHERE userID = ?
            ORDER BY time DESC LIMIT $limit
        ";

        $lastViewedArticles = $this->db->fetchAll($sql, array($userId));

        $this->config->offsetSet('sMAXCROSSSIMILAR', 1);

        foreach ($lastViewedArticles as $lastArticle) {
            $this->sSYSTEM->sMODULES['sMarketing']->sBlacklist[] = $lastArticle["articleID"];
        }

        $selectLastAlsoView = array();
        foreach ($lastViewedArticles as $lastArticle) {
            $temp = $this->sSYSTEM->sMODULES['sMarketing']->sGetSimilaryShownArticles($lastArticle["articleID"]);
            if ($temp[0]["id"]) {
                $selectLastAlsoView[]["articleID"] = $temp[0]["id"];
            }
        }

        return array_merge($lastViewedArticles, $selectLastAlsoView);
    }

    /**
     * Get last bought articles
     *
     * @param $userId
     * @param $limit
     *
     * @return array
     */
    private function getLastBoughtArticles($userId, $limit)
    {
        $sql = "
            SELECT DISTINCT articleID FROM s_order_details, s_order WHERE
            s_order.userID = ?
            AND s_order_details.orderID = s_order.id
            ORDER BY ordertime DESC LIMIT $limit
        ";

        $selectLastOrders = $this->db->fetchAll($sql, array($userId));
        foreach ($selectLastOrders as $lastArticle) {
            $this->sSYSTEM->sMODULES['sMarketing']->sBlacklist[] = $lastArticle["articleID"];
        }
        foreach ($selectLastOrders as $lastArticle) {
            $temp = $this->sSYSTEM->sMODULES['sMarketing']->sGetAlsoBoughtArticles($lastArticle["articleID"]);
            if ($temp[0]["id"]) {
                $selectLastAlsoBought[]["articleID"] = $temp[0]["id"];
            }
        }
        return $selectLastAlsoBought;
    }

}
