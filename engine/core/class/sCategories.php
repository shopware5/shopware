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
 * Deprecated Shopware Class that handles categories
 *
 * @category  Shopware
 * @package   Shopware\Core
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class sCategories
{
    public $sSYSTEM;

    /**
     * @var Shopware\Components\Model\ModelManager
     */
    public $manager;

    /**
     * @var Shopware\Models\Category\Repository
     */
    public $repository;

    /**
     * @var string
     */
    public $baseUrl;

    /**
     * Url to the blog controller
     * @var string
     */
    public $blogBaseUrl;

    /**
     * @var int
     */
    public $baseId;

    /**
     * @var int
     */
    public $customerGroupId;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->manager = Shopware()->Models();
        $this->repository = $this->manager->getRepository('Shopware\Models\Category\Category');
        $this->baseUrl = Shopware()->Config()->get('baseFile') . '?sViewport=cat&sCategory=';
        $this->blogBaseUrl = Shopware()->Config()->get('baseFile') . '?sViewport=blog&sCategory=';
        $this->baseId = (int) Shopware()->Shop()->get('parentID');
        $this->customerGroupId = (int) Shopware()->Modules()->System()->sSYSTEM->sUSERGROUPDATA['id'];
    }

    /**
     * Returns a category path as array for given category id
     *
     * @param $id
     * @return array
     */
    public function sGetCategories($id)
    {
        if ($id == $this->baseId) {
            return $this->sGetCategoriesByParentId($this->baseId);
        }

        $path = $this->repository->getPathById($id, 'id');
        $path = array_reverse($path);

        $categories = array();
        $lastCategoryId = null;
        foreach ($path as $categoryId) {
            $subCategories = $this->sGetCategoriesByParentId($categoryId);
            if (isset($lastCategoryId)) {
                $subCategories[$lastCategoryId]['flag'] = true;
                $subCategories[$lastCategoryId]['subcategories'] = $categories;
            }
            $categories = $categories[$categoryId]['subcategories'] = $subCategories;
            $lastCategoryId = $categoryId;
            if ($categoryId == $this->baseId) {
                break;
            }
        }

        return $categories;
    }

    /**
     * @param $id
     * @return array
     */
    protected function sGetCategoriesByParentId($id)
    {
        $categories = $this->repository
            ->getActiveByParentIdQuery($id, $this->customerGroupId)
            ->getArrayResult();
        $resultCategories = array();
        foreach ($categories as $category) {
            $url = $category['category']['blog'] ? $this->blogBaseUrl : $this->baseUrl;
            $resultCategories[$category['category']['id']] = array_merge($category['category'], array(
                'description' => $category['category']['name'],
                'childrenCount' => $category['childrenCount'],
                'articleCount' => $category['articleCount'],
                'hidetop' => $category['category']['hideTop'],
                'subcategories' => array(),
                'link' => $category['category']['external'] ?: $url . $category['category']['id'],
                'flag' => false
            ));
        }

        return $resultCategories;
    }

    /**
     * @param $articleId
     * @param $parentId
     * @return int|null
     */
    public function sGetCategoryIdByArticleId($articleId, $parentId = null)
    {
        if ($parentId === null) {
            $parentId = $this->baseId;
        }

        $sql = '
            SELECT STRAIGHT_JOIN
                   ac.categoryID as id
            FROM s_articles_categories_ro ac  FORCE INDEX (category_id_by_article_id)
                INNER JOIN s_categories c
                    ON  ac.categoryID = c.id
                    AND c.active = 1
                    AND c.path LIKE ?

                LEFT JOIN s_categories c2
                    ON c2.parent = c.id

            WHERE ac.articleID = ?
            AND c2.id IS NULL
            ORDER BY ac.id
        ';

        return (int) Shopware()->Db()->fetchOne($sql, array(
            '%|' . $parentId . '|%',
            $articleId
        ));
    }

    /**
     * Returns the main categories
     *
     * @return array
     */
    public function sGetMainCategories()
    {
        return $this->sGetCategoriesByParentId($this->baseId);
    }

    /**
     * Returns category path for the given category id
     *
     * @param $id
     * @return array
     */
    public function sGetCategoriesByParent($id)
    {
        $pathCategories = $this->repository->getPathById($id, array('id', 'name', 'blog'));

        $pathCategories = array_reverse($pathCategories);

        $categories = array();
        foreach ($pathCategories as $category) {
            if ($category['id'] == $this->baseId) {
                break;
            }

            $url = ($category["blog"]) ? $this->blogBaseUrl : $this->baseUrl;
            $category['link'] = $url . $category['id'];
            $categories[] = $category;
        }

        return $categories;
    }

    /**
     * Return a whole category tree by id
     * @param  int   $parentId
     * @param  null  $depth
     * @return array
     */
    public function sGetWholeCategoryTree($parentId = null, $depth = null)
    {
        if ($parentId === null) {
            $parentId = $this->baseId;
        }

        $result = $this->repository->getActiveChildrenTree($parentId, $this->customerGroupId, $depth);
        $result = $this->mapCategoryTree($result);

        return $result;
    }

    /**
     * @param array $categories
     * @return array
     */
    protected function mapCategoryTree($categories)
    {
        foreach ($categories as &$category) {
            $url = ($category['blog']) ? $this->blogBaseUrl : $this->baseUrl;
            $category['description'] = $category['name'];
            $category['link'] = $category['external'] ? : $url . $category['id'];
            $category['hidetop'] = $category['hideTop'];
            if ($category['sub']) {
                $category['sub'] = $this->mapCategoryTree($category['sub']);
            }
        }

        return $categories;
    }

    /**
     * Returns category content for the given category id
     *
     * @param $id
     * @return array
     */
    public function sGetCategoryContent($id)
    {
        if ($id === null) {
            $id = $this->baseId;
        }
        $category = $this->repository->getActiveByIdQuery($id, $this->customerGroupId)->getArrayResult();

        if (empty($category[0])) {
            return null;
        }
        $category = $category[0];

        $detailUrl = $category['category']['blog'] ? $this->blogBaseUrl : $this->baseUrl;
        $detailUrl .= $category['category']['id'];

        $canonical = $detailUrl;
        if (Shopware()->Config()->get('forceUnsecureCanonical')) {
            $canonical = str_replace('https://', 'http://', $canonical);
        }

        $category = array_merge($category['category'], array(
            'description' => $category['category']['name'],
            'cmsheadline' => $category['category']['cmsHeadline'],
            'cmstext' => $category['category']['cmsText'],
            'metakeywords' => $category['category']['metaKeywords'],
            'metadescription' => $category['category']['metaDescription'],
            'noviewselect' => $category['category']['noViewSelect'],
            'childrenCount' => (int) $category['childrenCount'],
            'articleCount' => (int) $category['articleCount'],
            'sSelf' => $detailUrl,
            'sSelfCanonical' => $canonical,
            'rssFeed' => $detailUrl . '&sRss=1',
            'atomFeed' => $detailUrl . '&sAtom=1',
        ));

        if (empty($category['template'])) {
            $category['template'] = Shopware()->Config()->get('categoryDefaultTpl');
        }

        if (empty($category['template'])) {
            $category['template'] = 'article_listing_3col.tpl';
        }

        if (preg_match('#article_listing_([1-4]col).tpl#', $category['template'], $match)) {
            $category['layout'] = $match[1];
        }

        return $category;
    }

    /**
     * Returns the category path for the given category id
     *
     * @param int|$id
     * @param  int|null $parentId
     * @return array
     */
    public function sGetCategoryPath($id, $parentId = null)
    {
        if ($parentId === null) {
            $parentId = $this->baseId;
        }
        $path = $this->repository->getPathById($id, 'id');
        foreach ($path as $key => $value) {
            unset($path[$key]);
            if ($value == $parentId) {
                break;
            }
        }

        return $path;
    }
}
