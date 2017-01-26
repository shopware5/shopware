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
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ShopPageServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\MemoryLimit;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Slug\SlugInterface;
use Shopware\Models\Article\Supplier;

/**
 * Deprecated Shopware Class that handles url rewrites
 *
 * @category  Shopware
 * @package   Shopware\Core
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class sRewriteTable
{
    /**
     * @var string|null
     */
    protected $rewriteArticleslastId;

    /**
     * @var sSystem
     */
    public $sSYSTEM;

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
     * Prepared update PDOStatement for the s_core_rewrite_urls table.
     *
     * @var PDOStatement
     */
    protected $preparedUpdate = null;

    /**
     * Prepared insert PDOStatement for the s_core_rewrite_urls table.
     * @var PDOStatement
     */
    protected $preparedInsert = null;

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
     * @param Enlight_Components_Db_Adapter_Pdo_Mysql $db
     * @param Shopware_Components_Config $config
     * @param ModelManager $modelManager
     * @param sSystem $systemModule
     * @param Enlight_Template_Manager $template
     * @param Shopware_Components_Modules $moduleManager
     * @param SlugInterface $slug
     * @param ContextServiceInterface $contextService
     * @param ShopPageServiceInterface $shopPageService
     */
    public function __construct(
        Enlight_Components_Db_Adapter_Pdo_Mysql $db = null,
        Shopware_Components_Config $config = null,
        ModelManager $modelManager = null,
        sSystem $systemModule = null,
        Enlight_Template_Manager $template = null,
        Shopware_Components_Modules $moduleManager = null,
        SlugInterface $slug = null,
        ContextServiceInterface $contextService = null,
        ShopPageServiceInterface $shopPageService = null
    ) {
        $this->db = $db ?: Shopware()->Db();
        $this->config = $config ?: Shopware()->Config();
        $this->modelManager = $modelManager ?: Shopware()->Models();
        $this->sSYSTEM = $systemModule ?: Shopware()->System();
        $this->template = $template ?: Shopware()->Template();
        $this->moduleManager = $moduleManager ?: Shopware()->Modules();
        $this->slug = $slug ?: Shopware()->Container()->get('shopware.slug');
        $this->contextService = $contextService ?: Shopware()->Container()->get('shopware_storefront.context_service');
        $this->shopPageService = $shopPageService ?: Shopware()->Container()->get('shopware_storefront.shop_page_service');
    }

    /**
     * Getter function for retriving last ID from sCreateRewriteTableArticles()
     * @return string|null
     */
    public function getRewriteArticleslastId()
    {
        return $this->rewriteArticleslastId;
    }

    /**
     * Getter function of the prepared insert PDOStatement
     *
     * @return null|PDOStatement
     */
    protected function getPreparedInsert()
    {
        if ($this->preparedInsert === null) {
            $this->preparedInsert = $this->db->prepare('
                INSERT IGNORE INTO s_core_rewrite_urls (org_path, path, main, subshopID)
                VALUES (?, ?, 1, ?)
                ON DUPLICATE KEY UPDATE main=1
            ');
        }
        return $this->preparedInsert;
    }


    /**
     * Getter function of the prepared update PDOStatement
     *
     * @return null|PDOStatement
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
     * Replace special chars with a URL compliant representation
     *
     * @param string $path
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
     */
    public function baseSetup()
    {
        MemoryLimit::setMinimumMemoryLimit(1024*1024*512);
        @set_time_limit(0);

        $keys = isset($this->template->registered_plugins['function']) ? array_keys($this->template->registered_plugins['function']) : [];
        if (!(in_array('sCategoryPath', $keys))) {
            $this->template->registerPlugin(
                Smarty::PLUGIN_FUNCTION, 'sCategoryPath',
                array($this, 'sSmartyCategoryPath')
            );
        }

        if (!(in_array('createSupplierPath', $keys))) {
            $this->template->registerPlugin(
                Smarty::PLUGIN_FUNCTION, 'createSupplierPath',
                array($this, 'createSupplierPath')
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
     * @return string
     */
    public function sCreateRewriteTable($lastUpdate)
    {
        $this->baseSetup();

        $context = $this->contextService->createShopContext(Shopware()->Shop()->getId());

        $this->sCreateRewriteTableCleanup();
        $this->sCreateRewriteTableStatic();
        $this->sCreateRewriteTableCategories();
        $this->sCreateRewriteTableBlog();
        $this->sCreateRewriteTableCampaigns();
        $lastUpdate = $this->sCreateRewriteTableArticles($lastUpdate);
        $this->sCreateRewriteTableContent(null, null, $context);
        $this->sCreateRewriteTableSuppliers(null, null, $context);

        return $lastUpdate;
    }

    /**
     * Cleanup the rewrite table from non-existing resources.
     */
    public function sCreateRewriteTableCleanup()
    {
        // Delete CMS / campaigns
        $this->db->query("
            DELETE ru FROM s_core_rewrite_urls ru
            LEFT JOIN s_cms_static cs
              ON ru.org_path LIKE CONCAT('sViewport=custom&sCustom=', cs.id)
            LEFT JOIN s_cms_support ct
              ON ru.org_path LIKE CONCAT('sViewport=ticket&sFid=', ct.id)
            WHERE (
                ru.org_path LIKE 'sViewport=custom&sCustom=%'
                OR ru.org_path LIKE 'sViewport=ticket&sFid=%'
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

        // delete non-existing articles
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
        $static = array();
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
     * @param null $offset
     * @param null $limit
     */
    public function sCreateRewriteTableCategories($offset = null, $limit = null)
    {
        $routerCategoryTemplate = $this->config->get('routerCategoryTemplate');
        if (empty($routerCategoryTemplate)) {
            return;
        }

        $parentId = Shopware()->Shop()->getCategory()->getId();
        $categories = $this->modelManager->getRepository('Shopware\Models\Category\Category')
            ->getActiveChildrenList($parentId);

        if (isset($offset) && isset($limit)) {
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
     * Create rewrite rules for articles
     *
     * @param string $lastUpdate
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function sCreateRewriteTableArticles($lastUpdate, $limit = 1000, $offset = 0)
    {
        $lastId = null;

        $routerArticleTemplate = $this->config->get('sROUTERARTICLETEMPLATE');
        if (empty($routerArticleTemplate)) {
            return $lastUpdate;
        }

        $this->db->query(
            'UPDATE `s_articles` SET `changetime`= NOW() WHERE `changetime`=?',
            array('0000-00-00 00:00:00')
        );

        $sql = $this->getSeoArticleQuery();
        $sql = $this->db->limit($sql, $limit, $offset);

        $shopFallbackId = (Shopware()->Shop()->getFallback() instanceof \Shopware\Models\Shop\Shop) ? Shopware()->Shop()->getFallback()->getId() : null;

        $result = $this->db->fetchAll(
            $sql,
            array(
                Shopware()->Shop()->get('parentID'),
                Shopware()->Shop()->getId(),
                $shopFallbackId,
                $lastUpdate
            )
        );

        $result = $this->mapArticleTranslationObjectData($result);

        $result = Shopware()->Events()->filter(
            'Shopware_Modules_RewriteTable_sCreateRewriteTableArticles_filterArticles',
            $result,
            array(
                'shop' => Shopware()->Shop()->getId()
            )
        );

        foreach ($result as $row) {
            $this->data->assign('sArticle', $row);
            $path = $this->template->fetch('string:' . $routerArticleTemplate, $this->data);
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
                array($lastUpdate, $lastId)
            );
        }

        $this->rewriteArticleslastId = $lastId;

        return $lastUpdate;
    }

    /**
     * Helper function which returns the sql query for the seo articles.
     * Used in multiple locations
     *
     * @return string
     */
    public function getSeoArticleQuery()
    {
        return "
            SELECT a.*, d.ordernumber, d.suppliernumber, s.name as supplier, datum as date,
                d.releasedate, changetime as changed, metaTitle, ct.objectdata, ctf.objectdata as objectdataFallback, at.attr1, at.attr2,
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
     * @param null $offset
     * @param null $limit
     */
    public function sCreateRewriteTableBlog($offset = null, $limit = null)
    {
        $query = $this->modelManager->getRepository('Shopware\Models\Category\Category')
            ->getBlogCategoriesByParentQuery(Shopware()->Shop()->get('parentID'));
        $blogCategories = $query->getArrayResult();

        //get all blog category ids
        $blogCategoryIds = array();
        foreach ($blogCategories as $blogCategory) {
            $blogCategoryIds[] = $blogCategory["id"];
        }

        /** @var $repository \Shopware\Models\Blog\Repository */
        $blogArticlesQuery = $this->modelManager->getRepository('Shopware\Models\Blog\Blog')
            ->getListQuery($blogCategoryIds, $offset, $limit);
        $blogArticlesQuery->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $blogArticles = $blogArticlesQuery->getArrayResult();

        $routerBlogTemplate = $this->config->get('routerBlogTemplate');
        foreach ($blogArticles as $blogArticle) {
            $this->data->assign('blogArticle', $blogArticle);
            $path = $this->template->fetch('string:' . $routerBlogTemplate, $this->data);
            $path = $this->sCleanupPath($path);

            $org_path = 'sViewport=blog&sAction=detail&sCategory=' . $blogArticle['categoryId'] . '&blogArticle=' . $blogArticle['id'];
            $this->sInsertUrl($org_path, $path);
        }
    }

    /**
     * @deprecated since 5.2 will be removed in 5.3, use \sRewriteTable::createManufacturerUrls
     * @param null $offset
     * @param null $limit
     * @param ShopContextInterface $context
     */
    public function sCreateRewriteTableSuppliers($offset = null, $limit = null, ShopContextInterface $context = null)
    {
        $context = $this->createFallbackContext($context);
        $this->createManufacturerUrls($context, $offset, $limit);
    }

    /**
     * @param ShopContextInterface $context
     * @param null $offset
     * @param null $limit
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
        $manufacturers = Shopware()->Container()->get('shopware_storefront.manufacturer_service')->getList($ids, $context);

        $seoSupplierRouteTemplate = $this->config->get('seoSupplierRouteTemplate');
        foreach ($manufacturers as $manufacturer) {
            $manufacturer = json_decode(json_encode($manufacturer), true);
            $this->data->assign('sSupplier', $manufacturer);
            $path = $this->template->fetch('string:' . $seoSupplierRouteTemplate, $this->data);
            $path = $this->sCleanupPath($path);

            $org_path = 'sViewport=listing&sAction=manufacturer&sSupplier=' . (int)$manufacturer['id'];
            $this->sInsertUrl($org_path, $path);
        }
    }

    /**
     * Create emotion rewrite rules
     *
     * @param null $offset
     * @param null $limit
     */
    public function sCreateRewriteTableCampaigns($offset = null, $limit = null)
    {
        /**@var $repo \Shopware\Models\Emotion\Repository */
        $repo = $this->modelManager->getRepository('Shopware\Models\Emotion\Emotion');
        $queryBuilder = $repo->getListQueryBuilder();

        $languageId = Shopware()->Shop()->getId();
        $fallbackId = null;

        $fallbackShop = Shopware()->Shop()->getFallback();

        if (!empty($fallbackShop)) {
            $fallbackId = $fallbackShop->getId();
        }

        $translator = new Shopware_Components_Translation();

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
            $this->sCreateRewriteTableForSingleCampaign($translator, $languageId, $fallbackId, $campaign, $routerCampaignTemplate);
        }
    }

    /**
     * @param Shopware_Components_Translation $translator
     * @param int $languageId
     * @param int $fallbackId
     * @param array $campaign
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
     * Create CMS rewrite rules
     * Used in multiple locations
     *
     * @param int $offset
     * @param int $limit
     * @param ShopContextInterface $context
     */
    public function sCreateRewriteTableContent($offset = null, $limit = null, ShopContextInterface $context = null)
    {
        //form urls
        $this->insertFormUrls($offset, $limit);

        //static pages urls
        $this->insertStaticPageUrls($offset, $limit, $context);
    }

    /**
     * Updates / create a single rewrite URL
     *
     * @param $org_path
     * @param $path
     * @return false|null False on empty args, null otherwise
     */
    public function sInsertUrl($org_path, $path)
    {
        $path = trim($path);
        $path = ltrim($path, '/');
        if (empty($path) || empty($org_path)) {
            return false;
        }

        $update = $this->getPreparedUpdate();
        $update->execute(array(
            $org_path,
            $path,
            Shopware()->Shop()->getId()
        ));

        $insert = $this->getPreparedInsert();
        $insert->execute(array(
            $org_path,
            $path,
            Shopware()->Shop()->getId()
        ));
    }

    /**
     * Returns the supplier name
     * Used internally as a Smarty extension
     *
     * @param array $params
     * @return string|null
     */
    public function createSupplierPath($params)
    {
        $parts = array();
        if (!empty($params['supplierID'])) {
            $parts[] = $this->modelManager->getRepository('Shopware\Models\Article\Supplier')
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
     * @return null|string Category path
     */
    public function sSmartyCategoryPath($params)
    {
        $parts = null;
        if (!empty($params['articleID'])) {
            $parts = $this->sCategoryPathByArticleId(
                $params['articleID'],
                isset($params['categoryID']) ? $params["categoryID"] : null
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
     * @return array Array containing the path parts
     */
    public function sCategoryPath($categoryId)
    {
        $parts = $this->modelManager->getRepository('Shopware\Models\Category\Category')
            ->getPathById($categoryId, 'name');
        $level = Shopware()->Shop()->getCategory()->getLevel() ? : 1;
        $parts = array_slice($parts, $level);

        return $parts;
    }

    /**
     * Returns the category path to which the
     * article belongs, inside the category subtree.
     * Used internally in sSmartyCategoryPath
     *
     * @param int $articleId Id of the article to look for
     * @param int $parentId Category subtree root id. If null, the shop category is used.
     * @return null|array Category path, or null if no category found
     */
    private function sCategoryPathByArticleId($articleId, $parentId = null)
    {
        $categoryId = $this->moduleManager->Categories()->sGetCategoryIdByArticleId(
            $articleId,
            $parentId
        );

        return empty($categoryId) ? null : $this->sCategoryPath($categoryId);
    }

    /**
     * @return Smarty_Data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Generates and inserts the form seo urls
     *
     * @param $offset
     * @param $limit
     */
    private function insertFormUrls($offset, $limit)
    {
        $formListData = $this->modelManager->getRepository('Shopware\Models\Form\Form')
            ->getListQuery(array(), array(), $offset, $limit)->getArrayResult();

        foreach ($formListData as $form) {
            $org_path = 'sViewport=ticket&sFid=' . $form['id'];
            $this->data->assign('form', $form);
            $path = $this->template->fetch('string:' . $this->config->get('seoFormRouteTemplate'), $this->data);
            $path = $this->sCleanupPath($path);
            $this->sInsertUrl($org_path, $path);
        }
    }

    /**
     * Generates and inserts static page urls
     *
     * @param $offset
     * @param $limit
     * @param ShopContextInterface $context
     * @throws Exception
     * @throws SmartyException
     */
    private function insertStaticPageUrls($offset, $limit, ShopContextInterface $context = null)
    {
        $context = $this->createFallbackContext($context);

        $sitesData = $this->modelManager->getRepository('Shopware\Models\Site\Site')
            ->getSitesWithoutLinkQuery($context->getShop()->getId(), $offset, $limit)
            ->getArrayResult();

        $pages = $this->shopPageService->getList(array_column($sitesData, 'id'), $context);

        foreach ($pages as $site) {
            $site = json_decode(json_encode($site), true);

            $org_path = 'sViewport=custom&sCustom=' . $site['id'];
            $this->data->assign('site', $site);
            $path = $this->template->fetch('string:' . $this->config->get('seoCustomSiteRouteTemplate'), $this->data);
            $path = $this->sCleanupPath($path);
            $this->sInsertUrl($org_path, $path);
        }
    }

    /**
     * Maps the translation of the objectdata from the s_core_translations in the article array
     * @param array $articles
     * @return mixed
     */
    public function mapArticleTranslationObjectData($articles)
    {
        foreach ($articles as &$article) {
            if (empty($article['objectdata']) && empty($article['objectdataFallback'])) {
                unset($article['objectdata'], $article['objectdataFallback']);
                continue;
            }

            $objectData = @unserialize($article['objectdata']);
            $objectDataFallback = @unserialize($article['objectdataFallback']);

            if (empty($objectData)) {
                $objectData = [];
            }

            if (empty($objectDataFallback)) {
                $objectDataFallback = [];
            }

            if (empty($objectData) && empty($objectDataFallback)) {
                continue;
            }

            unset($article['objectdata'], $article['objectdataFallback']);

            $article = $this->mapArticleObjectFields($article, $objectData, $objectDataFallback, [
                'name' => 'txtArtikel',
                'description_long' => 'txtlangbeschreibung',
                'description' => 'txtshortdescription',
                'keywords' => 'txtkeywords',
                'metaTitle' => 'metaTitle',
            ]);

            $article = $this->mapArticleObjectAttributeFields($article, $objectDataFallback);
            $article = $this->mapArticleObjectAttributeFields($article, $objectData);
        }

        return $articles;
    }

    /**
     * map article core translation including fallback fields for given article
     *
     * @param array $article
     * @param array $objectData
     * @param array $objectDataFallback
     * @param array $fieldMappings array(articleFieldName => objectDataFieldName)
     * @return array $article
     */
    private function mapArticleObjectFields(
        array $article,
        array $objectData,
        array $objectDataFallback,
        array $fieldMappings
    ) {
        foreach ($fieldMappings as $articleFieldName => $objectDataFieldName) {
            if (!empty($objectData[$objectDataFieldName])) {
                $article[$articleFieldName] = $objectData[$objectDataFieldName];
                continue;
            }

            if (!empty($objectDataFallback[$objectDataFieldName])) {
                $article[$articleFieldName] = $objectDataFallback[$objectDataFieldName];
            }
        }
        return $article;
    }

    /**
     * map article attribute translation including fallback fields for given article
     *
     * @param array $article
     * @param array $translations
     * @return array $article
     */
    private function mapArticleObjectAttributeFields($article, $translations)
    {
        foreach ($translations as $key => $value) {
            if (strpos($key, '__attribute_') === false || empty($value)) {
                continue;
            }

            $articleKey = str_replace('__attribute_', '', $key);

            $article[$articleKey] = $value;
        }

        return $article;
    }

    /**
     * @param int $offset
     * @param int $limit
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
        if ($limit !== null) {
            $criteria->limit = $limit;
        }

        $result = $repo->search($criteria);
        $suppliers = $result->getData();
        $ids = array_column($suppliers, 'id');
        return $ids;
    }

    /**
     * @param ShopContextInterface $context
     * @return ShopContextInterface
     */
    private function createFallbackContext(ShopContextInterface $context = null)
    {
        if ($context) {
            return $context;
        }

        /** @var \Shopware\Models\Shop\Shop $shop */
        if (Shopware()->Container()->has('shop')) {
            $shop = Shopware()->Container()->get('shop');
            return $this->contextService->createShopContext($shop->getId());
        }

        return $this->contextService->createShopContext(1);
    }
}
