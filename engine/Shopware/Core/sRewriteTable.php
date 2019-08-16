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

use Shopware\Bundle\AttributeBundle\Repository\SearchCriteria;
use Shopware\Bundle\ContentTypeBundle\Structs\Criteria;
use Shopware\Bundle\ContentTypeBundle\Structs\Type;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ShopPageServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\MemoryLimit;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Slug\SlugInterface;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Shop\Shop;

/**
 * Deprecated Shopware Class that handles url rewrites
 */
class sRewriteTable implements \Enlight_Hook
{
    /**
     * @var \sSystem
     */
    public $sSYSTEM;

    /**
     * @var string|null
     */
    protected $rewriteArticleslastId;

    /**
     * @var Enlight_Template_Manager
     */
    protected $template;

    /**
     * @var Smarty_Data
     */
    protected $data;

    /**
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * Prepared update PDOStatement for the s_core_rewrite_urls table.
     *
     * @var PDOStatement
     */
    protected $preparedUpdate = null;

    /**
     * Prepared insert PDOStatement for the s_core_rewrite_urls table.
     *
     * @var PDOStatement
     */
    protected $preparedInsert = null;

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
     * Module manager for core class instances
     *
     * @var Shopware_Components_Modules
     */
    private $moduleManager;

    /**
     * @var SlugInterface
     */
    private $slug;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var ShopPageServiceInterface
     */
    private $shopPageService;

    /**
     * @var Shopware_Components_Translation
     */
    private $translationComponent;

    /**
     * @param Enlight_Components_Db_Adapter_Pdo_Mysql $db
     * @param Shopware_Components_Config              $config
     * @param ModelManager                            $modelManager
     * @param \sSystem                                $systemModule
     * @param Enlight_Template_Manager                $template
     * @param Shopware_Components_Modules             $moduleManager
     * @param SlugInterface                           $slug
     * @param ContextServiceInterface                 $contextService
     * @param ShopPageServiceInterface                $shopPageService
     * @param Shopware_Components_Translation         $translationComponent
     *
     * @throws \Exception
     */
    public function __construct(
        Enlight_Components_Db_Adapter_Pdo_Mysql $db = null,
        Shopware_Components_Config $config = null,
        ModelManager $modelManager = null,
        \sSystem $systemModule = null,
        Enlight_Template_Manager $template = null,
        Shopware_Components_Modules $moduleManager = null,
        SlugInterface $slug = null,
        ContextServiceInterface $contextService = null,
        ShopPageServiceInterface $shopPageService = null,
        Shopware_Components_Translation $translationComponent = null
    ) {
        $this->db = $db ?: Shopware()->Db();
        $this->config = $config ?: Shopware()->Config();
        $this->modelManager = $modelManager ?: Shopware()->Models();
        $this->sSYSTEM = $systemModule ?: Shopware()->System();
        $this->template = $template ?: Shopware()->Template();
        $this->moduleManager = $moduleManager ?: Shopware()->Modules();
        $this->slug = $slug ?: Shopware()->Container()->get('shopware.slug');
        $this->contextService = $contextService ?: Shopware()->Container()->get('shopware_storefront.context_service');
        $this->shopPageService = $shopPageService ?: Shopware()->Container()
            ->get('shopware_storefront.shop_page_service');
        $this->translationComponent = $translationComponent ?: Shopware()->Container()->get('translation');
    }

    /**
     * Getter function for retrieving last ID from sCreateRewriteTableArticles()
     *
     * @return string|null
     */
    public function getRewriteArticleslastId()
    {
        return $this->rewriteArticleslastId;
    }

    /**
     * Replace special chars with a URL compliant representation
     *
     * @param string $path
     *
     * @return string
     */
    public function sCleanupPath($path)
    {
        $parts = explode('/', $path);
        $parts = array_map(function ($path) {
            return $this->slug->slugify($path);
        }, $parts);

        $path = implode('/', $parts);
        $path = strtr($path, ['-.' => '.']);

        return $path;
    }

    /**
     * Sets up the environment for seo url calculation
     *
     * @throws \SmartyException
     */
    public function baseSetup()
    {
        MemoryLimit::setMinimumMemoryLimit(1024 * 1024 * 512);
        @set_time_limit(0);

        $keys = isset($this->template->registered_plugins['function']) ? array_keys($this->template->registered_plugins['function']) : [];
        if (!in_array('sCategoryPath', $keys)) {
            $this->template->registerPlugin(
                Smarty::PLUGIN_FUNCTION, 'sCategoryPath',
                [$this, 'sSmartyCategoryPath']
            );
        }

        if (!in_array('createSupplierPath', $keys)) {
            $this->template->registerPlugin(
                Smarty::PLUGIN_FUNCTION, 'createSupplierPath',
                [$this, 'createSupplierPath']
            );
        }

        $this->data = $this->template->createData();

        $this->data->assign('sConfig', $this->config);
        $this->data->assign('sRouter', $this);
        $this->data->assign('sCategoryStart', Shopware()->Shop()->getCategory()->getId());
    }

    /**
     * Main method for re-creating the rewrite table. Triggers all other (more specific) methods
     *
     * @param string $lastUpdate
     *
     * @throws \Exception
     * @throws \SmartyException
     * @throws \Enlight_Event_Exception
     * @throws \Zend_Db_Adapter_Exception
     *
     * @return string
     */
    public function sCreateRewriteTable($lastUpdate)
    {
        $this->baseSetup();

        $context = $this->contextService->createShopContext(Shopware()->Shop()->getId());

        $this->sCreateRewriteTableCleanup();
        $this->sCreateRewriteTableStatic();
        $this->sCreateRewriteTableCategories();
        $this->sCreateRewriteTableBlog(null, null, $context);
        $this->sCreateRewriteTableCampaigns();
        $lastUpdate = $this->sCreateRewriteTableArticles($lastUpdate);
        $this->sCreateRewriteTableContent(null, null, $context);
        $this->createManufacturerUrls($context);
        $this->createContentTypeUrls($context);

        return $lastUpdate;
    }

    /**
     * Cleanup the rewrite table from non-existing resources.
     *
     * @throws \Zend_Db_Adapter_Exception
     */
    public function sCreateRewriteTableCleanup()
    {
        // Delete CMS / campaigns
        $this->db->query("
            DELETE ru FROM s_core_rewrite_urls ru
            LEFT JOIN s_cms_static cs
              ON ru.org_path LIKE CONCAT('sViewport=custom&sCustom=', cs.id)
            LEFT JOIN s_cms_support ct
              ON ru.org_path LIKE CONCAT('sViewport=forms&sFid=', ct.id)
            WHERE (
                ru.org_path LIKE 'sViewport=custom&sCustom=%'
                OR ru.org_path LIKE 'sViewport=forms&sFid=%'
                OR ru.org_path LIKE 'sViewport=campaign&sCampaign=%'
                OR ru.org_path LIKE 'sViewport=content&sContent=%'
            )
            AND cs.id IS NULL
            AND ct.id IS NULL"
        );

        // delete non-existing blog categories from rewrite table
        $this->db->query("
            DELETE ru FROM s_core_rewrite_urls ru
            LEFT JOIN s_categories c
              ON c.id = REPLACE(ru.org_path, 'sViewport=blog&sCategory=', '')
              AND c.blog = 1
            WHERE ru.org_path LIKE 'sViewport=blog&sCategory=%'
            AND c.id IS NULL"
        );

        // delete non-existing categories
        $this->db->query("
            DELETE ru FROM s_core_rewrite_urls ru
            LEFT JOIN s_categories c
              ON c.id = REPLACE(ru.org_path, 'sViewport=cat&sCategory=', '')
              AND (c.external = '' OR c.external IS NULL)
              AND c.blog = 0
            WHERE ru.org_path LIKE 'sViewport=cat&sCategory=%'
            AND c.id IS NULL"
        );

        // delete non-existing products
        $this->db->query("
            DELETE ru FROM s_core_rewrite_urls ru
            LEFT JOIN s_articles a
              ON a.id = REPLACE(ru.org_path, 'sViewport=detail&sArticle=', '')
            WHERE ru.org_path LIKE 'sViewport=detail&sArticle=%'
            AND a.id IS NULL"
        );

        // delete all non-existing suppliers
        $this->db->query("
            DELETE ru FROM s_core_rewrite_urls ru
            LEFT JOIN s_articles_supplier s
              ON s.id = REPLACE(ru.org_path, 'sViewport=listing&sAction=manufacturer&sSupplier=', '')
            WHERE ru.org_path LIKE 'sViewport=listing&sAction=manufacturer&sSupplier=%'
            AND s.id IS NULL"
        );
    }

    /**
     * Create the static rewrite rules from config
     */
    public function sCreateRewriteTableStatic()
    {
        $seoStaticUrls = $this->config->get('sSEOSTATICURLS');
        if (empty($seoStaticUrls)) {
            return;
        }
        $static = [];
        $urls = $this->template->fetch('string:' . $seoStaticUrls, $this->data);

        if (!empty($urls)) {
            foreach (explode("\n", $urls) as $url) {
                list($key, $value) = explode(',', trim($url));
                if (empty($key) || empty($value)) {
                    continue;
                }
                $static[$key] = $value;
            }
        }

        foreach ($static as $org_path => $name) {
            $path = $this->sCleanupPath($name);
            $this->sInsertUrl($org_path, $path);
        }
    }

    /**
     * Create rewrite rules for categories
     * Default, deprecated method which updates rewrite URLs depending on the current shop
     *
     * @param int|null $offset
     * @param int|null $limit
     */
    public function sCreateRewriteTableCategories($offset = null, $limit = null)
    {
        $routerCategoryTemplate = $this->config->get('routerCategoryTemplate');
        if (empty($routerCategoryTemplate)) {
            return;
        }

        $parentId = Shopware()->Shop()->getCategory()->getId();
        $categories = $this->modelManager->getRepository(\Shopware\Models\Category\Category::class)
            ->getActiveChildrenList($parentId);

        if (isset($offset, $limit)) {
            $categories = array_slice($categories, $offset, $limit);
        }

        $template = 'string:' . $routerCategoryTemplate;
        $template = $this->template->createTemplate($template, $this->data);

        foreach ($categories as $category) {
            if (!empty($category['external'])) {
                continue;
            }

            $template->assign('sCategory', $category);
            $path = $template->fetch();
            $path = $this->sCleanupPath($path);

            if ($category['blog']) {
                $orgPath = 'sViewport=blog&sCategory=' . $category['id'];
            } else {
                $orgPath = 'sViewport=cat&sCategory=' . $category['id'];
            }

            $this->sInsertUrl($orgPath, $path);
        }
    }

    /**
     * Create rewrite rules for products
     *
     * @param string $lastUpdate
     * @param int    $limit
     * @param int    $offset
     *
     * @throws \Enlight_Event_Exception
     * @throws \Zend_Db_Adapter_Exception
     *
     * @return string
     */
    public function sCreateRewriteTableArticles($lastUpdate, $limit = 1000, $offset = 0)
    {
        $lastId = null;

        $routerProductTemplate = $this->config->get('sROUTERARTICLETEMPLATE');
        if (empty($routerProductTemplate)) {
            return $lastUpdate;
        }

        $this->db->query(
            'UPDATE `s_articles` SET `changetime`= NOW() WHERE `changetime`=?',
            ['0000-00-00 00:00:00']
        );

        $sql = $this->getSeoArticleQuery();
        $sql = $this->db->limit($sql, $limit, $offset);

        $fallbackShop = Shopware()->Shop()->getFallback();
        $shopFallbackId = ($fallbackShop instanceof Shop) ? $fallbackShop->getId() : null;

        $result = $this->db->fetchAll(
            $sql,
            [
                Shopware()->Shop()->get('parentID'),
                Shopware()->Shop()->getId(),
                $shopFallbackId,
                $lastUpdate,
            ]
        );

        $result = $this->mapArticleTranslationObjectData($result);

        $result = Shopware()->Events()->filter(
            'Shopware_Modules_RewriteTable_sCreateRewriteTableArticles_filterArticles',
            $result,
            [
                'shop' => Shopware()->Shop()->getId(),
            ]
        );

        foreach ($result as $row) {
            $this->data->assign('sArticle', $row);
            $path = $this->template->fetch('string:' . $routerProductTemplate, $this->data);
            $path = $this->sCleanupPath($path);

            $orgPath = 'sViewport=detail&sArticle=' . $row['id'];
            $this->sInsertUrl($orgPath, $path);
            $lastUpdate = $row['changed'];
            $lastId = $row['id'];
        }

        if (!empty($lastId)) {
            $this->db->query(
                'UPDATE s_articles
                SET changetime = DATE_ADD(changetime, INTERVAL 1 SECOND)
                WHERE changetime=?
                AND id > ?',
                [$lastUpdate, $lastId]
            );
        }

        $this->rewriteArticleslastId = $lastId;

        return $lastUpdate;
    }

    /**
     * Helper function which returns the sql query for the seo products.
     * Used in multiple locations
     *
     * @return string
     */
    public function getSeoArticleQuery()
    {
        return "
            SELECT a.*, d.ordernumber, d.suppliernumber, s.name AS supplier, a.datum AS date,
                d.releasedate, a.changetime AS changed, ct.objectdata, ctf.objectdata AS objectdataFallback, at.attr1, at.attr2,
                at.attr3, at.attr4, at.attr5, at.attr6, at.attr7, at.attr8, at.attr9,
                at.attr10,at.attr11, at.attr12, at.attr13, at.attr14, at.attr15, at.attr16,
                at.attr17, at.attr18, at.attr19, at.attr20
            FROM s_articles a

            INNER JOIN s_articles_categories_ro ac
                ON  ac.articleID = a.id
                AND ac.categoryID = ?
            INNER JOIN s_categories c
                ON  c.id = ac.categoryID
                AND c.active = 1

            JOIN s_articles_details d
                ON d.id = a.main_detail_id

            LEFT JOIN s_articles_attributes at
                ON at.articledetailsID=d.id

            LEFT JOIN s_core_translations ct
                ON ct.objectkey=a.id
                AND ct.objectlanguage=?
                AND ct.objecttype='article'

            LEFT JOIN s_core_translations ctf
                ON ctf.objectkey=a.id
                AND ctf.objectlanguage=?
                AND ctf.objecttype='article'

            LEFT JOIN s_articles_supplier s
                ON s.id=a.supplierID

            WHERE a.active=1
            AND a.changetime > ?
            GROUP BY a.id
            ORDER BY a.changetime, a.id
          ";
    }

    /**
     * Create rewrite rules for blog articles
     * Used in multiple locations
     *
     * @param int|null $offset
     * @param int|null $limit
     */
    public function sCreateRewriteTableBlog($offset = null, $limit = null, ShopContextInterface $context = null)
    {
        $query = $this->modelManager->getRepository(\Shopware\Models\Category\Category::class)
            ->getBlogCategoriesByParentQuery(Shopware()->Shop()->get('parentID'));
        $blogCategories = $query->getArrayResult();

        //get all blog category ids
        $blogCategoryIds = [];
        foreach ($blogCategories as $blogCategory) {
            $blogCategoryIds[] = $blogCategory['id'];
        }

        if ($context === null) {
            $context = $this->contextService->getShopContext();
        }

        /** @var \Shopware\Models\Blog\Repository $repository */
        $blogArticlesQuery = $this->modelManager->getRepository(\Shopware\Models\Blog\Blog::class)
            ->getListQuery($blogCategoryIds, $offset, $limit);
        $blogArticlesQuery->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $blogArticles = $blogArticlesQuery->getArrayResult();

        $routerBlogTemplate = $this->config->get('routerBlogTemplate');
        foreach ($blogArticles as $blogArticle) {
            $blogTranslation = $this->translationComponent->readWithFallback(
                $context->getShop()->getId(),
                $context->getShop()->getFallbackId(),
                'blog',
                $blogArticle['id'],
                false
            );

            if (!empty($blogTranslation)) {
                $blogArticle = array_merge($blogArticle, $blogTranslation);
            }

            $this->data->assign('blogArticle', $blogArticle);
            $path = $this->template->fetch('string:' . $routerBlogTemplate, $this->data);
            $path = $this->sCleanupPath($path);

            $org_path = 'sViewport=blog&sAction=detail&sCategory=' . $blogArticle['categoryId'] . '&blogArticle=' . $blogArticle['id'];
            $this->sInsertUrl($org_path, $path);
        }
    }

    /**
     * @param int|null $offset
     * @param int|null $limit
     *
     * @throws Exception
     * @throws SmartyException
     */
    public function createManufacturerUrls(ShopContextInterface $context, $offset = null, $limit = null)
    {
        $seoSupplier = $this->config->get('sSEOSUPPLIER');
        if (empty($seoSupplier)) {
            return;
        }

        $ids = $this->getManufacturerIds($offset, $limit);
        $manufacturers = Shopware()->Container()->get('shopware_storefront.manufacturer_service')
            ->getList($ids, $context);

        $seoSupplierRouteTemplate = $this->config->get('seoSupplierRouteTemplate');
        foreach ($manufacturers as $manufacturer) {
            $manufacturer = json_decode(json_encode($manufacturer), true);
            $this->data->assign('sSupplier', $manufacturer);
            $path = $this->template->fetch('string:' . $seoSupplierRouteTemplate, $this->data);
            $path = $this->sCleanupPath($path);

            $org_path = 'sViewport=listing&sAction=manufacturer&sSupplier=' . (int) $manufacturer['id'];
            $this->sInsertUrl($org_path, $path);
        }
    }

    /**
     * Create emotion rewrite rules
     *
     * @param int|null $offset
     * @param int|null $limit
     *
     * @throws \Exception
     */
    public function sCreateRewriteTableCampaigns($offset = null, $limit = null)
    {
        /** @var \Shopware\Models\Emotion\Repository $repo */
        $repo = $this->modelManager->getRepository(\Shopware\Models\Emotion\Emotion::class);
        $queryBuilder = $repo->getListQueryBuilder();

        $languageId = Shopware()->Shop()->getId();
        $fallbackId = null;

        $fallbackShop = Shopware()->Shop()->getFallback();

        if (!empty($fallbackShop)) {
            $fallbackId = $fallbackShop->getId();
        }

        $queryBuilder->join('emotions.shops', 'shop', 'WITH', 'shop.id = :shopId')
            ->setParameter('shopId', $languageId);

        $queryBuilder
            ->andWhere('emotions.isLandingPage = 1')
            ->andWhere('emotions.parentId IS NULL')
            ->andWhere('emotions.active = 1');

        if ($limit !== null && $offset !== null) {
            $queryBuilder->setFirstResult($offset)->setMaxResults($limit);
        }

        $campaigns = $queryBuilder->getQuery()->getArrayResult();
        $routerCampaignTemplate = $this->config->get('routerCampaignTemplate');

        foreach ($campaigns as $campaign) {
            $this->sCreateRewriteTableForSingleCampaign(
                $this->translationComponent,
                $languageId,
                $fallbackId,
                $campaign,
                $routerCampaignTemplate
            );
        }
    }

    /**
     * @param int    $shopId
     * @param int    $fallbackShopId
     * @param string $routerCampaignTemplate
     *
     * @throws Exception
     * @throws SmartyException
     */
    public function sCreateRewriteTableForSingleCampaign(
        Shopware_Components_Translation $translator,
        $shopId,
        $fallbackShopId,
        array $campaign,
        $routerCampaignTemplate
    ) {
        $translation = $translator->readWithFallback($shopId, $fallbackShopId, 'emotion', $campaign['id']);

        $campaign = array_merge($campaign, $translation);

        $this->data->assign('campaign', $campaign);

        $path = $this->template->fetch('string:' . $routerCampaignTemplate, $this->data);
        $path = $this->sCleanupPath($path);

        $org_path = 'sViewport=campaign&emotionId=' . $campaign['id'];
        $this->sInsertUrl($org_path, $path);
    }

    /**
     * Create CMS rewrite rules, used in multiple locations
     *
     * @param int $offset
     * @param int $limit
     *
     * @throws \Exception
     */
    public function sCreateRewriteTableContent($offset = null, $limit = null, ShopContextInterface $context = null)
    {
        //form urls
        $this->insertFormUrls($offset, $limit, $context);

        //static pages urls
        $this->insertStaticPageUrls($offset, $limit, $context);
    }

    public function createSingleContentTypeUrl(Type $type): void
    {
        if (!$type->isShowInFrontend()) {
            return;
        }

        $translator = Shopware()->Container()->get(\Shopware\Bundle\ContentTypeBundle\Services\FrontendTypeTranslatorInterface::class);
        $type = $translator->translate($type);

        // insert controller, itself
        $path = $type->getName() . '/';
        $path = $this->sCleanupPath($path);
        $this->sInsertUrl('sViewport=' . $type->getControllerName() . '&sAction=index', $path);
    }

    public function createContentTypeUrls(ShopContextInterface $context): void
    {
        $translator = Shopware()->Container()->get(\Shopware\Bundle\ContentTypeBundle\Services\FrontendTypeTranslatorInterface::class);

        /** @var Type $type */
        foreach (Shopware()->Container()->get(\Shopware\Bundle\ContentTypeBundle\Services\TypeProvider::class)->getTypes() as $type) {
            if (!$type->isShowInFrontend()) {
                continue;
            }

            $type = $translator->translate($type);

            // insert controller, itself
            $path = $type->getName() . '/';
            $path = $this->sCleanupPath($path);
            $this->sInsertUrl('sViewport=' . $type->getControllerName() . '&sAction=index', $path);

            $typeArray = json_decode(json_encode($type), true);

            /** @var \Shopware\Bundle\ContentTypeBundle\Services\RepositoryInterface $repository */
            $repository = Shopware()->Container()->get('shopware.bundle.content_type.' . $type->getInternalName());

            $criteria = new Criteria();
            $criteria->loadAssociations = true;
            $criteria->loadTranslations = true;
            $criteria->limit = null;

            foreach ($repository->findAll($criteria)->items as $item) {
                $path = $this->template->fetch('string:' . $type->getSeoUrlTemplate(), ['type' => $typeArray, 'item' => $item, 'context' => $context]);
                $path = $this->sCleanupPath($path);

                $org_path = sprintf('sViewport=%s&sAction=detail&id=%d', $type->getControllerName(), $item['id']);
                $this->sInsertUrl($org_path, $path);
            }
        }
    }

    /**
     * Updates / create a single rewrite URL
     *
     * @param string $org_path
     * @param string $path
     *
     * @return false|null False on empty args, null otherwise
     */
    public function sInsertUrl($org_path, $path)
    {
        $path = trim($path);
        $path = ltrim($path, '/');
        if (empty($path) || empty($org_path)) {
            return false;
        }

        $shopId = Shopware()->Shop()->getId();

        $update = $this->getPreparedUpdate();
        $update->execute([
            $org_path,
            $path,
            $shopId,
        ]);

        $insert = $this->getPreparedInsert();
        $insert->execute([
            $org_path,
            $path,
            $shopId,
        ]);

        return null;
    }

    /**
     * Returns the supplier name
     * Used internally as a Smarty extension
     *
     * @param array $params
     *
     * @return string|null
     */
    public function createSupplierPath($params)
    {
        $parts = [];
        if (!empty($params['supplierID'])) {
            $parts[] = $this->modelManager->getRepository(\Shopware\Models\Article\Supplier::class)
                ->find($params['supplierID'])->getName();
        }
        if (empty($params['separator'])) {
            $params['separator'] = '/';
        }
        foreach ($parts as &$part) {
            $part = str_replace($params['separator'], '', $part);
        }

        return implode($params['separator'], $parts);
    }

    /**
     * Returns the category path based on the given params
     * Used internally as a Smarty extension
     *
     * @param array $params
     *
     * @return string|null Category path
     */
    public function sSmartyCategoryPath($params)
    {
        $parts = null;
        if (!empty($params['articleID'])) {
            $parts = $this->sCategoryPathByProductId(
                $params['articleID'],
                isset($params['categoryID']) ? $params['categoryID'] : null
            );
        } elseif (!empty($params['categoryID'])) {
            $parts = $this->sCategoryPath($params['categoryID']);
        }
        if (empty($params['separator'])) {
            $params['separator'] = '/';
        }
        if (!empty($parts)) {
            foreach ($parts as &$part) {
                $part = str_replace($params['separator'], '', $part);
            }
            $parts = implode($params['separator'], $parts);
        }

        return $parts;
    }

    /**
     * Given a category id, returns the category path
     *
     * @param int $categoryId Id of the category
     *
     * @return array Array containing the path parts
     */
    public function sCategoryPath($categoryId)
    {
        $parts = $this->modelManager->getRepository(\Shopware\Models\Category\Category::class)
            ->getPathById($categoryId);
        $level = Shopware()->Shop()->getCategory()->getLevel() ?: 1;
        $parts = array_slice($parts, $level);

        return $parts;
    }

    /**
     * @deprecated in 5.6, will be removed in 5.7 without replacement
     *
     * @return Smarty_Data
     */
    public function getData()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return $this->data;
    }

    /**
     * Maps the translation of the objectdata from the s_core_translations in the product array
     *
     * @param array $articles
     *
     * @return array
     */
    public function mapArticleTranslationObjectData($articles)
    {
        foreach ($articles as &$product) {
            if (empty($product['objectdata']) && empty($product['objectdataFallback'])) {
                unset($product['objectdata'], $product['objectdataFallback']);
                continue;
            }

            $objectData = @unserialize($product['objectdata'], ['allowed_classes' => false]);
            $objectDataFallback = @unserialize($product['objectdataFallback'], ['allowed_classes' => false]);

            if (empty($objectData)) {
                $objectData = [];
            }

            if (empty($objectDataFallback)) {
                $objectDataFallback = [];
            }

            if (empty($objectData) && empty($objectDataFallback)) {
                continue;
            }

            unset($product['objectdata'], $product['objectdataFallback']);

            $product = $this->mapProductObjectFields($product, $objectData, $objectDataFallback, [
                'name' => 'txtArtikel',
                'description_long' => 'txtlangbeschreibung',
                'description' => 'txtshortdescription',
                'shippingtime' => 'txtshippingtime',
                'keywords' => 'txtkeywords',
                'metaTitle' => 'metaTitle',
            ]);

            $product = $this->mapProductObjectAttributeFields($product, $objectDataFallback);
            $product = $this->mapProductObjectAttributeFields($product, $objectData);
        }

        return $articles;
    }

    /**
     * Getter function of the prepared insert PDOStatement
     *
     * @return PDOStatement
     */
    protected function getPreparedInsert()
    {
        if ($this->preparedInsert === null) {
            $this->preparedInsert = $this->db->prepare('
                REPLACE INTO s_core_rewrite_urls (org_path, path, main, subshopID)
                VALUES (?, ?, 1, ?)
            ');
        }

        return $this->preparedInsert;
    }

    /**
     * Getter function of the prepared update PDOStatement
     *
     * @return PDOStatement|null
     */
    protected function getPreparedUpdate()
    {
        if ($this->preparedUpdate === null) {
            $this->preparedUpdate = $this->db->prepare(
                'UPDATE s_core_rewrite_urls
                SET main = 0
                WHERE org_path = ?
                AND path != ?
                AND subshopID = ?
            ');
        }

        return $this->preparedUpdate;
    }

    /**
     * Returns the category path to which the
     * product belongs, inside the category subtree.
     * Used internally in sSmartyCategoryPath
     *
     * @param int $productId Id of the product to look for
     * @param int $parentId  Category subtree root id. If null, the shop category is used.
     *
     * @return array|null Category path, or null if no category found
     */
    private function sCategoryPathByProductId($productId, $parentId = null)
    {
        $categoryId = $this->moduleManager->Categories()->sGetCategoryIdByArticleId(
            $productId,
            $parentId
        );

        return empty($categoryId) ? null : $this->sCategoryPath($categoryId);
    }

    /**
     * Generates and inserts the form seo urls
     *
     * @param int                  $offset
     * @param int                  $limit
     * @param ShopContextInterface $context
     *
     * @throws \Exception
     */
    private function insertFormUrls($offset, $limit, ShopContextInterface $context = null)
    {
        $context = $this->createFallbackContext($context);
        $shopId = $context->getShop()->getId();

        $formListData = $this->modelManager->getRepository(\Shopware\Models\Form\Form::class)
            ->getListQueryBuilder([], [])
            ->andWhere('(form.shopIds LIKE :shopId OR form.shopIds IS NULL)')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('shopId', '%|' . $shopId . '|%')
            ->getQuery()->getArrayResult();

        foreach ($formListData as $form) {
            $formTranslation = $this->translationComponent->readWithFallback(
                $context->getShop()->getId(),
                $context->getShop()->getFallbackId(),
                'forms',
                $form['id'],
                false
            );

            if (!empty($formTranslation)) {
                $form = $formTranslation + $form;
            }

            $this->data->assign('form', $form);

            $org_path = 'sViewport=forms&sFid=' . $form['id'];
            $path = $this->template->fetch('string:' . $this->config->get('seoFormRouteTemplate'), $this->data);
            $path = $this->sCleanupPath($path);

            // Find out if some other form has a URL specific for this subshop
            $hasSpecificSubShopPath = $this->hasSpecificShopPath($org_path, $path, $shopId);

            // If our current form is specific for this subshop OR if we are for all shops and no other form is specific, write URL
            if (!empty($form['shopIds'])
                || (empty($form['shopIds']) && !$hasSpecificSubShopPath)) {
                $this->sInsertUrl($org_path, $path);
            }
        }
    }

    /**
     * Generates and inserts static page urls
     *
     * @param int                  $offset
     * @param int                  $limit
     * @param ShopContextInterface $context
     *
     * @throws Exception
     * @throws SmartyException
     * @throws \Zend_Db_Statement_Exception
     */
    private function insertStaticPageUrls($offset, $limit, ShopContextInterface $context = null)
    {
        $context = $this->createFallbackContext($context);
        $shopId = $context->getShop()->getId();

        $sitesData = $this->modelManager->getRepository(\Shopware\Models\Site\Site::class)
            ->getSitesWithoutLinkQuery($shopId, $offset, $limit)
            ->getArrayResult();

        $pages = $this->shopPageService->getList(array_column($sitesData, 'id'), $context);

        foreach ($pages as $site) {
            $site = json_decode(json_encode($site), true);

            $org_path = 'sViewport=custom&sCustom=' . $site['id'];
            $this->data->assign('site', $site);
            $path = $this->template->fetch('string:' . $this->config->get('seoCustomSiteRouteTemplate'), $this->data);
            $path = $this->sCleanupPath($path);

            // Find out if some other site has a URL specific for this subshop
            $hasSpecificSubShopPath = $this->hasSpecificShopPath($org_path, $path, $shopId);

            // If our current site is specific for this subshop OR if we are for all shops and no other site is specific, write URL
            if (!empty($form['shopIds'])
                || (empty($form['shopIds']) && !$hasSpecificSubShopPath)) {
                $this->sInsertUrl($org_path, $path);
            }
        }
    }

    /**
     * Map product core translation including fallback fields for given product
     *
     * @param array $fieldMappings array(productFieldName => objectDataFieldName)
     *
     * @return array
     */
    private function mapProductObjectFields(
        array $product,
        array $objectData,
        array $objectDataFallback,
        array $fieldMappings
    ) {
        foreach ($fieldMappings as $productFieldName => $objectDataFieldName) {
            if (!empty($objectData[$objectDataFieldName])) {
                $product[$productFieldName] = $objectData[$objectDataFieldName];
                continue;
            }

            if (!empty($objectDataFallback[$objectDataFieldName])) {
                $product[$productFieldName] = $objectDataFallback[$objectDataFieldName];
            }
        }

        return $product;
    }

    /**
     * Map product attribute translation including fallback fields for given product
     *
     * @param array $product
     * @param array $translations
     *
     * @return array
     */
    private function mapProductObjectAttributeFields($product, $translations)
    {
        foreach ($translations as $key => $value) {
            if (strpos($key, '__attribute_') === false || empty($value)) {
                continue;
            }

            $productKey = str_replace('__attribute_', '', $key);

            $product[$productKey] = $value;
        }

        return $product;
    }

    /**
     * @param int|null $offset
     * @param int|null $limit
     *
     * @throws \Exception
     *
     * @return array
     */
    private function getManufacturerIds($offset = null, $limit = null)
    {
        $criteria = new SearchCriteria(Supplier::class);
        $registry = Shopware()->Container()->get('shopware_attribute.repository_registry');
        $repo = $registry->getRepository($criteria);

        if ($offset !== null) {
            $criteria->offset = $offset;
        }

        $criteria->limit = $limit;
        $result = $repo->search($criteria);
        $suppliers = $result->getData();

        return array_column($suppliers, 'id');
    }

    /**
     * @throws \Exception
     *
     * @return ShopContextInterface
     */
    private function createFallbackContext(ShopContextInterface $context = null)
    {
        if ($context) {
            return $context;
        }

        /* @var Shop $shop */
        if (Shopware()->Container()->has('shop')) {
            $shop = Shopware()->Container()->get('shop');

            return $this->contextService->createShopContext($shop->getId());
        }

        return $this->contextService->createShopContext(1);
    }

    /**
     * Determines if there are any rewrite rules for other elements and this shop. This can happen if one e.g. form or site
     * is for all shops, another form with the same name for one specific subshop.
     *
     * @param string $org_path
     * @param string $path
     * @param int    $shopId
     *
     * @throws \Zend_Db_Statement_Exception
     *
     * @return bool
     */
    private function hasSpecificShopPath($org_path, $path, $shopId)
    {
        $statement = $this->db
            ->executeQuery(
                'SELECT `org_path`
                FROM `s_core_rewrite_urls`
                WHERE `path`=?
                  AND `main`=1
                  AND `subshopId`=?
                  AND `org_path`!=?',
                [$path, $shopId, $org_path]
            );

        if ($statement->rowCount() === 0) {
            return false;
        }

        $currentOrgPath = $statement->fetchColumn();
        $matches = [];

        // Check if the current url points to a form
        if (preg_match('/^sViewport=forms&sFid=(\d+)$/', $currentOrgPath, $matches) === 1) {
            return $this->checkSpecificShopForm($matches);
        }

        // Check if the current url points to a site
        if (preg_match('/^sViewport=custom&sCustom=(\d+)$/', $currentOrgPath, $matches) === 1) {
            return $this->checkSpecificShopSite($matches);
        }

        return false;
    }

    /**
     * @return bool
     */
    private function checkSpecificShopForm(array $matches)
    {
        // First match is the whole org_path
        $formId = (int) array_pop($matches);

        $formRepository = $this->modelManager->getRepository(\Shopware\Models\Form\Form::class);
        $form = $formRepository->find($formId);

        if (!$form) {
            return false;
        }

        // Finally, check if the current form is specific for this subshop or not
        return !empty($form->getShopIds());
    }

    /**
     * @return bool
     */
    private function checkSpecificShopSite(array $matches)
    {
        // First match is the whole org_path
        $siteId = (int) array_pop($matches);

        $siteRepository = $this->modelManager->getRepository(\Shopware\Models\Site\Site::class);
        $site = $siteRepository->find($siteId);

        if (!$site) {
            return false;
        }

        // Finally, check if the current site is specific for this subshop or not
        return !empty($site->getShopIds());
    }
}
