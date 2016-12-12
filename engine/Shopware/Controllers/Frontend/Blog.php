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
 * Shopware Frontend Controller for the Blog
 *
 * Frontend Controller for the blog article listing and the detail page.
 * Contains the logic for the listing of the blog articles and the detail page.
 * Furthermore it will manage the blog comment handling
 *
 * @category  Shopware
 * @package   Shopware\Controllers\Frontend
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_Blog extends Enlight_Controller_Action
{
    /**
     * @var \Shopware\Models\Blog\Repository
     */
    protected $repository;

    /**
     * @var \Shopware\Models\Category\Repository
     */
    protected $blogCommentRepository;

    /**
     * @var \Shopware\Models\Category\Repository
     */
    protected $categoryRepository;

    /**
     * @var \Shopware\Models\CommentConfirm\Repository
     */
    protected $commentConfirmRepository;

    /**
     * @var string
     */
    protected $blogBaseUrl;

    /**
     * Init controller method
     */
    public function init()
    {
        $this->blogBaseUrl = Shopware()->Config()->get('baseFile');
    }

    /**
     * Pre dispatch method
     */
    public function preDispatch()
    {
        $this->View()->setScope(Enlight_Template_Manager::SCOPE_PARENT);
    }

    /**
     * Helper Method to get access to the blog repository.
     *
     * @return Shopware\Models\Blog\Repository
     */
    public function getRepository()
    {
        if ($this->repository === null) {
            $this->repository = Shopware()->Models()->getRepository('Shopware\Models\Blog\Blog');
        }
        return $this->repository;
    }

    /**
     * Helper Method to get access to the blog comment repository.
     *
     * @return Shopware\Models\Blog\Repository
     */
    public function getBlogCommentRepository()
    {
        if ($this->blogCommentRepository === null) {
            $this->blogCommentRepository = Shopware()->Models()->getRepository('Shopware\Models\Blog\Comment');
        }
        return $this->blogCommentRepository;
    }

    /**
     * Helper Method to get access to the category repository.
     *
     * @return Shopware\Models\Category\Repository
     */
    public function getCategoryRepository()
    {
        if ($this->categoryRepository === null) {
            $this->categoryRepository = Shopware()->Models()->getRepository('Shopware\Models\Category\Category');
        }
        return $this->categoryRepository;
    }

    /**
     * Helper Method to get access to the commentConfirm repository.
     *
     * @return Shopware\Models\CommentConfirm\Repository
     */
    public function getCommentConfirmRepository()
    {
        if ($this->commentConfirmRepository === null) {
            $this->commentConfirmRepository = Shopware()->Models()->getRepository('Shopware\Models\CommentConfirm\CommentConfirm');
        }
        return $this->commentConfirmRepository;
    }

    /**
     * Index action method
     */
    public function indexAction()
    {
        $categoryId = (int) $this->Request()->getQuery('sCategory');
        $sPage = !empty($this->Request()->sPage) ? (int) $this->Request()->sPage : 1;
        $sFilterDate = urldecode($this->Request()->sFilterDate);
        $sFilterAuthor = urldecode($this->Request()->sFilterAuthor);
        $sFilterTags = urldecode($this->Request()->sFilterTags);


        // Redirect if blog's category is not a child of the current shop's category
        $shopCategory = Shopware()->Shop()->getCategory();
        $category = $this->getCategoryRepository()->findOneBy(array('id' => $categoryId, 'active' => true));
        $isChild = ($shopCategory && $category) ? $category->isChildOf($shopCategory) : false;
        if (!$isChild) {
            return $this->redirect(array('controller' => 'index'), array('code' => 301));
        }

        // PerPage
        if (!empty($this->Request()->sPerPage)) {
            $this->get('session')->sPerPage = (int) $this->Request()->sPerPage;
        }
        $sPerPage = $this->get('session')->sPerPage;
        if (empty($sPerPage)) {
            $sPerPage = (int) Shopware()->Config()->get('sARTICLESPERPAGE');
        }

        $filter = $this->createFilter($sFilterDate, $sFilterAuthor, $sFilterTags);

        // Start for Limit
        $sLimitStart = ($sPage - 1) * $sPerPage;
        $sLimitEnd = $sPerPage;

        //get all blog articles
        $query = $this->getCategoryRepository()->getBlogCategoriesByParentQuery($categoryId);
        $blogCategories = $query->getArrayResult();
        $blogCategoryIds = $this->getBlogCategoryListIds($blogCategories);
        $blogCategoryIds[] = $categoryId;
        $blogArticlesQuery = $this->getRepository()->getListQuery($blogCategoryIds, $sLimitStart, $sLimitEnd, $filter);
        $blogArticlesQuery->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $paginator = Shopware()->Models()->createPaginator($blogArticlesQuery);

        //returns the total count of the query
        $totalResult = $paginator->count();

        //returns the blog article data
        $blogArticles = $paginator->getIterator()->getArrayCopy();

        $mediaIds = array_map(function ($blogArticle) {
            if (isset($blogArticle['media']) && $blogArticle['media'][0]['mediaId']) {
                return $blogArticle['media'][0]['mediaId'];
            }
        }, $blogArticles);

        $context = $this->get('shopware_storefront.context_service')->getShopContext();
        $medias = $this->get('shopware_storefront.media_service')->getList($mediaIds, $context);

        foreach ($blogArticles as $key => $blogArticle) {
            //adding number of comments to the blog article
            $blogArticles[$key]["numberOfComments"] = count($blogArticle["comments"]);

            //adding tags and tag filter links to the blog article
            $tagsQuery = $this->repository->getTagsByBlogId($blogArticle["id"]);
            $tagsData = $tagsQuery->getArrayResult();
            $blogArticles[$key]["tags"] = $this->addLinksToFilter($tagsData, "sFilterTags", "name", false);

            //adding average vote data to the blog article
            $avgVoteQuery = $this->repository->getAverageVoteQuery($blogArticle["id"]);
            $blogArticles[$key]["sVoteAverage"] = $avgVoteQuery->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_SINGLE_SCALAR);

            //adding thumbnails to the blog article
            if (empty($blogArticle["media"][0]['mediaId'])) {
                continue;
            }

            $mediaId = $blogArticle["media"][0]['mediaId'];

            if (!isset($medias[$mediaId])) {
                continue;
            }

            /**@var $media \Shopware\Bundle\StoreFrontBundle\Struct\Media*/
            $media = $medias[$mediaId];
            $media = $this->get('legacy_struct_converter')->convertMediaStruct($media);

            $blogArticles[$key]['media'] = $media;
        }

        //RSS and ATOM Feed part
        if ($this->Request()->getParam('sRss') || $this->Request()->getParam('sAtom')) {
            $this->Response()->setHeader('Content-Type', 'text/xml');
            $type = $this->Request()->getParam('sRss') ? 'rss' : 'atom';
            $this->View()->loadTemplate('frontend/blog/' . $type . '.tpl');
        }

        $categoryContent = Shopware()->Modules()->Categories()->sGetCategoryContent($categoryId);
        $assigningData = array(
            'sBanner' => Shopware()->Modules()->Marketing()->sBanner($categoryId),
            'sBreadcrumb' => $this->getCategoryBreadcrumb($categoryId),
            'sCategoryContent' => $categoryContent,
            'sNumberArticles' => $totalResult,
            'sPage' => $sPage,
            'sPerPage' => $sPerPage,
            'sFilterDate' => $this->getDateFilterData($blogCategoryIds, $filter),
            'sFilterAuthor' => $this->getAuthorFilterData($blogCategoryIds, $filter),
            'sFilterTags' => $this->getTagsFilterData($blogCategoryIds, $filter),
            'sCategoryInfo' => $categoryContent,
            'sBlogArticles' => $blogArticles
        );

        $this->View()->assign(array_merge($assigningData, $this->getPagerData($totalResult, $sLimitEnd, $sPage, $categoryId)));
    }

    /**
     * Detail action method
     *
     * Contains the logic for the detail page of a blog article
     */
    public function detailAction()
    {
        $blogArticleId = intval($this->Request()->getQuery('blogArticle'));
        if (empty($blogArticleId)) {
            $this->forward("index", "index");
            return;
        }

        $blogArticleQuery = $this->getRepository()->getDetailQuery($blogArticleId);
        $blogArticleData = $blogArticleQuery->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        //redirect if the blog item is not available
        if (empty($blogArticleData) || empty($blogArticleData["active"])) {
            return $this->redirect(array('controller' => 'index'), array('code' => 301));
        }

        // Redirect if category is not available, inactive or external
        /** @var $category \Shopware\Models\Category\Category */
        $category = $this->getCategoryRepository()->find($blogArticleData['categoryId']);
        if ($category === null || !$category->getActive()) {
            $location = array('controller' => 'index');
        }

        // Redirect if blog's category is not a child of the current shop's category
        $shopCategory = Shopware()->Shop()->getCategory();
        $isChild = ($shopCategory && $category) ? $category->isChildOf($shopCategory) : false;
        if (!$isChild) {
            $location = array('controller' => 'index');
        }

        if (isset($location)) {
            return $this->redirect($location, array('code' => 301));
        }

        //load the right template
        if (!empty($blogArticleData['template'])) {
            $this->View()->loadTemplate('frontend/blog/' . $blogArticleData['template']);
        }

        $this->View()->userLoggedIn = !empty($this->get('session')->get('sUserId'));
        if (!empty($this->get('session')->get('sUserId')) && empty($this->Request()->name)
                && $this->Request()->getParam('__cache') === null) {
            $userData = Shopware()->Modules()->Admin()->sGetUserData();
            $this->View()->sFormData = array(
                'eMail' => $userData['additional']['user']['email'],
                'name' => $userData['billingaddress']['firstname'] . ' ' . $userData['billingaddress']['lastname']
            );
        }

        $mediaIds = array_column($blogArticleData["media"], 'mediaId');
        $context = $this->get('shopware_storefront.context_service')->getShopContext();
        $mediaStructs = $this->get('shopware_storefront.media_service')->getList($mediaIds, $context);

        //adding thumbnails to the blog article
        foreach ($blogArticleData["media"] as &$media) {
            $mediaId = $media['mediaId'];
            $mediaData = $this->get('legacy_struct_converter')->convertMediaStruct($mediaStructs[$mediaId]);
            if ($media['preview']) {
                $blogArticleData["preview"] = $mediaData;
            }
            $media = array_merge($media, $mediaData);
        }

        //add sRelatedArticles
        foreach ($blogArticleData["assignedArticles"] as &$assignedArticle) {
            $product = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, (int) $assignedArticle['id']);
            if ($product) {
                $blogArticleData["sRelatedArticles"][] = $product;
            }
        }

        //adding average vote data to the blog article
        $avgVoteQuery = $this->repository->getAverageVoteQuery($blogArticleId);
        $blogArticleData["sVoteAverage"] = $avgVoteQuery->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_SINGLE_SCALAR);

        //count the views of this blog item
        $visitedBlogItems = $this->get('session')->visitedBlogItems;
        if (!$this->get('session')->get('Bot') && !in_array($blogArticleId, $visitedBlogItems)) {
            //update the views count
            /* @var $blogModel Shopware\Models\Blog\Blog */
            $blogModel = $this->getRepository()->find($blogArticleId);
            if ($blogModel) {
                $blogModel->setViews($blogModel->getViews() + 1);
                Shopware()->Models()->flush($blogModel);

                //save it to the session
                $visitedBlogItems[] = $blogArticleId;
                $this->get('session')->visitedBlogItems = $visitedBlogItems;
            }
        }

        //generate breadcrumb
        $breadcrumb = $this->getCategoryBreadcrumb($blogArticleData["categoryId"]);
        $blogDetailLink = $this->Front()->Router()->assemble(array(
            'sViewport' => 'blog', 'sCategory' => $blogArticleData["categoryId"],
            'action' => 'detail', 'blogArticle' => $blogArticleId
        ));

        $breadcrumb[] = array('link' => $blogDetailLink, 'name' => $blogArticleData['title']);

        $this->View()->assign(array('sBreadcrumb' => $breadcrumb, 'sArticle' => $blogArticleData, 'rand' => md5(uniqid(rand()))));
    }

    /**
     * Rating action method
     *
     * Save and review the blog comment and rating
     */
    public function ratingAction()
    {
        $blogArticleId = intval($this->Request()->blogArticle);

        if (!empty($blogArticleId)) {
            $blogArticleQuery = $this->getRepository()->getDetailQuery($blogArticleId);
            $blogArticleData = $blogArticleQuery->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

            $this->View()->sAction = $this->Request()->getActionName();

            if ($hash = $this->Request()->sConfirmation) {
                //customer confirmed the link in the mail
                $commentConfirmQuery = $this->getCommentConfirmRepository()->getConfirmationByHashQuery($hash);
                $getComment = $commentConfirmQuery->getOneOrNullResult();

                if ($getComment) {
                    $commentData = unserialize($getComment->getData());

                    //delete the data in the comment confirm table we don't need it anymore
                    Shopware()->Models()->remove($getComment);
                    Shopware()->Models()->flush();

                    $this->sSaveComment($commentData, $blogArticleId);

                    return $this->forward('detail');
                }
            }

            //validation
            if (empty($this->Request()->name)) {
                $sErrorFlag['name'] = true;
            }
            if (empty($this->Request()->headline)) {
                $sErrorFlag['headline'] = true;
            }

            if (empty($this->Request()->comment)) {
                $sErrorFlag['comment'] = true;
            }

            if (empty($this->Request()->points)) {
                $sErrorFlag['points'] = true;
            }

            if (!empty(Shopware()->Config()->CaptchaColor)) {
                $captcha = str_replace(' ', '', strtolower($this->Request()->sCaptcha));
                $rand = $this->Request()->getPost('sRand');
                if (empty($rand) || $captcha != substr(md5($rand), 0, 5)) {
                    $sErrorFlag['sCaptcha'] = true;
                }
            }
            $validator = $this->get('validator.email');
            if (!empty(Shopware()->Config()->sOPTINVOTE) && (empty($this->Request()->eMail) || !$validator->isValid($this->Request()->eMail))) {
                $sErrorFlag['eMail'] = true;
            }

            if (empty($sErrorFlag)) {
                if (!empty(Shopware()->Config()->sOPTINVOTE) && empty($this->get('session')->get('sUserId'))) {
                    $hash = md5(uniqid(rand()));

                    //save comment confirm for the optin
                    $blogCommentModel = new \Shopware\Models\CommentConfirm\CommentConfirm();
                    $blogCommentModel->setCreationDate(new DateTime("now"));
                    $blogCommentModel->setHash($hash);
                    $blogCommentModel->setData(serialize($this->Request()->getPost()));

                    Shopware()->Models()->persist($blogCommentModel);
                    Shopware()->Models()->flush();

                    $link = $this->Front()->Router()->assemble(array('sViewport' => 'blog', 'action' => 'rating', 'blogArticle' => $blogArticleId, 'sConfirmation' => $hash));

                    $context = array('sConfirmLink' => $link, 'sArticle' => array('title' => $blogArticleData["title"]));
                    $mail = Shopware()->TemplateMail()->createMail('sOPTINVOTE', $context);
                    $mail->addTo($this->Request()->getParam('eMail'));
                    $mail->send();
                } else {
                    //save comment
                    $commentData = $this->Request()->getPost();
                    $this->sSaveComment($commentData, $blogArticleId);
                }
            } else {
                $this->View()->sFormData = Shopware()->System()->_POST->toArray();
                $this->View()->sErrorFlag = $sErrorFlag;
            }
        }
        $this->forward('detail');
    }

    /**
     * Save a new blog comment / voting
     *
     * @param array $commentData
     * @param int $blogArticleId
     * @throws Enlight_Exception
     */
    protected function sSaveComment($commentData, $blogArticleId)
    {
        if (empty($commentData)) {
            throw new Enlight_Exception("sSaveComment #00: Could not save comment");
        }

        $blogCommentModel = new \Shopware\Models\Blog\Comment();
        $blog = $this->getRepository()->find($blogArticleId);

        $blogCommentModel->setBlog($blog);
        $blogCommentModel->setCreationDate(new \DateTime());
        $blogCommentModel->setActive(false);

        $blogCommentModel->setName($commentData['name']);
        $blogCommentModel->setEmail($commentData['eMail']);
        $blogCommentModel->setHeadline($commentData['headline']);
        $blogCommentModel->setComment($commentData['comment']);
        $blogCommentModel->setPoints($commentData['points']);

        Shopware()->Models()->persist($blogCommentModel);
        Shopware()->Models()->flush();
    }

    /**
     * Returns all data needed to display the pager
     *
     * @param $totalResult
     * @param $sLimitEnd
     * @param $sPage
     * @param $categoryId
     * @return array
     */
    protected function getPagerData($totalResult, $sLimitEnd, $sPage, $categoryId)
    {
        // How many pages in this category?
        if ($sLimitEnd != 0) {
            $numberPages = ceil($totalResult / $sLimitEnd);
        } else {
            $numberPages = 0;
        }

        // Make Array with page-structure to render in template
        $pages = array();

        if ($numberPages > 1) {
            for ($i = 1; $i <= $numberPages; $i++) {
                if ($i == $sPage) {
                    $pages["numbers"][$i]["markup"] = true;
                } else {
                    $pages["numbers"][$i]["markup"] = false;
                }
                $pages["numbers"][$i]["value"] = $i;
                $pages["numbers"][$i]["link"] = $this->Front()->Router()->assemble(array('sViewport' => 'blog', 'sCategory' => $categoryId, 'sPage' => $i));
            }
            // Previous page
            if ($sPage != 1) {
                $pages["previous"] = $this->Front()->Router()->assemble(array('sViewport' => 'blog', 'sCategory' => $categoryId, 'sPage' => $sPage - 1));
            } else {
                $pages["previous"] = null;
            }
            // Next page
            if ($sPage != $numberPages) {
                $pages["next"] = $this->Front()->Router()->assemble(array('sViewport' => 'blog', 'sCategory' => $categoryId, "sPage" => $sPage + 1));
            } else {
                $pages["next"] = null;
            }
        }
        return array('sNumberPages' => $numberPages, 'sPages' => $pages);
    }

    /**
     * Returns all data needed to display the date filter
     *
     * @param $blogCategoryIds
     * @param $filter | selected filters
     * @return array
     */
    public function getDateFilterData($blogCategoryIds, $filter)
    {
        //date filter query
        $dateFilterQuery = $this->repository->getDisplayDateFilterQuery($blogCategoryIds, $filter);
        $dateFilterData = $dateFilterQuery->getArrayResult();
        return $this->addLinksToFilter($dateFilterData, "sFilterDate", "dateFormatDate");
    }

    /**
     * Returns all data needed to display the author filter
     *
     * @param $blogCategoryIds
     * @param $filter | selected filters
     * @return array
     */
    public function getAuthorFilterData($blogCategoryIds, $filter)
    {
        //date filter query
        $filterQuery = $this->repository->getAuthorFilterQuery($blogCategoryIds, $filter);
        $filterData = $filterQuery->getArrayResult();
        return $this->addLinksToFilter($filterData, "sFilterAuthor", "name");
    }

    /**
     * Returns all data needed to display the tags filter
     *
     * @param $blogCategoryIds
     * @param $filter | selected filters
     * @return array
     */
    public function getTagsFilterData($blogCategoryIds, $filter)
    {
        //date filter query
        $filterQuery = $this->repository->getTagsFilterQuery($blogCategoryIds, $filter);
        $filterData = $filterQuery->getArrayResult();
        return $this->addLinksToFilter($filterData, "sFilterTags", "name");
    }

    /**
     * Helper method to fill the data set with the right category link
     *
     * @param $filterData
     * @param $requestParameterName
     * @param $requestParameterValue
     * @param bool $addRemoveProperty | true to add a remove property to remove the selected filters
     * @return mixed
     */
    protected function addLinksToFilter($filterData, $requestParameterName, $requestParameterValue, $addRemoveProperty = true)
    {
        foreach ($filterData as $key => $dateData) {
            $filterData[$key]["link"] = $this->blogBaseUrl . Shopware()->Modules()->Core()->sBuildLink(
                array("sPage" => 1, $requestParameterName => urlencode($dateData[$requestParameterValue]))
            );
        }
        if ($addRemoveProperty) {
            $filterData[] = array("removeProperty" => 1, "link" => $this->blogBaseUrl . Shopware()->Modules()->Core()->sBuildLink(
                array("sPage" => 1, $requestParameterName => ''))
            );
        }
        return $filterData;
    }

    /**
     * Helper method to create the filter array for the query
     *
     * @param $sFilterDate
     * @param $sFilterAuthor
     * @param $sFilterTags
     * @return mixed
     */
    protected function createFilter($sFilterDate, $sFilterAuthor, $sFilterTags)
    {
        //date filter
        $filter = array();
        if (!empty($sFilterDate)) {
            $filter[] = array("property" => "blog.displayDate", "value" => $sFilterDate . "%");
        }

        //author filter
        if (!empty($sFilterAuthor)) {
            $filter[] = array("property" => "author.name", "value" => $sFilterAuthor);
        }

        //tags filter
        if (!empty($sFilterTags)) {
            $filter[] = array("property" => "tags.name", "value" => $sFilterTags);
        }

        return $filter;
    }

    /**
     * Helper method returns the blog category ids for the list query.
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
     * Returns listing breadcrumb
     *
     * @param int $categoryId
     * @return array
     */
    public function getCategoryBreadcrumb($categoryId)
    {
        return array_reverse(Shopware()->Modules()->Categories()->sGetCategoriesByParent($categoryId));
    }
}
