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
 * Sitemap controller
 *
 * @category  Shopware
 * @package   Shopware\Controllers\Frontend
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_SitemapXml extends Enlight_Controller_Action
{
    /**
     * @var \Shopware\Models\Category\Repository
     */
    protected $repository;

    /**
     * Init controller method
     */
    public function init()
    {
        $this->Response()->setHeader('Content-Type', 'text/xml; charset=utf-8');
        $this->Response()->sendResponse();

        $this->repository = Shopware()->Models()->getRepository(
            'Shopware\Models\Category\Category'
        );

        set_time_limit(0);
    }

    /**
     * Index action method
     */
    public function indexAction()
    {
        $parentId = Shopware()->Shop()->get('parentID');

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
     * @param int $parentId
     */
    public function readCategoryUrls($parentId)
    {
        $categories = $this->repository->getActiveChildrenList($parentId);

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
     * @param int $parentId
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
        $result = Shopware()->Db()->query($sql, array($parentId));

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
     * @param $parentId
     */
    public function readBlogUrls($parentId)
    {
        $blogs = array();

        $query = $this->repository->getBlogCategoriesByParentQuery($parentId);
        $blogCategories = $query->getArrayResult();

        $blogIds = array();
        foreach ($blogCategories as $blogCategory) {
            $blogIds[] = $blogCategory["id"];
        }
        if (empty($blogIds)) {
            return $blogs;
        }
        $blogIds = Shopware()->Db()->quote($blogIds);

        $sql = "
            SELECT id, category_id, DATE(display_date) as changed
            FROM s_blog
            WHERE active = 1 AND category_id IN($blogIds)
            ";
        $result = Shopware()->Db()->query($sql);

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
     */
    private function readStaticUrls()
    {
        $sites = $this->getSitesByShopId(Shopware()->Shop()->getId());

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

            if (!empty($site['link'])) {
                $userParams = parse_url($site['link'], PHP_URL_QUERY);
                parse_str($userParams, $site['urlParams']);
            }

            $site['show'] = $this->filterLink($site['link']);
        }

        return $sites;
    }
    /**
     * Helper function to read all static pages of a shop from the database
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

        $statement = Shopware()->Db()->executeQuery($sql, array($shopId));

        $keys = $statement->fetchAll(PDO::FETCH_COLUMN);

        /** @var Shopware\Models\Site\Repository $siteRepository */
        $siteRepository = $this->get('models')->getRepository('Shopware\Models\Site\Site');

        $sites = array();
        foreach ($keys as $key) {
            $current = $siteRepository->getSitesByNodeNameQueryBuilder($key)
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
     * @param string $link
     * @return bool
     */
    private function filterLink($link)
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
     */
    private function readSupplierUrls()
    {
        $supplierRepository = $this->get('models')->getRepository('Shopware\Models\Article\Supplier');

        $builder = $supplierRepository->createQueryBuilder('Supplier');
        $suppliers = $builder->getQuery()->getArrayResult();

        foreach ($suppliers as &$supplier) {
            $supplier['urlParams'] = array(
                'sViewport' => 'supplier',
                'sSupplier' => $supplier['id']
            );
        }

        return $suppliers;
    }

    /**
     * Helper function to read the landing pages urls
     */
    private function readLandingPageUrls()
    {
        /** @var Shopware\Models\Emotion\Repository $emotionRepository */
        $emotionRepository = $this->get('models')->getRepository('Shopware\Models\Emotion\Emotion');

        $builder = $emotionRepository->getCampaignsByCategoryId(Shopware()->Shop()->getCategory()->getId());
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
