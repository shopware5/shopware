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

use Shopware\Components\Model\ModelManager;

/**
 * Deprecated Shopware Class that handles url rewrites
 *
 * @category  Shopware
 * @package   Shopware\Core
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class sRewriteTable
{
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
     * sCategories core class instance
     *
     * @var sCategories
     */
    private $categoriesModule;

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
     * @param Enlight_Components_Db_Adapter_Pdo_Mysql $db
     * @param Shopware_Components_Config $config
     * @param ModelManager $modelManager
     * @param sSystem $systemModule
     * @param Enlight_Template_Manager $template
     * @param sCategories $categoriesModule
     */
    public function __construct(
        Enlight_Components_Db_Adapter_Pdo_Mysql $db                 = null,
        Shopware_Components_Config              $config             = null,
        ModelManager                            $modelManager       = null,
        sSystem                                 $systemModule       = null,
        Enlight_Template_Manager                $template           = null,
        sCategories                             $categoriesModule   = null
    )
    {
        $this->db = $db ? : Shopware()->Db();
        $this->config = $config ? : Shopware()->Config();
        $this->modelManager = $modelManager ? : Shopware()->Models();
        $this->sSYSTEM = $systemModule ? : Shopware()->System();
        $this->template = $template ? : Shopware()->Template();
        $this->categoriesModule = $categoriesModule ? : Shopware()->Modules()->Categories();
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
     * @param bool $remove_ds
     * @return string
     */
    public function sCleanupPath($path, $remove_ds = true)
    {
        $replace = array(
            ' & ' => '-und-',
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'Ü' => 'Ue',
            'Ä' => 'Ae',
            'Ö' => 'Oe',
            'ß' => 'ss',
            ':' => '-',
            ',' => '-',
            "'" => '-',
            '"' => '-',
            ' ' => '-',
            '+' => '-',
            'à' => 'a',
            'á' => 'a',
            'è' => 'e',
            'é' => 'e',
            'ù' => 'u',
            'ú' => 'u',
            'ë' => 'e',
            'ç' => 'c',
            'Ç' => 'C',
            '&#351;' => 's',
            '&#350;' => 'S',
            '&#287;' => 'g',
            '&#286;' => 'G',
            '&#304;' => 'i',
        );
        $path = html_entity_decode($path);
        $path = str_replace(array_keys($replace), array_values($replace), $path);
        if ($remove_ds) {
            $path = str_replace('/', '-', $path);
        }
        $path = preg_replace('/&[a-z0-9#]+;/i', '', $path);
        $path = preg_replace('#[^0-9a-z-_./]#i', '', $path);
        $path = preg_replace('/-+/', '-', $path);
        return trim($path, '-');
    }

    /**
     * Sets up the environment for seo url calculation
     */
    public function baseSetup()
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(0);

        $keys = array_keys($this->template->registered_plugins['function']);
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

        $this->sCreateRewriteTableCleanup();
        $this->sCreateRewriteTableStatic();
        $this->sCreateRewriteTableCategories();
        $this->sCreateRewriteTableBlog();
        $this->sCreateRewriteTableCampaigns();
        $lastUpdate = $this->sCreateRewriteTableArticles($lastUpdate);
        $this->sCreateRewriteTableContent();
        $this->sCreateRewriteTableSuppliers(Shopware()->Shop());

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
            LEFT JOIN s_emarketing_promotion_main ep
              ON ru.org_path LIKE CONCAT('sViewport=campaign&sCampaign=', ep.id)
            LEFT JOIN s_cms_groups cg
              ON ru.org_path LIKE CONCAT('sViewport=content&sContent=', cg.id)
            WHERE (
                ru.org_path LIKE 'sViewport=custom&sCustom=%'
                OR ru.org_path LIKE 'sViewport=ticket&sFid=%'
                OR ru.org_path LIKE 'sViewport=campaign&sCampaign=%'
                OR ru.org_path LIKE 'sViewport=content&sContent=%'
            )
            AND cs.id IS NULL
            AND ct.id IS NULL
            AND ep.id IS NULL
            AND cg.id IS NULL"
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
            LEFT JOIN s_articles a
              ON a.id = REPLACE(ru.org_path, 'sViewport=supplier&sSupplier=', '')
            WHERE ru.org_path LIKE 'sViewport=supplier&sSupplier=%'
            AND a.id IS NULL"
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
                if (empty($key) || empty($value)) continue;
                $static[$key] = $value;
            }
        }

        foreach ($static as $org_path => $name) {
            $path = $this->sCleanupPath($name, false);
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
            $path = $this->sCleanupPath($path, false);

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
     * @return string
     */
    public function sCreateRewriteTableArticles($lastUpdate, $limit = 1000)
    {
        $routerArticleTemplate = $this->config->get('sROUTERARTICLETEMPLATE');
        if (empty($routerArticleTemplate)) {
            return $lastUpdate;
        }

        $this->db->query(
            'UPDATE `s_articles` SET `changetime`= NOW() WHERE `changetime`=?',
            array('0000-00-00 00:00:00')
        );

        $sql = $this->getSeoArticleQuery();
        $sql = $this->db->limit($sql, $limit);

        $result = $this->db->fetchAll(
            $sql,
            array(
                Shopware()->Shop()->get('parentID'),
                Shopware()->Shop()->getId(),
                $lastUpdate
            )
        );

        foreach ($result as $row) {
            $this->data->assign('sArticle', $row);
            $path = $this->template->fetch('string:' . $routerArticleTemplate, $this->data);
            $path = $this->sCleanupPath($path, false);

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
            SELECT a.*, IF(atr.name IS NULL OR atr.name='', a.name, atr.name) as name,
                d.ordernumber, d.suppliernumber, s.name as supplier, datum as date,
                d.releasedate, changetime as changed, metaTitle, at.attr1, at.attr2,
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

            LEFT JOIN s_articles_translations atr
                ON atr.articleID=a.id
                AND atr.languageID=?

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
            $path = $this->sCleanupPath($path, false);

            $org_path = 'sViewport=blog&sAction=detail&sCategory=' . $blogArticle['categoryId'] . '&blogArticle=' . $blogArticle['id'];
            $this->sInsertUrl($org_path, $path);
        }
    }

    /**
     * Create emotion rewrite rules
     * Used in multiple locations
     *
     * @param null $offset
     * @param null $limit
     */
    public function sCreateRewriteTableSuppliers($offset = null, $limit = null)
    {
        $seoSupplier = $this->config->get('sSEOSUPPLIER');
        if (empty($seoSupplier)) {
            return;
        }

        $suppliers = $this->modelManager->getRepository('Shopware\Models\Article\Supplier')
            ->getFriendlyUrlSuppliersQuery($offset, $limit)->getArrayResult();

        $seoSupplierRouteTemplate = $this->config->get('seoSupplierRouteTemplate');
        foreach ($suppliers as $supplier) {
            $this->data->assign('sSupplier', $supplier);
            $path = $this->template->fetch('string:' . $seoSupplierRouteTemplate, $this->data);
            $path = $this->sCleanupPath($path, false);

            $org_path = 'sViewport=supplier&sSupplier=' . $supplier['id'];
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
        $campaigns = $this->modelManager->getRepository('Shopware\Models\Emotion\Emotion')
           ->getCampaigns($offset, $limit);
        $campaigns = $campaigns->getQuery()->getArrayResult();

        $routerCampaignTemplate = $this->config->get('routerCampaignTemplate');
        foreach ($campaigns as $campaign) {
            $campaign[0]["categoryId"] = $campaign["categoryId"];
            $campaign = $campaign[0];

            $this->data->assign('campaign', $campaign);
            $path = $this->template->fetch('string:' . $routerCampaignTemplate, $this->data);
            $path = $this->sCleanupPath($path, false);

            $org_path = 'sViewport=campaign&sCategory=' . $campaign['categoryId'] . '&emotionId=' . $campaign['id'];
            $this->sInsertUrl($org_path, $path);
        }
    }

    /**
     * Create CMS rewrite rules
     * Used in multiple locations
     *
     * @param int $offset
     * @param int $limit
     */
    public function sCreateRewriteTableContent($offset = null, $limit = null)
    {
        $sql = "SELECT id, description as name FROM `s_emarketing_promotion_main`";
        if ($limit !== null) {
            $sql = $this->db->limit($sql, $limit, $offset);
        }
        $eMarketingPromotion = $this->db->fetchAll($sql);
        foreach ($eMarketingPromotion as $row) {
            $org_path = 'sViewport=campaign&sCampaign=' . $row['id'];
            $path = $this->sCleanupPath($row['name']);
            $this->sInsertUrl($org_path, $path);
        }

        $sql = "SELECT id, name, ticket_typeID FROM `s_cms_support`";
        if ($limit !== null) {
            $sql = $this->db->limit($sql, $limit, $offset);
        }
        $cmsSupport = $this->db->fetchAll($sql);
        foreach ($cmsSupport as $row) {
            $org_path = 'sViewport=ticket&sFid=' . $row['id'];
            $path = $this->sCleanupPath($row['name']);
            $this->sInsertUrl($org_path, $path);
        }

        $sql = "SELECT id, description as name FROM `s_cms_static` WHERE link=''";
        if ($limit !== null) {
            $sql = $this->db->limit($sql, $limit, $offset);
        }
        $cmsStatic = $this->db->fetchAll($sql);
        foreach ($cmsStatic as $row) {
            $org_path = 'sViewport=custom&sCustom=' . $row['id'];
            $path = $this->sCleanupPath($row['name']);
            $this->sInsertUrl($org_path, $path);
        }

        $sql = "SELECT id, description as name FROM `s_cms_groups`";
        if ($limit !== null) {
            $sql = $this->db->limit($sql, $limit, $offset);
        }
        $cmsGroups = $this->db->fetchAll($sql);
        foreach ($cmsGroups as $row) {
            $org_path = 'sViewport=content&sContent=' . $row['id'];
            $path = $this->sCleanupPath($row['name']);
            $this->sInsertUrl($org_path, $path);
        }
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
        foreach ($parts as &$part) {
            $part = str_replace($params['separator'], '', $part);
        }
        $parts = implode($params['separator'], $parts);
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
        $level = Shopware()->Shop()->getCategory()->getLevel();
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
        $categoryId = $this->categoriesModule->sGetCategoryIdByArticleId(
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
}
