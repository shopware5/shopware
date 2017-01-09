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

use Shopware\Models\Blog\Blog as Blog;
use Shopware\Models\Blog\Tag as Tag;
use Shopware\Models\Blog\Media as Media;

/**
 * Shopware Backend Controller for the Blog Module
 *
 * Backend Controller for the blog backend module.
 * Displays all data in an Ext.grid.Panel and allows to delete,
 * add and edit items. On the detail page the blog data are displayed and can be edited
 */
class Shopware_Controllers_Backend_Blog extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Entity Manager
     * @var null
     */
    protected $manager = null;

    /**
     * @var \Shopware\Models\Blog\Repository
     */
    protected $blogRepository;

    /**
     * @var \Shopware\Models\Blog\Repository
     */
    protected $blogCommentRepository;

    /**
     * @var \Shopware\Models\Category\Repository
     */
    protected $categoryRepository;

    /**
     * @var \Shopware\Models\Category\Repository
     */
    protected $articleRepository;

    /**
     * Registers the different acl permission for the different controller actions.
     *
     * @return void
     */
    protected function initAcl()
    {
        /**
         * permission to get information of a blog
         */
        $this->addAclPermission('getDetail', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getList', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getTemplates', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getBlogCategories', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getBlogCategoryPath', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getBlogComments', 'read', 'Insufficient Permissions');

        /**
         * permission to delete the blog article
         */
        $this->addAclPermission('deleteBlogArticle', 'delete', 'Insufficient Permissions');

        /**
         * permission to save the blog article
         */
        $this->addAclPermission('saveBlogArticleAction', 'update', 'Insufficient Permissions');

        /**
         * permission to delete/accept blog comments
         */
        $this->addAclPermission('deleteBlogComment', 'comments', 'Insufficient Permissions');
        $this->addAclPermission('acceptBlogComment', 'comments', 'Insufficient Permissions');
    }

    /**
     * Helper Method to get access to the category repository.
     *
     * @return Shopware\Models\Category\Repository
     */
    public function getCategoryRepository()
    {
        if ($this->categoryRepository === null) {
            $this->categoryRepository = $this->getManager()->getRepository('Shopware\Models\Category\Category');
        }
        return $this->categoryRepository;
    }

    /**
     * Helper Method to get access to the article repository.
     *
     * @return Shopware\Models\Article\Repository
     */
    public function getArticleRepository()
    {
        if ($this->articleRepository === null) {
            $this->articleRepository = $this->getManager()->getRepository('Shopware\Models\Article\Article');
        }
        return $this->articleRepository;
    }

    /**
     * Helper Method to get access to the blog repository.
     *
     * @return Shopware\Models\Blog\Repository
     */
    public function getRepository()
    {
        if ($this->blogRepository === null) {
            $this->blogRepository = $this->getManager()->getRepository('Shopware\Models\Blog\Blog');
        }
        return $this->blogRepository;
    }

    /**
     * Helper Method to get access to the blog comment repository.
     *
     * @return Shopware\Models\Blog\Repository
     */
    public function getBlogCommentRepository()
    {
        if ($this->blogCommentRepository === null) {
            $this->blogCommentRepository = $this->getManager()->getRepository('Shopware\Models\Blog\Comment');
        }
        return $this->blogCommentRepository;
    }

    /**
     * Internal helper function to get access to the entity manager.
     *
     * @return null
     */
    private function getManager()
    {
        if ($this->manager === null) {
            $this->manager = Shopware()->Models();
        }
        return $this->manager;
    }

    /**
     * returns a JSON string with all found blog articles for the backend listing
     *
     * @return void
     */
    public function getListAction()
    {
        try {
            $limit = intval($this->Request()->limit);
            $offset = intval($this->Request()->start);
            $categoryId = (intval($this->Request()->categoryId) == 0) ? 1 : intval($this->Request()->categoryId);

            //order data
            $order = (array) $this->Request()->getParam('sort', array());

            /** @var $filter array */
            $filter = $this->Request()->getParam('filter', array());

            $query = $this->getCategoryRepository()->getBlogCategoriesByParentQuery($categoryId);
            $blogCategories = $query->getArrayResult();

            $blogCategoryIds = $this->getBlogCategoryListIds($blogCategories);
            $blogCategoryIds[] = $categoryId;

            /** @var $repository \Shopware\Models\Blog\Repository */
            $repository = $this->getRepository();
            $dataQuery = $repository->getBackendListQuery($blogCategoryIds, $filter, $order, $offset, $limit);

            $totalCount = $this->getManager()->getQueryCount($dataQuery);
            $data = $dataQuery->getArrayResult();

            $this->View()->assign(array('success' => true, 'data' => $data, 'totalCount' => $totalCount));
        } catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
    }

    /**
     * Reads all known blog categories to show it in the category treepanel
     */
    public function getBlogCategoriesAction()
    {
        /** @var $filter array */
        $filter = $this->Request()->getParam('filter', array());
        $node = $this->Request()->getParam('node');

        if ($node !== null) {
            $node = is_numeric($node) ? (int) $node : 1;
            $filter[] = array('property' => 'c.parentId', 'value' => $node);
        }

        $query = $this->getCategoryRepository()->getBlogCategoryTreeListQuery($filter);
        $data = $query->getArrayResult();

        foreach ($data as $key => $category) {
            $data[$key]['text'] = $category['name'];
            $data[$key]['cls'] = 'folder';
            $data[$key]['childrenCount'] = (int) $category['childrenCount'];
            $data[$key]['leaf'] = empty($data[$key]['childrenCount']);
            $data[$key]['allowDrag'] = true;
        }

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => count($data)));
    }

    /**
     * Creates or updates new Blog article
     *
     * @return void
     */
    public function saveBlogArticleAction()
    {
        $params = $this->Request()->getParams();

        $id = $this->Request()->id;

        if (!empty($id)) {
            //edit Data
            $blogModel = $this->getManager()->Blog()->find($id);
            //deletes all old blog tags
            $this->deleteOldTags($id);
        } else {
            //new Data
            $blogModel = new Blog();
        }
        // setting the date in this way cause ext js got no datetime field
        $params['displayDate'] = $params["displayDate"] . " " . $params["displayTime"];

        $this->prepareTagAssociatedData($params, $blogModel);
        $params = $this->prepareAssignedArticlesAssociatedData($params);
        $params = $this->prepareAuthorAssociatedData($params);

        unset($params["tags"]);
        $params["media"] = $this->prepareMediaDataForSaving($params["media"]);

        $blogModel->fromArray($params);

        try {
            $this->getManager()->persist($blogModel);
            $this->getManager()->flush();

            /** @var $repository \Shopware\Models\Blog\Repository */
            $repository = $this->getManager()->Blog();

            $filter = array(array("property" => "id", "value" => $blogModel->getId()));
            $dataQuery = $repository->getBackendDetailQuery($filter);
            $data = $dataQuery->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
            $this->View()->assign(array('success' => true, 'data' => $data));
        } catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    /**
     * returns a JSON string to show all blog detail information
     *
     * @return void
     */
    public function getDetailAction()
    {
        /** @var $filter array */
        $filter = $this->Request()->getParam('filter', array());
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        $dataQuery = $this->getRepository()->getBackendDetailQuery($filter);
        $data = $dataQuery->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        foreach ($data["media"] as $key => $media) {
            unset($data["media"][$key]["media"]);
            $data["media"][$key] = array_merge($data["media"][$key], $media["media"]);
            $data['media'][$key]['path'] = $mediaService->getUrl($data['media'][$key]['path']);
        }

        $data["tags"] = $this->flatBlogTags($data["tags"]);

        if ($data["displayDate"] instanceof \DateTime) {
            $data["displayTime"] = $data["displayDate"]->format("H:i");
            $data["displayDate"] = $data["displayDate"]->format("d.m.Y");
        } else {
            $data["displayTime"] = null;
            $data["displayDate"] = null;
        }

        $this->View()->assign(array('success' => true, 'data' => $data));
    }

    /**
     * returns a JSON string to show all blog detail information
     *
     * @return void
     */
    public function getBlogCommentsAction()
    {
        $limit = intval($this->Request()->limit);
        $offset = intval($this->Request()->start);
        //order data
        $order = (array) $this->Request()->getParam('sort', array());

        if (empty($order)) {
            $order = array(array('property' => 'creationDate', 'direction' => 'DESC'));
        }
        /** @var $filter array */
        $filter = $this->Request()->getParam('filter', array());
        $blogId = intval($this->Request()->blogId);

        $dataQuery = $this->getRepository()->getBlogCommentsById($blogId, $filter, $order, $offset, $limit);
        $totalCount = $this->getManager()->getQueryCount($dataQuery);
        $data = $dataQuery->getArrayResult();

        $this->View()->assign(array('success' => true, 'data' => $data, 'totalCount' => $totalCount));
    }

    /**
     * Returns the blog category ids for the list query.
     *
     * @param $blogCategories
     * @return array
     */
    private function getBlogCategoryListIds($blogCategories)
    {
        $ids = array();
        foreach ($blogCategories as $blogCategory) {
            $ids[] = $blogCategory["id"];
        }
        return $ids;
    }

    /**
     * flat the blog tags for the box select component
     *
     * @param $tags
     * @return array
     */
    private function flatBlogTags($tags)
    {
        $flattedTags = array();
        foreach ($tags as $tag) {
            $flattedTags[] = $tag["name"];
        }
        return implode(", ", $flattedTags);
    }

    /**
     * Returns a list of all blog detail templates for the blog templates combobox
     *
     * @return array
     */
    public function getTemplatesAction()
    {
        $config = Shopware()->Config()->blogdetailtemplates;
        $data = array();
        foreach (explode(';', $config) as $path) {
            if (!empty($path)) {
                list($id, $name) = explode(':', $path);
                $data[] = array('id' => $id, 'name' => $name);
            }
        }

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => count($data)));
    }

    /**
     * Returns the whole category path by an category id
     */
    public function getBlogCategoryPathAction()
    {
        $separator = $this->Request()->getParam('separator', ' > ');

        $query = $this->getCategoryRepository()->getBlogCategoriesByParentQuery(1);
        $blogCategories = $query->getArrayResult();
        $blogCategoryIds = $this->getBlogCategoryListIds($blogCategories);
        $data = array();
        foreach ($blogCategoryIds as $id) {
            $path = $this->getCategoryRepository()->getPathById($id, 'name', $separator);
            $data[] = array("id" => $id, "name" => $path);
        }

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => count($data)));
    }

    /**
     * This method loads prepares the tag associated data for saving it directly to the blog model
     *
     * @param $data
     * @param $blogModel
     * @return array
     */
    protected function prepareTagAssociatedData($data, $blogModel)
    {
        $tags = explode(",", $data["tags"]);
        foreach ($tags as $tag) {
            if (!empty($tag)) {
                $tagModel = new Tag();
                $tagModel->setName($tag);
                $tagModel->setBlog($blogModel);
                $this->getManager()->persist($tagModel);
            }
        }
    }

    /**
     * This function loads the assigned articles models for the passed ids in the "assignedArticles" parameter.
     *
     * @param $data
     * @return array
     */
    private function prepareAssignedArticlesAssociatedData($data)
    {
        $assignedArticlesRequestData = array();
        foreach ($data['assignedArticles'] as $assignedArticleData) {
            if (empty($assignedArticleData['id'])) {
                continue;
            }
            /**@var $assignedArticle \Shopware\Models\Article\Article*/
            $assignedArticle = $this->getArticleRepository()->find($assignedArticleData['id']);
            $assignedArticlesRequestData[] = $assignedArticle;
        }
        $data['assignedArticles'] = $assignedArticlesRequestData;
        return $data;
    }

    /**
     * This function loads the author model for the passed id authorId parameter
     *
     * @param $data
     * @return array
     */
    private function prepareAuthorAssociatedData($data)
    {
        /**@var $author \Shopware\Models\User\User*/
        if (!empty($data["authorId"])) {
            $data["author"] = $this->getManager()->find('Shopware\Models\User\User', $data['authorId']);
        } else {
            $data["author"] = null;
        }
        unset($data["authorId"]);
        return $data;
    }

    /**
     * This method prepares the media data for saving it directly to the blog model
     *
     * @param $mediaData
     * @return array
     */
    protected function prepareMediaDataForSaving($mediaData)
    {
        $mediaModels = array();
        foreach ($mediaData as $media) {
            $mediaModel = new Media();
            $media["media"] = $this->getManager()->find('Shopware\Models\Media\Media', $media["mediaId"]);
            unset($media["mediaId"]);
            $mediaModel->fromArray($media);
            $mediaModels[] = $mediaModel;
        }

        return $mediaModels;
    }

    /**
     * Helper method to delete all old tags mappings by the given blogId
     *
     * @param $blogId
     * @return array
     */
    protected function deleteOldTags($blogId)
    {
        $blogTagsQuery = $this->getRepository()->getBlogTagsById($blogId);
        $blogTags = $blogTagsQuery->execute();

        foreach ($blogTags as $tagModel) {
            $this->getManager()->remove($tagModel);
        }
    }

    /**
     * Delete blog articles
     *
     * @return void
     */
    public function deleteBlogArticleAction()
    {
        $multipleBlogArticles = $this->Request()->getPost('blogArticles');
        $blogArticleRequestData = empty($multipleBlogArticles) ? array(array("id" => $this->Request()->id)) : $multipleBlogArticles;
        try {
            foreach ($blogArticleRequestData as $blogArticle) {
                /**@var $model \Shopware\Models\Blog\Blog*/
                $model = $this->getRepository()->find($blogArticle["id"]);
                $this->getManager()->remove($model);
            }
            $this->getManager()->flush();
            $this->View()->assign(array('success' => true, 'data' => $blogArticleRequestData));
        } catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
    }

    /**
     * Delete blog comments
     *
     * @return void
     */
    public function deleteBlogCommentAction()
    {
        $multipleBlogComments = $this->Request()->getPost('blogComments');
        $blogCommentRequestData = empty($multipleBlogComments) ? array(array("id" => $this->Request()->id)) : $multipleBlogComments;
        try {
            foreach ($blogCommentRequestData as $blogComment) {
                /**@var $model \Shopware\Models\Blog\Comment*/
                $model = $this->getBlogCommentRepository()->find($blogComment["id"]);
                $this->getManager()->remove($model);
            }
            $this->getManager()->flush();
            $this->View()->assign(array('success' => true, 'data' => $blogCommentRequestData));
        } catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
    }

    /**
     * Delete blog comments
     *
     * @return void
     */
    public function acceptBlogCommentAction()
    {
        $multipleBlogComments = $this->Request()->getPost('blogComments');
        $blogCommentRequestData = empty($multipleBlogComments) ? array(array("id" => $this->Request()->id)) : $multipleBlogComments;
        try {
            foreach ($blogCommentRequestData as $blogComment) {
                /**@var $model \Shopware\Models\Blog\Comment*/
                $model = $this->getBlogCommentRepository()->find($blogComment["id"]);
                $model->setActive(true);
            }
            $this->getManager()->flush();
            $this->View()->assign(array('success' => true, 'data' => $blogCommentRequestData));
        } catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
    }
}
