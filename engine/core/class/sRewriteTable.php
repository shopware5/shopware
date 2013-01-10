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
 *
 * @category   Shopware
 * @package    Shopware_Core
 * @subpackage Class
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Deprecated Shopware Class that handle url rewrites
 *
 * todo@all: Documentation
 */
class sRewriteTable
{
    public $sSYSTEM;

    /**
     * @var Enlight_Template_Manager
     */
    protected $template;

    /**
     * @var
     */
    protected $data;

    /**
     * @var Shopware\Components\Model\ModelManager
     */
    protected $manager;

    /**
     * @var Shopware\Models\Category\Repository
     */
    protected $repository;

    /**
     * @var Shopware\Models\Blog\Repository
     */
    protected $blogRepository;

    /**
     * @var Shopware\Models\Category\Category
     */
    protected $baseCategory;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->manager = Shopware()->Models();
        $this->repository = $this->manager->getRepository('Shopware\Models\Category\Category');
        $this->blogRepository = $this->manager->getRepository('Shopware\Models\Blog\Blog');
        $this->baseCategory = Shopware()->Shop()->getCategory();
    }

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

    public function sCreateRewriteTable($last_update)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(0);

        $this->template = $this->sSYSTEM->sSMARTY;
        $this->template->registerPlugin(
            Smarty::PLUGIN_FUNCTION, 'sCategoryPath',
            array($this, 'sSmartyCategoryPath')
        );
        $this->data = $this->template->createData();

        $this->data->assign('sConfig', $this->sSYSTEM->sCONFIG);
        $this->data->assign('sRouter', $this);
        $this->data->assign('sCategoryStart', $this->baseCategory->getId());

        $this->sCreateRewriteTableCleanup();
        $this->sCreateRewriteTableStatic();
        $this->sCreateRewriteTableCategories();
        $this->sCreateRewriteTableBlog();
        $this->sCreateRewriteTableCampaigns();
        $last_update = $this->sCreateRewriteTableArticles($last_update);
        $this->sCreateRewriteTableContent();

        return $last_update;
    }

    protected function sCreateRewriteTableCleanup()
    {
        $sql = "
			DELETE ru FROM s_core_rewrite_urls ru
			LEFT JOIN s_cms_static cs
			ON ru.org_path LIKE CONCAT('sViewport=custom&sCustom=', cs.id)
			LEFT JOIN s_cms_support ct
			ON ru.org_path LIKE CONCAT('sViewport=ticket&sFid=', ct.id)
			LEFT JOIN s_emarketing_promotion_main ep
			ON ru.org_path LIKE CONCAT('sViewport=campaign&sCampaign=', ep.id)
			LEFT JOIN s_cms_groups cg
			ON ru.org_path LIKE CONCAT('sViewport=content&sContent=', cg.id)
			WHERE (ru.org_path LIKE 'sViewport=custom&sCustom=%'
			OR ru.org_path LIKE 'sViewport=ticket&sFid=%'
			OR ru.org_path LIKE 'sViewport=campaign&sCampaign=%'
			OR ru.org_path LIKE 'sViewport=content&sContent=%')
			AND cs.id IS NULL
			AND ct.id IS NULL
			AND ep.id IS NULL
			AND cg.id IS NULL
		";
        $this->sSYSTEM->sDB_CONNECTION->Execute($sql);

        $sql = "
			DELETE ru FROM s_core_rewrite_urls ru
			LEFT JOIN s_categories c
			ON c.id = REPLACE(ru.org_path, 'sViewport=blog&sCategory=', '')
			AND c.blog = 1
			WHERE ru.org_path LIKE 'sViewport=blog&sCategory=%'
			AND c.id IS NULL
		";
        $this->sSYSTEM->sDB_CONNECTION->Execute($sql);

        $sql = "
			DELETE ru FROM s_core_rewrite_urls ru
			LEFT JOIN s_categories c
			ON c.id = REPLACE(ru.org_path, 'sViewport=cat&sCategory=', '')
			AND (c.external = '' OR c.external IS NULL)
			AND c.blog = 0
			WHERE ru.org_path LIKE 'sViewport=cat&sCategory=%'
			AND c.id IS NULL
		";
        $this->sSYSTEM->sDB_CONNECTION->Execute($sql);

        $sql = "
			DELETE ru FROM s_core_rewrite_urls ru
			LEFT JOIN s_articles a
			ON a.id = REPLACE(ru.org_path, 'sViewport=detail&sArticle=', '')
			WHERE ru.org_path LIKE 'sViewport=detail&sArticle=%'
			AND a.id IS NULL
		";
        $this->sSYSTEM->sDB_CONNECTION->Execute($sql);
    }

    protected function sCreateRewriteTableStatic()
    {
        if (empty($this->sSYSTEM->sCONFIG['sSEOSTATICURLS'])) {
            return;
        }
        $static = array();
        $urls = $this->template->fetch('string:' . $this->sSYSTEM->sCONFIG['sSEOSTATICURLS'], $this->data);
        if (!empty($urls))
        foreach (explode("\n", $urls) as $url) {
            list($key, $value) = explode(',', trim($url));
            if (empty($key) || empty($value)) continue;
            $static[$key] = $value;
        }
        foreach ($static as $org_path => $name) {
            $path = $this->sCleanupPath($name, false);
            $this->sInsertUrl($org_path, $path);
        }
    }

    protected function sCreateRewriteTableCategories()
    {
        if (empty(Shopware()->Config()->routerCategoryTemplate)) {
            return;
        }

        $parentId = $this->baseCategory->getId();
        $result = $this->repository
            ->getActiveChildrenByIdQuery($parentId)
            ->getArrayResult();

        $categories = array();
        foreach($result as $category){
            $categories[$category['category']['id']] = array_merge($category['category'], array(
                'description' => $category['category']['name'],
                'childrenCount' => $category['childrenCount'],
                'articleCount' => $category['articleCount']
            ));
        }

        $template = 'string:' . Shopware()->Config()->routerCategoryTemplate;
        $template = $this->template->createTemplate($template, $this->data);

        foreach ($categories as $category) {
            if (!empty($category['external'])) {
                continue;
            }

            $template->assign('sCategory', $category);
            $path = $template->fetch();
            $path = $this->sCleanupPath($path, false);

            if($category['blog']) {
                $orgPath = 'sViewport=blog&sCategory=' . $category['id'];
            } else {
                $orgPath = 'sViewport=cat&sCategory=' . $category['id'];
            }

            $this->sInsertUrl($orgPath, $path);
        }
    }

    protected function sCreateRewriteTableArticles($last_update)
    {
        if (empty($this->sSYSTEM->sCONFIG['sROUTERARTICLETEMPLATE'])) {
            return $last_update;
        }

        $sql = 'UPDATE `s_articles` SET `changetime`= NOW() WHERE `changetime`=?';
        Shopware()->Db()->query($sql, array('0000-00-00 00:00:00'));

        $sql = "
			SELECT a.*, IF(atr.name IS NULL OR atr.name='', a.name, atr.name) as name,
			    d.ordernumber, d.suppliernumber, s.name as supplier, datum as date, d.releasedate, changetime as changed,
				at.attr1, at.attr2, at.attr3, at.attr4, at.attr5, at.attr6, at.attr7, at.attr8, at.attr9, at.attr10,
				at.attr11, at.attr12, at.attr13, at.attr14, at.attr15, at.attr16, at.attr17, at.attr18, at.attr19, at.attr20
			FROM s_categories c, s_categories c2, s_articles_categories ac, s_articles a
			JOIN s_articles_details d
			ON d.id = a.main_detail_id
			LEFT JOIN s_articles_attributes at
			ON at.articledetailsID=d.id
			LEFT JOIN s_articles_translations atr
			ON atr.articleID=a.id
			AND atr.languageID=?
			LEFT JOIN s_articles_supplier s
			ON s.id=a.supplierID
			WHERE c.id=?
            AND c2.active=1
            AND c2.left >= c.left
            AND c2.right <= c.right
            AND ac.articleID=a.id
            AND ac.categoryID=c2.id

            AND a.active=1
			AND a.changetime > ?
			GROUP BY a.id
			ORDER BY a.changetime, a.id
			LIMIT 1000
		";
        $result = $this->sSYSTEM->sDB_CONNECTION->Execute($sql, array(
            Shopware()->Shop()->getId(),
            Shopware()->Shop()->get('parentID'),
            $last_update
        ));

        if ($result !== false) {
            while ($row = $result->FetchRow()) {
                $this->data->assign('sArticle', $row);
                $path = $this->template->fetch('string:' . $this->sSYSTEM->sCONFIG['sROUTERARTICLETEMPLATE'], $this->data);
                $path = $this->sCleanupPath($path, false);

                $org_path = 'sViewport=detail&sArticle=' . $row['id'];
                $this->sInsertUrl($org_path, $path);
                $last_update = $row['changed'];
                $last_id = $row['id'];
            }
        }

        if (!empty($last_id)) {
            $sql = 'UPDATE s_articles SET changetime=DATE_ADD(changetime, INTERVAL 1 SECOND) WHERE changetime=? AND id > ?';
            $this->sSYSTEM->sDB_CONNECTION->Execute($sql, array($last_update, $last_id));
        }

        return $last_update;
    }

    protected function sCreateRewriteTableBlog()
    {
        $query = $this->repository->getBlogCategoriesByParentQuery(Shopware()->Shop()->get('parentID'));
        $blogCategories = $query->getArrayResult();

        //get all blog category ids
        $blogCategoryIds = array();
        foreach ($blogCategories as $blogCategory) {
            $blogCategoryIds[] = $blogCategory["id"];
        }

        /** @var $repository \Shopware\Models\Blog\Repository */
        $blogArticlesQuery = $this->blogRepository->getListQuery($blogCategoryIds);

        $blogArticlesQuery->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $blogArticles = $blogArticlesQuery->getArrayResult();

        foreach ($blogArticles as $blogArticle) {
            $this->data->assign('blogArticle', $blogArticle);
            $path = $this->template->fetch('string:' . Shopware()->Config()->routerBlogTemplate, $this->data);
            $path = $this->sCleanupPath($path, false);

            $org_path = 'sViewport=blog&sAction=detail&sCategory=' . $blogArticle['categoryId'] . '&blogArticle=' . $blogArticle['id'];
            $this->sInsertUrl($org_path, $path);
        }
    }

    protected function sCreateRewriteTableCampaigns()
    {
       $campaignsRepository = Shopware()->Models()->getRepository('Shopware\Models\Emotion\Emotion');

       $campaigns = $campaignsRepository->getCampaigns();
       $campaigns = $campaigns->getQuery()->getArrayResult();


       foreach ($campaigns as $campaign) {
           $campaign[0]["categoryId"] = $campaign["categoryId"];
           $campaign = $campaign[0];

           $this->data->assign('campaign', $campaign);
           $path = $this->template->fetch('string:' . Shopware()->Config()->routerCampaignTemplate, $this->data);
           $path = $this->sCleanupPath($path, false);

           $org_path = 'sViewport=campaign&sCategory=' . $campaign['categoryId'] . '&emotionId=' . $campaign['id'];
           $this->sInsertUrl($org_path, $path);
       }
    }

    protected function sCreateRewriteTableContent()
    {
        $sql = 'SELECT id, description as name FROM `s_emarketing_promotion_main`';
        $result = $this->sSYSTEM->sDB_CONNECTION->Execute($sql);
        if ($result !== false) {
            while ($row = $result->FetchRow()) {
                $org_path = 'sViewport=campaign&sCampaign=' . $row['id'];
                $path = $this->sCleanupPath($row['name']);
                $this->sInsertUrl($org_path, $path);
            }
        }

        $sql = 'SELECT id, name, ticket_typeID FROM `s_cms_support`';
        $result = $this->sSYSTEM->sDB_CONNECTION->Execute($sql);
        if ($result !== false) {
            while ($row = $result->FetchRow()) {
                $org_path = 'sViewport=ticket&sFid=' . $row['id'];
                $path = $this->sCleanupPath($row['name']);
                $this->sInsertUrl($org_path, $path);
            }
        }

        $sql = 'SELECT id, description as name FROM `s_cms_static` WHERE link=\'\'';
        $result = $this->sSYSTEM->sDB_CONNECTION->Execute($sql);
        if ($result !== false) {
            while ($row = $result->FetchRow()) {
                $org_path = 'sViewport=custom&sCustom=' . $row['id'];
                $path = $this->sCleanupPath($row['name']);
                $this->sInsertUrl($org_path, $path);
            }
        }

        $sql = 'SELECT id, description as name FROM `s_cms_groups`';
        $result = $this->sSYSTEM->sDB_CONNECTION->Execute($sql);
        if ($result !== false) {
            while ($row = $result->FetchRow()) {
                $org_path = 'sViewport=content&sContent=' . $row['id'];
                $path = $this->sCleanupPath($row['name']);
                $this->sInsertUrl($org_path, $path);
            }
        }
    }

    public function sInsertUrl($org_path, $path)
    {
        $path = trim($path);
        $path = ltrim($path, '/');
        if (empty($path) || empty($org_path)) {
            return false;
        }
        $sql_rewrite = 'UPDATE s_core_rewrite_urls SET main=0 WHERE org_path=? AND path!=? AND subshopID=?';
        $this->sSYSTEM->sDB_CONNECTION->Execute($sql_rewrite, array($org_path, $path, Shopware()->Shop()->getId()));
        $sql_rewrite = '
			INSERT IGNORE INTO s_core_rewrite_urls (org_path, path, main, subshopID)
			VALUES (?, ?, 1, ?)
			ON DUPLICATE KEY UPDATE main=1
		';
        $this->sSYSTEM->sDB_CONNECTION->Execute($sql_rewrite, array($org_path, $path, Shopware()->Shop()->getId()));
    }

    public function sSmartyCategoryPath($params)
    {
        if (!empty($params['articleID']))
            $parts = $this->sCategoryPathByArticleId($params['articleID'], isset($params['categoryID']) ? $params["categoryID"] : null);
        elseif (!empty($params['categoryID']))
            $parts = $this->sCategoryPath($params['categoryID']);
        if (empty($params['separator']))
            $params['separator'] = '/';
        foreach ($parts as &$part) {
            $part = str_replace($params['separator'], '', $part);
        }
        $parts = implode($params['separator'], $parts);
        return $parts;
    }

    public function sCategoryPath($category)
    {
        $parts = $this->repository->getPathById($category, 'name');
        $level = $this->baseCategory->getLevel() + 1;
        $parts = array_slice($parts, $level);
        return $parts;
    }

    /**
     * @param   $articleId
     * @param   null $parentId
     * @return  null|string
     */
    public function sCategoryPathByArticleId($articleId, $parentId = null)
    {
        //if (empty($parentId)) {
        //    $parentId = $this->baseCategory->getId();
        //}
        //$categoryId = $this->repository
        //    ->getActiveByArticleIdQuery($articleId, $categoryId)
        //    ->setMaxResults(1)
        //    ->getOneOrNullResult();
        $categoryId = Shopware()->Modules()->Categories()->sGetCategoryIdByArticleId(
            $articleId, $parentId
        );
        return $categoryId !== null ? $this->sCategoryPath($categoryId) : null;
    }
}
