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
 * @subpackage Frontend
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Account controller
 *
 * todo@all: Documentation
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
		if(!in_array($this->Request()->getActionName(), array('login', 'logout', 'password', 'ajax_login', 'ajax_logout'))
			&& !$this->admin->sCheckUser())
		{
			return $this->forward('login');
		}
		$this->View()->sUserData = $this->admin->sGetUserData();
	}

	/**
	 * Index action method
	 *
	 * Read orders and notes
	 */
	public function indexAction()
	{
		$this->View()->sOrders = $this->admin->sGetOpenOrderData();
		$this->View()->sNotes = Shopware()->Modules()->Basket()->sGetNotes();
		if($this->Request()->getParam('success')) {
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

		if(!empty($this->View()->sUserData['billingaddress']))
		{
			$address = $this->View()->sUserData['billingaddress'];
			$address['country'] = $address['countryID'];
            $address['country_state_'.$address['countryID']] = $address['stateID'];


			unset($address['id'], $address['countryID']);
			if(!empty($address['birthday']))
			{
				list($address['birthyear'], $address['birthmonth'], $address['birthday']) = explode('-', $address['birthday']);
			}
			if($this->Request()->isPost())
			{
				$address = array_merge($address, $this->Request()->getPost());
			}

			$this->View()->sFormData = $address;
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

		if(!empty($this->View()->sUserData['shippingaddress']))
		{
            if($this->Request()->isPost())
            {
                $address = array_merge($this->View()->sUserData['shippingaddress'], $this->Request()->getPost());
            }else {
                $address = $this->View()->sUserData['shippingaddress'];
            }

			$address['country'] = $address['countryID'];
            $address['country_shipping_state_'.$address['countryID']] = $address['stateID'];

			unset($address['id'], $address['countryID']);

			$this->View()->sFormData = $address;
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

		$getPaymentDetails = $this->admin->sGetPaymentMeanById($this->View()->sFormData['payment']);
		if ($getPaymentDetails['table'])
		{
			$paymentClass = $this->admin->sInitiatePaymentClass($getPaymentDetails);
			if (!empty($paymentClass))
			{
				$this->View()->sFormData += $paymentClass->getData();
			}
		}

		if($this->Request()->isPost())
		{
			$values = $this->Request()->getPost();
			$values['payment'] = $this->Request()->getPost('register');
			$values['payment'] = $values['payment']['payment'];
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
		$this->View()->sOpenOrders = $this->admin->sGetOpenOrderData();
	}

	/**
	 * Downloads action method
	 *
	 * Read last downloads
	 */
	public function downloadsAction()
	{
		$this->View()->sDownloads = $this->admin->sGetDownloads();
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
        $dataQuery = $repository->getStatisticChartQuery($partnerId, $fromDate, $toDate);
        $this->View()->sPartnerOrderChartData = $dataQuery->getArrayResult();

        $dataQuery = $repository->getStatisticListQuery(null, null, null, $partnerId, false, $fromDate, $toDate);
        $this->View()->sPartnerOrders = $dataQuery->getArrayResult();

        $dataQuery = $repository->getStatisticListQuery(null, null, null, $partnerId, true, $fromDate, $toDate);
        $this->View()->sTotalPartnerAmount = $dataQuery->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
    }

	/**
	 * Logout action method
	 *
	 * Logout account and delete session
	 */
	public function logoutAction()
	{
		Shopware()->Session()->unsetAll();
        $this->refreshBasket();
	}

	/**
	 * Login action method
	 *
	 * Login account and show login erros
	 */
	public function loginAction()
	{
		$this->View()->sTarget = $this->Request()->getParam('sTarget');

		if($this->Request()->isPost()) {
			$checkUser = $this->admin->sLogin();
			if (!empty($checkUser['sErrorMessages'])) {
				$this->View()->sFormData = $this->Request()->getPost();
				$this->View()->sErrorFlag = $checkUser['sErrorFlag'];
				$this->View()->sErrorMessages = $checkUser['sErrorMessages'];
			} else {
                $this->refreshBasket();
            }
		}

		if(empty($this->View()->sErrorMessages) && $this->admin->sCheckUser()) {
			if(!$target = $this->Request()->getParam('sTarget')) {
				$target = 'account';
			}
			$this->redirect(array('controller' => $target));
		}
	}

	/**
	 * Save billing action
	 *
	 * Save billing address data
	 */
	public function saveBillingAction()
	{
		if($this->Request()->isPost())
		{
			$rules = array(
				'salutation'=>array('required'=>1),
				'company'=>array('required'=>0),
				'firstname'=>array('required'=>1),
				'lastname'=>array('required'=>1),
				'street'=>array('required'=>1),
				'streetnumber'=>array('required'=>1),
				'zipcode'=>array('required'=>1),
				'city'=>array('required'=>1),
				'phone'=>array('required'=>1),
				'fax'=>array('required'=>0),
				'country'=>array('required'=>1),
				'department'=>array('required'=>0),
				'shippingAddress'=>array('required'=>0),
				//'ustid'=>array('required'=>0),
				'text1'=>array('required'=>0),
				'text2'=>array('required'=>0),
				'text3'=>array('required'=>0),
				'text4'=>array('required'=>0),
				'text5'=>array('required'=>0),
				'text6'=>array('required'=>0),
				'birthyear'=>array('required'=>0),
				'birthmonth'=>array('required'=>0),
				'birthday'=>array('required'=>0),
			);

            $values = $this->Request()->getPost('register');

            // State selection
            if (!empty($values["billing"]["country"])){
               $stateSelectionRequired = Shopware()->Db()->fetchRow("
               SELECT display_state_in_registration, force_state_in_registration
               FROM s_core_countries WHERE id = ?
                ",array($values["billing"]["country"]));
               if ($stateSelectionRequired["display_state_in_registration"] == true && $stateSelectionRequired["force_state_in_registration"] == true){
                   $rules["stateID"] = array("required" => true);
               }else {
                   $rules["stateID"] = array("required" => false);
               }

               if ($stateSelectionRequired["display_state_in_registration"] != true && $stateSelectionRequired["force_state_in_registration"] != true){
                   $this->admin->sSYSTEM->_POST["register"]["billing"]["stateID"] = $values["billing"]["stateID"] = 0;
               }else {
                   $this->admin->sSYSTEM->_POST["register"]["billing"]["stateID"] = $values["billing"]["stateID"] = $values["billing"]["country_state_".$values["billing"]["country"]];
               }

               unset($values["billing"]["country_state_".$values["billing"]["country"]]);
            }

			if ($this->Request()->getParam('sSelectAddress'))
			{
				$address = $this->admin->sGetPreviousAddresses('billing', $this->Request()->getParam('sSelectAddress'));
				if (!empty($address['hash']))
				{
					$address = array_merge($this->View()->sUserData['billingaddress'], $address);
					$this->admin->sSYSTEM->_POST = $address;
				}
			}



			if((!empty($values['personal']['customer_type'])&&$values['personal']['customer_type']=='private'))
			{
				$values['billing']['company'] = '';
				$values['billing']['department'] = '';
				$values['billing']['ustid'] = '';
			}
			elseif((!empty($values['personal']['customer_type'])||!empty($values['billing']['company'])))
			{
				$rules['ustid'] = array('required'=>0);
			}

			if(!empty($values))
			{
				$this->admin->sSYSTEM->_POST = array_merge($values['personal'], $values['billing'], $this->admin->sSYSTEM->_POST);
			}


			$checkData = $this->admin->sValidateStep2($rules, true);

			if (!empty($checkData['sErrorMessages']))
			{
				$this->View()->sErrorFlag = $checkData['sErrorFlag'];
				$this->View()->sErrorMessages = $checkData['sErrorMessages'];
				return $this->forward('billing');
			}
			else
			{
				$this->admin->sUpdateBilling();
			}
		}
		if(!$target = $this->Request()->getParam('sTarget'))
		{
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
		if($this->Request()->isPost())
		{
			$rules = array(
				'salutation'=>array('required'=>1),
				'company'=>array('required'=>0),
				'firstname'=>array('required'=>1),
				'lastname'=>array('required'=>1),
				'street'=>array('required'=>1),
				'streetnumber'=>array('required'=>1),
				'zipcode'=>array('required'=>1),
				'city'=>array('required'=>1),
				'department'=>array('required'=>0),
				'country'=>array('required'=>1),
				'text1'=>array('required'=>0),
				'text2'=>array('required'=>0),
				'text3'=>array('required'=>0),
				'text4'=>array('required'=>0),
				'text5'=>array('required'=>0),
				'text6'=>array('required'=>0)
			);

			if (Shopware()->Config()->get('sCOUNTRYSHIPPING')){
				$rules['country'] = array('required'=>1);
			} else {
				$rules['country'] = array('required'=>0);
			}

			if ($this->Request()->getParam('sSelectAddress'))
			{
				$address = $this->admin->sGetPreviousAddresses('shipping', $this->Request()->getParam('sSelectAddress'));
				if (!empty($address['hash']))
				{
					$address = array_merge($this->View()->sUserData['shippingaddress'], $address);
					$this->admin->sSYSTEM->_POST = $address;
				}
			}
			else
			{
				$this->admin->sSYSTEM->_POST =  $this->Request()->getPost();
			}

			$values = $this->Request()->getPost('register');

            // State selection
            if (!empty($values["shipping"]["country"]) && !empty($rules["country"])){
               $stateSelectionRequired = Shopware()->Db()->fetchRow("
               SELECT display_state_in_registration, force_state_in_registration
               FROM s_core_countries WHERE id = ?
                ",array($values["shipping"]["country"]));
               if ($stateSelectionRequired["display_state_in_registration"] == true && $stateSelectionRequired["force_state_in_registration"] == true){
                   $rules["stateID"] = array("required" => true);
               }else {
                   $rules["stateID"] = array("required" => false);
               }

               if ($stateSelectionRequired["display_state_in_registration"] == false && $stateSelectionRequired["force_state_in_registration"] == false){
                   $this->admin->sSYSTEM->_POST["register"]["shipping"]["stateID"] = $values["shipping"]["stateID"] = 0;
               }else {
                   $this->admin->sSYSTEM->_POST["register"]["shipping"]["stateID"] = $values["shipping"]["stateID"] = $values["shipping"]["country_shipping_state_".$values["shipping"]["country"]];
               }

               unset($values["shipping"]["country_shipping_state_".$values["shipping"]["country"]]);
            }



			if(!empty($values))
			{
				$this->admin->sSYSTEM->_POST = array_merge($values['shipping'], $this->admin->sSYSTEM->_POST);
			}

			$checkData = $this->admin->sValidateStep2ShippingAddress($rules, true);
			if (!empty($checkData['sErrorMessages']))
			{
				$this->View()->sErrorFlag = $checkData['sErrorFlag'];
				$this->View()->sErrorMessages = $checkData['sErrorMessages'];
				return $this->forward('shipping');
			}
			else
			{
				$this->admin->sUpdateShipping();
			}
		}
		if(!$target = $this->Request()->getParam('sTarget'))
		{
			$target = 'account';
		}
		$this->redirect(array('controller'=>$target, 'action'=>'index', 'success'=>'shipping'));
	}

	/**
	 * Save shipping action
	 *
	 * Save shipping address data
	 */
	public function savePaymentAction()
	{
		if($this->Request()->isPost())
		{
            $sourceIsCheckoutConfirm = $this->Request()->getParam('sourceCheckoutConfirm');
			$values = $this->Request()->getPost('register');
			$this->admin->sSYSTEM->_POST['sPayment'] = $values['payment'];

			$checkData = $this->admin->sValidateStep3();

			if (!empty($checkData['checkPayment']['sErrorMessages']) || empty($checkData['sProcessed']))
			{
                if(empty($sourceIsCheckoutConfirm)) {
				    $this->View()->sErrorFlag = $checkData['checkPayment']['sErrorFlag'];
				    $this->View()->sErrorMessages = $checkData['checkPayment']['sErrorMessages'];
                }
				return $this->forward('payment');
			}
			else
			{
				$previousPayment = $this->admin->sGetUserData();
				$previousPayment = $previousPayment['additional']['user']['paymentID'];

				$previousPayment = $this->admin->sGetPaymentMeanById($previousPayment);
				if ($previousPayment['paymentTable']){
					$deleteSQL = 'DELETE FROM '.$previousPayment['paymentTable'].' WHERE userID=?';
					Shopware()->Db()->query($deleteSQL, array(Shopware()->Session()->sUserId));
				}

				$this->admin->sUpdatePayment();

				if (method_exists($checkData['sPaymentObject'],'sUpdate')){
					$checkData['sPaymentObject']->sUpdate();
				}
			}
		}

		if(!$target = $this->Request()->getParam('sTarget'))
		{
			$target = 'account';
		}
		$this->redirect(array('controller'=>$target, 'action'=>'index', 'success'=>'payment'));
	}

	/**
	 * Save newsletter action
	 *
	 * Save newsletter address data
	 */
	public function saveNewsletterAction()
	{
		if($this->Request()->isPost())
		{
			$status = $this->Request()->getPost('newsletter') ? true : false;
			$this->admin->sUpdateNewsletter($status, $this->admin->sGetUserMailById(), true);
			$this->View()->sSuccessAction = 'newsletter';
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
		if($this->Request()->isPost())
		{
			$checkData = $this->admin->sValidateStep1(true);
			if (!empty($checkData["sErrorMessages"])){
				foreach ($checkData["sErrorMessages"] as $key=>$error_message) {
					$checkData["sErrorMessages"][$key] = $this->View()->fetch('string:'.$error_message);
				}
			}
			if (empty($checkData['sErrorMessages'])){
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

		if(empty($esdID))
		{
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

		if(empty($download))
		{
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

		if(empty($download['file']))
		{
			$this->View()->sErrorCode = 1;
			return $this->forward('downloads');
		}

		$file = 'files/'.Shopware()->Config()->get('sESDKEY').'/'.$download['file'];

        $filePath = Shopware()->OldPath() . $file;

        if (!file_exists($filePath)) {
            $this->View()->sErrorCode = 2;
            return $this->forward('downloads');
        }

        if (Shopware()->Config()->get("redirectDownload")) {
            $this->redirect($this->Request()->getBasePath() . '/' .  $file);
        } else {
            @set_time_limit(0);
            $this->Response()
                    ->setHeader('Content-Type', 'application/octet-stream')
                    ->setHeader('Content-Disposition', 'attachment; filename="'.$download['file'].'"')
                    ->setHeader('Content-Length', filesize($filePath));

            $this->Front()->Plugins()->ViewRenderer()->setNoRender();

            readfile($filePath);
        }
	}

	/**
	 * Read saved billing address
	 */
	public function selectBillingAction()
	{
		$this->View()->sTarget = $this->Request()->getParam('sTarget', $this->Request()->getControllerName());
		$this->View()->sBillingAddresses = $this->admin->sGetPreviousAddresses('billing');
	}

	/**
	 * Read saved shipping address
	 */
	public function selectShippingAction()
	{
		$this->View()->sTarget = $this->Request()->getParam('sTarget', $this->Request()->getControllerName());
		$this->View()->sShippingAddresses = $this->admin->sGetPreviousAddresses('shipping');
	}

	/**
	 * Send new account password
	 */
	public function passwordAction()
	{
		$this->View()->sTarget = $this->Request()->getParam('sTarget');

		if ($this->Request()->isPost()) {
			$checkUser = $this->sendPassword($this->Request()->getParam('email'));
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
	 * Send new password by email address
	 *
	 * @param string $email
	 * @return array
	 */
	public function sendPassword($email)
	{
        $snippets = Shopware()->Snippets()->getNamespace('frontend/account/password');

		if (empty($email)) {
			return array('sErrorMessages' => array($snippets->get('ErrorForgotMail')));
		}

		$userID = Shopware()->System()->sMODULES['sAdmin']->sGetUserByMail($email);
		if (empty($userID)) {
			return array('sErrorMessages' => array($snippets->get('ErrorForgotMailUnknown')));
		}

		$password = substr(md5(uniqid(rand())), 0, 6);

        $encoderName = Shopware()->PasswordEncoder()->getDefaultPasswordEncoderName();
        $hash     = Shopware()->PasswordEncoder()->encodePassword($password, $encoderName);

		$sql = "UPDATE s_user SET password=?, encoder=?, failedlogins=4, lockeduntil='lockeduntil' WHERE id=?";
		Shopware()->Db()->query($sql, array($hash, $encoderName, $userID));

        $context = array(
            'sMail'     => $email,
            'sPassword' => $password,
        );

        $mail = Shopware()->TemplateMail()->createMail('sPASSWORD', $context);
        $mail->addTo($email);
        $mail->send();

        return array('sSuccess' => true);
    }

	/**
	 * Login account by ajax request
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

		if($this->admin->sCheckUser()) {
			return $this->View()->setTemplate();
		}

		if(!$this->Request()->getParam('accountmode')) {
			return;
		}

		if (empty(Shopware()->Session()->sRegister)) {
			Shopware()->Session()->sRegister = array();
		}

		$this->admin->sSYSTEM->_POST = array();
		$this->admin->sSYSTEM->_POST['email'] = $this->Request()->getParam('email');
		$this->admin->sSYSTEM->_POST['password'] = $this->Request()->getParam('password');

		if($this->Request()->getParam('accountmode')==0 || $this->Request()->getParam('accountmode')==1) {
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
		Shopware()->Session()->unsetAll();
        $this->refreshBasket();
	}

    /**
     *
     */
    protected function refreshBasket()
    {
        Shopware()->Modules()->Basket()->sRefreshBasket();
    }
}
