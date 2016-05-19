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
use Shopware\Models\Customer\Customer;

/**
 * Account controller
 */
class Shopware_Controllers_Frontend_Account extends Enlight_Controller_Action
{
    /**
     * @var sAdmin
     */
    protected $admin;

    /**
     * Init controller method
     */
    public function init()
    {
        $this->admin = Shopware()->Modules()->Admin();
    }

    /**
     * Pre dispatch method
     */
    public function preDispatch()
    {
        $this->View()->setScope(Enlight_Template_Manager::SCOPE_PARENT);
        if (!in_array($this->Request()->getActionName(), array('login', 'logout', 'password', 'ajax_login', 'ajax_logout', 'resetPassword'))
            && !$this->admin->sCheckUser()) {
            // If using the new template, the 'GET' action will be handled
            // in the Register controller (unified login/register page)
            if (Shopware()->Shop()->getTemplate()->getVersion() >= 3) {
                return $this->forward('index', 'register');
            } else {
                // redirecting to login action should be considered deprecated
                return $this->forward('login');
            }
        }
        $this->View()->sUserData = $this->admin->sGetUserData();
        $this->View()->sUserLoggedIn = $this->admin->sCheckUser();
        $this->View()->sAction = $this->Request()->getActionName();
    }

    /**
     * Index action method
     */
    public function indexAction()
    {
        if (
            Shopware()->Shop()->getTemplate()->getVersion() >= 3 &&
            $this->View()->sUserData['additional']['user']['accountmode'] == 1
        ) {
            $this->logoutAction();
            return $this->redirect(array('controller'=> 'register'));
        }

        if ($this->Request()->getParam('success')) {
            $this->View()->sSuccessAction = $this->Request()->getParam('success');
        }
    }

    /**
     * Billing action method
     *
     * Read billing address data
     */
    public function billingAction()
    {
        $this->View()->sBillingPreviously = $this->admin->sGetPreviousAddresses('billing');
        $this->View()->sCountryList = $this->admin->sGetCountryList();
        $this->View()->sTarget = $this->Request()->getParam('sTarget', $this->Request()->getControllerName());

        if (!empty($this->View()->sUserData['billingaddress'])) {
            $address = $this->View()->sUserData['billingaddress'];
            $address['country'] = $address['countryID'];
            $address['country_state_'.$address['countryID']] = $address['stateID'];


            unset($address['id'], $address['countryID']);
            if (!empty($address['birthday'])) {
                list($address['birthyear'], $address['birthmonth'], $address['birthday']) = explode('-', $address['birthday']);
            }
            if ($this->Request()->isPost()) {
                $address = array_merge($address, $this->Request()->getPost());
            }

            $this->View()->sFormData = $address;
        }

        // If using the new template and we get a request to change address from the checkout page
        // we need to use a different template
        if (Shopware()->Shop()->getTemplate()->getVersion() >= 3 && $this->View()->sTarget == 'checkout') {
            $this->Request()->setControllerName('checkout');
            return $this->View()->loadTemplate('frontend/account/billing_checkout.tpl');
        }
    }

    /**
     * Shipping action method
     *
     * Read shipping address data
     */
    public function shippingAction()
    {
        $this->View()->sShippingPreviously = $this->admin->sGetPreviousAddresses('shipping');
        $this->View()->sCountryList = $this->admin->sGetCountryList();
        $this->View()->sTarget = $this->Request()->getParam('sTarget', $this->Request()->getControllerName());

        if (!empty($this->View()->sUserData['shippingaddress'])) {
            if ($this->Request()->isPost()) {
                $address = array_merge($this->View()->sUserData['shippingaddress'], $this->Request()->getPost());
            } else {
                $address = $this->View()->sUserData['shippingaddress'];
            }

            $address['country'] = $address['countryID'];
            $address['country_shipping_state_'.$address['countryID']] = $address['stateID'];

            unset($address['id'], $address['countryID']);

            $this->View()->sFormData = $address;
        }

        // If using the new template and we get a request to change address from the checkout page
        // we need to use a different template
        if (Shopware()->Shop()->getTemplate()->getVersion() >= 3 && $this->View()->sTarget == 'checkout') {
            $this->Request()->setControllerName('checkout');
            return $this->View()->loadTemplate('frontend/account/shipping_checkout.tpl');
        }
    }

    /**
     * Payment action method
     *
     * Read and change payment mean and payment data
     */
    public function paymentAction()
    {
        $this->View()->sPaymentMeans = $this->admin->sGetPaymentMeans();
        $this->View()->sFormData = array('payment'=>$this->View()->sUserData['additional']['user']['paymentID']);
        $this->View()->sTarget = $this->Request()->getParam('sTarget', $this->Request()->getControllerName());
        $this->View()->sTargetAction = $this->Request()->getParam('sTargetAction', 'index');

        $getPaymentDetails = $this->admin->sGetPaymentMeanById($this->View()->sFormData['payment']);

        $paymentClass = $this->admin->sInitiatePaymentClass($getPaymentDetails);
        if ($paymentClass instanceof \ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod) {
            $data = $paymentClass->getCurrentPaymentDataAsArray(Shopware()->Session()->sUserId);
            if (!empty($data)) {
                $this->View()->sFormData += $data;
            }
        }

        if ($this->Request()->isPost()) {
            $values = $this->Request()->getPost();
            $values['payment'] = $this->Request()->getPost('register');
            $values['payment'] = $values['payment']['payment'];
            $values['isPost'] = true;
            $this->View()->sFormData = $values;
        }
    }


    /**
     * Orders action method
     *
     * Read last orders
     */
    public function ordersAction()
    {
        $destinationPage = (int)$this->Request()->sPage;
        $orderData = $this->admin->sGetOpenOrderData($destinationPage);
        $this->View()->sOpenOrders = $orderData["orderData"];
        $this->View()->sNumberPages = $orderData["numberOfPages"];
        $this->View()->sPages = $orderData["pages"];

        //this has to be assigned here because the config method in smarty can't handle array structures
        $this->View()->sDownloadAvailablePaymentStatus = Shopware()->Config()->get('downloadAvailablePaymentStatus');
    }

    /**
     * Downloads action method
     *
     * Read last downloads
     */
    public function downloadsAction()
    {
        $destinationPage = (int)$this->Request()->sPage;

        if (empty($destinationPage)) {
            $destinationPage = 1;
        }

        $orderData = $this->admin->sGetDownloads($destinationPage);
        $this->View()->sDownloads = $orderData["orderData"];
        $this->View()->sNumberPages = $orderData["numberOfPages"];
        $this->View()->sPages = $orderData["pages"];

        //this has to be assigned here because the config method in smarty can't handle array structures
        $this->View()->sDownloadAvailablePaymentStatus = Shopware()->Config()->get('downloadAvailablePaymentStatus');
    }

    /**
     * PartnerStatisticMenuItem action method
     *
     * The partner statistic menu item action displays
     * the menu item in the account menu
     */
    public function partnerStatisticMenuItemAction()
    {
        // show partner statistic menu
        $partnerModel = Shopware()->Models()->getRepository('Shopware\Models\Partner\Partner')
                                            ->findOneBy(array('customerId' => Shopware()->Session()->sUserId));
        if (!empty($partnerModel)) {
            $this->View()->partnerId = $partnerModel->getId();
            Shopware()->Session()->partnerId = $partnerModel->getId();
        }
    }

    /**
     * Partner Statistic action method
     * This action returns all data for the partner statistic page
     *
     */
    public function partnerStatisticAction()
    {
        $partnerId = Shopware()->Session()->partnerId;

        if (empty($partnerId)) {
            return $this->forward('index');
        }

        $toDate = $this->Request()->toDate;
        $fromDate = $this->Request()->fromDate;

        //if a to date passed, format it over the \DateTime object. Otherwise create a new date with today
        if (empty($fromDate) || !Zend_Date::isDate($fromDate)) {
            $fromDate = new \DateTime();
            $fromDate = $fromDate->sub(new DateInterval('P1M'));
        } else {
            $fromDate = new \DateTime($fromDate);
        }

        //if a to date passed, format it over the \DateTime object. Otherwise create a new date with today
        if (empty($toDate) || !Zend_Date::isDate($toDate)) {
            $toDate = new \DateTime();
        } else {
            $toDate = new \DateTime($toDate);
        }

        $this->View()->partnerStatisticToDate = $toDate->format("d.m.Y");
        $this->View()->partnerStatisticFromDate = $fromDate->format("d.m.Y");

        //to get the right value cause 2012-02-02 is smaller than 2012-02-02 15:33:12
        $toDate = $toDate->add(new DateInterval('P1D'));

        /** @var $repository \Shopware\Models\Partner\Repository */
        $repository = Shopware()->Models()->Partner();

        //get the information of the partner chart
        $userCurrencyFactor = Shopware()->Shop()->getCurrency()->getFactor();

        $dataQuery = $repository->getStatisticChartQuery($partnerId, $fromDate, $toDate, $userCurrencyFactor);
        $this->View()->sPartnerOrderChartData = $dataQuery->getArrayResult();

        $dataQuery = $repository->getStatisticListQuery(null, null, null, $partnerId, false, $fromDate, $toDate, $userCurrencyFactor);
        $this->View()->sPartnerOrders = $dataQuery->getArrayResult();

        $dataQuery = $repository->getStatisticListQuery(null, null, null, $partnerId, true, $fromDate, $toDate, $userCurrencyFactor);
        $this->View()->sTotalPartnerAmount = $dataQuery->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * Logout action method
     *
     * Logout account and delete session
     */
    public function logoutAction()
    {
        $this->admin->logout();
    }

    /**
     * Login action method
     *
     * Login account and show login errors
     */
    public function loginAction()
    {
        $this->View()->sTarget = $this->Request()->getParam('sTarget');

        if ($this->Request()->isPost()) {
            $checkUser = $this->admin->sLogin();
            if (!empty($checkUser['sErrorMessages'])) {
                $this->View()->sFormData = $this->Request()->getPost();
                $this->View()->sErrorFlag = $checkUser['sErrorFlag'];
                $this->View()->sErrorMessages = $checkUser['sErrorMessages'];
            } else {
                $this->refreshBasket();
            }
        }

        if (empty($this->View()->sErrorMessages) && $this->admin->sCheckUser()) {
            return $this->redirect(
                array(
                    'controller' => $this->Request()->getParam('sTarget', 'account'),
                    'action' => $this->Request()->getParam('sTargetAction', 'index')
                )
            );
        }

        // If using the new template, the 'GET' action will be handled
        // in the Register controller (unified login/register page)
        if (Shopware()->Shop()->getTemplate()->getVersion() >= 3) {
            $this->forward(array(
                'action' => 'index',
                'controller' => 'register',
                'sTarget' => $this->View()->sTarget
            ));
        }
    }

    /**
     * Save billing action
     *
     * Save billing address data
     */
    public function saveBillingAction()
    {
        if ($this->Request()->isPost()) {
            $countryData = $this->admin->sGetCountryList();
            $countryIds = array();

            foreach ($countryData as $key => $country) {
                $countryIds[$key] = $country['id'];
            }

            $requirePhone = (bool) (Shopware()->Config()->get('showPhoneNumberField')
                && Shopware()->Config()->get('requirePhoneField'));

            $rules = array(
                'salutation'    => array('required' => 1),
                'company'       => array('required' => 0),
                'firstname'     => array('required' => 1),
                'lastname'      => array('required' => 1),
                'street'        => array('required' => 1),
                'zipcode'       => array('required' => 1),
                'city'          => array('required' => 1),
                'phone'         => array('required' => $requirePhone),
                'fax'           => array('required' => 0),
                'country'       => array(
                    'required' => 1,
                    'in' => $countryIds
                ),
                'department'    => array('required' => 0),
                'shippingAddress'=>array('required' => 0),
                'text1'         => array('required' => 0),
                'text2'         => array('required' => 0),
                'text3'         => array('required' => 0),
                'text4'         => array('required' => 0),
                'text5'         => array('required' => 0),
                'text6'         => array('required' => 0),
                'birthyear'     => array('required' => 0, 'date' => ['d' => 'birthday', 'm' => 'birthmonth', 'y' => 'birthyear']),
                'birthmonth'    => array('required' => 0, 'date' => ['d' => 'birthday', 'm' => 'birthmonth', 'y' => 'birthyear']),
                'birthday'      => array('required' => 0, 'date' => ['d' => 'birthday', 'm' => 'birthmonth', 'y' => 'birthyear']),
                'additional_address_line1' => array(
                    'required' => (Shopware()->Config()->requireAdditionAddressLine1 && Shopware()->Config()->showAdditionAddressLine1) ? 1 : 0
                ),
                'additional_address_line2' => array(
                    'required' => (Shopware()->Config()->requireAdditionAddressLine2 && Shopware()->Config()->showAdditionAddressLine2) ? 1 : 0
                )
            );

            $values = $this->Request()->getPost('register');

            // State selection
            if (!empty($values["billing"]["country"])) {
                $stateSelectionRequired = Shopware()->Db()->fetchRow(
                   "SELECT display_state_in_registration, force_state_in_registration
                   FROM s_core_countries WHERE id = ?",
                   array($values["billing"]["country"]))
               ;

                if ($stateSelectionRequired["display_state_in_registration"]) {
                    $countryDataIndex = array_search($values["billing"]["country"], $countryIds);
                    $statesIds = array_column($countryData[$countryDataIndex]['states'], 'id');

                    // if not required, allow empty values
                    if (!$stateSelectionRequired["force_state_in_registration"]) {
                        $statesIds[] = "";
                    }

                    $rules["stateID"] = array(
                        "required" => $stateSelectionRequired["force_state_in_registration"],
                        'in' => $statesIds
                    );
                }

                if (
                    $stateSelectionRequired["display_state_in_registration"] != true
                    && $stateSelectionRequired["force_state_in_registration"] != true
                ) {
                    $this->admin->sSYSTEM->_POST["register"]["billing"]["stateID"] = $values["billing"]["stateID"] = 0;
                } else {
                    $this->admin->sSYSTEM->_POST["register"]["billing"]["stateID"] = $values["billing"]["stateID"] = $values["billing"]["country_state_".$values["billing"]["country"]];
                }

                unset($values["billing"]["country_state_".$values["billing"]["country"]]);
            }

            if ($this->Request()->getParam('sSelectAddress')) {
                $address = $this->admin->sGetPreviousAddresses('billing', $this->Request()->getParam('sSelectAddress'));
                if (!empty($address['hash'])) {
                    $address = array_merge($this->View()->sUserData['billingaddress'], $address);
                    $this->admin->sSYSTEM->_POST = $address;
                }
            }

            if (!empty($values['personal']['customer_type'])) {
                if ($values['personal']['customer_type'] === 'private') {
                    $values['billing']['company'] = '';
                    $values['billing']['department'] = '';
                    $values['billing']['ustid'] = '';
                } else {
                    $rules['company'] = array('required' => 1);
                    $rules['ustid'] = array('required' => 0);
                }
            }

            if (!empty($values)) {
                $this->admin->sSYSTEM->_POST = array_merge($values['personal'], $values['billing'], $this->admin->sSYSTEM->_POST->toArray());
            }


            $checkData = $this->admin->sValidateStep2($rules, true);

            if (!empty($checkData['sErrorMessages'])) {
                $this->View()->sErrorFlag = $checkData['sErrorFlag'];
                $this->View()->sErrorMessages = $checkData['sErrorMessages'];
                return $this->forward('billing');
            } else {
                $this->admin->sUpdateBilling();
            }
        }
        if (!$target = $this->Request()->getParam('sTarget')) {
            $target = 'account';
        }
        $this->redirect(array('controller'=>$target, 'action'=>'index', 'success'=>'billing'));
    }

    /**
     * Save shipping action
     *
     * Save shipping address data
     */
    public function saveShippingAction()
    {
        if ($this->Request()->isPost()) {
            $countryData = $this->admin->sGetCountryList();
            $countryIds = array();

            foreach ($countryData as $key => $country) {
                $countryIds[$key] = $country['id'];
            }

            $rules = array(
                'salutation'        => array('required' => 1),
                'company'           => array('required' => 0),
                'firstname'         => array('required' => 1),
                'lastname'          => array('required' => 1),
                'street'            => array('required' => 1),
                'zipcode'           => array('required' => 1),
                'city'              => array('required' => 1),
                'department'        => array('required' => 0),
                'text1'             => array('required' => 0),
                'text2'             => array('required' => 0),
                'text3'             => array('required' => 0),
                'text4'             => array('required' => 0),
                'text5'             => array('required' => 0),
                'text6'             => array('required' => 0),
                'additional_address_line1' => array('required' => (Shopware()->Config()->requireAdditionAddressLine1 && Shopware()->Config()->showAdditionAddressLine1) ? 1 : 0),
                'additional_address_line2' => array('required' => (Shopware()->Config()->requireAdditionAddressLine2 && Shopware()->Config()->showAdditionAddressLine2) ? 1 : 0)
            );

            if (Shopware()->Config()->get('sCOUNTRYSHIPPING')) {
                $rules['country'] = array('required'=>1);
            } else {
                $rules['country'] = array('required'=>0);
            }

            if ($this->Request()->getParam('sSelectAddress')) {
                $address = $this->admin->sGetPreviousAddresses('shipping', $this->Request()->getParam('sSelectAddress'));
                if (!empty($address['hash'])) {
                    $address = array_merge($this->View()->sUserData['shippingaddress'], $address);
                    $this->admin->sSYSTEM->_POST = $address;
                }
            } else {
                $this->admin->sSYSTEM->_POST =  $this->Request()->getPost();
            }

            $values = $this->Request()->getPost('register');

            if (Shopware()->Config()->get('sCOUNTRYSHIPPING')) {
                $rules['country'] = array(
                    'required' => 1,
                    'in' => $countryIds
                );

                // State selection
                if (!empty($values["shipping"]["country"])) {
                    $stateSelectionRequired = Shopware()->Db()->fetchRow("
                    SELECT display_state_in_registration, force_state_in_registration
                    FROM s_core_countries WHERE id = ?",
                        array($values["shipping"]["country"])
                    );

                    if ($stateSelectionRequired["display_state_in_registration"]) {
                        $countryDataIndex = array_search($values["shipping"]["country"], $countryIds);
                        $statesIds = array_column($countryData[$countryDataIndex]['states'], 'id');

                        // if not required, allow empty values
                        if (!$stateSelectionRequired["force_state_in_registration"]) {
                            $statesIds[] = "";
                        }

                        $rules["stateID"] = array(
                            "required" => $stateSelectionRequired["force_state_in_registration"],
                            'in' => $statesIds
                        );
                    }

                    if (
                        $stateSelectionRequired["display_state_in_registration"] == false
                        && $stateSelectionRequired["force_state_in_registration"] == false
                    ) {
                        $this->admin->sSYSTEM->_POST["register"]["shipping"]["stateID"] = $values["shipping"]["stateID"] = 0;
                    } else {
                        $this->admin->sSYSTEM->_POST["register"]["shipping"]["stateID"] = $values["shipping"]["stateID"] = $values["shipping"]["country_shipping_state_".$values["shipping"]["country"]];
                    }

                    unset($values["shipping"]["country_shipping_state_".$values["shipping"]["country"]]);
                }
            }

            if (!empty($values)) {
                $this->admin->sSYSTEM->_POST = array_merge($values['shipping'], $this->admin->sSYSTEM->_POST->toArray());
            }

            $checkData = $this->admin->sValidateStep2ShippingAddress($rules, true);
            if (!empty($checkData['sErrorMessages'])) {
                $this->View()->sErrorFlag = $checkData['sErrorFlag'];
                $this->View()->sErrorMessages = $checkData['sErrorMessages'];
                return $this->forward('shipping');
            } else {
                $this->admin->sUpdateShipping();
            }
        }
        if (!$target = $this->Request()->getParam('sTarget')) {
            $target = 'account';
        }
        $targetAction = $this->Request()->getParam('sTargetAction', 'index');
        $this->redirect(array(
            'controller' => $target,
            'action' => $targetAction,
            'success' => 'shipping'
        ));
    }

    /**
     * Save shipping action
     *
     * Save shipping address data
     */
    public function savePaymentAction()
    {
        if ($this->Request()->isPost()) {
            $sourceIsCheckoutConfirm = $this->Request()->getParam('sourceCheckoutConfirm');
            $values = $this->Request()->getPost('register');
            $this->admin->sSYSTEM->_POST['sPayment'] = $values['payment'];
            $checkData = $this->admin->sValidateStep3();

            if (!empty($checkData['checkPayment']['sErrorMessages']) || empty($checkData['sProcessed'])) {
                if (empty($sourceIsCheckoutConfirm)) {
                    $this->View()->sErrorFlag = $checkData['checkPayment']['sErrorFlag'];
                    $this->View()->sErrorMessages = $checkData['checkPayment']['sErrorMessages'];
                }
                return $this->forward('payment');
            } else {
                $previousPayment = $this->admin->sGetUserData();
                $previousPayment = $previousPayment['additional']['user']['paymentID'];

                $previousPayment = $this->admin->sGetPaymentMeanById($previousPayment);
                if ($previousPayment['paymentTable']) {
                    $deleteSQL = 'DELETE FROM '.$previousPayment['paymentTable'].' WHERE userID=?';
                    Shopware()->Db()->query($deleteSQL, array(Shopware()->Session()->sUserId));
                }

                $this->admin->sUpdatePayment();

                if ($checkData['sPaymentObject'] instanceof \ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod) {
                    $checkData['sPaymentObject']->savePaymentData(Shopware()->Session()->sUserId, $this->Request());
                }
            }
        }

        if (!$target = $this->Request()->getParam('sTarget')) {
            $target = 'account';
        }
        $targetAction = $this->Request()->getParam('sTargetAction', 'index');
        $this->redirect(array(
            'controller' => $target,
            'action' => $targetAction,
            'success' => 'payment'
        ));
    }

    /**
     * Save newsletter action
     *
     * Save newsletter address data
     */
    public function saveNewsletterAction()
    {
        if ($this->Request()->isPost()) {
            $status = $this->Request()->getPost('newsletter') ? true : false;
            $this->admin->sUpdateNewsletter($status, $this->admin->sGetUserMailById(), true);
            $successMessage =  $status ? 'newsletter' : 'deletenewsletter';
            if (Shopware()->Config()->optinnewsletter && $status) {
                $successMessage = 'optinnewsletter';
            }
            $this->View()->sSuccessAction = $successMessage;
            $this->container->get('session')->offsetSet('sNewsletter', $status);
        }
        $this->forward('index');
    }

    /**
     * Save account action
     *
     * Save account address data and create error messages
     *
     */
    public function saveAccountAction()
    {
        if ($this->Request()->isPost()) {
            $checkData = $this->admin->sValidateStep1(true);
            if (!empty($checkData["sErrorMessages"])) {
                foreach ($checkData["sErrorMessages"] as $key=>$error_message) {
                    $checkData["sErrorMessages"][$key] = $this->View()->fetch('string:'.$error_message);
                }
            }
            if (empty($checkData['sErrorMessages'])) {
                $this->admin->sUpdateAccount();
                $this->View()->sSuccessAction = 'account';
            } else {
                $this->View()->sErrorFlag = $checkData['sErrorFlag'];
                $this->View()->sErrorMessages = $checkData['sErrorMessages'];
            }
        }
        $this->forward('index');
    }

    /**
     * Download action
     *
     * Read and test download file
     */
    public function downloadAction()
    {
        $esdID = $this->request->getParam('esdID');

        if (empty($esdID)) {
            return $this->forward('downloads');
        }

        $sql = '
            SELECT file, articleID
            FROM s_articles_esd ae, s_order_esd oe
            WHERE ae.id=oe.esdID
            AND	oe.userID=?
            AND oe.orderdetailsID=?
        ';
        $download = Shopware()->Db()->fetchRow($sql, array(Shopware()->Session()->sUserId, $esdID));

        if (empty($download)) {
            $sql = '
                SELECT e.file, ad.articleID
                FROM s_articles_esd e, s_order_details od, s_articles_details ad, s_order o
                WHERE e.articledetailsID=ad.id
                AND ad.ordernumber=od.articleordernumber
                AND o.id=od.orderID
                AND o.userID=?
                AND od.id=?
            ';
            $download = Shopware()->Db()->fetchRow($sql, array(Shopware()->Session()->sUserId, $esdID));
        }

        if (empty($download['file'])) {
            $this->View()->sErrorCode = 1;
            return $this->forward('downloads');
        }

        $file = 'files/'.Shopware()->Config()->get('sESDKEY').'/'.$download['file'];

        $filePath = Shopware()->OldPath() . $file;

        if (!file_exists($filePath)) {
            $this->View()->sErrorCode = 2;
            return $this->forward('downloads');
        }

        switch (Shopware()->Config()->get("esdDownloadStrategy")) {
            case 0:
                $this->redirect($this->Request()->getBasePath() . '/' .  $file);
                break;
            case 1:
                @set_time_limit(0);
                $this->Response()
                    ->setHeader('Content-Type', 'application/octet-stream')
                    ->setHeader('Content-Disposition', 'attachment; filename="'.$download['file'].'"')
                    ->setHeader('Content-Length', filesize($filePath));

                $this->Front()->Plugins()->ViewRenderer()->setNoRender();

                readfile($filePath);
                break;
            case 2:
                // Apache2 + X-Sendfile
                $this->Response()
                    ->setHeader('Content-Type', 'application/octet-stream')
                    ->setHeader('Content-Disposition', 'attachment; filename="'.$download['file'].'"')
                    ->setHeader('X-Sendfile', $filePath);

                $this->Front()->Plugins()->ViewRenderer()->setNoRender();

                break;
            case 3:
                // Nginx + X-Accel
                $this->Response()
                    ->setHeader('Content-Type', 'application/octet-stream')
                    ->setHeader('Content-Disposition', 'attachment; filename="'.$download['file'].'"')
                    ->setHeader('X-Accel-Redirect', '/'.$file);

                $this->Front()->Plugins()->ViewRenderer()->setNoRender();

                break;
        }
    }

    /**
     * Read saved billing address
     */
    public function selectBillingAction()
    {
        $this->View()->sTarget = $this->Request()->getParam('sTarget', $this->Request()->getControllerName());
        $this->View()->sBillingAddresses = $this->admin->sGetPreviousAddresses('billing');

        // If using the new template and we get a request to change address from the checkout page
        // we need to use a different template
        if (Shopware()->Shop()->getTemplate()->getVersion() >= 3 && $this->View()->sTarget == 'checkout') {
            $this->Request()->setControllerName('checkout');
            return $this->View()->loadTemplate('frontend/account/select_billing_checkout.tpl');
        }
    }

    /**
     * Read saved shipping address
     */
    public function selectShippingAction()
    {
        $this->View()->sTarget = $this->Request()->getParam('sTarget', $this->Request()->getControllerName());
        $this->View()->sShippingAddresses = $this->admin->sGetPreviousAddresses('shipping');

        // If using the new template and we get a request to change address from the checkout page
        // we need to use a different template
        if (Shopware()->Shop()->getTemplate()->getVersion() >= 3 && $this->View()->sTarget == 'checkout') {
            $this->Request()->setControllerName('checkout');
            return $this->View()->loadTemplate('frontend/account/select_shipping_checkout.tpl');
        }
    }

    /**
     * Send new account password
     */
    public function passwordAction()
    {
        $this->View()->sTarget = $this->Request()->getParam('sTarget');

        if ($this->Request()->isPost()) {
            $checkUser = $this->sendResetPasswordConfirmationMail($this->Request()->getParam('email'));
            if (!empty($checkUser['sErrorMessages'])) {
                $this->View()->sFormData = $this->Request()->getPost();
                $this->View()->sErrorFlag = $checkUser['sErrorFlag'];
                $this->View()->sErrorMessages = $checkUser['sErrorMessages'];
            } else {
                $this->View()->sSuccess = true;
            }
        }
    }

    /**
     * Send a mail asking the customer, if he actually wants to reset his password
     * @param string $email
     * @return array
     */
    public function sendResetPasswordConfirmationMail($email)
    {
        $snippets = Shopware()->Snippets()->getNamespace('frontend/account/password');

        if (empty($email)) {
            return array('sErrorMessages' => array($snippets->get('ErrorForgotMail')));
        }

        $userID = Shopware()->System()->sMODULES['sAdmin']->sGetUserByMail($email);
        if (empty($userID)) {
            return array('sErrorMessages' => array($snippets->get('ErrorForgotMailUnknown')));
        }

        $hash = \Shopware\Components\Random::getAlphanumericString(32);

        $context = array(
            'sUrlReset' => $this->Front()->Router()->assemble(array('controller' => 'account', 'action'=>'resetPassword', 'hash'=>$hash)),
            'sUrl'      => $this->Front()->Router()->assemble(array('controller' => 'account', 'action'=>'resetPassword')),
            'sKey'      => $hash
        );

        // Send mail
        $mail = Shopware()->TemplateMail()->createMail('sCONFIRMPASSWORDCHANGE', $context);
        $mail->addTo($email);
        $mail->send();

        // Add the hash to the optin table
        $sql = "INSERT INTO `s_core_optin` (`type`, `datum`, `hash`, `data`) VALUES ('password', NOW(), ?, ?)";
        Shopware()->Db()->query($sql, array($hash, $userID));
    }

    /**
     * Shows the reset password form and triggers password reset on submit
     */
    public function resetPasswordAction()
    {
        $hash = $this->Request()->getParam('hash', null);
        $newPassword = $this->Request()->getParam('password', null);
        $passwordConfirmation = $this->Request()->getParam('passwordConfirmation', null);

        $this->View()->hash = $hash;

        if (!$this->Request()->isPost()) {
            return;
        }

        list($errors, $errorMessages) = $this->validatePasswordResetForm($hash, $newPassword, $passwordConfirmation);

        if (empty($errors)) {
            try {
                $customerModel = $this->resetPassword($hash, $newPassword);
            } catch (\Exception $e) {
                $errorMessages[] = $e->getMessage();
            }
        }

        if (!empty($errorMessages)) {
            $this->View()->sErrorFlag = $errors;
            $this->View()->sErrorMessages = $errorMessages;
            return;
        }

        // Perform a login for the user and redirect him to his account
        $this->admin->sSYSTEM->_POST['email'] = $customerModel->getEmail();
        $this->admin->sLogin();

        if (!$target = $this->Request()->getParam('sTarget')) {
            $target = 'account';
        }

        $this->redirect(array(
            'controller' => $target,
            'action' => 'index',
            'success' => 'resetPassword'
        ));
    }

    /**
     * Validates the data of the password reset form
     * @param string $hash
     * @param string $newPassword
     * @param string $passwordConfirmation
     * @return array
     */
    public function validatePasswordResetForm($hash, $newPassword, $passwordConfirmation)
    {
        $errors = array();
        $errorMessages = array();
        $resetPasswordNamespace = $this->container->get('snippets')->getNamespace('frontend/account/reset_password');
        $frontendNamespace = $this->container->get('snippets')->getNamespace('frontend');

        if (empty($hash)) {
            $errors['hash'] = true;
            $errorMessages[] = $resetPasswordNamespace->get(
                'PasswordResetNewLinkError',
                'Confirmation link not found. Note that the confirmation link is only valid for 2 hours. After that you have to request a new confirmation link.'
            );
        }

        if ($newPassword !== $passwordConfirmation) {
            $errors['password'] = true;
            $errors['passwordConfirmation'] = true;
            $errorMessages[] = $frontendNamespace->get(
                'RegisterPasswordNotEqual',
                'The passwords do not match.'
            );
        }

        if (!$newPassword
            || strlen(trim($newPassword)) == 0
            || !$passwordConfirmation
            || (strlen($newPassword) < Shopware()->Config()->sMINPASSWORD)
        ) {
            $errorMessages[] = $this->View()->fetch('string:'.$frontendNamespace->get(
                'RegisterPasswordLength',
                'Your password should contain at least {config name=\"MinPassword\"} characters'
            ));
            $errors['password'] = true;
            $errors['passwordConfirmation'] = true;
        }

        return array($errors, $errorMessages);
    }

    /**
     * Performs a password reset based on a given s_core_optin hash
     * @param string $hash
     * @param string $password
     * @return Customer
     * @throws Exception
     */
    public function resetPassword($hash, $password)
    {
        $resetPasswordNamespace = $this->container->get('snippets')->getNamespace('frontend/account/reset_password');

        $em = $this->get('models');

        $this->deleteExpiredOptInItems();

        /** @var $confirmModel \Shopware\Models\CommentConfirm\CommentConfirm */
        $confirmModel = $em->getRepository('Shopware\Models\CommentConfirm\CommentConfirm')
            ->findOneBy(array('hash' => $hash, 'type' => 'password'));

        if (!$confirmModel) {
            throw new Exception(
                $resetPasswordNamespace->get(
                    'PasswordResetNewLinkError',
                    'Confirmation link not found. Please check the spelling. Note that the confirmation link is only valid for 2 hours. After that you have to require a new confirmation link.'
                )
            );
        }

        /** @var $customer Customer */
        $customer = $em->find('Shopware\Models\Customer\Customer', $confirmModel->getData());
        if (!$customer) {
            throw new Exception($resetPasswordNamespace->get(
                sprintf('PasswordResetNewMissingId', $confirmModel->getData()),
                sprintf('Could not find the user with the ID "%s".', $confirmModel->getData())
            ));
        }

        // Generate the new password
        /** @var \Shopware\Components\Password\Manager $passwordEncoder */
        $passwordEncoder = $this->get('PasswordEncoder');

        $encoderName = $passwordEncoder->getDefaultPasswordEncoderName();
        $password = $passwordEncoder->encodePassword($password, $encoderName);

        $conn = $this->get('dbal_connection');
        $conn->executeUpdate(
            'UPDATE s_user SET password = ?, encoder = ? WHERE id = ?',
            [$password, $encoderName, $customer->getId()]
        );

        // Delete the confirm model
        $em->remove($confirmModel);
        $em->flush();

        return $customer;
    }

    /**
     * Delete old expired password-hashes after two hours
     */
    private function deleteExpiredOptInItems()
    {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $this->get('dbal_connection');

        $connection->executeUpdate(
            'DELETE FROM s_core_optin WHERE datum <= (NOW() - INTERVAL 2 HOUR) AND type = "password"'
        );
    }

    /**
     * Login account by ajax request
     *
     * @deprecated only used for SW4.x templates
     */
    public function ajaxLoginAction()
    {
        Enlight()->Plugins()->Controller()->Json()->setPadding();

        // Fix same origin miss match
        $response = $this->Response();
        $shop = Shopware()->Shop();
        if ($shop->getSecure()) {
            $response->setHeader(
                'Access-Control-Allow-Origin',
                'http://' . $shop->getHost()
            );
            $response->setHeader(
                'Access-Control-Allow-Methods', 'POST, GET'
            );
            $response->setHeader(
                'Access-Control-Allow-Credentials', 'true'
            );
        }

        if ($this->admin->sCheckUser()) {
            return $this->View()->setTemplate();
        }

        if (!$this->Request()->getParam('accountmode')) {
            return;
        }

        if (empty(Shopware()->Session()->sRegister)) {
            Shopware()->Session()->sRegister = array();
        }

        if ($this->Request()->getParam('accountmode')==0 || $this->Request()->getParam('accountmode')==1) {
            Shopware()->Session()->sRegister['auth']['email'] = $this->admin->sSYSTEM->_POST['email'];
            Shopware()->Session()->sRegister['auth']['accountmode'] = (int) $this->Request()->getParam('accountmode');

            $this->View()->setTemplate();
        } else {
            $checkData = $this->admin->sLogin();

            if (empty($checkData['sErrorMessages'])) {
                $this->refreshBasket();
                $this->View()->setTemplate();
            } else {
                $this->View()->sFormData = $this->Request()->getParams();
                $this->View()->sErrorFlag = $checkData['sErrorFlag'];
                $this->View()->sErrorMessages = $checkData['sErrorMessages'];
            }
        }
    }

    /**
     * Logout account by ajax request
     */
    public function ajaxLogoutAction()
    {
        Enlight()->Plugins()->Controller()->Json()->setPadding();

        $this->admin->logout();
    }

    /**
     *
     */
    protected function refreshBasket()
    {
        Shopware()->Modules()->Basket()->sRefreshBasket();
    }
}
