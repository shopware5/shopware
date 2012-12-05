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
 * @package    Shopware_Modules
 * @subpackage Categories
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

use Shopware\Models\Category\Category;

/**
 * Deprecated Shopware Class that handle categories
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
        if($id == $this->baseId) {
            return $this->sGetCategoriesByParentId($this->baseId);
        }

        $path = $this->repository->getPathById($id, 'id');
        $path = array_reverse($path);

        $categories = array(); $lastCategoryId = null;
        foreach($path as $categoryId) {
            $subCategories = $this->sGetCategoriesByParentId($categoryId);
            if(isset($lastCategoryId)) {
                $subCategories[$lastCategoryId]['flag'] = true;
                $subCategories[$lastCategoryId]['subcategories'] = $categories;
            }
            $categories = $categories[$categoryId]['subcategories'] = $subCategories;
            $lastCategoryId = $categoryId;
            if($categoryId == $this->baseId) {
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
        foreach($categories as $category) {
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
        if($parentId === null) {
            $parentId = $this->baseId;
        }
        //$query = $this->repository->getActiveByArticleIdQuery($articleId, $parentId);
        //$result = $query->setMaxResults(1)->getArrayResult();
        //return isset($result[0]) ? $result[0] : null;
        $sql = '
            SELECT c2.id
            FROM s_categories c, s_categories c2, s_articles_categories ac
            WHERE c.id = ?
            AND c2.left > c.left
            AND c2.right < c.right
            AND ac.articleID = ?
            AND c2.id = ac.categoryID
            AND c2.active = 1
            ORDER BY ac.id
        ';
        return (int)Shopware()->Db()->fetchOne($sql, array(
            $parentId, $articleId
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
    public function sGetCategoriesByParent ($id)
    {
        $pathCategories = $this->repository
            ->getPathById($id, array('id', 'name', 'blog'));
        $pathCategories = array_reverse($pathCategories);

        $categories = array();
        foreach($pathCategories as $category){
            if($category['id'] == $this->baseId) {
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
     * @param int $parentId
     * @param null $depth
     * @return array
     */
    public function sGetWholeCategoryTree($parentId = null, $depth = null)
    {
        if($parentId === null) {
            $parentId = $this->baseId;
        }

        $result = $this->repository
            ->getActiveChildrenByIdQuery($parentId, $this->customerGroupId, $depth)
            ->getArrayResult();

        $categories = array();
        foreach($result as $category){
            $url = ($category['category']['blog']) ? $this->blogBaseUrl : $this->baseUrl;
            $categories[$category['category']['id']] = array_merge($category['category'], array(
                'description' => $category['category']['name'],
                'childrenCount' => $category['childrenCount'],
                'media' => $category['category']['media'],
                'articleCount' => $category['articleCount'],
                'hidetop' => $category['category']['hideTop'],
                'link' => $category['category']['external'] ? : $url . $category['category']['id'],
            ));
        }

        $categories = $this->repository->buildTree(
            $categories,
            array('childrenField' => 'sub')
        );

        return $categories;
    }

    /**
     * Returns category level for the given category id
     *
     * @param int $id
     * @return int|null
     */
    public function sGetCategoryDepth($id)
    {
        $category = $this->repository->find($id);
        return $category !== null ? $category->getLevel() - 1 : null;
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
        $category = $this->repository
            ->getActiveByIdQuery($id, $this->customerGroupId)
            ->getArrayResult();
        if(empty($category[0])) {
            return null;
        }
        $category = $category[0];

        $detailUrl = $category['category']['blog'] ? $this->blogBaseUrl : $this->baseUrl;
        $detailUrl .= $category['category']['id'];

        $category = array_merge($category['category'], array(
            'description' => $category['category']['name'],
            'cmsheadline' => $category['category']['cmsHeadline'],
            'cmstext' => $category['category']['cmsText'],
            'metakeywords' => $category['category']['metaKeywords'],
            'metadescription' => $category['category']['metaDescription'],
            'noviewselect' => $category['category']['noViewSelect'],
            'childrenCount' => (int)$category['childrenCount'],
            'articleCount' => (int)$category['articleCount'],
            'sSelf' => $detailUrl,
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
     * @param int|null $parentId
     * @return array
     */
    public function sGetCategoryPath($id, $parentId = null)
    {
        if($parentId === null) {
            $parentId = $this->baseId;
        }
        $path = $this->repository->getPathById($id, 'id');
        foreach($path as $key => $value) {
            unset($path[$key]);
            if($value == $parentId) {
                break;
            }
        }
        return $path;
    }
}
