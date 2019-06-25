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

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Models\Article\Article;
use Shopware\Models\Category\Category;
use Shopware\Models\Media\Media;

/**
 * Backend Controller for the category backend module.
 * Displays all data in an Ext JS TreePanel and allows to delete,
 * add and edit items. On the detail page the category data is displayed and can be edited
 */
class Shopware_Controllers_Backend_Category extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var \Shopware\Models\Category\Repository
     */
    protected $repository;

    /**
     * @var \Shopware\Models\Customer\Repository
     */
    protected $customerRepository = null;

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Helper Method to get access to the category repository.
     *
     * @return Shopware\Models\Category\Repository
     */
    public function getRepository()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        if ($this->repository === null) {
            $this->repository = Shopware()->Models()->getRepository(Category::class);
        }

        return $this->repository;
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * @return \Shopware\Components\Model\CategoryDenormalization
     */
    public function getCategoryComponent()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return Shopware()->Container()->get('categorydenormalization');
    }

    /**
     * Reads all known categories into an array to show it in the category treepanel
     */
    public function getListAction()
    {
        /** @var array $filter */
        $filter = $this->Request()->getParam('filter', []);
        $node = (int) $this->Request()->getParam('node');
        $preselectedNodes = $this->Request()->getParam('preselected');

        if (empty($filter)) {
            $node = !empty($node) ? $node : 1;
            $filter[] = ['property' => 'c.parentId', 'value' => $node];
        }

        $query = $this->getRepository()->getBackendListQuery(
            $filter,
            $this->Request()->getParam('sort', []),
            $this->Request()->getParam('limit'),
            $this->Request()->getParam('start')
        )->getQuery();

        $count = Shopware()->Models()->getQueryCount($query);

        $data = $query->getArrayResult();

        foreach ($data as $key => $category) {
            $data[$key]['text'] = $category['name'];
            $data[$key]['cls'] = 'folder';
            $data[$key]['childrenCount'] = (int) $category['childrenCount'];
            $data[$key]['leaf'] = empty($data[$key]['childrenCount']);
            $data[$key]['allowDrag'] = true;
            if ($preselectedNodes !== null) {
                $data[$key]['checked'] = in_array($category['id'], $preselectedNodes);
            }
        }

        $this->View()->assign([
            'success' => true, 'data' => $data, 'total' => $count,
        ]);
    }

    /**
     * Controller action which can be accessed over an request.
     * This function adds the passed product ids which have to be in the "ids" parameter
     * to the passed category.
     */
    public function addCategoryArticlesAction()
    {
        $this->View()->assign(
            $this->addCategoryArticles(
                $this->Request()->getParam('categoryId'),
                json_decode($this->Request()->getParam('ids'))
            )
        );
    }

    /**
     * Controller action which can be accessed over an request.
     * This function adds the passed product ids which have to be in the "ids" parameter
     * to the passed category.
     */
    public function removeCategoryArticlesAction()
    {
        $this->View()->assign(
            $this->removeCategoryArticles(
                $this->Request()->getParam('categoryId'),
                json_decode($this->Request()->getParam('ids'))
            )
        );
    }

    /**
     * Gets all category detail information by the category node
     */
    public function getDetailAction()
    {
        $node = $this->Request()->getParam('node', 1);
        if ($node !== null) {
            $node = is_numeric($node) ? (int) $node : 1;
            $filter[] = ['property' => 'c.parentId', 'value' => $node];
        }
        $query = $this->getRepository()->getBackendDetailQuery($node)->getQuery();
        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $paginator = $this->getModelManager()->createPaginator($query);
        $data = $paginator->getIterator()->getArrayCopy();
        $data = $data[0];

        $data['imagePath'] = $data['media']['id'];

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Returns the whole category path by an category id
     */
    public function getPathByQueryAction()
    {
        $separator = $this->Request()->getParam('separator', '>');

        if (($ids = $this->Request()->getParam('id')) !== null) {
            $result = [];
            if (is_string($ids)) {
                $ids = explode(', ', $ids);
            }
            foreach ($ids as $id) {
                $result[] = $this->getRepository()->getPathById($id, 'name', $separator);
            }
        } else {
            $query = $this->Request()->getParam('query');
            $parents = (bool) $this->Request()->getParam('parents', true);
            $result = $this->getPathByQuery($query, $separator, $parents);
        }

        $data = [];

        if ($this->Request()->getParam('includeRoot', false)) {
            $data[] = ['id' => 1, 'name' => 'Shopware'];
        }

        foreach ($result as $id => $name) {
            $data[] = ['id' => $id, 'name' => $name];
        }

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => count($data)]);
    }

    /**
     * Reads the id paths for the given category ids
     */
    public function getIdPathAction()
    {
        $separator = $this->Request()->getParam('separator', '/');
        $categoryIds = (array) $this->Request()->getParam('categoryIds', []);

        $data = [];
        if (empty($categoryIds)) {
            $categoryIds = ['1'];
        }
        foreach ($categoryIds as $categoryId) {
            $data[] = $separator . $this->getRepository()->getPathById($categoryId, 'id', $separator);
        }

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => count($data)]);
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * helper method used in the getPathByQueryAction to return the path information
     *
     * @param int|string|null $query
     * @param string          $separator
     * @param bool            $parents
     *
     * @return array
     */
    public function getPathByQuery($query = null, $separator = '>', $parents = false)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        if (empty($query)) {
            $where = 'parent=1';
        } elseif (is_numeric($query)) {
            $where = 'parent=' . (int) $query;
        } else {
            $where = 'description LIKE ' . Shopware()->Db()->quote('%' . trim($query) . '%');
        }

        $paths = [];

        $sql = '
            SELECT id, description as name, parent
            FROM s_categories
            WHERE ' . $where . '
            ORDER BY parent DESC, name
        ';
        $result = Shopware()->Db()->fetchAll($sql);
        if (!empty($result)) {
            foreach ($result as $category) {
                if (!empty($query) && !is_numeric($query) && $category['parent'] != 1) {
                    $category['name'] = $this->getRepository()->getPathById($category['id'], 'name', $separator);
                }
                $children = $this->getPathByQuery($category['id'], $separator, $parents);
                if (empty($children) || $parents) {
                    $paths[$category['id']] = $category['name'];
                }
                if (!empty($children)) {
                    foreach ($children as $key => $child) {
                        $paths[$key] = $category['name'] . $separator . $child;
                    }
                }
            }
        }

        return $paths;
    }

    /**
     * Reads the Config categoryTemplate and returns the settings entries for the category templates
     */
    public function getTemplateSettingsAction()
    {
        $categoryTemplates = array_filter(explode(';', Shopware()->Config()->categoryTemplates));
        $data = [];
        foreach ($categoryTemplates as $templateConfigRaw) {
            list($template, $name) = explode(':', $templateConfigRaw);
            $data[] = ['template' => $template, 'name' => $name];
        }
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => count($data)]);
    }

    /**
     * Wrapper around the save method to have better ACL control
     */
    public function updateDetailAction()
    {
        $this->saveDetail();
    }

    /**
     * Wrapper around the save method to have better ACL control
     */
    public function createDetailAction()
    {
        $this->saveDetail();
    }

    /**
     * returns a JSON string to the view the products for the product mapping
     */
    public function getArticlesAction()
    {
        $categoryId = (int) $this->Request()->getParam('categoryId', 0);
        $offset = (int) $this->Request()->getParam('start', 0);
        $limit = (int) $this->Request()->getParam('limit', 20);
        $search = $this->Request()->getParam('search', '');
        $params = [
            'categoryId' => $categoryId,
        ];

        /** @var QueryBuilder $builder */
        $builder = $this->get('dbal_connection')->createQueryBuilder();
        $builder->select([
            'SQL_CALC_FOUND_ROWS articles.id as articleId',
            'articles.name',
            'details.ordernumber as number',
            'suppliers.name as supplierName',
        ]);
        $builder->from('s_articles', 'articles')
            ->leftJoin('articles', 's_articles_categories', 'categories', 'articles.id = categories.articleID AND categories.categoryID = :categoryId')
            ->join('articles', 's_articles_supplier', 'suppliers', 'articles.supplierID = suppliers.id')
            ->join('articles', 's_articles_details', 'details', 'articles.main_detail_id = details.id')
            ->andWhere('categories.categoryID IS NULL');

        if (!empty($search)) {
            $builder->andWhere('(articles.name LIKE :search OR details.ordernumber LIKE :search OR suppliers.name LIKE :search)');
            $params['search'] = '%' . $search . '%';
        }

        $builder->setFirstResult($offset);
        $builder->setMaxResults($limit);
        $builder->setParameters($params);
        $result = $builder->execute()->fetchAll();

        $count = $this->get('dbal_connection')->fetchColumn('SELECT FOUND_ROWS()');

        $this->View()->assign([
            'success' => true,
            'data' => $result,
            'total' => (int) $count,
        ]);
    }

    /**
     * Controller action which is used to get a paginated
     * list of all assigned category products.
     */
    public function getCategoryArticlesAction()
    {
        $categoryId = (int) $this->Request()->getParam('categoryId');
        $offset = (int) $this->Request()->getParam('start', 0);
        $limit = (int) $this->Request()->getParam('limit', 20);
        $search = $this->Request()->getParam('search', '');

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select([
            'articles.id as articleId',
            'articles.name',
            'details.number',
            'suppliers.name as supplierName',
        ]);
        $builder->from(Article::class, 'articles')
            ->innerJoin('articles.categories', 'categories')
            ->innerJoin('articles.supplier', 'suppliers')
            ->innerJoin('articles.mainDetail', 'details')
            ->where('categories.id = :categoryId')
            ->setParameter('categoryId', $categoryId);

        if (!empty($search)) {
            $builder->andWhere('(articles.name LIKE :search OR suppliers.name LIKE :search OR details.number LIKE :search)');
            $builder->setParameter('search', '%' . $search . '%');
        }

        $builder->setFirstResult($offset)
            ->setMaxResults($limit);

        $query = $builder->getQuery();

        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $paginator = $this->getModelManager()->createPaginator($query);

        $data = $paginator->getIterator()->getArrayCopy();
        $count = $paginator->count();

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'total' => $count,
        ]);
    }

    /**
     * returns a JSON string to the view the customers for the customer group mapping
     */
    public function getCustomerGroupsAction()
    {
        $usedIds = $this->Request()->usedIds;

        $offset = (int) $this->Request()->getParam('start');
        $limit = (int) $this->Request()->getParam('limit', 20);

        /** @var \Shopware\Models\Customer\Repository $customerRepository */
        $customerRepository = $this->getCustomerRepository();
        $dataQuery = $customerRepository->getCustomerGroupsWithoutIdsQuery($usedIds, $offset, $limit);

        $total = Shopware()->Models()->getQueryCount($dataQuery);
        $data = $dataQuery->getArrayResult();

        // Return the data and total count
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
    }

    /**
     * moves a category by the categoryId under a new parent node
     */
    public function moveTreeItemAction()
    {
        $itemId = (int) $this->Request()->getParam('id');
        $parentId = (int) $this->Request()->getParam('parentId', 1);

        /** @var Category|null $item */
        $item = $this->getRepository()->find($itemId);
        if ($item === null) {
            $this->View()->assign([
                'success' => false,
                'message' => "Category by id $itemId not found",
            ]);

            return;
        }

        /** @var Category|null $parent */
        $parent = $this->getRepository()->find($parentId);
        if ($parent === null) {
            $this->View()->assign([
                'success' => false,
                'message' => "Parent by id $parentId not found",
            ]);

            return;
        }

        $needsRebuild = false;

        if ($item->getParent()->getId() != $parent->getId()) {
            $item->setParent($parent);

            $parents = $this->getCategoryComponent()->getParentCategoryIds($parentId);

            $path = implode('|', $parents);
            if (empty($path)) {
                $path = null;
            } else {
                $path = '|' . $path . '|';
            }

            $item->internalSetPath($path);

            $batchModeEnabled = Shopware()->Config()->get('moveBatchModeEnabled');

            if ($item->isLeaf() || !$batchModeEnabled) {
                $needsRebuild = false;
            } else {
                Shopware()->Container()->get('categorysubscriber')->disableForNextFlush();
                $needsRebuild = true;
            }

            Shopware()->Models()->flush($item);
        }

        $this->View()->assign([
            'success' => true,
            'needsRebuild' => $needsRebuild,
        ]);
    }

    /**
     * saves the positions of all children of this parent category
     */
    public function saveNewChildPositionsAction()
    {
        $ids = json_decode($this->Request()->getParam('ids'));
        foreach ($ids as $key => $categoryId) {
            /** @var Category $category */
            $category = Shopware()->Models()->getReference(Category::class, (int) $categoryId);
            $category->setPosition($key);
        }
        Shopware()->Models()->flush();

        $this->View()->assign(['success' => true]);
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Saves a single category. If no category id is passed,
     * the save function will create a new category model and persist
     * it.
     *
     * To successful saving a category a parent category id must supplied.
     */
    public function saveDetail()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $params = $this->Request()->getParams();
        $categoryId = (int) $params['id'];

        if (empty($categoryId)) {
            $categoryModel = new Category();
            Shopware()->Models()->persist($categoryModel);

            // Find parent for newly created category
            $params['parentId'] = is_numeric($params['parentId']) ? (int) $params['parentId'] : 1;
            /** @var Category $parentCategory */
            $parentCategory = $this->getRepository()->find($params['parentId']);
            $categoryModel->setParent($parentCategory);

            // If Leaf-Category gets childcategory move all assignments to new childcategory
            if ($parentCategory->getChildren()->count() === 0 && $parentCategory->getArticles()->count() > 0) {
                /** @var Article $product */
                foreach ($parentCategory->getArticles() as $product) {
                    $product->removeCategory($parentCategory);
                    $product->addCategory($categoryModel);
                }
            }
        } else {
            $categoryModel = $this->getRepository()->find($categoryId);
        }

        $categoryModel->setStream(null);
        if ($params['streamId']) {
            $params['stream'] = Shopware()->Models()->find(\Shopware\Models\ProductStream\ProductStream::class, (int) $params['streamId']);
        }

        $params = $this->prepareCustomerGroupsAssociatedData($params);
        $params = $this->prepareMediaAssociatedData($params);

        unset($params['articles'], $params['emotion'], $params['imagePath'], $params['parentId'], $params['parent']);

        if (!array_key_exists('template', $params)) {
            $params['template'] = null;
        }

        $params['changed'] = new \DateTime();
        $categoryModel->fromArray($params);
        $categoryModel->setShops($this->Request()->getParam('shops'));
        Shopware()->Models()->flush();

        $categoryId = $categoryModel->getId();
        $query = $this->getRepository()->getBackendDetailQuery($categoryId)->getQuery();
        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $paginator = $this->getModelManager()->createPaginator($query);
        $data = $paginator->getIterator()->getArrayCopy();
        $data = $data[0];
        $data['imagePath'] = $data['media']['path'];

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => count($data)]);
    }

    /**
     * Deletes a category and its children
     */
    public function deleteAction()
    {
        if (!($id = $this->Request()->getParam('id'))) {
            $this->View()->assign(['success' => false, 'message' => 'No valid form Id']);

            return;
        }
        $result = $this->getRepository()->find($id);
        if (!$result) {
            $this->View()->assign(['success' => false, 'message' => 'Category not found']);

            return;
        }

        // Doctrine removes all child-categories and assignments of parent and child categories
        Shopware()->Models()->remove($result);
        Shopware()->Models()->flush();

        $this->View()->assign(['success' => true]);
    }

    public function getRebuildCategoryPathCountAction()
    {
        $categoryId = $this->Request()->getParam('categoryId');

        $count = $this->getCategoryComponent()->rebuildCategoryPathCount($categoryId);

        $this->view->assign([
            'success' => true,
            'data' => [
                'count' => $count,
                'batchSize' => 20,
            ],
        ]);
    }

    public function rebuildCategoryPathAction()
    {
        // Try to set maximum execution time
        @set_time_limit(0);

        $categoryId = $this->Request()->getParam('categoryId');
        $offset = (int) $this->Request()->getParam('offset');
        $count = $this->Request()->getParam('limit');

        $this->getCategoryComponent()->rebuildCategoryPath($categoryId, $count, $offset);

        $this->view->assign([
            'success' => true,
        ]);
    }

    public function getRemoveOldAssignmentsCountAction()
    {
        $categoryId = $this->Request()->getParam('categoryId');

        $count = $this->getCategoryComponent()->removeOldAssignmentsCount($categoryId);

        $this->view->assign([
            'success' => true,
            'data' => [
                'count' => $count,
                'batchSize' => 1,
            ],
        ]);
    }

    public function removeOldAssignmentsAction()
    {
        // Try to set maximum execution time
        @set_time_limit(0);

        $categoryId = $this->Request()->getParam('categoryId');
        $offset = $this->Request()->getParam('offset');
        $count = $this->Request()->getParam('limit');

        $this->getCategoryComponent()->removeOldAssignments($categoryId, $count, $offset);

        $this->view->assign([
            'success' => true,
        ]);
    }

    public function getRebuildAssignmentsCountAction()
    {
        $categoryId = $this->Request()->getParam('categoryId');

        $count = $this->getCategoryComponent()->rebuildAssignmentsCount($categoryId);

        $this->view->assign([
            'success' => true,
            'data' => [
                'count' => $count,
                'batchSize' => 1,
            ],
        ]);
    }

    public function rebuildAssignmentsAction()
    {
        // Try to set maximum execution time
        @set_time_limit(0);

        $categoryId = $this->Request()->getParam('categoryId');
        $offset = $this->Request()->getParam('offset');
        $count = $this->Request()->getParam('limit');

        $this->getCategoryComponent()->rebuildAssignments($categoryId, $count, $offset);

        $this->view->assign([
            'success' => true,
        ]);
    }

    /**
     * Returns the number of categories that exist under the given one
     */
    public function getCategoryTreeCountAction()
    {
        $categoryId = $this->Request()->getParam('categoryId');

        $count = $this->getRepository()->getChildrenCountList($categoryId);

        $this->view->assign(
            [
                'success' => true,
                'data' => [
                    'count' => $count,
                    'batchSize' => 1,
                ],
            ]
        );
    }

    /**
     * Duplicates the given categories into the new parent
     */
    public function duplicateCategoryAction()
    {
        /** @var \Shopware\Components\CategoryHandling\CategoryDuplicator $categoryDuplicator */
        $categoryDuplicator = $this->get('CategoryDuplicator');

        $copyProductAssociations = $this->Request()->getParam('reassignArticleAssociations');
        $categoryIds = $this->Request()->getParam('children');
        if (!is_array($categoryIds)) {
            $categoryIds = [$categoryIds];
        }
        $newParentId = (int) $this->Request()->getParam('categoryId');
        $newParentId = $newParentId === 0 ? 1 : $newParentId;
        $result = [];

        foreach ($categoryIds as $categoryId) {
            $newCategoryId = $categoryDuplicator->duplicateCategory(
                $categoryId,
                $newParentId,
                $copyProductAssociations
            );

            $childrenStmt = $this->get('db')->prepare('SELECT id FROM s_categories WHERE parent = :parent');
            $childrenStmt->execute([':parent' => $categoryId]);
            $children = $childrenStmt->fetchAll(\PDO::FETCH_COLUMN);

            if (count($children)) {
                $result[] = [
                    'categoryId' => $newCategoryId,
                    'children' => $children,
                ];
            }
        }

        $this->view->assign(
            [
                'success' => true,
                'processed' => count($categoryIds),
                'needsRebuild' => true,
                'result' => $result,
            ]
        );
    }

    /**
     * Method to define acl dependencies in backend controllers
     * <code>
     * $this->addAclPermission("name_of_action_with_action_prefix","name_of_assigned_privilege","optionally error message");
     * // $this->addAclPermission("indexAction","read","Ops. You have no permission to view that...");
     * </code>
     */
    protected function initAcl()
    {
        // Read
        $this->addAclPermission('getList', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getDetail', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getPathByQuery', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getIdPath', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getTemplateSettings', 'read', 'Insufficient Permissions');

        // Update
        $this->addAclPermission('updateDetail', 'update', 'Insufficient Permissions');

        // Delete
        $this->addAclPermission('delete', 'delete', 'Insufficient Permissions');

        // Create
        $this->addAclPermission('createDetail', 'create', 'Insufficient Permissions');
    }

    /**
     * Internal function which is used to remove the passed product ids
     * from the assigned category.
     *
     * @param int   $categoryId
     * @param array $articleIds
     *
     * @return array
     */
    protected function removeCategoryArticles($categoryId, $articleIds)
    {
        if (empty($articleIds)) {
            return ['success' => false, 'error' => 'No articles selected'];
        }

        if (empty($categoryId)) {
            return ['success' => false, 'error' => 'No category id passed.'];
        }

        /** @var Category $category */
        $category = Shopware()->Models()->getReference(Category::class, (int) $categoryId);

        $counter = 0;
        foreach ($articleIds as $productId) {
            if (empty($productId)) {
                continue;
            }

            /** @var Article $product */
            $product = Shopware()->Models()->getReference(Article::class, (int) $productId);
            $product->removeCategory($category);

            ++$counter;
        }

        Shopware()->Models()->flush();

        return ['success' => true, 'counter' => $counter];
    }

    /**
     * Helper function to add multiple products to an category.
     *
     * @param int   $categoryId
     * @param array $articleIds
     *
     * @return array
     */
    protected function addCategoryArticles($categoryId, $articleIds)
    {
        if (empty($articleIds)) {
            return ['success' => false, 'error' => 'No products selected'];
        }

        if (empty($categoryId)) {
            return ['success' => false, 'error' => 'No category id passed.'];
        }

        /** @var Category $category */
        $category = Shopware()->Models()->getReference(Category::class, (int) $categoryId);

        $counter = 0;
        foreach ($articleIds as $productId) {
            if (empty($productId)) {
                continue;
            }

            /** @var Article $product */
            $product = Shopware()->Models()->getReference(Article::class, (int) $productId);
            $product->addCategory($category);

            ++$counter;
        }

        Shopware()->Models()->flush();

        return ['success' => true, 'counter' => $counter];
    }

    /**
     * This method finds the mediaId by the media path to save it in the right way
     *
     * @param array $data
     *
     * @return array
     */
    protected function prepareMediaAssociatedData($data)
    {
        if (!empty($data['imagePath'])) {
            $data['media'] = $this->get('models')->find(Media::class, $data['imagePath']);
        } else {
            $data['media'] = null;
        }

        return $data;
    }

    /**
     * Helper Method to get access to the customer repository.
     *
     * @return \Shopware\Models\Article\Repository
     */
    private function getCustomerRepository()
    {
        if ($this->customerRepository === null) {
            $this->customerRepository = Shopware()->Models()->getRepository(\Shopware\Models\Customer\Customer::class);
        }

        return $this->customerRepository;
    }

    /**
     * This method loads the customer group models for the passed ids in the "customerGroups" parameter.
     *
     * @param array $data
     *
     * @return array
     */
    private function prepareCustomerGroupsAssociatedData($data)
    {
        $customerGroups = [];
        foreach ($data['customerGroups'] as $customerGroupData) {
            if (!empty($customerGroupData['id'])) {
                $model = Shopware()->Models()->find(\Shopware\Models\Customer\Group::class, $customerGroupData['id']);
                $customerGroups[] = $model;
            }
        }
        $data['customerGroups'] = $customerGroups;

        return $data;
    }
}
