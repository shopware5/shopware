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

use Shopware\Bundle\StoreFrontBundle;
use Shopware\Models\Banner\Banner;

/**
 * Deprecated Shopware Class that handles marketing related functions
 *
 * @category  Shopware
 * @package   Shopware\Core
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
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
     * @var StoreFrontBundle\Service\ContextServiceInterface
     */
    private $contextService;

    /**
     * @var StoreFrontBundle\Service\AdditionalTextServiceInterface
     */
    private $additionalTextService;

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
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    /**
     * Class constructor.
     */
    public function __construct(
        StoreFrontBundle\Service\ContextServiceInterface $contextService = null,
        StoreFrontBundle\Service\AdditionalTextServiceInterface $additionalTextService = null
    ) {
        $this->category = Shopware()->Shop()->getCategory();
        $this->categoryId = $this->category->getId();
        $this->customerGroupId = (int) Shopware()->Modules()->System()->sUSERGROUPDATA['id'];

        $this->contextService = $contextService;
        $this->additionalTextService = $additionalTextService;

        if ($this->contextService == null) {
            $this->contextService = Shopware()->Container()->get('shopware_storefront.context_service');
        }

        if ($this->additionalTextService == null) {
            $this->additionalTextService = Shopware()->Container()->get('shopware_storefront.additional_text_service');
        }

        $this->db = Shopware()->Db();
    }

    public function sGetSimilaryShownArticles($articleId, $limit = 0)
    {
        if (empty($limit)) {
            $limit = empty($this->sSYSTEM->sCONFIG['sMAXCROSSSIMILAR']) ? 4 : (int) $this->sSYSTEM->sCONFIG['sMAXCROSSSIMILAR'];
        }
        $limit = (int) $limit;

        $where = '';
        if (!empty($this->sBlacklist)) {
            $where = Shopware()->Db()->quote($this->sBlacklist);
            $where = 'AND similarShown.related_article_id NOT IN (' . $where . ')';
        }

        $sql = "
            SELECT
                 similarShown.viewed as hits,
                 similarShown.related_article_id as id,
                 detail.ordernumber as `number`

            FROM s_articles_similar_shown_ro as similarShown

              INNER JOIN s_articles as a
                ON  a.id = similarShown.related_article_id
                AND a.active = 1

              INNER JOIN s_articles_details as detail
                ON detail.id = a.main_detail_id

              INNER JOIN s_articles_categories_ro ac
                ON  ac.articleID = similarShown.related_article_id
                AND ac.categoryID = :categoryId

              INNER JOIN s_categories c
                ON  c.id = ac.categoryID
                AND c.active = 1

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
            $limit = empty($this->sSYSTEM->sCONFIG['sMAXCROSSALSOBOUGHT']) ? 4 : (int) $this->sSYSTEM->sCONFIG['sMAXCROSSALSOBOUGHT'];
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
                alsoBought.related_article_id as id,
                detail.ordernumber as `number`

            FROM   s_articles_also_bought_ro alsoBought
                INNER JOIN s_articles articles
                    ON  alsoBought.related_article_id = articles.id
                    AND articles.active = 1

                INNER JOIN s_articles_details detail
                    ON detail.id = articles.main_detail_id

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
        $limit = (int) $limit;
        try {
            $bannerRepository = Shopware()->Models()->getRepository(Banner::class);
            $bannerQuery = $bannerRepository->getAllActiveBanners($sCategory, $limit);
            if ($bannerQuery) {
                $getBanners = $bannerQuery->getArrayResult();
            } else {
                return array();
            }
        } catch (Exception $e) {
            return false;
        }


        $images = array_column($getBanners, 'image');
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        array_walk($images, function (&$image) use ($mediaService) {
            $image = $mediaService->normalize($image);
        });

        $mediaIds = $this->getMediaIdsOfPath($images);
        $context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();
        $medias = Shopware()->Container()->get('shopware_storefront.media_service')->getList($mediaIds, $context);

        foreach ($getBanners as &$getAffectedBanners) {
            // converting to old format
            $getAffectedBanners['valid_from'] = $getAffectedBanners['validFrom'];
            $getAffectedBanners['valid_to'] = $getAffectedBanners['validTo'];
            $getAffectedBanners['link_target'] = $getAffectedBanners['linkTarget'];
            $getAffectedBanners['categoryID'] = $getAffectedBanners['categoryId'];

            $getAffectedBanners['img'] = $getAffectedBanners['image'];

            $media = $this->getMediaByPath($medias, $getAffectedBanners['image']);
            if ($media !== null) {
                $media = Shopware()->Container()->get('legacy_struct_converter')->convertMediaStruct($media);
                $getAffectedBanners['media'] = $media;
            }

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

    /**
     * @param StoreFrontBundle\Struct\Media[] $media
     * @param string $path
     * @return null|\Shopware\Bundle\StoreFrontBundle\Struct\Media
     */
    private function getMediaByPath($media, $path)
    {
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        foreach ($media as $single) {
            if ($mediaService->normalize($single->getFile()) == $path) {
                return $single;
            }
        }
        return null;
    }

    public function sGetPremiums()
    {
        $context = $this->contextService->getContext();

        $sql = "
            SELECT id, esdarticle FROM s_order_basket
            WHERE sessionID=?
            AND modus=0
            ORDER BY esdarticle DESC
        ";

        $checkForEsdOnly = $this->db->fetchAll(
            $sql,
            array($this->sSYSTEM->sSESSION_ID)
        );

        foreach ($checkForEsdOnly as $esdCheck) {
            if ($esdCheck["esdarticle"]) {
                $esdOnly = true;
            } else {
                $esdOnly = false;
            }
        }
        if (!empty($esdOnly)) {
            return array();
        }

        $sBasketAmount = $this->sSYSTEM->sMODULES['sBasket']->sGetAmount();
        if (empty($sBasketAmount["totalAmount"])) {
            $sBasketAmount = 0;
        } else {
            $sBasketAmount = $sBasketAmount["totalAmount"];
        }
        $sql = "
            SELECT
                p.ordernumber AS premium_ordernumber,
                startprice, subshopID, a.id AS articleID,
                a.main_detail_id
            FROM
                s_addon_premiums p,
                s_articles a,
                s_articles_details d2
            WHERE p.ordernumber=d2.ordernumber
            AND d2.articleID=a.id
            AND (p.subshopID = ? OR p.subshopID = 0)
            ORDER BY p.startprice ASC
        ";
        $activeShopId = $context->getShop()->getId();
        $premiums = $this->db->fetchAll($sql, array($activeShopId));

        foreach ($premiums as &$premium) {
            $activeFactor = $this->sSYSTEM->sCurrency["factor"];

            if ($premium['subshopID'] === "0") {
                $sql= "
                SELECT factor FROM s_core_currencies
                INNER JOIN s_core_shops
                  ON s_core_shops.currency_id = s_core_currencies.id
                WHERE s_core_shops.`default` = 1 LIMIT 1
                ";
                $premiumFactor = Shopware()->Db()->fetchOne($sql, array());
            } else {
                $sql= "
                SELECT factor FROM s_core_currencies
                INNER JOIN s_core_shops
                  ON s_core_shops.currency_id = s_core_currencies.id
                WHERE s_core_shops.id = ? LIMIT 1
                ";
                $premiumFactor = Shopware()->Db()->fetchOne($sql, array($activeShopId));
            }

            if ($premiumFactor != 0) {
                $activeFactor = $activeFactor / $premiumFactor;
            } else {
                $activeFactor = 0;
            }

            $premium["startprice"] *= $activeFactor;

            if ($sBasketAmount >= $premium["startprice"]) {
                $premium["available"] = 1;
            } else {
                $premium["available"] = 0;
            }

            if (empty($premium["available"])) {
                $premium["sDifference"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($premium["startprice"] - $sBasketAmount);
            }
            $premium["sArticle"] = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById("fix", 0, $premium["articleID"]);
            $premium["startprice"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($premium["startprice"]);
            $premium["sVariants"] = $this->getVariantDetailsForPremiumArticles($premium["articleID"], $premium["main_detail_id"]);
        }
        return $premiums;
    }

    /**
     * For the provided article id, returns the associated variant numbers and additional texts
     *
     * @param $articleId
     * @param $mainDetailId
     * @return array
     */
    private function getVariantDetailsForPremiumArticles($articleId, $mainDetailId)
    {
        $context = $this->contextService->getShopContext();

        $sql = "SELECT id, ordernumber, additionaltext
            FROM s_articles_details
            WHERE articleID = :articleId AND kind != 3";

        $variantsData = Shopware()->Db()->fetchAll(
            $sql,
            array('articleId' => $articleId)
        );

        foreach ($variantsData as $variantData) {
            $product = new StoreFrontBundle\Struct\ListProduct(
                $articleId,
                $variantData['id'],
                $variantData['ordernumber']
            );

            if ($variantData['id'] == $mainDetailId) {
                $variantData = Shopware()->Modules()->Articles()->sGetTranslation(
                    $variantData,
                    $articleId,
                    "article"
                );
            } else {
                $variantData = Shopware()->Modules()->Articles()->sGetTranslation(
                    $variantData,
                    $variantData['id'],
                    "variant"
                );
            }

            $product->setAdditional($variantData['additionaltext']);
            $products[$variantData['ordernumber']] = $product;
        }

        $products = $this->additionalTextService->buildAdditionalTextLists($products, $context);

        return array_map(
            function (StoreFrontBundle\Struct\ListProduct $elem) {
                return array(
                    'ordernumber' => $elem->getNumber(),
                    'additionaltext' => $elem->getAdditional()
                );
            },
            $products
        );
    }

    public function sBuildTagCloud($categoryId = null)
    {
        $categoryId = (int) $categoryId;
        if (empty($categoryId)) {
            $categoryId = $this->categoryId;
        }

        if (!empty($this->sSYSTEM->sCONFIG['sTAGCLOUDMAX'])) {
            $tagSize = (int) $this->sSYSTEM->sCONFIG['sTAGCLOUDMAX'];
        } else {
            $tagSize = 50;
        }
        if (!empty($this->sSYSTEM->sCONFIG['sTAGTIME'])) {
            $tagTime = (int) $this->sSYSTEM->sCONFIG['sTAGTIME'];
        } else {
            $tagTime = 3;
        }

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

        $articles = $this->db->fetchAssoc($sql);
        array_walk($articles, function (&$article) {
            unset($article['articleID']);
        });

        if (empty($articles)) {
            return array();
        }
        $articles = $this->sSYSTEM->sMODULES["sArticles"]->sGetTranslations($articles, "article");

        $pos = 1;
        $anz = count($articles);
        if (!empty($this->sSYSTEM->sCONFIG['sTAGCLOUDSPLIT'])) {
            $steps = (int) $this->sSYSTEM->sCONFIG['sTAGCLOUDSPLIT'];
        } else {
            $steps = 3;
        }
        if (!empty($this->sSYSTEM->sCONFIG['sTAGCLOUDCLASS'])) {
            $class = (string) $this->sSYSTEM->sCONFIG['sTAGCLOUDCLASS'];
        } else {
            $class = "tag";
        }
        $link = $this->sSYSTEM->sCONFIG['sBASEFILE'] . "?sViewport=detail&sArticle=";

        foreach ($articles as $articleId => $article) {
            $name = strip_tags(html_entity_decode($article['articleName'], ENT_QUOTES, 'UTF-8'));
            $name = preg_replace("/[^\\w0-9äöüßÄÖÜ´`.-]/u", " ", $name);
            $name = preg_replace('/\s\s+/', ' ', $name);
            $name = preg_replace('/\(.*\)/', '', $name);
            $name = trim($name, " -");
            $articles[$articleId]["articleID"] = $articleId;
            $articles[$articleId]["name"] = $name;

            if ($anz != 0) {
                $articles[$articleId]["class"] = $class . round($pos / $anz * $steps);
            } else {
                $articles[$articleId]["class"] = $class . 0;
            }

            $articles[$articleId]["link"] = $link . $articleId;
            $pos++;
        }

        shuffle($articles);
        return $articles;
    }

    public function sGetSimilarArticles($articleId = null, $limit = null)
    {
        $limit = empty($limit) ? 6 : (int) $limit;
        $articleId = empty($articleId) ? (int) $this->sSYSTEM->_GET['sArticle'] : (int) $articleId;

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
        $similarArticleIds = $this->db->fetchCol($sql);

        $similarArticles = array();
        if (!empty($similarArticleIds)) {
            foreach ($similarArticleIds as $similarArticleId) {
                $article = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById("fix", 0, (int) $similarArticleId);
                if (!empty($article)) {
                    $similarArticles[] = $article;
                }
            }
        }
        return $similarArticles;
    }

    public function sMailCampaignsGetDetail($id)
    {
        $sql = "
        SELECT * FROM s_campaigns_mailings
        WHERE id=$id
        ";
        $getCampaigns = $this->db->fetchRow($sql);

        if (!$getCampaigns) {
            return false;
        } else {
            // Fetch all positions
            $sql = "
            SELECT id, type, description, value FROM s_campaigns_containers
            WHERE promotionID=$id
            ORDER BY position
            ";
            $sql = Shopware()->Events()->filter('Shopware_Modules_Marketing_MailCampaignsGetDetail_FilterSQL', $sql,
                array(
                    'subject' => $this,
                    'id' => $id
                )
            );

            $getCampaignContainers = $this->db->fetchAll($sql);
            $mediaService = Shopware()->Container()->get('shopware_media.media_service');

            foreach ($getCampaignContainers as $campaignKey => $campaignValue) {
                switch ($campaignValue["type"]) {
                    case "ctBanner":
                        // Query Banner
                        $getBanner = $this->db->fetchRow("
                        SELECT image, link, linkTarget, description FROM s_campaigns_banner
                        WHERE parentID={$campaignValue["id"]}
                        ");
                        // Rewrite banner
                        if ($getBanner["image"]) {
                            $getBanner["image"] = $mediaService->getUrl($getBanner["image"]);
                        }

                        if (!preg_match("/http/", $getBanner["link"]) && $getBanner["link"]) {
                            $getBanner["link"] = "http://" . $getBanner["link"];
                        }

                        $getCampaignContainers[$campaignKey]["description"] = $getBanner["description"];
                        $getCampaignContainers[$campaignKey]["data"] = $getBanner;
                        break;
                    case "ctLinks":
                        // Query links
                        $getLinks = $this->db->fetchAll("
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

                        $getArticles = $this->db->fetchAll($sql);
                        $getCampaignContainers[$campaignKey]["data"] = $this->sGetMailCampaignsArticles($getArticles);
                        break;
                    case "ctText":
                    case "ctVoucher":
                        $getText = $this->db->fetchRow("
                            SELECT headline, html,image,alignment,link FROM s_campaigns_html
                            WHERE parentID={$campaignValue["id"]}
                        ");
                        if ($getText["image"]) {
                            $getText["image"] = $mediaService->getUrl($getText["image"]);
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

    /**
     * Processes the newsletter articles and returns the corresponding data.
     *
     * @param $articles
     * @return array
     */
    private function sGetMailCampaignsArticles($articles)
    {
        /** @var \Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface $contextService */
        $contextService = Shopware()->Container()->get('shopware_storefront.context_service');
        $categoryId = $contextService->getShopContext()->getShop()->getCategory()->getId();

        $articleData = [];
        foreach ($articles as $article) {
            $articleData[] = Shopware()->Modules()->Articles()->sGetPromotionById($article['type'], $categoryId, $article['articleordernumber']);
        }

        return $articleData;
    }

    /**
     * @param $images
     * @return int[]
     * @throws Exception
     */
    private function getMediaIdsOfPath($images)
    {
        /**@var $query \Doctrine\DBAL\Query\QueryBuilder */
        $query = Shopware()->Container()->get('dbal_connection')->createQueryBuilder();
        $query->select(['media.id'])
            ->from('s_media', 'media')
            ->where('media.path IN (:path)')
            ->setParameter(':path', $images, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);

        $statement = $query->execute();
        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }
}
