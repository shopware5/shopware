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

use Shopware\Models\Banner\Banner;

/**
 * This controller is used to create, update, delete and get banner data from the database.
 * Any prior live shopping code has been removed. Only non live shopping banners are used by this controller.
 * The frontend part is handled direct in engine/core/class/sMarketing.php in the method sBanner().
 */
class Shopware_Controllers_Backend_Banner extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Test repository injection variable
     *
     * @var \Shopware\Models\Banner\Repository
     * @scope private
     */
    public static $testRepository = null;

    /**
     * Holds the Repository from doctrine
     *
     * @var \Shopware\Models\Banner\Repository
     */
    private $repository;

    /**
     * Stores in which namespace we are in
     *
     * @var Enlight_Components_Snippet_Namespace
     */
    private $namespace;

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

        $query = Shopware()->Models()->getRepository(\Shopware\Models\Category\Category::class)->getListQuery(
            $filter,
            $this->Request()->getParam('sort', []),
            $this->Request()->getParam('limit'),
            $this->Request()->getParam('start'),
            false
        );

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
     * Default init method
     *
     * @codeCoverageIgnore
     */
    public function init()
    {
        parent::init();
        if (self::$testRepository !== null) {
            $this->repository = self::$testRepository;
        } else {
            $this->repository = Shopware()->Models()->getRepository(Banner::class);
        }
        $this->namespace = Shopware()->Snippets()->getNamespace('backend/banner/banner');
    }

    /**
     * Basis Method to gather banner information.
     *
     * If the parameter is set true, every banner will be counted as shown
     */
    public function getAllBanners()
    {
        $params = $this->Request()->getParams();
        $filter = (empty($params['categoryId'])) ? '' : $params['categoryId'];

        $query = $this->repository->getBanners($filter);
        $banners = $query->getArrayResult();

        // Restructures the data to better fit extjs model
        $nodes = $this->prepareBannerData($banners);
        $this->View()->assign(['success' => !empty($nodes), 'data' => $nodes]);
    }

    /**
     * Returns all known banner entries. Live shopping items will be ignored.
     *
     * This call will have NO impact on the generated statistic - this method
     * should be uses for backend operations only!
     */
    public function getAllBannersAction()
    {
        $this->getAllBanners();
    }

    /**
     * Wrapper methods to use ACL
     *
     * @see saveBanner()
     */
    public function createBannerAction()
    {
        $this->saveBanner();
    }

    /**
     * Wrapper methods to use ACL
     *
     * @see saveBanner()
     */
    public function updateBannerAction()
    {
        $this->saveBanner();
    }

    /**
     * Universal Method to save a Banner model. If there is an id provided the model with that id will be updated.
     */
    public function saveBanner()
    {
        if (!$this->defaultCheck()) {
            return;
        }

        // Check if there are more than one media is submitted
        if (strpos($this->Request()->get('media-manager-selection'), ',') !== false) {
            $this->View()->assign([
                'success' => false,
                'errorMsg' => $this
                    ->namespace
                    ->get('error_more_than_one_file', 'More then one file has been submitted - just one is allowed here.'), ]);

            return;
        }
        $errorMsg = null;
        $createMode = false;

        // Add or edit detection
        $tmpId = $this->Request()->get('id');
        $id = null;

        // Collecting form data
        if (!empty($tmpId)) {
            $id = (int) $tmpId;
        } else {
            $createMode = true;
        }
        unset($tmpId);
        // Check if we are allowed to create a new db entry
        if (!$this->_isAllowed('create') && $createMode) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->namespace->get('no_create_rights', 'Create access denied.'), ]);
        }
        // Check if we are allowed to update a db entry
        if (!$this->_isAllowed('update')) {
            $this->View()->assign([
                'success' => false,
                'errorMsg' => $this->namespace->get('no_update_rights', 'Update access denied.'), ]);
        }

        $params = $this->Request()->getParams();

        // Build a single from date instead of two parts
        $params['validFrom'] = $this->prepareDateAndTime($this->Request()->get('validFromDate'), $this->Request()->get('validFromTime'));
        // Build a single till date instead of two dates
        $params['validTo'] = $this->prepareDateAndTime($this->Request()->get('validToDate'), $this->Request()->get('validToTime'));
        // Get media manager
        $mediaManagerData = $this->Request()->get('media-manager-selection');

        // Update database entries
        if (!$createMode) {
            // Load model from db
            $bannerModel = $this->repository->find($id);
        } else {
            // Check if there are none files submitted
            if (empty($mediaManagerData)) {
                $this->View()->assign([
                    'success' => false,
                    'errorMsg' => $this->namespace->get('no_banner_selected', 'No banner has been selected.'), ]);

                return;
            }
            $bannerModel = new Banner();
        }
        // Read data
        $bannerModel->fromArray($params);

        // Set new image and extension if necessary
        if (!empty($mediaManagerData)) {
            $bannerModel->setImage($mediaManagerData);
        }

        // Strip full qualified url
        $mediaService = $this->get('shopware_media.media_service');
        $bannerModel->setImage($mediaService->normalize($bannerModel->getImage()));

        // Write model to db
        try {
            Shopware()->Models()->persist($bannerModel);
            Shopware()->Models()->flush();
            $params['id'] = $bannerModel->getId();
            $this->View()->assign(['success' => 'true', 'data' => $params]);
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            $this->View()->assign(['success' => 'false', 'errorMsg' => $errorMsg]);
        }
    }

    /**
     * Method to delete a banner. It takes either a single ID  or an array of IDs to determine the banners to delete.
     * If there is no ID parameter given, it will look if there is a parameter banners available
     *
     * e.g. id=1 or banners[[id => 1], [id => 2], [id => 3]]
     */
    public function deleteBannerAction()
    {
        $multipleBanner = $this->Request()->getPost('banners');
        $bannerRequestData = empty($multipleBanner) ? [['id' => $this->Request()->id]] : $multipleBanner;
        try {
            foreach ($bannerRequestData as $banner) {
                $model = Shopware()->Models()->find(\Shopware\Models\Banner\Banner::class, $banner['id']);
                Shopware()->Models()->remove($model);
            }
            Shopware()->Models()->flush();
            $this->View()->assign(['success' => true]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
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
        $this->namespace = Shopware()->Snippets()->getNamespace('backend/banner/banner');
        $this->addAclPermission('getAllBannersAction', 'read', $this->namespace->get('no_list_rights', 'Read access denied.'));
        $this->addAclPermission('getListAction', 'read', $this->namespace->get('no_list_rights', 'Read access denied.'));
        $this->addAclPermission('getBannerAction', 'read', $this->namespace->get('no_list_rights', 'Read access denied.'));
        $this->addAclPermission('deleteBannerAction', 'delete', $this->namespace->get('no_delete_rights', 'Delete access denied.'));
        $this->addAclPermission('updateBannerAction', 'update', $this->namespace->get('no_update_rights', 'Update access denied.'));
        $this->addAclPermission('createBannerAction', 'create', $this->namespace->get('no_create_rights', 'Create access denied.'));
    }

    /**
     * Build an array and re-formats the date for a banner.
     * If the second parameter is set true, every banner will be tracked.
     *
     * @param array $banners
     *
     * @return array|null
     */
    private function prepareBannerData($banners)
    {
        $cnt = 0;
        $nodes = null;
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        foreach ($banners as $banner) {
            // We have to split the datetime to date and time
            if (!empty($banner['validFrom'])) {
                $banner['validFromDate'] = $banner['validFrom']->format('d.m.Y');
                $banner['validFromTime'] = $banner['validFrom']->format('H:i');
            }
            // We have to split the datetime to date and time
            if (!empty($banner['validTo'])) {
                $banner['validToDate'] = $banner['validTo']->format('d.m.Y');
                $banner['validToTime'] = $banner['validTo']->format('H:i');
            }

            $banner['image'] = $mediaService->getUrl($banner['image']);

            $nodes[$cnt++] = $banner;
        }

        return $nodes;
    }

    /**
     * Transforms a ISO Date in to an easy processable dateTime Object.
     *
     * @param string $date
     * @param string $time
     *
     * @return \DateTime|null
     */
    private function prepareDateAndTime($date, $time)
    {
        // do not convert empty dates - this would cause the date to become the current date
        if (empty($date)) {
            return null;
        }
        $datePart = new \DateTime($date);
        $timePart = new \DateTime($time);
        // Fill the timePart with the datePart
        return $timePart->setDate((int) $datePart->format('Y'), (int) $datePart->format('m'), (int) $datePart->format('d'));
    }

    /**
     * Helper to assure that everything is alright.
     *
     * @return bool
     */
    private function defaultCheck()
    {
        if (!$this->Request()->isPost()) {
            $this->View()->assign([
                'success' => false,
                'errorMsg' => $this->namespace->get('wrong_transmit_method', 'Wrong transmit method.'), ]);

            return false;
        }

        return true;
    }
}
