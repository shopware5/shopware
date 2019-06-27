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

use Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Category;

/**
 * Shopware Class that handles categories
 */
class sCategories implements \Enlight_Hook
{
    /**
     * @deprecated in 5.6, will be removed in 5.7 without replacement
     */
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
     *
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
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * @var CategoryServiceInterface
     */
    private $categoryService;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var Enlight_Controller_Front
     */
    private $frontController;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->db = Shopware()->Container()->get('db');
        $this->config = Shopware()->Container()->get('config');
        $this->manager = Shopware()->Container()->get('models');
        $this->repository = $this->manager->getRepository('Shopware\Models\Category\Category');
        $this->baseUrl = $this->config->get('baseFile') . '?sViewport=cat&sCategory=';
        $this->blogBaseUrl = $this->config->get('baseFile') . '?sViewport=blog&sCategory=';
        $this->baseId = (int) Shopware()->Shop()->get('parentID');
        $this->customerGroupId = (int) Shopware()->Modules()->System()->sUSERGROUPDATA['id'];
        $this->connection = Shopware()->Container()->get('dbal_connection');
        $this->categoryService = Shopware()->Container()->get('shopware_storefront.category_service');
        $this->contextService = Shopware()->Container()->get('shopware_storefront.context_service');
        $this->frontController = Shopware()->Container()->get('front');
    }

    /**
     * Returns the category tree from the root until the category
     * with the provided id. Also loads siblings for elements in the
     * category path.
     *
     * @param int $id Id of the category to load
     *
     * @return array Tree of categories
     */
    public function sGetCategories($id)
    {
        $pathIds = $this->getCategoryPath($id);

        $grouped = $this->getCategoryIdsWithParent($pathIds);

        $ids = array_merge($pathIds, array_keys($grouped));

        $context = $this->contextService->getShopContext();

        $categories = $this->categoryService->getList($ids, $context);

        unset($grouped[$this->baseId]);

        $tree = $this->buildTree($grouped, $this->baseId);

        $result = $this->assignCategoriesToTree(
            $categories,
            $tree,
            $pathIds,
            $this->getChildrenCountOfCategories($ids)
        );

        return $result;
    }

    /**
     * @param array $childrenCounts
     *
     * @return array
     */
    public function convertCategory(Category $category, $childrenCounts)
    {
        $childrenCount = 0;
        if (isset($childrenCounts[$category->getId()])) {
            $childrenCount = $childrenCounts[$category->getId()];
        }

        $url = $category->isBlog() ? $this->blogBaseUrl : $this->baseUrl;

        $attribute = [];
        foreach ($category->getAttributes() as $struct) {
            $attribute = array_merge($attribute, $struct->toArray());
        }

        $media = [];
        if ($category->getMedia()) {
            $media = [
                'id' => $category->getMedia()->getId(),
                'name' => $category->getMedia()->getName(),
                'description' => $category->getMedia()->getDescription(),
                'path' => $category->getMedia()->getFile(),
                'type' => $category->getMedia()->getType(),
                'extension' => $category->getMedia()->getExtension(),
            ];
        }

        $path = $category->getPath() ? '|' . implode('|', $category->getPath()) . '|' : '';

        return [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'metaKeywords' => $category->getMetaKeywords(),
            'metaDescription' => $category->getMetaDescription(),
            'cmsHeadline' => $category->getCmsHeadline(),
            'cmsText' => $category->getCmsText(),
            'active' => true,
            'template' => $category->getTemplate(),
            'blog' => $category->isBlog(),
            'path' => $path,
            'external' => $category->getExternalLink(),
            'externalTarget' => $category->getExternalTarget(),
            'hideFilter' => !$category->displayFacets(),
            'hideTop' => !$category->displayInNavigation(),
            'hidetop' => !$category->displayInNavigation(),
            'attribute' => $attribute,
            'media' => $media,
            'description' => $category->getName(),
            'link' => $category->getExternalLink() ?: $url . $category->getId(),
            'flag' => false,
            'subcategories' => [],
            'childrenCount' => $childrenCount,
        ];
    }

    /**
     * Returns the leaf category to which the
     * product belongs, inside the category subtree.
     *
     * @param int $articleId Id of the product to look for
     * @param int $parentId  Category subtree root id. If null, the shop category is used.
     *
     * @return int id of the leaf category, or 0 if none found
     */
    public function sGetCategoryIdByArticleId($articleId, $parentId = null, $shopId = null)
    {
        if ($parentId === null) {
            $parentId = $this->baseId;
        }
        if ($shopId === null) {
            $shopId = Shopware()->Shop()->getId();
        }

        $id = (int) $this->db->fetchOne(
            'SELECT category_id
             FROM s_articles_categories_seo
             WHERE article_id = :articleId
             AND shop_id = :shopId',
            [':articleId' => $articleId, ':shopId' => $shopId]
        );

        if ($id) {
            return $id;
        }

        $sql = '
           SELECT ac.categoryID AS id
            FROM s_articles_categories ac
                INNER JOIN s_categories c
                    ON  ac.categoryID = c.id
                    AND c.active = 1
                    AND c.path LIKE ?
                LEFT JOIN s_categories c2
                    ON c2.parent = c.id
            WHERE ac.articleID = ?
            AND c2.id IS NULL
            ORDER BY ac.id
            LIMIT 1
        ';

        $id = (int) $this->db->fetchOne($sql, [
            '%|' . $parentId . '|%',
            $articleId,
        ]);

        return $id;
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
     * @param int $id Id of the category
     *
     * @return array Array of categories in path
     */
    public function sGetCategoriesByParent($id)
    {
        $pathCategories = $this->repository->getPathById($id, ['id', 'name', 'blog']);

        $pathCategories = array_reverse($pathCategories);

        $categories = [];
        foreach ($pathCategories as $category) {
            if ($category['id'] == $this->baseId) {
                break;
            }

            $url = ($category['blog']) ? $this->blogBaseUrl : $this->baseUrl;
            $category['link'] = $url . $category['id'];
            $categories[] = $category;
        }

        return $categories;
    }

    /**
     * Return a the category subtree for the given root
     *
     * @param int $parentId Id of the root category, defaults to the current shop category
     * @param int $depth    Depth to use, defaults to null (unlimited depth)
     * @param int $shopId   Needed for shop limitation
     *
     * @return array Category tree for the provided args
     */
    public function sGetWholeCategoryTree($parentId = null, $depth = null, $shopId = null)
    {
        if ($parentId === null) {
            $parentId = $this->baseId;
        }

        $result = $this->repository->getActiveChildrenTree($parentId, $this->customerGroupId, $depth, $shopId);
        $result = $this->mapCategoryTree($result);

        return $result;
    }

    /**
     * Returns category content for the given category id
     *
     * @param int $id
     *
     * @return array|null
     */
    public function sGetCategoryContent($id)
    {
        if (!$id) {
            $id = $this->baseId;
        }

        $context = $this->contextService->getShopContext();
        $category = Shopware()->Container()->get('shopware_storefront.category_service')->get($id, $context);
        if (empty($category)) {
            return null;
        }

        return Shopware()->Container()->get('legacy_struct_converter')->convertCategoryStruct($category);
    }

    /**
     * @param int $categoryId
     *
     * @return int|string
     */
    public function getProductBoxLayout($categoryId)
    {
        $category = $this->repository->find($categoryId);

        if (!$category) {
            return 'basic';
        }

        if ($category->getProductBoxLayout() !== 'extend' && $category->getProductBoxLayout() !== null) {
            return $category->getProductBoxLayout();
        }

        while (null !== $parent = $category->getParent()) {
            $category = $parent;

            if ($category->getProductBoxLayout() !== 'extend' && $category->getProductBoxLayout() !== null) {
                return $category->getProductBoxLayout();
            }
        }

        return 'basic';
    }

    /**
     * Returns the category path from root to the given category id
     *
     * @param int      $id       Category id
     * @param int|null $parentId If provided
     *
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

    /**
     * Loads category details from db
     *
     * @param int $id Id of the category to load
     *
     * @return array Category details
     */
    protected function sGetCategoriesByParentId($id)
    {
        $categories = $this->repository
            ->getActiveByParentIdQuery($id, $this->customerGroupId)
            ->getArrayResult();
        $resultCategories = [];
        foreach ($categories as $category) {
            $url = $category['category']['blog'] ? $this->blogBaseUrl : $this->baseUrl;
            $resultCategories[$category['category']['id']] = array_merge($category['category'], [
                'description' => $category['category']['name'],
                'childrenCount' => $category['childrenCount'],
                'articleCount' => $category['articleCount'],
                'hidetop' => $category['category']['hideTop'],
                'subcategories' => [],
                'link' => $category['category']['external'] ?: $url . $category['category']['id'],
                'flag' => false,
            ]);
        }

        return $resultCategories;
    }

    /**
     * @param array $categories
     *
     * @return array
     */
    protected function mapCategoryTree($categories)
    {
        foreach ($categories as &$category) {
            $url = ($category['blog']) ? $this->blogBaseUrl : $this->baseUrl;
            $category['description'] = $category['name'];
            $category['link'] = $category['external'] ?: $url . $category['id'];
            $category['hidetop'] = $category['hideTop'];
            if ($category['sub']) {
                $category['sub'] = $this->mapCategoryTree($category['sub']);
            }
        }

        return $categories;
    }

    /**
     * Returns a key value array which contains the category id
     * as key and the count of category children as value.
     *
     * @param int[] $ids
     *
     * @return array
     */
    private function getChildrenCountOfCategories($ids)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select(['parent as id', 'COUNT(id) as childrenCount']);
        $query->from('s_categories', 'category')
            ->where('parent IN ( :ids )')
            ->andWhere('category.active = 1')
            ->groupBy('parent')
            ->setParameter(':ids', $ids, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);

        /** @var PDOStatement $statement */
        $statement = $query->execute();

        return $statement->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Returns a associated array with the category id and the parent id
     * of the category.
     * The category id is used as array key and the parent id as array value.
     *
     * @param int[] $ids
     *
     * @return array
     */
    private function getCategoryIdsWithParent($ids)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['category.id', 'category.parent']);
        $shopId = $this->contextService->getShopContext()->getShop()->getId();

        $query->from('s_categories', 'category')
            ->where('(category.parent IN( :parentId ) OR category.id IN ( :parentId ))')
            ->andWhere('category.active = 1')
            ->andWhere('category.shops IS NULL OR category.shops LIKE :shopId')
            ->orderBy('category.position', 'ASC')
            ->addOrderBy('category.id')
            ->setParameter(':parentId', $ids, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
            ->setParameter(':shopId', '%|' . $shopId . '|%');

        /** @var PDOStatement $statement */
        $statement = $query->execute();

        return $statement->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Returns all ids, additionally with the provided one,
     * of the category path of the provided id.
     *
     * @param int $id
     *
     * @return array
     */
    private function getCategoryPath($id)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['category.path'])
            ->from('s_categories', 'category')
            ->where('category.id = :id')
            ->setParameter(':id', $id);

        /** @var PDOStatement $statement */
        $statement = $query->execute();

        $path = $statement->fetch(PDO::FETCH_COLUMN);

        $ids = [$id];

        if (!$path) {
            return $ids;
        }

        $pathIds = explode('|', $path);

        return array_filter(array_merge($ids, $pathIds));
    }

    /**
     * Creates a nested category id tree.
     *
     * @param array $associated Contains a id => parentId array
     * @param int   $parentId
     *
     * @return array
     */
    private function buildTree($associated, $parentId)
    {
        $categories = [];
        foreach ($associated as $id => $parent) {
            if ($parentId == $parent) {
                unset($associated[$id]);

                $categories[$id] = $this->buildTree(
                    $associated,
                    $id
                );
            }
        }

        return $categories;
    }

    /**
     * Assigns the provided categories to the nested tree structure.
     *
     * @param array $categories
     * @param array $tree
     * @param array $activePath
     * @param array $childrenCounts
     *
     * @return array
     */
    private function assignCategoriesToTree($categories, $tree, $activePath, $childrenCounts)
    {
        $result = [];
        foreach ($tree as $categoryId => $children) {
            if (!isset($categories[$categoryId])) {
                continue;
            }

            $category = $this->convertCategory(
                $categories[$categoryId],
                $childrenCounts
            );

            if (!empty($children)) {
                $category['subcategories'] = $this->assignCategoriesToTree(
                    $categories,
                    $children,
                    $activePath,
                    $childrenCounts
                );
            }

            $category['flag'] = in_array($categoryId, $activePath);

            $result[$categoryId] = $category;
        }

        return $result;
    }
}
