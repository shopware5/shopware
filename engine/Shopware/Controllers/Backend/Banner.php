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
 * Shopware Backend Banner Management
 *
 * This controller is used to create, update, delete and get banner data from the database.
 * Any prior live shopping code has been removed. Only non live shopping banners are used by this controller.
 * The frontend part is handled direct in engine/core/class/sMarketing.php in the method sBanner().
 *
 */
class Shopware_Controllers_Backend_Banner extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Test repository injection variable
     *
     * @var
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
     * Contains the user role who is executing this controller
     *
     * @var String
     */
    private $userRole;

    /**
     * Name of the default resource (Name of this controller)
     *
     * @var String
     */
    private $defaultResource;

    /**
     * Stores in which namespace we are in
     *
     * @var Enlight_Components_Snippet_Namespace
     */
    private $namespace;

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
     * Reads all known categories into an array to show it in the category treepanel
     */
    public function getListAction()
    {
        /** @var $filter array */
        $filter = $this->Request()->getParam('filter', array());
        $node = (int) $this->Request()->getParam('node');
        $preselectedNodes = $this->Request()->getParam('preselected');

        if (empty($filter)) {
            $node = !empty($node) ? $node : 1;
            $filter[] = array('property' => 'c.parentId', 'value' => $node);
        }

        $query = Shopware()->Models()->getRepository('Shopware\Models\Category\Category')->getListQuery(
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
            $data[$key]['childrenCount'] = (int) $category['childrenCount'];
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
     * Default init method
     *
     * @codeCoverageIgnore
     * @return void
     */
    public function init()
    {
        parent::init();
        if (!is_null(self::$testRepository)) {
            $this->repository = self::$testRepository;
        } else {
            $this->repository = Shopware()->Models()->Banner();
        }
        $this->namespace = Shopware()->Snippets()->getNamespace('backend/banner/banner');
    }

    /**
     * Basis Method to gather banner information.
     *
     * If the parameter is set true, every banner will be counted as shown
     *
     */
    public function getAllBanners()
    {
        $params = $this->Request()->getParams();
        $filter = (empty($params["categoryId"])) ? "" : $params["categoryId"];

        $query   = $this->repository->getBanners($filter);
        $banners = $query->getArrayResult();

        // restructures the data to better fit extjs model
        $nodes = $this->prepareBannerData($banners);
        $this->View()->assign(array('success' => !empty($nodes), 'data' => $nodes));
    }

    /**
     * Returns all known banner entries. Live shopping items will be ignored.
     *
     * This call will have NO impact on the generated statistic - this method
     * should be uses for backend operations only!
     * @return \Doctrine\ORM\Query
     */
    public function getAllBannersAction()
    {
        $this->getAllBanners();
    }

    /**
     * Build an array and reformats the date for a banner.
     * If the second parameter is set true, every banner will be tracked.
     *
     * @param $banners
     * @return array|null
     */
    private function prepareBannerData($banners)
    {
        $cnt   = 0;
        $nodes = null;
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        foreach ($banners as $banner) {
            // we have to split the datetime to date and time
            if (!empty($banner['validFrom'])) {
                $banner['validFromDate'] = $banner['validFrom']->format('d.m.Y');
                $banner['validFromTime'] = $banner['validFrom']->format('H:i');
            }
            // we have to split the datetime to date and time
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
     * @param $date
     * @param $time
     * @return DateTime
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
        return $timePart->setDate($datePart->format('Y'), $datePart->format('m'), $datePart->format('d'));
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
     *
     * @return mixed
     */
    public function saveBanner()
    {
        if (!$this->defaultCheck()) {
            return;
        }

        // check if there are more than one media is submitted
        if (false !== strpos($this->Request()->get('media-manager-selection'), ',')) {
            $this->View()->assign(array(
                'success' => false,
                'errorMsg' => $this
                    ->namespace
                    ->get('error_more_than_one_file', 'More then one file has been submitted - just one is allowed here.')));
            return;
        }
        $errorMsg   = null;
        $createMode = false;

        // add or edit detection
        $tmpId = $this->Request()->get('id');

        // Collecting form data
        if (!empty($tmpId)) {
            $id             = (int) $tmpId;
        } else {
            $createMode     = true;
        }
        unset($tmpId);
        // Check if we are allowed to create a new db entry
        if (!$this->_isAllowed('create') && $createMode) {
            $this->View()->assign(array(
                'success' => false,
                'data' => $this->namespace->get('no_create_rights', 'Create access denied.')));
        }
        // Check if we are allowed to update a db entry
        if (!$this->_isAllowed('update')) {
            $this->View()->assign(array(
                'success' => false,
                'errorMsg' => $this->namespace->get('no_update_rights', 'Update access denied.')));
        }

        $params = $this->Request()->getParams();

        // build a single from date instead of two parts
        $params['validFrom'] = $this->prepareDateAndTime($this->Request()->get('validFromDate'), $this->Request()->get('validFromTime'));
        // build a single till date instead of two dates
        $params['validTo'] = $this->prepareDateAndTime($this->Request()->get('validToDate'), $this->Request()->get('validToTime'));
        // Get media manager
        $mediaManagerData = $this->Request()->get('media-manager-selection');

        // update database entries
        if (!$createMode) {
            // load model from db
            $bannerModel = $this->repository->find($id);
        } else {
            // check if there are none files submitted
            if (empty($mediaManagerData)) {
                $this->View()->assign(array(
                    'success' => false,
                    'errorMsg' => $this->namespace->get('no_banner_selected', 'No banner has been selected.')));
                return;
            }
            $bannerModel = new \Shopware\Models\Banner\Banner();
        }
        // read data
        $bannerModel->fromArray($params);

        // set new image and extension if necessary
        if (!empty($mediaManagerData)) {
            $bannerModel->setImage($mediaManagerData);
        }

        // strip full qualified url
        $mediaService = $this->get('shopware_media.media_service');
        $bannerModel->setImage($mediaService->normalize($bannerModel->getImage()));

        // write model to db
        try {
            Shopware()->Models()->persist($bannerModel);
            Shopware()->Models()->flush();
            $params['id'] = $bannerModel->getId();
            $this->View()->assign(array('success' => 'true', 'data' => $params));
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            $this->View()->assign(array('success' => 'false', 'errorMsg' => $errorMsg));
        }
    }

    /**
     * Method to delete a banner. It takes either a single ID  or an array of IDs to determine the banners to delete.
     * If there is no ID parameter given, it will look if there is a parameter banners available
     *
     * e.g. id=1 or banners[[id => 1], [id => 2], [id => 3]]
     *
     */
    public function deleteBannerAction()
    {
        $multipleBanner    = $this->Request()->getPost('banners');
        $bannerRequestData = empty($multipleBanner) ? array(array("id" => $this->Request()->id)) : $multipleBanner;
        try {
            foreach ($bannerRequestData as $banner) {
                $model = Shopware()->Models()->find('Shopware\Models\Banner\Banner', $banner["id"]);
                Shopware()->Models()->remove($model);
            }
            Shopware()->Models()->flush();
            $this->View()->assign(array('success' => true));
        } catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'errorMsg' => $e->getMessage()));
        }
    }

    /**
     * Helper to assure that everything is alright.
     *
     * @return bool
     */
    private function defaultCheck()
    {
        if (!$this->Request()->isPost()) {
            $this->View()->assign(array(
                'success' => false,
                'errorMsg' => $this->namespace->get('wrong_transmit_method', 'Wrong transmit method.')));
            return false;
        }
        return true;
    }
}
