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

/**
 * Sitemap controller
 *
 * @category  Shopware
 * @package   Shopware\Controllers\Frontend
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_SitemapXml extends Enlight_Controller_Action
{
    /** @var  \Shopware\Models\Category\Repository */
    private $categoryRepository;

    /** @var  \Shopware\Models\Site\Repository */
    private $siteRepository;

    /** @var  \Shopware\Components\Model\ModelRepository */
    private $supplierRepository;

    /** @var  \Shopware\Models\Emotion\Repository */
    private $emotionRepository;

    /**
     * Helper function to get the category repository
     *
     * @return \Shopware\Models\Category\Repository
     */
    private function getCategoryRepository()
    {
        if (empty($this->categoryRepository)) {
            $this->categoryRepository = $this->get('models')->getRepository('Shopware\Models\Category\Category');
        }

        return $this->categoryRepository;
    }

    /**
     * Helper function to get the site repository
     *
     * @return \Shopware\Models\Site\Repository
     */
    private function getSiteRepository()
    {
        if (empty($this->siteRepository)) {
            $this->siteRepository = $this->get('models')->getRepository('Shopware\Models\Site\Site');
        }

        return $this->siteRepository;
    }

    /**
     * Helper function to get the emotion repository
     *
     * @return \Shopware\Models\Emotion\Repository
     */
    private function getEmotionRepository()
    {
        if (empty($this->emotionRepository)) {
            $this->emotionRepository = $this->get('models')->getRepository('Shopware\Models\Emotion\Emotion');
        }

        return $this->emotionRepository;
    }

    /**
     * Init controller method
     */
    public function init()
    {
        $this->Response()->setHeader('Content-Type', 'text/xml; charset=utf-8');
        $this->Response()->sendResponse();

        set_time_limit(0);
    }

    /**
     * Index action method
     */
    public function indexAction()
    {
        $parentId = $this->get('shop')->get('parentID');

        $this->View()->sitemap = array(
            'categories' => $this->readCategoryUrls($parentId),
            'articles' => $this->readArticleUrls($parentId),
            'blogs' => $this->readBlogUrls($parentId),
            'customPages' => $this->readStaticUrls(),
            'suppliers' => $this->readSupplierUrls(),
            'landingPages' => $this->readLandingPageUrls()
        );
    }

    /**
     * Print category urls
     *
     * @param integer $parentId
     * @return array
     */
    public function readCategoryUrls($parentId)
    {
        $categories = $this->getCategoryRepository()->getActiveChildrenList($parentId);

        foreach ($categories as &$category) {
            $category['show'] = empty($category['external']);

            $category['urlParams'] = array(
                'sViewport' => 'cat',
                'sCategory' => $category['id'],
                'title' => $category['name']
            );

            if ($category['blog']) {
                $category['urlParams']['sViewport'] = 'blog';
            }
        }

        return $categories;
    }

    /**
     * Read article urls
     *
     * @param integer $parentId
     * @return array
     */
    public function readArticleUrls($parentId)
    {
        $articles = array();

        $sql = "
            SELECT
                a.id,
                DATE(a.changetime) as changed
            FROM s_articles a
                INNER JOIN s_articles_categories_ro ac
                    ON  ac.articleID  = a.id
                    AND ac.categoryID = ?
                INNER JOIN s_categories c
                    ON  c.id = ac.categoryID
                    AND c.active = 1
            WHERE a.active = 1
            GROUP BY a.id
        ";
        /** @var Zend_Db_Statement_Pdo $result */
        $result = $this->get('db')->query($sql, array($parentId));

        while ($article = $result->fetch()) {
            $article['changed'] = new DateTime($article['changed']);
            $article['urlParams'] = array(
                'sViewport' => 'detail',
                'sArticle'  => $article['id']
            );

            $articles[] = $article;
        }

        return $articles;
    }

    /**
     * Reads the blog item urls
     *
     * @param integer $parentId
     * @return array
     */
    public function readBlogUrls($parentId)
    {
        $blogs = array();

        $query = $this->getCategoryRepository()->getBlogCategoriesByParentQuery($parentId);
        $blogCategories = $query->getArrayResult();

        $blogIds = array();
        foreach ($blogCategories as $blogCategory) {
            $blogIds[] = $blogCategory["id"];
        }
        if (empty($blogIds)) {
            return $blogs;
        }
        $blogIds = $this->get('db')->quote($blogIds);

        $sql = "
            SELECT id, category_id, DATE(display_date) as changed
            FROM s_blog
            WHERE active = 1 AND category_id IN($blogIds)
            ";
        /** @var Zend_Db_Statement_Pdo $result */
        $result = $this->get('db')->query($sql);

        while ($blog = $result->fetch()) {
            $blog['changed'] = new DateTime($blog['changed']);
            $blog['urlParams'] = array(
                'sViewport' => 'blog',
                'sAction' => 'detail',
                'sCategory' => $blog['category_id'],
                'blogArticle' => $blog['id']
            );

            $blogs[] = $blog;
        }

        return $blogs;
    }

    /**
     * Helper function to Read the static pages urls
     *
     * @return array
     */
    private function readStaticUrls()
    {
        $sites = $this->getSitesByShopId($this->get('shop')->getId());

        foreach ($sites as $site) {
            if(!empty($site['children'])) {
                $sites = array_merge($sites, $site['children']);
            }
        }

        foreach ($sites as &$site) {

            $site['urlParams'] = array(
                'sViewport' => 'custom',
                'sCustom' => $site['id']
            );

            $site['show'] = $this->filterLink($site['link'], $site['urlParams']);
        }

        return $sites;
    }

    /**
     * Helper function to read all static pages of a shop from the database
     *
     * @param integer $shopId
     * @return array
     */
    private function getSitesByShopId($shopId)
    {
        $sql = "
            SELECT groups.key
            FROM s_core_shop_pages shopPages
              INNER JOIN s_cms_static_groups groups
                ON groups.id = shopPages.group_id
            WHERE shopPages.shop_id = ?
        ";

        /** @var Zend_Db_Statement_Pdo $statement */
        $statement = $this->get('db')->executeQuery($sql, array($shopId));

        $keys = $statement->fetchAll(PDO::FETCH_COLUMN);

        $sites = array();
        foreach ($keys as $key) {
            $current = $this->getSiteRepository()->getSitesByNodeNameQueryBuilder($key, $shopId)
                ->resetDQLPart('from')
                ->from('Shopware\Models\Site\Site', 'sites', 'sites.id')
                ->getQuery()
                ->getArrayResult();

            $sites += $current;
        }

        return $sites;
    }

    /**
     * Helper function to filter predefined links, which should not be in the sitemap (external links, sitemap links itself)
     * Returns false, if the link is not allowed
     *
     * @param string $link
     * @param array $userParams
     * @return bool
     */
    private function filterLink($link, &$userParams)
    {
        if (empty($link)) {
            return true;
        }

        $userParams = parse_url($link, PHP_URL_QUERY);
        parse_str($userParams, $userParams);

        $blacklist = array('', 'sitemap', 'sitemapXml');

        if (in_array($userParams['sViewport'], $blacklist)) {
            return false;
        }

        return true;
    }

    /**
     * Helper function to read the supplier pages urls
     *
     * @return array
     */
    private function readSupplierUrls()
    {
        $suppliers = $this->getSupplierForSitemap();
        foreach ($suppliers as &$supplier) {
            $supplier['urlParams'] = array(
                'sViewport' => 'listing',
                'sAction' => 'manufacturer',
                'sSupplier' => $supplier['id']
            );
        }

        return $suppliers;
    }

    /**
     * Gets all suppliers that have products for the current shop
     *
     * @return array
     * @throws Exception
     */
    private function getSupplierForSitemap()
    {
        $context = $this->get('shopware_storefront.context_service_core')->getShopContext();
        $categoryId = $context->getShop()->getCategory()->getId();

        /**@var $query QueryBuilder */
        $query = $this->get('dbal_connection')->createQueryBuilder();
        $query->select(['manufacturer.id', 'manufacturer.name']);

        $query->from('s_articles_supplier', 'manufacturer');
        $query->innerJoin('manufacturer', 's_articles', 'product', 'product.supplierID = manufacturer.id')
            ->innerJoin('product', 's_articles_categories_ro', 'categories', 'categories.articleID = product.id AND categories.categoryID = :categoryId')
            ->setParameter(':categoryId', $categoryId);

        $query->groupBy('manufacturer.id');

        /**@var $statement PDOStatement */
        $statement = $query->execute();

        $suppliers = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $suppliers;
    }

    /**
     * Helper function to read the landing pages urls
     *
     * @return array
     */
    private function readLandingPageUrls()
    {
        $builder = $this->getEmotionRepository()->getCampaignsByCategoryId($this->get('shop')->getCategory()->getId());
        $campaigns = $builder->getQuery()->getArrayResult();

        foreach ($campaigns as &$campaign) {
            $campaign['show'] = $this->filterCampaign($campaign[0]['validFrom'], $campaign[0]['validTo']);
            $campaign['urlParams'] = array(
                'sViewport' => 'campaign',
                'emotionId' => $campaign[0]['id'],
                'sCategory' => $campaign['categoryId']
            );
        }

        return $campaigns;
    }

    /**
     * Helper function to filter emotion campaigns
     * Returns false, if the campaign starts later or is outdated
     *
     * @param null $from
     * @param null $to
     * @return bool
     */
    private function filterCampaign($from = null, $to = null)
    {
        $now = new DateTime();

        if (isset($from) && $now < $from) {
            return false;
        }

        if (isset($to) && $now > $to) {
            return false;
        }

        return true;
    }
}
