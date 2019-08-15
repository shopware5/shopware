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

use Doctrine\ORM\AbstractQuery;
use Shopware\Bundle\StoreFrontBundle\Struct\Media;
use Shopware\Components\Random;
use Shopware\Models\Blog\Blog;
use Shopware\Models\Shop\Shop;

/**
 * Frontend Controller for the blog article listing and the detail page.
 * Contains the logic for the listing of the blog articles and the detail page.
 * Furthermore it will manage the blog comment handling
 */
class Shopware_Controllers_Frontend_Blog extends Enlight_Controller_Action
{
    /**
     * @var \Shopware\Models\Blog\Repository
     */
    protected $repository;

    /**
     * @var \Shopware\Models\Blog\Repository
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
            $this->repository = Shopware()->Models()->getRepository(Blog::class);
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
            $this->blogCommentRepository = Shopware()->Models()->getRepository(Shopware\Models\Blog\Comment::class);
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
            $this->categoryRepository = Shopware()->Models()->getRepository(Shopware\Models\Category\Category::class);
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
            $this->commentConfirmRepository = Shopware()->Models()->getRepository(Shopware\Models\CommentConfirm\CommentConfirm::class);
        }

        return $this->commentConfirmRepository;
    }

    /**
     * Index action method
     */
    public function indexAction()
    {
        $categoryId = (int) $this->Request()->getQuery('sCategory');
        $page = (int) $this->request->getParam('sPage', 1);
        $page = $page >= 1 ? $page : 1;
        $filterDate = urldecode($this->Request()->getParam('sFilterDate'));
        $filterAuthor = urldecode($this->Request()->getParam('sFilterAuthor'));
        $filterTags = urldecode($this->Request()->getParam('sFilterTags'));

        // Redirect if blog's category is not a child of the current shop's category
        $shopCategory = $this->get('shop')->getCategory();
        $category = $this->getCategoryRepository()->findOneBy(['id' => $categoryId, 'active' => true]);
        $isChild = ($shopCategory && $category) ? $category->isChildOf($shopCategory) : false;
        if (!$isChild) {
            throw new Enlight_Controller_Exception(
                'Blog category missing, non-existent or invalid for the current shop',
                404
            );
        }

        $session = $this->get('session');

        // PerPage
        if (!empty($this->Request()->getParam('sPerPage'))) {
            $session->offsetSet('sPerPage', (int) $this->Request()->getParam('sPerPage'));
        }

        $perPage = (int) $session->offsetGet('sPerPage');
        if (empty($perPage)) {
            $perPage = (int) $this->get('config')->get('sARTICLESPERPAGE');
        }

        $filter = $this->createFilter($filterDate, $filterAuthor, $filterTags);

        // Start for Limit
        $limitStart = ($page - 1) * $perPage;
        $limitEnd = $perPage;

        // Get all blog articles
        $query = $this->getCategoryRepository()->getBlogCategoriesByParentQuery($categoryId);
        $blogCategoryIds = array_column($query->getArrayResult(), 'id');
        $blogCategoryIds[] = $categoryId;
        $shopId = (int) $this->get('shop')->getId();
        $blogArticlesQuery = $this->getRepository()->getListQuery($blogCategoryIds, $limitStart, $limitEnd, $filter, $shopId);
        $blogArticlesQuery->setHydrationMode(AbstractQuery::HYDRATE_ARRAY);

        $paginator = $this->get('models')->createPaginator($blogArticlesQuery);

        // Returns the total count of the query
        $totalResult = $paginator->count();

        // Returns the blog article data
        $blogArticles = $paginator->getIterator()->getArrayCopy();

        $blogArticles = $this->translateBlogArticles($blogArticles);

        $mediaIds = array_map(function ($blogArticle) {
            if (isset($blogArticle['media']) && $blogArticle['media'][0]['mediaId']) {
                return $blogArticle['media'][0]['mediaId'];
            }
        }, $blogArticles);

        $context = $this->get('shopware_storefront.context_service')->getShopContext();
        $medias = $this->get('shopware_storefront.media_service')->getList($mediaIds, $context);

        foreach ($blogArticles as $key => $blogArticle) {
            // Adding number of comments to the blog article
            $blogArticles[$key]['numberOfComments'] = count($blogArticle['comments']);

            // Adding tags and tag filter links to the blog article
            $tagsData = $this->repository->getTagsByBlogId($blogArticle['id'])->getArrayResult();
            $blogArticles[$key]['tags'] = $this->addLinksToFilter($tagsData, 'sFilterTags', 'name', false);

            // Adding average vote data to the blog article
            $avgVoteQuery = $this->repository->getAverageVoteQuery($blogArticle['id'], $shopId);
            $blogArticles[$key]['sVoteAverage'] = $avgVoteQuery->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

            // Adding thumbnails to the blog article
            if (empty($blogArticle['media'][0]['mediaId'])) {
                continue;
            }

            $mediaId = $blogArticle['media'][0]['mediaId'];

            if (!isset($medias[$mediaId])) {
                continue;
            }

            /** @var Media $media */
            $media = $medias[$mediaId];
            $media = $this->get('legacy_struct_converter')->convertMediaStruct($media);

            $blogArticles[$key]['media'] = $media;
        }

        // RSS and ATOM Feed part
        if ($this->Request()->getParam('sRss') || $this->Request()->getParam('sAtom')) {
            $this->Response()->headers->set('content-type', 'text/xml');
            $type = $this->Request()->getParam('sRss') ? 'rss' : 'atom';
            $this->View()->loadTemplate('frontend/blog/' . $type . '.tpl');
        }

        $categoryContent = $this->get('modules')->Categories()->sGetCategoryContent($categoryId);

        // Make sure the category exists and is a blog category
        if (empty($categoryContent) || !$categoryContent['blog']) {
            throw new Enlight_Controller_Exception(sprintf('Blog category by id "%d" is invalid', $categoryId), 404);
        }

        if (!empty($categoryContent['external'])) {
            return $this->redirect($categoryContent['external'], ['code' => 301]);
        }

        $assigningData = [
            'sBanner' => $this->get('modules')->Marketing()->sBanner($categoryId),
            'sBreadcrumb' => $this->getCategoryBreadcrumb($categoryId),
            'sCategoryContent' => $categoryContent,
            'sNumberArticles' => $totalResult,
            'sPage' => $page,
            'sPerPage' => $perPage,
            'sFilterDate' => $this->getDateFilterData($blogCategoryIds, $filter, $shopId),
            'sFilterAuthor' => $this->getAuthorFilterData($blogCategoryIds, $filter, $shopId),
            'sFilterTags' => $this->getTagsFilterData($blogCategoryIds, $filter, $shopId),
            'sCategoryInfo' => $categoryContent,
            'sBlogArticles' => $blogArticles,
        ];

        $filters = [
            'sFilterDate' => urlencode($filterDate),
            'sFilterAuthor' => urlencode($filterAuthor),
            'sFilterTags' => urlencode($filterTags),
        ];

        $this->View()->assign(array_merge($assigningData, $this->getPagerData($totalResult, $limitEnd, $page, $categoryId, $filters)));
    }

    /**
     * Detail action method
     *
     * Contains the logic for the detail page of a blog article
     *
     * @throws Enlight_Controller_Exception
     */
    public function detailAction()
    {
        $blogArticleId = (int) $this->Request()->getQuery('blogArticle');
        if (empty($blogArticleId)) {
            throw new Enlight_Controller_Exception(
                'Missing necessary parameter "blogArticle"',
                Enlight_Controller_Exception::PROPERTY_NOT_FOUND
            );
        }

        $shop = $this->get('shop');

        $blogArticleQuery = $this->getRepository()->getDetailQuery($blogArticleId, $shop->getId());
        $blogArticleData = $blogArticleQuery->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        $translation = $this->get('translation')->readWithFallback($shop->getId(), $shop->getFallback() ? $shop->getFallback()->getId() : null, 'blog', $blogArticleId);
        $blogArticleData = array_merge($blogArticleData, $translation);

        // Redirect if the blog item is not available
        if (empty($blogArticleData) || empty($blogArticleData['active'])) {
            throw new Enlight_Controller_Exception(
                sprintf('Blog article with id %d not found or inactive', $blogArticleId),
                404
            );
        }

        // Redirect if category is not available, inactive or external
        /** @var \Shopware\Models\Category\Category|null $category */
        $category = $this->getCategoryRepository()->find($blogArticleData['categoryId']);
        if ($category === null || !$category->getActive()) {
            $location = ['controller' => 'index'];
        }

        // Redirect if blog's category is not a child of the current shop's category
        $shopCategory = Shopware()->Shop()->getCategory();
        $isChild = ($shopCategory && $category) ? $category->isChildOf($shopCategory) : false;
        if (!$isChild) {
            $location = ['controller' => 'index'];
        }

        if (isset($location)) {
            return $this->redirect($location, ['code' => 301]);
        }

        // Load the right template
        if (!empty($blogArticleData['template'])) {
            $this->View()->loadTemplate('frontend/blog/' . $blogArticleData['template']);
        }

        $this->View()->assign('userLoggedIn', !empty(Shopware()->Session()->sUserId));
        if (!empty(Shopware()->Session()->sUserId) && empty($this->Request()->name)
            && $this->Request()->getParam('__cache') === null) {
            $userData = Shopware()->Modules()->Admin()->sGetUserData();
            $this->View()->assign('sFormData', [
                'eMail' => $userData['additional']['user']['email'],
                'name' => $userData['billingaddress']['firstname'] . ' ' . $userData['billingaddress']['lastname'],
            ]);
        }

        $mediaIds = array_column($blogArticleData['media'], 'mediaId');
        $context = $this->get('shopware_storefront.context_service')->getShopContext();
        $mediaStructs = $this->get('shopware_storefront.media_service')->getList($mediaIds, $context);

        // Adding thumbnails to the blog article
        foreach ($blogArticleData['media'] as &$media) {
            $mediaId = $media['mediaId'];
            $mediaData = $this->get('legacy_struct_converter')->convertMediaStruct($mediaStructs[$mediaId]);
            if ($media['preview']) {
                $blogArticleData['preview'] = $mediaData;
            }
            $media = array_merge($media, $mediaData);
        }

        // Add sRelatedArticles
        foreach ($blogArticleData['assignedArticles'] as &$assignedArticle) {
            $product = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, (int) $assignedArticle['id']);
            if ($product) {
                $blogArticleData['sRelatedArticles'][] = $product;
            }
        }

        // Adding average vote data to the blog article
        $avgVoteQuery = $this->repository->getAverageVoteQuery($blogArticleId, $shop->getId());
        $blogArticleData['sVoteAverage'] = $avgVoteQuery->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

        // Count the views of this blog item
        $visitedBlogItems = Shopware()->Session()->visitedBlogItems;
        if (!Shopware()->Session()->Bot && !in_array($blogArticleId, $visitedBlogItems)) {
            // Update the views count
            /* @var Shopware\Models\Blog\Blog $blogModel */
            $blogModel = $this->getRepository()->find($blogArticleId);
            if ($blogModel) {
                $blogModel->setViews($blogModel->getViews() + 1);
                Shopware()->Models()->flush($blogModel);

                // Save it to the session
                $visitedBlogItems[] = $blogArticleId;
                Shopware()->Session()->visitedBlogItems = $visitedBlogItems;
            }
        }

        // Generate breadcrumb
        $breadcrumb = $this->getCategoryBreadcrumb($blogArticleData['categoryId']);
        $blogDetailLink = $this->Front()->Router()->assemble([
            'sViewport' => 'blog', 'sCategory' => $blogArticleData['categoryId'],
            'action' => 'detail', 'blogArticle' => $blogArticleId,
        ]);

        $breadcrumb[] = ['link' => $blogDetailLink, 'name' => $blogArticleData['title']];

        $this->View()->assign(['sBreadcrumb' => $breadcrumb, 'sArticle' => $blogArticleData, 'rand' => Random::getAlphanumericString(32)]);
    }

    /**
     * Rating action method
     *
     * Save and review the blog comment and rating
     */
    public function ratingAction()
    {
        $blogArticleId = (int) $this->Request()->getParam('blogArticle');
        $sErrorFlag = null;

        if (!empty($blogArticleId)) {
            $blogArticleQuery = $this->getRepository()->getDetailQuery($blogArticleId, $this->get('shop')->getId());
            $blogArticleData = $blogArticleQuery->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

            $this->View()->assign('sAction', $this->Request()->getActionName());

            if ($hash = $this->Request()->sConfirmation) {
                // Customer confirmed the link in the mail
                $commentConfirmQuery = $this->getCommentConfirmRepository()->getConfirmationByHashQuery($hash);
                $getComment = $commentConfirmQuery->getOneOrNullResult();

                if ($getComment) {
                    $commentData = unserialize($getComment->getData(), ['allowed_classes' => false]);

                    // Delete the data in the s_core_optin table. We don't need it anymore
                    Shopware()->Models()->remove($getComment);
                    Shopware()->Models()->flush();

                    $this->sSaveComment($commentData, $blogArticleId);

                    return $this->forward('detail');
                }
                $sErrorFlag['invalidHash'] = true;
            }

            // Validation only occurs when entering data, but not on failed Double-Opt-In
            if (!$sErrorFlag['invalidHash']) {
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
                    /** @var \Shopware\Components\Captcha\CaptchaValidator $captchaValidator */
                    $captchaValidator = $this->container->get('shopware.captcha.validator');

                    if (!$captchaValidator->validate($this->Request())) {
                        $sErrorFlag['sCaptcha'] = true;
                    }
                }

                $validator = $this->container->get('validator.email');
                if (!empty(Shopware()->Config()->sOPTINVOTE) && (empty($this->Request()->eMail) || !$validator->isValid($this->Request()->eMail))) {
                    $sErrorFlag['eMail'] = true;
                }
            }

            if (empty($sErrorFlag)) {
                if (!empty(Shopware()->Config()->sOPTINVOTE) && empty(Shopware()->Session()->sUserId)) {
                    $hash = Random::getAlphanumericString(32);

                    // Save comment confirm for the optin
                    $blogCommentModel = new \Shopware\Models\CommentConfirm\CommentConfirm();
                    $blogCommentModel->setCreationDate(new DateTime('now'));
                    $blogCommentModel->setHash($hash);
                    $blogCommentModel->setData(serialize($this->Request()->getPost()));

                    Shopware()->Models()->persist($blogCommentModel);
                    Shopware()->Models()->flush();

                    $link = $this->Front()->Router()->assemble(['sViewport' => 'blog', 'action' => 'rating', 'blogArticle' => $blogArticleId, 'sConfirmation' => $hash]);

                    $context = ['sConfirmLink' => $link, 'sArticle' => ['title' => $blogArticleData['title']]];
                    $mail = Shopware()->TemplateMail()->createMail('sOPTINBLOGCOMMENT', $context);
                    $mail->addTo($this->Request()->getParam('eMail'));
                    $mail->send();
                } else {
                    //Save comment
                    $commentData = $this->Request()->getPost();
                    $this->sSaveComment($commentData, $blogArticleId);
                }
            } else {
                $this->View()->assign('sFormData', Shopware()->System()->_POST->toArray());
                $this->View()->assign('sErrorFlag', $sErrorFlag);
            }
        }
        $this->forward('detail');
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Returns all data needed to display the date filter
     *
     * @param int[]    $blogCategoryIds
     * @param array    $selectedFilters
     * @param int|null $shopId
     *
     * @return array
     */
    public function getDateFilterData($blogCategoryIds, $selectedFilters, $shopId = null)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        // Date filter query
        $dateFilterData = $this->repository
            ->getDisplayDateFilterQuery($blogCategoryIds, $selectedFilters, $shopId)
            ->getArrayResult();

        return $this->addLinksToFilter($dateFilterData, 'sFilterDate', 'dateFormatDate');
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Returns all data needed to display the author filter
     *
     * @param int[]    $blogCategoryIds
     * @param array    $filter          selected filters
     * @param int|null $shopId
     *
     * @return array
     */
    public function getAuthorFilterData($blogCategoryIds, $filter, $shopId = null)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        // Date filter query
        $filterData = $this->repository
            ->getAuthorFilterQuery($blogCategoryIds, $filter, $shopId)
            ->getArrayResult();

        return $this->addLinksToFilter($filterData, 'sFilterAuthor', 'name');
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Returns all data needed to display the tags filter
     *
     * @param int[]    $blogCategoryIds
     * @param array    $filter          | selected filters
     * @param int|null $shopId
     *
     * @return array
     */
    public function getTagsFilterData($blogCategoryIds, $filter, $shopId = null)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        // Date filter query
        $filterData = $this->repository
            ->getTagsFilterQuery($blogCategoryIds, $filter, $shopId)
            ->getArrayResult();

        return $this->addLinksToFilter($filterData, 'sFilterTags', 'name');
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Returns listing breadcrumb
     *
     * @param int $categoryId
     *
     * @return array
     */
    public function getCategoryBreadcrumb($categoryId)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return array_reverse(Shopware()->Modules()->Categories()->sGetCategoriesByParent($categoryId));
    }

    /**
     * Save a new blog comment / voting
     *
     * @param array $commentData
     * @param int   $blogArticleId
     *
     * @throws Enlight_Exception
     */
    protected function sSaveComment($commentData, $blogArticleId)
    {
        if (empty($commentData)) {
            throw new Enlight_Exception('sSaveComment #00: Could not save comment');
        }

        $blogCommentModel = new \Shopware\Models\Blog\Comment();
        /** @var \Shopware\Models\Blog\Blog $blog */
        $blog = $this->getRepository()->find($blogArticleId);
        /** @var Shop $shop */
        $shop = $this->getModelManager()->getReference(\Shopware\Models\Shop\Shop::class, $this->get('shop')->getId());

        $blogCommentModel->setBlog($blog);
        $blogCommentModel->setCreationDate(new \DateTime());
        $blogCommentModel->setActive(false);

        $blogCommentModel->setName($commentData['name']);
        $blogCommentModel->setEmail($commentData['eMail']);
        $blogCommentModel->setHeadline($commentData['headline']);
        $blogCommentModel->setComment($commentData['comment']);
        $blogCommentModel->setPoints($commentData['points']);
        $blogCommentModel->setShop($shop);

        Shopware()->Models()->persist($blogCommentModel);
        Shopware()->Models()->flush();
    }

    /**
     * Returns all data needed to display the pager
     *
     * @param int $totalResult
     * @param int $limitEnd
     * @param int $page
     * @param int $categoryId
     *
     * @return array
     */
    protected function getPagerData($totalResult, $limitEnd, $page, $categoryId, array $filters = [])
    {
        $numberPages = 0;

        // How many pages in this category?
        if ($limitEnd !== 0) {
            $numberPages = ceil($totalResult / $limitEnd);
        }

        // Make Array with page-structure to render in template
        $pages = [];

        // Delete empty filters and add needed parameters to array
        $userParams = array_filter($filters);
        $userParams['sViewport'] = 'blog';
        $userParams['sCategory'] = $categoryId;

        if ($numberPages > 1) {
            for ($i = 1; $i <= $numberPages; ++$i) {
                if ($i === $page) {
                    $pages['numbers'][$i]['markup'] = true;
                } else {
                    $pages['numbers'][$i]['markup'] = false;
                }
                $userParams['sPage'] = $i;

                $pages['numbers'][$i]['value'] = $i;
                $pages['numbers'][$i]['link'] = $this->Front()->Router()->assemble($userParams);
            }
            // Previous page
            if ($page !== 1) {
                $userParams['sPage'] = $page - 1;
                $pages['previous'] = $this->Front()->Router()->assemble($userParams);
            } else {
                $pages['previous'] = null;
            }
            // Next page
            if ($page !== $numberPages) {
                $userParams['sPage'] = $page + 1;
                $pages['next'] = $this->Front()->Router()->assemble($userParams);
            } else {
                $pages['next'] = null;
            }
        }

        return ['sNumberPages' => $numberPages, 'sPages' => $pages];
    }

    /**
     * Helper method to fill the data set with the right category link
     *
     * @param string $requestParameterName
     * @param string $requestParameterValue
     * @param bool   $addRemoveProperty     | true to add a remove property to remove the selected filters
     */
    protected function addLinksToFilter(array $filterData, $requestParameterName, $requestParameterValue, $addRemoveProperty = true)
    {
        foreach ($filterData as $key => $dateData) {
            $filterData[$key]['link'] = $this->blogBaseUrl . Shopware()->Modules()->Core()->sBuildLink(
                ['sPage' => 1, $requestParameterName => urlencode($dateData[$requestParameterValue])]
            );
        }
        if ($addRemoveProperty) {
            $filterData[] = ['removeProperty' => 1, 'link' => $this->blogBaseUrl . Shopware()->Modules()->Core()->sBuildLink(
                ['sPage' => 1, $requestParameterName => '']),
            ];
        }

        return $filterData;
    }

    /**
     * Helper method to create the filter array for the query
     *
     * @param string $filterDate
     * @param string $filterAuthor
     * @param string $filterTags
     *
     * @return array
     */
    protected function createFilter($filterDate, $filterAuthor, $filterTags)
    {
        // Date filter
        $filter = [];
        if (!empty($filterDate)) {
            $filter[] = ['property' => 'blog.displayDate', 'value' => $filterDate . '%'];
        }

        // Author filter
        if (!empty($filterAuthor)) {
            $filter[] = ['property' => 'author.name', 'value' => $filterAuthor];
        }

        // Tags filter
        if (!empty($filterTags)) {
            $filter[] = ['property' => 'tags.name', 'value' => $filterTags];
        }

        return $filter;
    }

    private function translateBlogArticles(array $blogArticles): array
    {
        if (!$blogArticles) {
            return [];
        }

        $ids = array_column($blogArticles, 'id');
        $shop = $this->get('shop');
        $data = [];

        foreach ($blogArticles as $blogArticle) {
            $data[$blogArticle['id']] = $blogArticle;
        }

        $translations = $this->get('translation')->readBatchWithFallback($shop->getId(), $shop->getFallback() ? $shop->getFallback()->getId() : null, 'blog', $ids, false);

        foreach ($translations as $translation) {
            $data[$translation['objectkey']] = array_merge($data[$translation['objectkey']], $translation['objectdata']);
        }

        return $data;
    }
}
