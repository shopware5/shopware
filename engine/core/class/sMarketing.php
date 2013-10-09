<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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
 * Deprecated Shopware Class that handles marketing related functions
 *
 * @category  Shopware
 * @package   Shopware\Core
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class sMarketing
{
    /**
     * Pointer to Shopware-Core-public functions
     *
     * @var    object
     * @access private
     */
    public $sSYSTEM;

    /**
     * Array with blacklisted articles (already in basket)
     *
     * @var array
     */
    public $sBlacklist = array();

    /**
     * @var int
     */
    public $categoryId;

    /**
     * @var int
     */
    public $customerGroupId;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->category = Shopware()->Shop()->getCategory();
        $this->categoryId = $this->category->getId();
        $this->customerGroupId = (int) Shopware()->Modules()->System()->sSYSTEM->sUSERGROUPDATA['id'];
    }

    public function sGetSimilaryShownArticles($articleId, $limit = 0)
    {
        if (empty($limit)) {
            $limit = empty($this->sSYSTEM->sCONFIG['sMAXCROSSSIMILAR']) ? 4 : (int)$this->sSYSTEM->sCONFIG['sMAXCROSSSIMILAR'];
        }
        $limit = (int) $limit;

        $where = '';
        if (!empty($this->sBlacklist)) {
            $where = Shopware()->Db()->quote($this->sBlacklist);
            $where = 'AND similarShown.related_article_id NOT IN (' . $where . ')';
        }

        $sql = "
            SELECT STRAIGHT_JOIN
                 lastArticles.articleID as id,
                 similarShown.viewed as hits
            FROM s_articles_similar_shown_ro as similarShown FORCE INDEX (viewed)

              INNER JOIN s_emarketing_lastarticles as lastArticles
                ON  lastArticles.articleID = similarShown.related_article_id

              INNER JOIN s_articles_categories_ro ac
                ON  ac.articleID = similarShown.related_article_id
                AND ac.categoryID = :categoryId

              INNER JOIN s_categories c
                ON  c.id = ac.categoryID
                AND c.active = 1

              INNER JOIN s_articles as a
                ON  a.id = similarShown.related_article_id
                AND a.active = 1

              LEFT JOIN s_articles_avoid_customergroups ag
                ON  ag.articleID = a.id
                AND ag.customergroupID= :customerGroupId

            WHERE similarShown.article_id = :articleId
            AND   ag.articleID IS NULL

            $where

            GROUP BY similarShown.viewed, similarShown.related_article_id
            ORDER BY similarShown.viewed DESC, similarShown.related_article_id DESC
            LIMIT $limit";


        $similarShownArticles = Shopware()->Db()->fetchAll($sql, array(
            'articleId'       => (int) $articleId,
            'categoryId'      => (int) $this->categoryId,
            'customerGroupId' => (int) $this->customerGroupId
        ));

        Shopware()->Events()->notify('Shopware_Modules_Marketing_GetSimilarShownArticles', array(
            'subject'  => $this,
            'articles' => $similarShownArticles
        ));

        return $similarShownArticles;
    }

    public function sGetAlsoBoughtArticles($articleID, $limit = 0)
    {
        if (empty($limit)) {
            $limit = empty($this->sSYSTEM->sCONFIG['sMAXCROSSALSOBOUGHT']) ? 4 : (int)$this->sSYSTEM->sCONFIG['sMAXCROSSALSOBOUGHT'];
        }
        $limit = (int) $limit;
        $where = '';

        if (!empty($this->sBlacklist)) {
            $where = Shopware()->Db()->quote($this->sBlacklist);
            $where = ' AND alsoBought.related_article_id NOT IN (' . $where . ')';
        }

        $sql = "
            SELECT DISTINCT
                alsoBought.sales as sales,
                alsoBought.related_article_id as id

            FROM   s_articles_also_bought_ro alsoBought
                INNER JOIN s_articles articles
                    ON  alsoBought.related_article_id = articles.id
                    AND articles.active = 1

                INNER JOIN s_articles_categories_ro articleCategories
                    ON  alsoBought.related_article_id = articleCategories.articleID
                    AND articleCategories.categoryID = :categoryId

                INNER JOIN s_categories categories
                    ON categories.id = articleCategories.categoryID

                LEFT JOIN s_articles_avoid_customergroups customerGroups
                    ON  customerGroups.articleID = articles.id
                    AND customerGroups.customergroupID = :customerGroupId

            WHERE alsoBought.article_id = :articleId
            AND   customerGroups.articleID IS NULL

            $where

            ORDER BY alsoBought.sales DESC, alsoBought.related_article_id DESC

            LIMIT $limit
        ";

        $alsoBought = Shopware()->Db()->fetchAll($sql, array(
            'articleId' => (int) $articleID,
            'categoryId' => (int) $this->categoryId,
            'customerGroupId' => (int) $this->customerGroupId
        ));


        Shopware()->Events()->notify('Shopware_Modules_Marketing_AlsoBoughtArticles', array(
            'subject'  => $this,
            'articles' => $alsoBought
        ));

        return $alsoBought;
    }

    /**
     * Get banners to display in this category
     * @param $sCategory
     * @param int $limit
     * @return array     Contains all information about the banner-object
     * @access public
     */
    public function sBanner($sCategory, $limit = 1)
    {
        $limit = (int)$limit;
        try {
            $bannerRepository = Shopware()->Models()->Banner();
            $bannerQuery = $bannerRepository->getAllActiveBanners($sCategory, $limit);
            if ($bannerQuery) {
                $getBanners = $bannerQuery->getArrayResult();
            } else {
                return array();
            }
        }
        catch (Exception $e) {
            return false;
        }

        foreach ($getBanners as &$getAffectedBanners) {
            // converting to old format
            $getAffectedBanners['valid_from'] = $getAffectedBanners['validFrom'];
            $getAffectedBanners['valid_to'] = $getAffectedBanners['validTo'];
            $getAffectedBanners['link_target'] = $getAffectedBanners['linkTarget'];
            $getAffectedBanners['categoryID'] = $getAffectedBanners['categoryId'];

            $getAffectedBanners['img'] = $getAffectedBanners['image'];

            // count views.
            /** @var $statRepository \Shopware\Models\Tracking\Repository */
            $statRepository = Shopware()->Models()->getRepository('\Shopware\Models\Tracking\Banner');
            $bannerStatistics = $statRepository->getOrCreateBannerStatsModel($getAffectedBanners['id']);
            $bannerStatistics->increaseViews();
            Shopware()->Models()->persist($bannerStatistics);
            Shopware()->Models()->flush($bannerStatistics);

            if (!empty($getAffectedBanners["link"])) {
                $query = array(
                    'module' => 'frontend',
                    'controller' => 'tracking',
                    'action' => 'countBannerClick',
                    'bannerId' => $getAffectedBanners["id"]
                );
                $getAffectedBanners["link"] = Shopware()->Front()->Router()->assemble($query);
            }
        }
        if ($limit == 1) {
            $getBanners = $getBanners[0];
        }

        return $getBanners;
    }

    public function sGetPremiums()
    {
        $sql = "
            SELECT id, esdarticle FROM s_order_basket
            WHERE sessionID='" . $this->sSYSTEM->sSESSION_ID . "'
            AND modus=0
            ORDER BY esdarticle DESC
		";

        $checkForEsdOnly = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);

        foreach ($checkForEsdOnly as $esdCheck) {
            if ($esdCheck["esdarticle"]) {
                $esdOnly = true;
            } else {
                $esdOnly = false;
            }
        }
        if (!empty($esdOnly)) return array();

        $sBasketAmount = $this->sSYSTEM->sMODULES['sBasket']->sGetAmount();
        if (empty($sBasketAmount["totalAmount"]))
            $sBasketAmount = 0;
        else
            $sBasketAmount = $sBasketAmount["totalAmount"];
        $sql = "
			SELECT
				p.ordernumber as premium_ordernumber, startprice,subshopID, a.id as articleID
			FROM
				s_addon_premiums p,
				s_articles a,
				s_articles_details d2
			WHERE p.ordernumber=d2.ordernumber
			AND d2.articleID=a.id
			AND (p.subshopID = ? OR p.subshopID = 0)
			ORDER BY p.startprice ASC
		";

        $premiums = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql, array($this->sSYSTEM->sSubShop["id"]));

        foreach ($premiums as &$premium) {

			$activeShopId = Shopware()->Shop()->getId();
			$activeFactor = $this->sSYSTEM->sCurrency["factor"];
			if($premium['subshopID'] === "0"){
				$sql= "SELECT factor FROM s_core_currencies WHERE id=(SELECT defaultcurrency FROM s_core_multilanguage WHERE `default` = 1 LIMIT 1)";
				$premiumFactor = Shopware()->Db()->fetchOne($sql, array());
			}else{
				$sql= "SELECT factor FROM s_core_currencies WHERE id=(SELECT defaultcurrency FROM s_core_multilanguage WHERE id=?)";
				$premiumFactor = Shopware()->Db()->fetchOne($sql, array($activeShopId));
			}
			if($premiumFactor == $activeFactor){
				$activeFactor = 1;
			}else{
				$activeFactor = $activeFactor/$premiumFactor;
			}

            $premium["startprice"] *= $activeFactor;

			if($sBasketAmount >= $premium["startprice"]){
				$premium["available"] = 1;
			}else{
				$premium["available"] = 0;
			}

            if (empty($premium["available"])) $premium["sDifference"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($premium["startprice"] - $sBasketAmount);
            $premium["sArticle"] = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById("fix", 0, $premium["premium_ordernumber"]);
            $premium["startprice"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($premium["startprice"]);
            $sql = "SELECT ordernumber, additionaltext FROM s_articles_details WHERE articleID={$premium["articleID"]} AND kind != 3";
            $premium["sVariants"] = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
        }
        return $premiums;
    }

    public function sBuildTagCloud($categoryId = null)
    {
        $categoryId = (int) $categoryId;
        if (empty($categoryId)) {
            $categoryId = $this->categoryId;
        }

        if (!empty($this->sSYSTEM->sCONFIG['sTAGCLOUDMAX']))
            $tagSize = (int)$this->sSYSTEM->sCONFIG['sTAGCLOUDMAX'];
        else
            $tagSize = 50;
        if (!empty($this->sSYSTEM->sCONFIG['sTAGTIME']))
            $tagTime = (int)$this->sSYSTEM->sCONFIG['sTAGTIME'];
        else
            $tagTime = 3;

        $sql = "
			SELECT
			  a.id as articleID,
			  a.name as articleName,
			  COUNT(r.articleID) as relevance

			FROM s_articles a
			INNER JOIN s_articles_categories_ro ac
                ON  ac.articleID = a.id
                AND ac.categoryID = $categoryId
            INNER JOIN s_categories c
                ON  c.id = ac.categoryID
                AND c.active = 1

			LEFT JOIN s_emarketing_lastarticles r
			ON a.id = r.articleID
			AND r.time >= DATE_SUB(NOW(),INTERVAL $tagTime DAY)

			LEFT JOIN s_articles_avoid_customergroups ag
            ON ag.articleID=a.id
            AND ag.customergroupID={$this->customerGroupId}

			WHERE a.active = 1
	        AND ag.articleID IS NULL

			GROUP BY a.id
			ORDER BY COUNT(r.articleID) DESC
			LIMIT $tagSize
		";
        $articles = $this->sSYSTEM->sDB_CONNECTION->CacheGetAssoc($this->sSYSTEM->sCONFIG['sCACHEARTICLE'], $sql);
        if (empty($articles)) {
            return array();
        }
        $articles = $this->sSYSTEM->sMODULES["sArticles"]->sGetTranslations($articles, "article");

        $pos = 1;
        $anz = count($articles);
        if (!empty($this->sSYSTEM->sCONFIG['sTAGCLOUDSPLIT']))
            $steps = (int)$this->sSYSTEM->sCONFIG['sTAGCLOUDSPLIT'];
        else
            $steps = 3;
        if (!empty($this->sSYSTEM->sCONFIG['sTAGCLOUDCLASS']))
            $class = (string)$this->sSYSTEM->sCONFIG['sTAGCLOUDCLASS'];
        else
            $class = "tag";
        $link = $this->sSYSTEM->sCONFIG['sBASEFILE'] . "?sViewport=detail&sArticle=";

        foreach ($articles as $articleId => $article) {
            $name = strip_tags(html_entity_decode($article['articleName'], ENT_QUOTES, 'UTF-8'));
            $name = preg_replace("/[^\\w0-9äöüßÄÖÜ´`.-]/u", " ", $name);
            $name = preg_replace('/\s\s+/', ' ', $name);
            $name = preg_replace('/\(.*\)/', '', $name);
            $name = trim($name, " -");
            $articles[$articleId]["articleID"] = $articleId;
            $articles[$articleId]["name"] = $name;
            $articles[$articleId]["class"] = $class . round($pos / $anz * $steps);
            $articles[$articleId]["link"] = $link . $articleId;
            $pos++;
        }

        shuffle($articles);
        return $articles;
    }

    public function sGetSimilarArticles($articleId = null, $limit = null)
    {
        $limit = empty($limit) ? 6 : (int)$limit;
        $articleId = empty($articleId) ? (int)$this->sSYSTEM->_GET['sArticle'] : (int)$articleId;

        $sql = "
			SELECT
			  a.id as articleID,
			  a.name as articleName,
			  IF(s.id, 2, 0) + -- Similar article
			  IF(s2.id, 1, 0)  -- Same category
			    as relevance

			FROM s_articles a

            INNER JOIN s_articles_categories_ro ac
                ON ac.articleID=a.id
                AND ac.categoryID = {$this->categoryId}
            INNER JOIN s_categories c
                ON c.id = ac.categoryID
                AND c.active = 1

			LEFT JOIN s_articles_avoid_customergroups ag
            ON ag.articleID=a.id
            AND ag.customergroupID={$this->customerGroupId}

            LEFT JOIN s_articles o
            ON o.id=$articleId

            LEFT JOIN s_articles_similar s
            ON s.articleID=o.id
            AND s.relatedarticle=a.id

            LEFT JOIN s_articles_categories_ro s1
            ON s1.articleID=o.id

            LEFT JOIN s_articles_categories_ro s2
            ON s2.categoryID=s1.categoryID
            AND s2.articleID=a.id

			WHERE a.active = 1
	        AND ag.articleID IS NULL
	        AND a.id!=$articleId

			GROUP BY a.id
			ORDER BY relevance DESC
			LIMIT $limit
		";
        $similarArticleIds = $this->sSYSTEM->sDB_CONNECTION->CacheGetCol($this->sSYSTEM->sCONFIG['sCACHEARTICLE'], $sql);

        $similarArticles = array();
        if (!empty($similarArticleIds))
            foreach ($similarArticleIds as $similarArticleId) {
                $article = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById("fix", 0, (int)$similarArticleId);
                if (!empty($article)) {
                    $similarArticles[] = $article;
                }
            }
        return $similarArticles;
    }

    public function sCampaignsGetList($id, $group = false)
    {
        $sToday = date("Y-m-d");

        if ($group) {
            $sqlGroup = "AND positionGroup='$group'";
        } else {
            $sqlGroup = "";
        }

        $id = intval($id);

        $licenceAdd = "";


        $getCampaigns = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll(3600, "
		SELECT id, image, description, link, linktarget FROM s_emarketing_promotion_main
		WHERE parentID=$id AND active=1
		$sqlGroup
		AND ((TO_DAYS(start) <= TO_DAYS('$sToday') AND
		TO_DAYS(end) >= TO_DAYS('$sToday')) OR
		(start='0000-00-00' AND end='0000-00-00'))
		ORDER BY position
		$licenceAdd
		");

        foreach ($getCampaigns as $campaignKey => $campaignValue) {
            if ($getCampaigns[$campaignKey]["image"]) {
                $getCampaigns[$campaignKey]["image"] = $this->sSYSTEM->sPathBanner . $getCampaigns[$campaignKey]["image"];
            }

            if (!preg_match("/http/", $getCampaigns[$campaignKey]["link"]) && $getCampaigns[$campaignKey]["link"]) {
                $getCampaigns[$campaignKey]["link"] = "http://" . $getCampaigns[$campaignKey]["link"];

            } elseif (!$getCampaigns[$campaignKey]["link"]) {
                // Building link to detail-page
                $getCampaigns[$campaignKey]["link"] = $this->sSYSTEM->sCONFIG['sBASEFILE'] . "?sViewport=campaign&sCampaign=" . $campaignValue["id"];
            }
        }

        return $getCampaigns;
    }

    public function sCampaignsGetDetail($id)
    {

        $id = intval($id);

        $sql = "
		SELECT id, image, description, link, linktarget FROM s_emarketing_promotion_main
		WHERE id=$id
		AND ((TO_DAYS(start) <= TO_DAYS(now()) AND
		TO_DAYS(end) >= TO_DAYS(now())) OR
		(start='0000-00-00' AND end='0000-00-00'))
		ORDER BY position
		";

        $getCampaigns = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($sql);

        if (!$getCampaigns["id"]) {
            return false;
        } else {
            // Fetch all positions
            $sql = "
			SELECT id, type, description FROM s_emarketing_promotion_containers
			WHERE promotionID=$id
			ORDER BY position
			";

            $getCampaignContainers = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll($sql);

            foreach ($getCampaignContainers as $campaignKey => $campaignValue) {
                switch ($campaignValue["type"]) {
                    case "ctBanner":
                        // Query Banner
                        $getBanner = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow(3600, "
						SELECT image, link, linkTarget, description FROM s_emarketing_promotion_banner
						WHERE parentID={$campaignValue["id"]}
						");
                        // Rewrite banner
                        if ($getBanner["image"]) {
                            $getBanner["image"] = $this->sSYSTEM->sPathBanner . $getBanner["image"];
                        }

                        if (!preg_match("/http/", $getBanner["link"]) && $getBanner["link"]) {
                            $getBanner["link"] = "http://" . $getBanner["link"];
                        }

                        $getCampaignContainers[$campaignKey]["description"] = $getBanner["description"];
                        $getCampaignContainers[$campaignKey]["data"] = $getBanner;
                        break;
                    case "ctLinks":
                        // Query links
                        $getLinks = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll(3600, "
						SELECT description, link, target FROM s_emarketing_promotion_links
						WHERE parentID={$campaignValue["id"]}
						ORDER BY position
						");
                        $getCampaignContainers[$campaignKey]["data"] = $getLinks;
                        break;
                    case "ctArticles":
                        $getArticles = $this->sSYSTEM->sDB_CONNECTION->CacheGetAll(3600, "
						SELECT * FROM s_emarketing_promotion_articles
						WHERE parentID={$campaignValue["id"]}
						ORDER BY position
						");
                        unset($articleData);
                        foreach ($getArticles as $article) {


                            if ($article["type"]) {
                                $category = $this->sSYSTEM->_GET["sCategory"] ? $this->sSYSTEM->_GET["sCategory"] : $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"];
                                if ($article["type"] == "image") {
                                    $tmpContainer = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById($article["type"], $category, $article);
                                } else {
                                    $articleID = (int)$this->sSYSTEM->sMODULES['sArticles']->sGetArticleIdByOrderNumber($article['articleordernumber']);
                                    $tmpContainer = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById($article["type"], $category, $articleID);
                                }

                                if (count($tmpContainer) && isset($tmpContainer["articleName"])) {
                                    $articleData[] = $tmpContainer;
                                } elseif ($article["type"] == "image") {
                                    $articleData[] = $tmpContainer;
                                }
                            }

                        }

                        $getCampaignContainers[$campaignKey]["data"] = $articleData;
                        break;
                    case "ctText":
                        $getText = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow(3600, "
						SELECT headline, html FROM s_emarketing_promotion_html
						WHERE parentID={$campaignValue["id"]}
						");
                        $getCampaignContainers[$campaignKey]["description"] = $getText["headline"];
                        $getCampaignContainers[$campaignKey]["data"] = $getText;
                        break;

                }
            }

            //print_r($getCampaignContainers);
            $getCampaigns["containers"] = $getCampaignContainers;
            return $getCampaigns;
        }
    }

    public function sMailCampaignsGetDetail($id)
    {
        $sql = "
		SELECT * FROM s_campaigns_mailings
		WHERE id=$id
		";
        $getCampaigns = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);

        if (!$getCampaigns["id"]) {
            return false;
        } else {
            // Fetch all positions
            $sql = "
			SELECT id, type, description, value FROM s_campaigns_containers
			WHERE promotionID=$id
			ORDER BY position
			";
            $sql = Enlight()->Events()->filter('Shopware_Modules_Marketing_MailCampaignsGetDetail_FilterSQL', $sql,
                array(
                    'subject' => $this,
                    'id' => $id
                )
            );

            $getCampaignContainers = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);

            foreach ($getCampaignContainers as $campaignKey => $campaignValue) {
                switch ($campaignValue["type"]) {
                    case "ctBanner":
                        // Query Banner
                        $getBanner = $this->sSYSTEM->sDB_CONNECTION->GetRow("
						SELECT image, link, linkTarget, description FROM s_campaigns_banner
						WHERE parentID={$campaignValue["id"]}
						");
                        // Rewrite banner
                        if ($getBanner["image"]) {
                            $getBanner["image"] = $this->sSYSTEM->sPathBanner . $getBanner["image"];
                        }

                        if (!preg_match("/http/", $getBanner["link"]) && $getBanner["link"]) {
                            $getBanner["link"] = "http://" . $getBanner["link"];
                        }

                        $getCampaignContainers[$campaignKey]["description"] = $getBanner["description"];
                        $getCampaignContainers[$campaignKey]["data"] = $getBanner;
                        break;
                    case "ctLinks":
                        // Query links
                        $getLinks = $this->sSYSTEM->sDB_CONNECTION->GetAll("
						SELECT description, link, target FROM s_campaigns_links
						WHERE parentID={$campaignValue["id"]}
						ORDER BY position
						");
                        $getCampaignContainers[$campaignKey]["data"] = $getLinks;
                        break;
                    case "ctArticles":
                        $sql = "
						SELECT articleordernumber, type FROM s_campaigns_articles
						WHERE parentID={$campaignValue["id"]}
						ORDER BY position
						";

                        $getArticles = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
                        unset($articleData);
                        foreach ($getArticles as $article) {
                            if ($article["type"]) {
                                $category = $this->sSYSTEM->_GET["sCategory"] ? $this->sSYSTEM->_GET["sCategory"] : $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["parentID"];
                                $tmpContainer = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById($article["type"], $category, $article['articleordernumber']);

                                if (count($tmpContainer) && isset($tmpContainer["articleName"])) {
                                    $articleData[] = $tmpContainer;
                                }
                            }

                        }

                        $getCampaignContainers[$campaignKey]["data"] = $articleData;
                        break;
                    case "ctText":
                    case "ctVoucher":
                        $getText = $this->sSYSTEM->sDB_CONNECTION->GetRow("
							SELECT headline, html,image,alignment,link FROM s_campaigns_html
							WHERE parentID={$campaignValue["id"]}
						");
                        if ($getText["image"]) {
                            $getText["image"] = $this->sSYSTEM->sPathBanner . $getText["image"];
                        }
                        if (!preg_match("/http/", $getText["link"]) && $getText["link"]) {
                            $getText["link"] = "http://" . $getText["link"];
                        }
                        $getCampaignContainers[$campaignKey]["description"] = $getText["headline"];
                        $getCampaignContainers[$campaignKey]["data"] = $getText;
                        break;
                }
            }
            $getCampaigns["containers"] = $getCampaignContainers;
            return $getCampaigns;
        }
    }

    public function sCampaignsGetSuggestions($id, $userid = 0)
    {
        return array();
    }
}
