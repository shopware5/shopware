<?php

declare(strict_types=1);
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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\AdditionalTextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\MediaServiceInterface as StorefrontMediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Media;
use Shopware\Components\Compatibility\LegacyStructConverter;
use Shopware\Components\ContainerAwareEventManager;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Banner\Banner;
use Shopware\Models\Newsletter\Container;
use Shopware\Models\Tracking\Banner as TrackingBanner;

/**
 * Deprecated Shopware Class that handles marketing related functions
 */
class sMarketing implements Enlight_Hook
{
    /**
     * Pointer to Shopware-Core-public functions
     *
     * @var sSystem
     */
    public $sSYSTEM;

    /**
     * Array with blacklisted products (already in basket)
     *
     * @var array<int>
     */
    public $sBlacklist = [];

    /**
     * @var int
     */
    public $categoryId;

    /**
     * @var int
     */
    public $customerGroupId;

    private ContextServiceInterface $contextService;

    private AdditionalTextServiceInterface $additionalTextService;

    private Shopware_Components_Config $config;

    private sArticles $productModule;

    private sBasket $basketModule;

    private Enlight_Controller_Front $front;

    private Connection $connection;

    private ModelManager $modelManager;

    private MediaServiceInterface $mediaService;

    private StorefrontMediaServiceInterface $storefrontMediaService;

    private LegacyStructConverter $legacyStructConverter;

    private ContainerAwareEventManager $eventManager;

    public function __construct(
        ?ContextServiceInterface $contextService = null,
        ?AdditionalTextServiceInterface $additionalTextService = null,
        ?Shopware_Components_Config $config = null,
        ?sArticles $productModule = null,
        ?sBasket $basketModule = null,
        ?Enlight_Controller_Front $front = null,
        ?Connection $connection = null,
        ?ModelManager $modelManager = null,
        ?MediaServiceInterface $mediaService = null,
        ?StorefrontMediaServiceInterface $storefrontMediaService = null,
        ?LegacyStructConverter $legacyStructConverter = null,
        ?ContainerAwareEventManager $eventManager = null
    ) {
        $container = Shopware()->Container();

        $category = $container->get('shop')->getCategory();
        $this->categoryId = (int) ($category ? $category->getId() : 0);
        $this->contextService = $contextService ?? $container->get(ContextServiceInterface::class);
        $this->customerGroupId = (int) $this->contextService->getShopContext()->getCurrentCustomerGroup()->getId();

        $this->additionalTextService = $additionalTextService ?? $container->get(AdditionalTextServiceInterface::class);

        $this->config = $config ?? $container->get('config');
        $this->productModule = $productModule ?? $container->get('modules')->Articles();
        $this->basketModule = $basketModule ?? $container->get('modules')->Basket();
        $this->front = $front ?: $container->get('front');
        $this->connection = $connection ?: $container->get(Connection::class);
        $this->modelManager = $modelManager ?: $container->get(ModelManager::class);
        $this->mediaService = $mediaService ?: $container->get(MediaServiceInterface::class);
        $this->storefrontMediaService = $storefrontMediaService ?: $container->get(StorefrontMediaServiceInterface::class);
        $this->legacyStructConverter = $legacyStructConverter ?: $container->get(LegacyStructConverter::class);
        $this->eventManager = $eventManager ?: $container->get(ContainerAwareEventManager::class);
    }

    /**
     * @param int $articleId
     * @param int $limit
     *
     * @return array<array<string, mixed>>
     */
    public function sGetSimilaryShownArticles($articleId, $limit = 0)
    {
        if (empty($limit)) {
            $limit = empty($this->config->get('sMAXCROSSSIMILAR')) ? 4 : (int) $this->config->get('sMAXCROSSSIMILAR');
        }
        $limit = (int) $limit;

        $where = '';
        if (!empty($this->sBlacklist)) {
            $where = sprintf(' AND similarShown.related_article_id NOT IN (%s)', implode(',', $this->sBlacklist));
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

        $similarShownProducts = $this->connection->fetchAllAssociative($sql, [
            'articleId' => (int) $articleId,
            'categoryId' => (int) $this->categoryId,
            'customerGroupId' => (int) $this->customerGroupId,
        ]);

        $this->eventManager->notify('Shopware_Modules_Marketing_GetSimilarShownArticles', [
            'subject' => $this,
            'articles' => $similarShownProducts,
        ]);

        return $similarShownProducts;
    }

    /**
     * @param int $articleID
     * @param int $limit
     *
     * @return array<array<string, mixed>>
     */
    public function sGetAlsoBoughtArticles($articleID, $limit = 0)
    {
        if (empty($limit)) {
            $limit = empty($this->config->get('sMAXCROSSALSOBOUGHT')) ? 4 : (int) $this->config->get('sMAXCROSSALSOBOUGHT');
        }
        $limit = (int) $limit;
        $where = '';

        if (!empty($this->sBlacklist)) {
            $where = sprintf(' AND alsoBought.related_article_id NOT IN (%s)', implode(',', $this->sBlacklist));
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

        $alsoBought = $this->connection->fetchAllAssociative($sql, [
            'articleId' => (int) $articleID,
            'categoryId' => (int) $this->categoryId,
            'customerGroupId' => (int) $this->customerGroupId,
        ]);

        $this->eventManager->notify('Shopware_Modules_Marketing_AlsoBoughtArticles', [
            'subject' => $this,
            'articles' => $alsoBought,
        ]);

        return $alsoBought;
    }

    /**
     * Get banners to display in this category
     *
     * @param int $sCategory
     * @param int $limit
     *
     * @return array<string, mixed>|array<array<string, mixed>>|false Contains all information about the banner-object
     */
    public function sBanner($sCategory, $limit = 1)
    {
        $limit = (int) $limit;
        try {
            $bannerQuery = $this->modelManager->getRepository(Banner::class)->getAllActiveBanners($sCategory, $limit);
            if ($bannerQuery) {
                $getBanners = $bannerQuery->getArrayResult();
            } else {
                return [];
            }
        } catch (Exception $e) {
            return false;
        }

        $images = array_column($getBanners, 'image');
        array_walk($images, function (&$image) {
            $image = $this->mediaService->normalize($image);
        });

        $mediaIds = $this->getMediaIdsOfPath($images);
        $context = $this->contextService->getShopContext();
        $medias = $this->storefrontMediaService->getList($mediaIds, $context);

        foreach ($getBanners as &$getAffectedBanners) {
            // converting to old format
            $getAffectedBanners['valid_from'] = $getAffectedBanners['validFrom'];
            $getAffectedBanners['valid_to'] = $getAffectedBanners['validTo'];
            $getAffectedBanners['link_target'] = $getAffectedBanners['linkTarget'];
            $getAffectedBanners['categoryID'] = $getAffectedBanners['categoryId'];

            $getAffectedBanners['img'] = $getAffectedBanners['image'];

            $media = $this->getMediaByPath($medias, $getAffectedBanners['image']);
            if ($media !== null) {
                $media = $this->legacyStructConverter->convertMediaStruct($media);
                $getAffectedBanners['media'] = $media;
            }

            $bannerStatistics = $this->modelManager->getRepository(TrackingBanner::class)->getOrCreateBannerStatsModel($getAffectedBanners['id']);
            $bannerStatistics->increaseViews();
            $this->modelManager->persist($bannerStatistics);
            $this->modelManager->flush($bannerStatistics);

            if (!empty($getAffectedBanners['link'])) {
                $query = [
                    'module' => 'frontend',
                    'controller' => 'tracking',
                    'action' => 'countBannerClick',
                    'bannerId' => $getAffectedBanners['id'],
                ];
                $getAffectedBanners['link'] = $this->front->ensureRouter()->assemble($query);
            }
        }
        if ($limit === 1) {
            $getBanners = $getBanners[0];
        }

        return $getBanners;
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function sGetPremiums()
    {
        $context = $this->contextService->getContext();

        $sql = 'SELECT id, esdarticle FROM s_order_basket
                WHERE sessionID=?
                    AND modus=0
                ORDER BY esdarticle DESC';

        $checkForEsdOnly = $this->connection->fetchAllAssociative(
            $sql,
            [Shopware()->Session()->get('sessionId')]
        );

        foreach ($checkForEsdOnly as $esdCheck) {
            if ($esdCheck['esdarticle']) {
                $esdOnly = true;
            } else {
                $esdOnly = false;
            }
        }
        if (!empty($esdOnly)) {
            return [];
        }

        $sBasketAmount = $this->basketModule->sGetAmount();
        if (empty($sBasketAmount['totalAmount'])) {
            $sBasketAmount = 0;
        } else {
            $sBasketAmount = $sBasketAmount['totalAmount'];
        }
        $sql = '
            SELECT
                p.ordernumber AS premium_ordernumber,
                startprice,
                subshopID,
                a.id AS articleID,
                a.main_detail_id
            FROM
                s_addon_premiums p,
                s_articles a,
                s_articles_details d2
            WHERE p.ordernumber=d2.ordernumber
            AND d2.articleID=a.id
            AND (p.subshopID = ? OR p.subshopID = 0)
            ORDER BY p.startprice ASC
        ';
        $activeShopId = $context->getShop()->getId();
        $premiums = $this->connection->fetchAllAssociative($sql, [$activeShopId]);

        $activeFactor = $context->getCurrency()->getFactor();
        foreach ($premiums as &$premium) {
            if ($premium['subshopID'] === '0') {
                $sql = '
                SELECT factor FROM s_core_currencies
                INNER JOIN s_core_shops
                  ON s_core_shops.currency_id = s_core_currencies.id
                WHERE s_core_shops.`default` = 1 LIMIT 1
                ';
                $premiumFactor = $this->connection->fetchOne($sql, []);
            } else {
                $sql = '
                SELECT factor FROM s_core_currencies
                INNER JOIN s_core_shops
                  ON s_core_shops.currency_id = s_core_currencies.id
                WHERE s_core_shops.id = ? LIMIT 1
                ';
                $premiumFactor = $this->connection->fetchOne($sql, [$activeShopId]);
            }

            if ($premiumFactor != 0) {
                $activeFactor = $activeFactor / $premiumFactor;
            } else {
                $activeFactor = 0;
            }

            $premium['startprice'] *= $activeFactor;

            if ($sBasketAmount >= $premium['startprice']) {
                $premium['available'] = 1;
            } else {
                $premium['available'] = 0;
            }

            if (empty($premium['available'])) {
                $premium['sDifference'] = $this->productModule->sFormatPrice(
                    $premium['startprice'] - $sBasketAmount
                );
            }
            $premium['sArticle'] = $this->productModule->sGetPromotionById(
                'fix',
                0,
                $premium['articleID']
            );
            $premium['startprice'] = $this->productModule->sFormatPrice($premium['startprice']);
            $premium['sVariants'] = $this->getVariantDetailsForPremiumProducts(
                (int) $premium['articleID'],
                (int) $premium['main_detail_id']
            );
        }

        return $premiums;
    }

    /**
     * @param int|null $categoryId
     *
     * @return array<array<string, mixed>>
     */
    public function sBuildTagCloud($categoryId = null)
    {
        $categoryId = (int) $categoryId;
        if (empty($categoryId)) {
            $categoryId = $this->categoryId;
        }

        if (!empty($this->config->get('sTAGCLOUDMAX'))) {
            $tagSize = (int) $this->config->get('sTAGCLOUDMAX');
        } else {
            $tagSize = 50;
        }
        if (!empty($this->config->get('sTAGTIME'))) {
            $tagTime = (int) $this->config->get('sTAGTIME');
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

        $products = $this->connection->executeQuery($sql)->fetchAllAssociativeIndexed();

        if (empty($products)) {
            return [];
        }
        $products = $this->productModule->sGetTranslations($products, 'article');

        $pos = 1;
        $productCount = \count($products);
        if (!empty($this->config->get('sTAGCLOUDSPLIT'))) {
            $steps = (int) $this->config->get('sTAGCLOUDSPLIT');
        } else {
            $steps = 3;
        }
        if (!empty($this->config->get('sTAGCLOUDCLASS'))) {
            $class = (string) $this->config->get('sTAGCLOUDCLASS');
        } else {
            $class = 'tag';
        }
        $link = $this->config->get('sBASEFILE') . '?sViewport=detail&sArticle=';

        foreach ($products as $productId => $product) {
            $name = strip_tags(html_entity_decode($product['articleName'], ENT_QUOTES, 'UTF-8'));
            $name = (string) preg_replace('/[^\\w0-9äöüßÄÖÜ´`.-]/u', ' ', $name);
            $name = (string) preg_replace('/\s\s+/', ' ', $name);
            $name = (string) preg_replace('/\(.*\)/', '', $name);
            $name = trim($name, ' -');
            $products[$productId]['articleID'] = $productId;
            $products[$productId]['name'] = $name;

            if ($productCount !== 0) {
                $products[$productId]['class'] = $class . round($pos / $productCount * $steps);
            } else {
                $products[$productId]['class'] = $class . 0;
            }

            $products[$productId]['link'] = $link . $productId;
            ++$pos;
        }

        shuffle($products);

        return $products;
    }

    /**
     * @param int|null $articleId
     * @param int|null $limit
     *
     * @return list<array<string, mixed>>
     */
    public function sGetSimilarArticles($articleId = null, $limit = null)
    {
        $limit = empty($limit) ? 6 : (int) $limit;
        $productId = (empty($articleId) && $this->front->Request()) ? (int) $this->front->Request()->getParam('sArticle') : (int) $articleId;
        $sql = <<<SQL
SELECT u.articleID, u.articleName, u.Rel
  FROM (

    (
    SELECT DISTINCT

      a.id as articleID,
      a.name as articleName,
      3 as Rel

    FROM s_articles a

      INNER JOIN s_articles_categories_ro ac
        ON ac.articleID = a.id
        AND ac.categoryID = {$this->categoryId}

      INNER JOIN s_categories c
        ON c.id = ac.categoryID
        AND c.active = 1

      LEFT JOIN s_articles_avoid_customergroups ag
        ON ag.articleID = a.id
        AND ag.customergroupID = {$this->customerGroupId}

      INNER JOIN s_articles_similar s
        ON s.relatedarticle = a.id

      INNER JOIN s_articles_categories_ro s1
        ON s1.articleID = a.id

      INNER JOIN s_articles_categories_ro s2
        ON s2.categoryID = s1.categoryID
        AND s2.articleID = a.id

      WHERE a.active = 1
        AND ag.articleID IS NULL
        AND a.id != {$productId}
        AND s.articleID = {$productId}

    LIMIT {$limit}
  )
  UNION ( SELECT DISTINCT

      a.id as articleID,
      a.name as articleName,
      2 as Rel

    FROM s_articles a

      INNER JOIN s_articles_categories_ro ac
        ON ac.articleID = a.id
        AND ac.categoryID = {$this->categoryId}

      INNER JOIN s_categories c
        ON c.id = ac.categoryID
        AND c.active = 1

      LEFT JOIN s_articles_avoid_customergroups ag
        ON ag.articleID = a.id
        AND ag.customergroupID = {$this->customerGroupId}

      INNER JOIN s_articles_similar s
        ON s.articleID = a.id
        AND s.relatedarticle = a.id

      WHERE a.active = 1
        AND ag.articleID IS NULL
        AND a.id != {$productId}

    LIMIT {$limit}

  ) UNION ( SELECT DISTINCT

      a.id as articleID,
      a.name as articleName,
      1 as Rel

    FROM s_articles a

      INNER JOIN s_articles_categories_ro ac
        ON ac.articleID = a.id
        AND ac.categoryID = {$this->categoryId}

      INNER JOIN s_categories c
        ON c.id = ac.categoryID
        AND c.active = 1

      LEFT JOIN s_articles_avoid_customergroups ag
        ON ag.articleID = a.id
        AND ag.customergroupID = {$this->customerGroupId}

      INNER JOIN s_articles_categories_ro s1
        ON s1.articleID = a.id

      INNER JOIN s_articles_categories_ro s2
        ON s2.categoryID = s1.categoryID
        AND s2.articleID = a.id

      WHERE a.active = 1
        AND ag.articleID IS NULL
        AND a.id != {$productId}

    LIMIT {$limit}
  )
) AS u
GROUP BY u.articleID
ORDER BY u.Rel DESC

LIMIT {$limit};

SQL;

        $similarProductIds = $this->connection->fetchFirstColumn($sql);

        $similarProducts = [];
        if (!empty($similarProductIds)) {
            foreach ($similarProductIds as $similarProductId) {
                $product = $this->productModule->sGetPromotionById('fix', 0, (int) $similarProductId);
                if (!empty($product)) {
                    $similarProducts[] = $product;
                }
            }
        }

        return $similarProducts;
    }

    /**
     * @param int $id
     *
     * @return array<string, mixed>|false
     */
    public function sMailCampaignsGetDetail($id)
    {
        $sql = "SELECT * FROM s_campaigns_mailings
                WHERE id=$id";
        $getCampaigns = $this->connection->fetchAssociative($sql);

        if (!$getCampaigns) {
            return false;
        }
        // Fetch all positions
        $sql = "SELECT id, type, description, value FROM s_campaigns_containers
                WHERE promotionID=$id
                ORDER BY position";
        $sql = $this->eventManager->filter(
            'Shopware_Modules_Marketing_MailCampaignsGetDetail_FilterSQL',
            $sql,
            [
                'subject' => $this,
                'id' => $id,
            ]
        );

        $getCampaignContainers = $this->connection->fetchAllAssociative($sql);

        foreach ($getCampaignContainers as $campaignKey => $campaignValue) {
            $parentId = $campaignValue['id'];
            switch ($campaignValue['type']) {
                case Container::TYPE_BANNER:
                    // Query Banner
                    $getBanner = $this->connection->fetchAssociative(
                        'SELECT image, link, linkTarget, description
                         FROM s_campaigns_banner
                         WHERE parentID=:parentId',
                        ['parentId' => $parentId]
                    );
                    if (!\is_array($getBanner)) {
                        break;
                    }
                    // Rewrite banner
                    if ($getBanner['image']) {
                        $getBanner['image'] = $this->mediaService->getUrl($getBanner['image']);
                    }

                    if (!str_contains($getBanner['link'], 'http') && $getBanner['link']) {
                        $getBanner['link'] = 'http://' . $getBanner['link'];
                    }

                    $getCampaignContainers[$campaignKey]['description'] = $getBanner['description'];
                    $getCampaignContainers[$campaignKey]['data'] = $getBanner;
                    break;
                case Container::TYPE_LINKS:
                    // Query links
                    $getLinks = $this->connection->fetchAllAssociative(
                        'SELECT description, link, target
                         FROM s_campaigns_links
                         WHERE parentID=:parentId
                         ORDER BY position',
                        ['parentId' => $parentId]
                    );
                    $getCampaignContainers[$campaignKey]['data'] = $getLinks;
                    break;
                case Container::TYPE_PRODUCTS:
                    $sql = 'SELECT articleordernumber, type
                            FROM s_campaigns_articles
                            WHERE parentID=:parentId
                            ORDER BY position';

                    $getProducts = $this->connection->fetchAllAssociative($sql, ['parentId' => $parentId]);
                    $getCampaignContainers[$campaignKey]['data'] = $this->sGetMailCampaignsProducts($getProducts);
                    break;
                case Container::TYPE_TEXT:
                case Container::TYPE_VOUCHER:
                    $getText = $this->connection->fetchAssociative(
                        'SELECT headline, html, image, alignment, link
                         FROM s_campaigns_html
                         WHERE parentID=:parentId',
                        ['parentId' => $parentId]
                    );
                    if (!\is_array($getText)) {
                        break;
                    }
                    if ($getText['image']) {
                        $getText['image'] = $this->mediaService->getUrl($getText['image']);
                    }
                    if (!str_contains($getText['link'], 'http') && $getText['link']) {
                        $getText['link'] = 'http://' . $getText['link'];
                    }
                    $getCampaignContainers[$campaignKey]['description'] = htmlspecialchars($getText['headline'], ENT_COMPAT);
                    $getCampaignContainers[$campaignKey]['data'] = $getText;
                    break;
            }
        }
        $getCampaigns['containers'] = $getCampaignContainers;

        return $getCampaigns;
    }

    /**
     * @param Media[] $media
     */
    private function getMediaByPath(array $media, string $path): ?Media
    {
        foreach ($media as $single) {
            if ($this->mediaService->normalize($single->getFile()) === $path) {
                return $single;
            }
        }

        return null;
    }

    /**
     * For the provided product id, returns the associated variant numbers and additional texts
     *
     * @return array<string, array{ordernumber: string, additionaltext: string}>
     */
    private function getVariantDetailsForPremiumProducts(int $productId, int $mainVariantId): array
    {
        $context = $this->contextService->getShopContext();

        $sql = 'SELECT id, ordernumber, additionaltext
            FROM s_articles_details
            WHERE articleID = :articleId AND kind != 3';

        $products = [];
        $variantsData = $this->connection->fetchAllAssociative(
            $sql,
            ['articleId' => $productId]
        );

        foreach ($variantsData as $variantData) {
            $product = new ListProduct(
                $productId,
                $variantData['id'],
                $variantData['ordernumber']
            );

            if ($variantData['id'] == $mainVariantId) {
                $variantData = $this->productModule->sGetTranslation(
                    $variantData,
                    $productId,
                    'article'
                );
            } else {
                $variantData = $this->productModule->sGetTranslation(
                    $variantData,
                    $variantData['id'],
                    'variant'
                );
            }

            $product->setAdditional($variantData['additionaltext']);
            $products[(string) $variantData['ordernumber']] = $product;
        }

        $products = $this->additionalTextService->buildAdditionalTextLists($products, $context);

        return array_map(
            function (ListProduct $elem) {
                return [
                    'ordernumber' => $elem->getNumber(),
                    'additionaltext' => $elem->getAdditional(),
                ];
            },
            $products
        );
    }

    /**
     * Processes the newsletter articles and returns the corresponding data.
     *
     * @param array<array<string, mixed>> $products
     *
     * @return list<array<string, mixed>|false>
     */
    private function sGetMailCampaignsProducts(array $products): array
    {
        $categoryId = $this->contextService->getShopContext()->getShop()->getCategory()->getId();

        $productData = [];
        foreach ($products as $product) {
            $productData[] = $this->productModule->sGetPromotionById($product['type'], $categoryId, $product['articleordernumber']);
        }

        return $productData;
    }

    /**
     * @param array<string> $images
     *
     * @throws Exception
     *
     * @return array<int>
     */
    private function getMediaIdsOfPath(array $images): array
    {
        $ids = $this->connection
            ->createQueryBuilder()
            ->select(['media.id'])
            ->from('s_media', 'media')
            ->where('media.path IN (:path)')
            ->setParameter(':path', $images, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchFirstColumn();

        return array_map('intval', $ids);
    }
}
