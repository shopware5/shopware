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
 * @package    Shopware_Controllers
 * @subpackage Backend
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Shopware Categories
 *
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
     * @var \Shopware\Models\Article\Repository
     */
    protected $articleRepository = null;

    /**
     * @var \Shopware\Models\Media\Repository
     */
    protected $mediaRepository = null;

    /**
     * @var \Shopware\Models\Customer\Repository
     */
    protected $customerRepository = null;

    /**
     * Method to define acl dependencies in backend controllers
     * <code>
     * $this->addAclPermission("name_of_action_with_action_prefix","name_of_assigned_privilege","optionally error message");
     * // $this->addAclPermission("indexAction","read","Ops. You have no permission to view that...");
     * </code>
     */
    protected function initAcl()
    {
        // read
        $this->addAclPermission('getList', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getDetail', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getPathByQuery', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getIdPath', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getTemplateSettings', 'read', 'Insufficient Permissions');

        // update
        $this->addAclPermission('updateDetail', 'update', 'Insufficient Permissions');

        //delete
        $this->addAclPermission('delete', 'delete', 'Insufficient Permissions');

        // create
        $this->addAclPermission('createDetail', 'create', 'Insufficient Permissions');
    }

    /**
     * Helper Method to get access to the category repository.
     *
     * @return Shopware\Models\Category\Repository
     */
    public function getRepository()
    {
        if ($this->repository === null) {
            $this->repository = Shopware()->Models()->getRepository('Shopware\Models\Category\Category');
        }
        return $this->repository;
    }

    /**
     * Helper Method to get access to the article repository.
     *
     * @return \Shopware\Models\Article\Repository
     */
    private function getArticleRepository()
    {
        if ($this->articleRepository === null) {
            $this->articleRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Article');
        }
        return $this->articleRepository;
    }

    /**
     * Helper Method to get access to the media repository.
     *
     * @return \Shopware\Models\Media\Repository
     */
    private function getMediaRepository()
    {
        if ($this->mediaRepository === null) {
            $this->mediaRepository = Shopware()->Models()->getRepository('Shopware\Models\Media\Media');
        }
        return $this->mediaRepository;
    }

    /**
     * Helper Method to get access to the customer repository.
     *
     * @return \Shopware\Models\Article\Repository
     */
    private function getCustomerRepository()
    {
        if ($this->customerRepository === null) {
            $this->customerRepository = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer');
        }
        return $this->customerRepository;
    }

    /**
     * To recover the tree panel
     */
    public function indexAction()
    {
        if ($this->getRepository()->verify() !== true) {
            $this->fixTree();
        }
        parent::indexAction();
    }

    /**
     * Fix category tree
     */
    public function fixTree()
    {
        set_time_limit(360);
        $db = Shopware()->Db();
        $db->beginTransaction();

        $sql = 'UPDATE s_categories c SET c.left = 0, c.right = 0, c.level = 0';
        $db->exec($sql);
        $sql = 'UPDATE s_categories c SET c.left = 1, c.right = 2 WHERE c.id = 1';
        $db->exec($sql);

        $categoryIds = array(1);
        while(($categoryId = array_shift($categoryIds)) !== null) {
            $sql = 'SELECT c.right, c.level FROM s_categories c WHERE c.id = :categoryId';
            $query = $db->prepare($sql);
            $query->execute(array('categoryId' => $categoryId));
            list($right, $level) = $query->fetch(Zend_Db::FETCH_NUM);
            $sql = 'SELECT c.id FROM s_categories c WHERE c.parent = :categoryId ORDER BY position, id';
            $query = $db->prepare($sql);
            $query->execute(array('categoryId' => $categoryId));
            $childrenIds = $query->fetchAll(Zend_Db::FETCH_COLUMN);
            if(empty($childrenIds)) {
                continue;
            }
            foreach($childrenIds as $childrenId) {
                $sql = 'UPDATE s_categories c SET c.right = c.right + 2 WHERE c.right >= :right';
                $db->prepare($sql)->execute(array('right' => $right));
                $sql = 'UPDATE s_categories c SET c.left = c.left + 2 WHERE c.left > :right';
                $db->prepare($sql)->execute(array('right' => $right));
                $sql = '
                    UPDATE s_categories c
                    SET c.left = :right, c.right = :right + 1, c.level = :level + 1
                    WHERE c.id = :childrenId
                ';
                $db->prepare($sql)->execute(array(
                    'right' => $right, 'level' => $level,
                    'childrenId' => $childrenId
                ));
                $right += 2;
            }
            $categoryIds = array_merge($childrenIds, $categoryIds);
        }

        $sql = 'DELETE c FROM s_categories c WHERE c.left = 0';
        $db->exec($sql);

        $db->commit();
    }

    /**
     * Reads all known categories into an array to show it in the category treepanel
     */
    public function getListAction()
    {
        /** @var $filter array */
        $filter = $this->Request()->getParam('filter', array());
        $node = (int)$this->Request()->getParam('node');
        $preselectedNodes = $this->Request()->getParam('preselected');

        if (empty($filter)) {
            $node = !empty($node) ? $node : 1;
            $filter[] = array('property' => 'c.parentId', 'value' => $node);
        }

        $query = $this->getRepository()->getListQuery(
            $filter,
            $this->Request()->getParam('sort', array()),
            $this->Request()->getParam('limit', null),
            $this->Request()->getParam('start'),
            false
        );

        $count = Shopware()->Models()->getQueryCount($query);

        $data = $query->getArrayResult();

        foreach ($data as $key => $category) {
            $data[$key]['text'] = $category['name'];
            $data[$key]['cls'] = 'folder';
            $data[$key]['childrenCount'] = (int)$category['childrenCount'];
            $data[$key]['leaf'] = empty($data[$key]['childrenCount']);
            $data[$key]['allowDrag'] = true;
            if ($preselectedNodes !== null) {
                $data[$key]['checked'] = in_array($category['id'], $preselectedNodes);
            }
        }

        $this->View()->assign(array(
            'success' => true, 'data' => $data, 'total' => $count
        ));
    }

    /**
     * Gets all category detail information by the category node
     */
    public function getDetailAction()
    {
        $node = $this->Request()->getParam('node', 1);
        if ($node !== null) {
            $node = is_numeric($node) ? (int)$node : 1;
            $filter[] = array('property' => 'c.parentId', 'value' => $node);
        }
        $query = $this->getRepository()->getDetailQuery($node);
        $data = $query->getOneOrNullResult(Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $data["imagePath"] = $data["media"]["path"];

        $this->View()->assign(array('success' => true, 'data' => $data));
    }

    /**
     * Returns the whole category path by an category id
     */
    public function getPathByQueryAction()
    {
        $separator = $this->Request()->getParam('separator', '>');

        if (($ids = $this->Request()->getParam('id')) !== null) {
            $result = array();
            if (is_string($ids)) {
                $ids = explode(', ', $ids);
            }
            foreach ($ids as $id) {
                $result[] = $this->getRepository()->getPathById($id, 'name', $separator);
            }
        } else {
            $query = $this->Request()->getParam('query');
            $parents = (bool)$this->Request()->getParam('parents', false);
            $result = $this->getPathByQuery($query, $separator, $parents);
        }

        $data = array();
        foreach ($result as $id => $name) {
            $data[] = array('id' => $id, 'name' => $name);
        }

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => count($data)));
    }

    /**
     * Reads the id paths for the given category ids
     */
    public function getIdPathAction()
    {
        $separator = $this->Request()->getParam('separator', '/');
        $categoryIds = (array)$this->Request()->getParam('categoryIds', array());

        $data = array();
        if (empty($categoryIds)) {
            $categoryIds = array('1');
        }
        foreach ($categoryIds as $categoryId) {
            $data[] = $separator . $this->getRepository()->getPathById($categoryId, 'id', $separator);
        }

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => count($data)));
    }

    /**
     * helper method used in the getPathByQueryAction to return the path information
     *
     * @param null $query
     * @param string $separator
     * @param bool $parents
     * @return array
     */
    function getPathByQuery($query = null, $separator = '>', $parents = false)
    {
        if (empty($query)) {
            $where = 'parent=1';
        } elseif (is_numeric($query)) {
            $where = 'parent=' . (int)$query;
        } else {
            $where = 'description LIKE ' . Shopware()->Db()->quote('%' . trim($query) . '%');
        }

        $paths = array();

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
        $categoryTemplates = explode(';', Shopware()->Config()->categoryTemplates);
        $data = array();
        foreach ($categoryTemplates as $templateConfigRaw) {
            list($template, $name) = explode(':', $templateConfigRaw);
            $data[] = array('template' => $template, 'name' => $name);
        }
        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => count($data)));
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
     * returns a JSON string to the view the articles for the article mapping
     *
     * @return void
     */
    public function getArticlesAction()
    {
        $usedIds = $this->Request()->usedIds;

        $offset = $this->Request()->getParam('start', null);
        $limit = $this->Request()->getParam('limit', 20);

        $dataQuery = $this->getArticleRepository()
            ->getArticlesWithExcludedIdsQuery($usedIds, $this->Request()->getParam('filter', array()), $offset, $limit);

        $total = Shopware()->Models()->getQueryCount($dataQuery);
        $data = $dataQuery->getArrayResult();

        //return the data and total count
        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
    }

    /**
     * returns a JSON string to the view the customers for the customer group mapping
     *
     * @return void
     */
    public function getCustomerGroupsAction()
    {
        $usedIds = $this->Request()->usedIds;

        $offset = $this->Request()->getParam('start', null);
        $limit = $this->Request()->getParam('limit', 20);

        /** @var $customerRepository \Shopware\Models\Customer\Repository */
        $customerRepository = $this->getCustomerRepository();
        $dataQuery = $customerRepository->getCustomerGroupsWithoutIdsQuery($usedIds, $offset, $limit);

        $total = Shopware()->Models()->getQueryCount($dataQuery);
        $data = $dataQuery->getArrayResult();

        //return the data and total count
        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
    }

    /**
     * moves a category by the categoryId to a new position or under a new parent node
     */
    public function moveTreeItemAction()
    {
        $repository = $this->getRepository();
        $itemId = (int)$this->Request()->getParam('id');
        /** @var $item \Shopware\Models\Category\Category */
        $item = $this->getRepository()->find($itemId);
        $parentId = (int)$this->Request()->getParam('parentId', 1);
        $previousId = $this->Request()->getParam('previousId');
        $position = (int)$this->Request()->getParam('position');

        $item->setPosition($position);

        if($previousId !== null){
            /** @var $previous \Shopware\Models\Category\Category */
            $previous = $this->getRepository()->find($previousId);
            $repository->persistAsNextSiblingOf($item, $previous);
        } else {
            /** @var $parent \Shopware\Models\Category\Category */
            $parent = $this->getRepository()->find($parentId);
            $repository->persistAsFirstChildOf($item, $parent);
        }
        Shopware()->Models()->flush();

        $this->View()->assign(array(
            'success' => true
        ));
    }

    /**
     * Saves a single category. If no category id is passed,
     * the save function will create a new category model and persist
     * it.
     *
     * To successful saving a category a parent category id must supplied.
     */
    public function saveDetail()
    {
        try {
            $params = $this->Request()->getParams();
            $categoryId = $params['id'];
            if (empty($categoryId)) {
                $categoryModel = new \Shopware\Models\Category\Category();
            } else {
                $categoryModel = $this->getRepository()->find($categoryId);
                $categoryModel->getArticles()->clear();
                Shopware()->Models()->flush();
            }

            $this->prepareArticleAssociatedData($params, $categoryModel);
            $params = $this->prepareAttributeAssociatedData($params);
            $params = $this->prepareCustomerGroupsAssociatedData($params);
            $params = $this->prepareMediaAssociatedData($params);
            unset($params["articles"]);
            unset($params["emotion"]);
            unset($params["imagePath"]);

            $categoryModel->fromArray($params);

            $params['parentId'] = is_numeric($params['parentId']) ? (int)$params['parentId'] : 1;
            $parent = $this->getRepository()->find($params['parentId']);
            $categoryModel->setParent($parent);

            Shopware()->Models()->persist($categoryModel);
            Shopware()->Models()->flush();

            $params['id'] = $categoryModel->getId();

            $query = $this->getRepository()->getDetailQuery($params['id']);
            $data = $query->getOneOrNullResult(Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
            $data["imagePath"] = $data["media"]["path"];

            $this->View()->assign(array('success' => true, 'data' => $data, 'total' => count($data)));
        }
        catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    /**
     * Deletes a category and its children
     */
    public function deleteAction()
    {
        try {
            if (!($id = $this->Request()->getParam('id'))) {
                $this->View()->assign(array('success' => false, 'message' => 'No valid form Id'));
                return;
            }
            $result = $this->getRepository()->find($id);
            if (!$result || !is_object($result)) {
                $this->View()->assign(array('success' => false, 'message' => 'Category not found'));
                return;
            }

            $children = $this->getRepository()->children($result, false, 'left', 'DESC');
            foreach($children as $node) {
                $this->getRepository()->removeFromTree($node);
            }
            Shopware()->Models()->remove($result);
            Shopware()->Models()->flush();

            $this->View()->assign(array('success' => true));
        }
        catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    /**
     * helper method to move the category item to the right position
     *
     * @param $moveItemId int
     * @param $newPosition int
     * @param $categoryChildArray Array
     * @return Array | sorted category array
     */
    protected function moveCategoryItem($moveItemId, $newPosition, $categoryChildArray)
    {
        $movedChildKey = 0;
        foreach ($categoryChildArray as $key => $child) {
            if ($child["id"] == $moveItemId) {
                $movedChildKey = $key;
            }
        }

        if ($newPosition != 0) {
            //set the right position based on the array index
            $newPosition--;
        }
        $temporaryCategoryArray = array_splice($categoryChildArray, $movedChildKey, 1);
        array_splice($categoryChildArray, $newPosition, 0, $temporaryCategoryArray);
        return $categoryChildArray;
    }

    /**
     * This method loads the article models for the passed ids in the "articles" parameter.
     *
     * @param $data
     * @param $categoryModel
     * @return array
     */
    protected function prepareArticleAssociatedData($data, $categoryModel)
    {
        foreach ($data['articles'] as $articleData) {
            if (!empty($articleData['id'])) {
                /** @var $articleModel \Shopware\Models\Article\Article */
                $articleModel = Shopware()->Models()->find('Shopware\Models\Article\Article', $articleData['id']);
                $articleModel->getCategories()->add($categoryModel);
            }
        }
    }

    /**
     * This method loads the customer group models for the passed ids in the "customerGroups" parameter.
     *
     * @param $data
     * @return array
     */
    private function prepareCustomerGroupsAssociatedData($data)
    {
        $customerGroups = array();
        foreach ($data['customerGroups'] as $customerGroupData) {
            if (!empty($customerGroupData['id'])) {
                $model = Shopware()->Models()->find('Shopware\Models\Customer\Group', $customerGroupData['id']);
                $customerGroups[] = $model;
            }
        }
        $data['customerGroups'] = $customerGroups;
        return $data;
    }

    /**
     * This method loads the article models for the passed ids in the "articles" parameter.
     *
     * @param $data
     * @return array
     */
    protected function prepareAttributeAssociatedData($data)
    {
        $data['attribute'] = $data['attribute'][0];
        return $data;
    }

    /**
     * This method finds the mediaId by the media path to save it in the right way
     *
     * @param $data
     * @return array
     */
    protected function prepareMediaAssociatedData($data)
    {
        if (!empty($data["imagePath"])) {
            $mediaQuery = $this->getMediaRepository()->getMediaByPathQuery($data["imagePath"]);
            $mediaModel = $mediaQuery->getOneOrNullResult();
            $data["media"] = $mediaModel;
        } else {
            $data["media"] = null;
        }

        return $data;
    }
}