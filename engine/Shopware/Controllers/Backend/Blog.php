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

use Shopware\Models\Blog\Blog;
use Shopware\Models\Blog\Media;
use Shopware\Models\Blog\Tag;

/**
 * Backend Controller for the blog backend module.
 * Displays all data in an Ext.grid.Panel and allows to delete,
 * add and edit items. On the detail page the blog data are displayed and can be edited
 */
class Shopware_Controllers_Backend_Blog extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Entity Manager
     *
     * @var \Shopware\Components\Model\ModelManager
     */
    protected $manager;

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
     * @deprecated in 5.6, will be private in 5.8
     *
     * Helper Method to get access to the category repository.
     *
     * @return Shopware\Models\Category\Repository
     */
    public function getCategoryRepository()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        if ($this->categoryRepository === null) {
            $this->categoryRepository = $this->getManager()->getRepository(\Shopware\Models\Category\Category::class);
        }

        return $this->categoryRepository;
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Helper Method to get access to the article repository.
     *
     * @return Shopware\Models\Article\Repository
     */
    public function getArticleRepository()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        if ($this->articleRepository === null) {
            $this->articleRepository = $this->getManager()->getRepository(\Shopware\Models\Article\Article::class);
        }

        return $this->articleRepository;
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Helper Method to get access to the blog repository.
     *
     * @return Shopware\Models\Blog\Repository
     */
    public function getRepository()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        if ($this->blogRepository === null) {
            $this->blogRepository = $this->getManager()->getRepository(\Shopware\Models\Blog\Blog::class);
        }

        return $this->blogRepository;
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Helper Method to get access to the blog comment repository.
     *
     * @return Shopware\Models\Blog\Repository
     */
    public function getBlogCommentRepository()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        if ($this->blogCommentRepository === null) {
            $this->blogCommentRepository = $this->getManager()->getRepository(\Shopware\Models\Blog\Comment::class);
        }

        return $this->blogCommentRepository;
    }

    /**
     * returns a JSON string with all found blog articles for the backend listing
     */
    public function getListAction()
    {
        try {
            $limit = (int) $this->Request()->limit;
            $offset = (int) $this->Request()->start;
            $categoryId = ((int) $this->Request()->categoryId == 0) ? 1 : (int) $this->Request()->categoryId;

            // Order data
            $order = (array) $this->Request()->getParam('sort', []);

            /** @var array $filter */
            $filter = $this->Request()->getParam('filter', []);

            $query = $this->getCategoryRepository()->getBlogCategoriesByParentQuery($categoryId);
            $blogCategories = $query->getArrayResult();

            $blogCategoryIds = $this->getBlogCategoryListIds($blogCategories);
            $blogCategoryIds[] = $categoryId;

            /** @var \Shopware\Models\Blog\Repository $repository */
            $repository = $this->getRepository();
            $dataQuery = $repository->getBackendListQuery($blogCategoryIds, $filter, $order, $offset, $limit);

            $totalCount = $this->getManager()->getQueryCount($dataQuery);
            $data = $dataQuery->getArrayResult();

            $this->View()->assign(['success' => true, 'data' => $data, 'totalCount' => $totalCount]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Reads all known blog categories to show it in the category treepanel
     */
    public function getBlogCategoriesAction()
    {
        /** @var array $filter */
        $filter = $this->Request()->getParam('filter', []);
        $node = $this->Request()->getParam('node');

        if ($node !== null) {
            $node = is_numeric($node) ? (int) $node : 1;
            $filter[] = ['property' => 'c.parentId', 'value' => $node];
        }

        $data = $this->getCategoryRepository()
            ->getBlogCategoryTreeListQuery($filter)
            ->getArrayResult();

        foreach ($data as $key => $category) {
            $data[$key]['text'] = $category['name'];
            $data[$key]['cls'] = 'folder';
            $data[$key]['childrenCount'] = (int) $category['childrenCount'];
            $data[$key]['leaf'] = empty($data[$key]['childrenCount']);
            $data[$key]['allowDrag'] = true;
        }

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => count($data)]);
    }

    /**
     * Creates or updates new Blog article
     */
    public function saveBlogArticleAction()
    {
        $params = $this->Request()->getParams();

        $id = (int) $this->Request()->getParam('id');

        if (!empty($id)) {
            // Edit Data
            /** @var Blog $blogModel */
            $blogModel = $this->getManager()->getRepository(Blog::class)->find($id);
            // Deletes all old blog tags
            $this->deleteOldTags($id);
        } else {
            // New Data
            $blogModel = new Blog();
        }
        // Setting the date in this way cause ext js got no datetime field
        $params['displayDate'] = $params['displayDate'] . ' ' . $params['displayTime'];

        if (!$params['shopIds']) {
            $params['shopIds'] = null;
        }

        $this->prepareTagAssociatedData($params, $blogModel);
        $params = $this->prepareAssignedArticlesAssociatedData($params);
        $params = $this->prepareAuthorAssociatedData($params);

        unset($params['tags']);
        $params['media'] = $this->prepareMediaDataForSaving($params['media']);

        $blogModel->fromArray($params);

        try {
            $this->getManager()->persist($blogModel);
            $this->getManager()->flush();

            /** @var \Shopware\Models\Blog\Repository $repository */
            $repository = $this->getManager()->getRepository(Blog::class);

            $filter = [['property' => 'id', 'value' => $blogModel->getId()]];
            $dataQuery = $repository->getBackendDetailQuery($filter);
            $data = $dataQuery->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
            $this->View()->assign(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * returns a JSON string to show all blog detail information
     */
    public function getDetailAction()
    {
        /** @var array $filter */
        $filter = $this->Request()->getParam('filter', []);
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        $data = $this->getRepository()
            ->getBackendDetailQuery($filter)
            ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        foreach ($data['media'] as $key => $media) {
            unset($data['media'][$key]['media']);
            $data['media'][$key] = array_merge($data['media'][$key], $media['media']);
            $data['media'][$key]['path'] = $mediaService->getUrl($data['media'][$key]['path']);
        }

        $data['tags'] = $this->flatBlogTags($data['tags']);

        if ($data['displayDate'] instanceof \DateTime) {
            $data['displayTime'] = $data['displayDate']->format('H:i');
            $data['displayDate'] = $data['displayDate']->format('d.m.Y');
        } else {
            $data['displayTime'] = null;
            $data['displayDate'] = null;
        }

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * returns a JSON string to show all blog detail information
     */
    public function getBlogCommentsAction()
    {
        $limit = (int) $this->Request()->getParam('limit');
        $offset = (int) $this->Request()->getParam('start');
        // Order data
        $order = (array) $this->Request()->getParam('sort', []);

        if (empty($order)) {
            $order = [['property' => 'creationDate', 'direction' => 'DESC']];
        }
        /** @var array $filter */
        $filter = $this->Request()->getParam('filter', []);
        $blogId = (int) $this->Request()->getParam('blogId');

        $dataQuery = $this->getRepository()->getBlogCommentsById($blogId, $filter, $order, $offset, $limit);
        $totalCount = $this->getManager()->getQueryCount($dataQuery);
        $data = $dataQuery->getArrayResult();

        $this->View()->assign(['success' => true, 'data' => $data, 'totalCount' => $totalCount]);
    }

    /**
     * Returns a list of all blog detail templates for the blog templates combobox
     */
    public function getTemplatesAction()
    {
        $config = Shopware()->Config()->blogdetailtemplates;
        $data = [];
        foreach (explode(';', $config) as $path) {
            if (!empty($path)) {
                list($id, $name) = explode(':', $path);
                $data[] = ['id' => $id, 'name' => $name];
            }
        }

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => count($data)]);
    }

    /**
     * Returns the whole category path by an category id
     */
    public function getBlogCategoryPathAction()
    {
        $separator = $this->Request()->getParam('separator', ' > ');

        $blogCategories = $this->getCategoryRepository()
            ->getBlogCategoriesByParentQuery(1)
            ->getArrayResult();

        $blogCategoryIds = $this->getBlogCategoryListIds($blogCategories);
        $data = [];
        foreach ($blogCategoryIds as $id) {
            $path = $this->getCategoryRepository()->getPathById($id, 'name', $separator);
            $data[] = ['id' => $id, 'name' => $path];
        }

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => count($data)]);
    }

    /**
     * Delete blog articles
     */
    public function deleteBlogArticleAction()
    {
        $multipleBlogArticles = $this->Request()->getPost('blogArticles');
        $blogArticleRequestData = empty($multipleBlogArticles) ? [['id' => $this->Request()->getParam('id')]] : $multipleBlogArticles;
        try {
            foreach ($blogArticleRequestData as $blogArticle) {
                /** @var \Shopware\Models\Blog\Blog $model */
                $model = $this->getRepository()->find($blogArticle['id']);
                $this->getManager()->remove($model);
            }
            $this->getManager()->flush();
            $this->View()->assign(['success' => true, 'data' => $blogArticleRequestData]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Delete blog comments
     */
    public function deleteBlogCommentAction()
    {
        $multipleBlogComments = $this->Request()->getPost('blogComments');
        $blogCommentRequestData = empty($multipleBlogComments) ? [['id' => $this->Request()->getParam('id')]] : $multipleBlogComments;
        try {
            foreach ($blogCommentRequestData as $blogComment) {
                /** @var \Shopware\Models\Blog\Comment $model */
                $model = $this->getBlogCommentRepository()->find($blogComment['id']);
                $this->getManager()->remove($model);
            }
            $this->getManager()->flush();
            $this->View()->assign(['success' => true, 'data' => $blogCommentRequestData]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Delete blog comments
     *
     * @deprecated since 5.5, will be removed with 5.7
     */
    public function acceptBlogCommentAction()
    {
        $multipleBlogComments = $this->Request()->getPost('blogComments');
        $blogCommentRequestData = empty($multipleBlogComments) ? [['id' => $this->Request()->getParam('id')]] : $multipleBlogComments;
        try {
            foreach ($blogCommentRequestData as $blogComment) {
                /** @var \Shopware\Models\Blog\Comment $model */
                $model = $this->getBlogCommentRepository()->find($blogComment['id']);
                $model->setActive(true);
            }
            $this->getManager()->flush();
            $this->View()->assign(['success' => true, 'data' => $blogCommentRequestData]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Update blog comment(s)
     */
    public function updateBlogCommentAction()
    {
        $multipleBlogComments = $this->Request()->getPost('blogComments');
        $blogCommentRequestData = empty($multipleBlogComments) ? [$this->Request()->getParams()] : $multipleBlogComments;

        try {
            foreach ($blogCommentRequestData as $blogComment) {
                /** @var \Shopware\Models\Blog\Comment $model */
                $model = $this->getBlogCommentRepository()->find($blogComment['id']);
                $model->fromArray($blogComment);
            }
            $this->getManager()->flush();
            $this->View()->assign(['success' => true, 'data' => $blogCommentRequestData]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Registers the different acl permission for the different controller actions.
     */
    protected function initAcl()
    {
        /*
         * Permission to get information of a blog
         */
        $this->addAclPermission('getDetail', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getList', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getTemplates', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getBlogCategories', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getBlogCategoryPath', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getBlogComments', 'read', 'Insufficient Permissions');

        /*
         * Permission to delete the blog article
         */
        $this->addAclPermission('deleteBlogArticle', 'delete', 'Insufficient Permissions');

        /*
         * permission to save the blog article
         */
        $this->addAclPermission('saveBlogArticleAction', 'update', 'Insufficient Permissions');

        /*
         * Permission to delete/accept blog comments
         */
        $this->addAclPermission('deleteBlogComment', 'comments', 'Insufficient Permissions');
        $this->addAclPermission('acceptBlogComment', 'comments', 'Insufficient Permissions');
    }

    /**
     * This method loads prepares the tag associated data for saving it directly to the blog model
     *
     * @param array                      $data
     * @param \Shopware\Models\Blog\Blog $blogModel
     */
    protected function prepareTagAssociatedData($data, $blogModel)
    {
        $tags = explode(',', $data['tags']);
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
     * This method prepares the media data for saving it directly to the blog model
     *
     * @param array $mediaData
     *
     * @return array
     */
    protected function prepareMediaDataForSaving($mediaData)
    {
        $mediaModels = [];
        foreach ($mediaData as $media) {
            $mediaModel = new Media();
            $media['media'] = $this->getManager()->find(\Shopware\Models\Media\Media::class, $media['mediaId']);
            unset($media['mediaId']);
            $mediaModel->fromArray($media);
            $mediaModels[] = $mediaModel;
        }

        return $mediaModels;
    }

    /**
     * Helper method to delete all old tags mappings by the given blogId
     *
     * @param int $blogId
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
     * Internal helper function to get access to the entity manager.
     *
     * @return \Shopware\Components\Model\ModelManager
     */
    private function getManager()
    {
        if ($this->manager === null) {
            $this->manager = Shopware()->Models();
        }

        return $this->manager;
    }

    /**
     * Returns the blog category ids for the list query.
     *
     * @param array $blogCategories
     *
     * @return array
     */
    private function getBlogCategoryListIds($blogCategories)
    {
        $ids = [];
        foreach ($blogCategories as $blogCategory) {
            $ids[] = $blogCategory['id'];
        }

        return $ids;
    }

    /**
     * flat the blog tags for the box select component
     *
     * @param array $tags
     *
     * @return string
     */
    private function flatBlogTags($tags)
    {
        $flattedTags = [];
        foreach ($tags as $tag) {
            $flattedTags[] = $tag['name'];
        }

        return implode(', ', $flattedTags);
    }

    /**
     * This function loads the assigned articles models for the passed ids in the "assignedArticles" parameter.
     *
     * @param array $data
     *
     * @return array
     */
    private function prepareAssignedArticlesAssociatedData($data)
    {
        $assignedArticlesRequestData = [];
        foreach ($data['assignedArticles'] as $assignedArticleData) {
            if (empty($assignedArticleData['id'])) {
                continue;
            }
            /** @var \Shopware\Models\Article\Article $assignedArticle */
            $assignedArticle = $this->getArticleRepository()->find($assignedArticleData['id']);
            $assignedArticlesRequestData[] = $assignedArticle;
        }
        $data['assignedArticles'] = $assignedArticlesRequestData;

        return $data;
    }

    /**
     * This function loads the author model for the passed id authorId parameter
     *
     * @param array $data
     *
     * @return array
     */
    private function prepareAuthorAssociatedData($data)
    {
        /* @var \Shopware\Models\User\User $author */
        if (!empty($data['authorId'])) {
            $data['author'] = $this->getManager()->find(\Shopware\Models\User\User::class, $data['authorId']);
        } else {
            $data['author'] = null;
        }
        unset($data['authorId']);

        return $data;
    }
}
