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
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $this->Front()->setParam('disableOutputBuffering', true);
        $this->Front()->returnResponse(true);

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
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
        echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\r\n";

        $parentId = Shopware()->Shop()->get('parentID');

        $this->readCategoryUrls($parentId);

        $this->readArticleUrls($parentId);

        $this->readBlogUrls($parentId);

        $this->readStaticUrls();

        $this->readSupplierUrls();

        $this->readLandingPageUrls();

        echo "</urlset>\r\n";
    }

    /**
     * Print category urls
     *
     * @param int $parentId
     */
    public function readCategoryUrls($parentId)
    {
        $categories = $this->repository->getActiveChildrenList($parentId);

        foreach ($categories as $category) {
            if (!empty($category['external'])) {
                continue;
            }

            //use a different link if it is a blog category
            if (!empty($category['blog'])) {
                $category['link'] = $this->Front()->Router()->assemble(array(
                    'sViewport' => 'blog',
                    'sCategory' => $category['id'],
                    'title' => $category['name']
                ));
            } else {
                $category['link'] = $this->Front()->Router()->assemble(array(
                    'sViewport' => 'cat',
                    'sCategory' => $category['id'],
                    'title' => $category['name']
                ));
            }

            $this->printCategoryUrl(array(
                'changed' => $category['changed'],
                'link' => $category['link']
            ));
        }
    }

    /**
     * Print category url
     *
     * @param array $url
     */
    public function printCategoryUrl($url)
    {
        $line = '<url>';
        $line .= '<loc>' . $url['link'] . '</loc>';
        if (!empty($url['changed'])) {
            $line .= '<lastmod>' . $url['changed']->format('Y-m-d') . '</lastmod>';
        }
        $line .= '<changefreq>weekly</changefreq><priority>0.5</priority>';
        $line .= '</url>';
        $line .= "\r\n";
        echo $line;
    }

    /**
     * Read article urls
     *
     * @param int $parentId
     */
    public function readArticleUrls($parentId)
    {
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
        if (!$result->rowCount()) {
            return;
        }
        while ($url = $result->fetch()) {
            $url['link'] = $this->Front()->Router()->assemble(array(
                'sViewport' => 'detail',
                'sArticle'  => $url['id']
            ));
            $this->printArticleUrls($url);
        }
    }

    /**
     * Reads the blog item urls
     *
     * @param $parentId
     */
    public function readBlogUrls($parentId)
    {
        $query = $this->repository->getBlogCategoriesByParentQuery($parentId);
        $blogCategories = $query->getArrayResult();

        $blogIds = array();
        foreach ($blogCategories as $blogCategory) {
            $blogIds[] = $blogCategory["id"];
        }
        if (empty($blogIds)) {
            return;
        }
        $blogIds = Shopware()->Db()->quote($blogIds);

        $sql = "
            SELECT id, category_id, DATE(display_date) as changed
            FROM s_blog
            WHERE active = 1 AND category_id IN($blogIds)
            ";
        $result = Shopware()->Db()->query($sql);
        if (!$result->rowCount()) {
            return;
        }
        while ($blogUrlData = $result->fetch()) {
            $blogUrlData['link'] = $this->Front()->Router()->assemble(array(
                'sViewport' => 'blog',
                'sAction' => 'detail',
                'sCategory' => $blogUrlData['category_id'],
                'blogArticle' => $blogUrlData['id']
            ));
            $this->printArticleUrls($blogUrlData);
        }
    }

    /**
     * Print article url
     *
     * @param array $url
     */
    public function printArticleUrls($url)
    {
        $line = '<url>';
        $line .= '<loc>' . $url['link'] . '</loc>';
        if (!empty($url['changed'])) {
            $line .= '<lastmod>' . $url['changed'] . '</lastmod>';
        }
        $line .= '<changefreq>weekly</changefreq><priority>0.5</priority>';
        $line .= '</url>';
        $line .= "\r\n";
        echo $line;
    }

    /**
     * Helper function to Read the static pages urls
     */
    private function readStaticUrls()
    {
        $sites = $this->getSitesByShopId(Shopware()->Shop()->getId());

        foreach ($sites as $site) {
            $this->printSite($site);
        }
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
     * Helper function to print a custom page and its children to sitemap.xml
     * @param $site
     * @return mixed
     */
    private function printSite($site)
    {
        if (!$this->filterLink($site['link'])) {
            return;
        }

        $this->printCategoryUrl(
            $this->getSitemapArray(
                $site['id'],
                $site['changed'],
                'custom',
                'sCustom',
                $site['link']
            )
        );

        foreach($site['children'] as $child) {
            $this->printSite($child);
        }
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

        if (empty($userParams['sViewport'])) {
            return false;
        }

        $blacklist = array('sitemap', 'sitemapXml');

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

        foreach ($suppliers as $supplier) {
            $this->printCategoryUrl(
                $this->getSitemapArray(
                    $supplier['id'],
                    $supplier['changed'],
                    'supplier',
                    'sSupplier'
                )
            );
        }
    }

    /**
     * Helper functio to read the landing pages urls
     */
    private function readLandingPageUrls()
    {
        /** @var Shopware\Models\Emotion\Repository $emotionRepository */
        $emotionRepository = $this->get('models')->getRepository('Shopware\Models\Emotion\Emotion');

        $builder = $emotionRepository->getCampaigns();
        $campaigns = $builder->getQuery()->getScalarResult();

        foreach ($campaigns as $campaign) {
            $this->printCategoryUrl(
                $this->getSitemapArray(
                    $campaign['emotions_id'],
                    $campaign['emotions_modified'],
                    'campaign',
                    'emotionId',
                    array('sCategory' => $campaign['categoryId'])
                )
            );
        }
    }

    /**
     * Helper function to create a sitemap readable array
     * If $link is an array, it will be used as additional params for link assembling
     * @param integer $id
     * @param string $changed
     * @param string $viewport
     * @param string $idParam
     * @param string|array $link
     * @return array
     */
    private function getSitemapArray($id, $changed, $viewport, $idParam, $link = '')
    {
        if(is_array($link) || !strlen($link)){
            $userParams = array(
                'sViewport' => $viewport,
                $idParam => $id
            );

            if(is_array($link))
            {
                $userParams = array_merge($userParams, $link);
            }

            $link = $userParams;
        }

        $link = $this->Front()->Router()->assemble($link);

        return array(
            'changed' => $changed,
            'link' => $link
        );
    }
}
