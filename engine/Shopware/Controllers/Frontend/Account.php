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
use Shopware\Bundle\AccountBundle\Form\Account\EmailUpdateFormType;
use Shopware\Bundle\AccountBundle\Form\Account\PasswordUpdateFormType;
use Shopware\Bundle\AccountBundle\Form\Account\ProfileUpdateFormType;
use Shopware\Bundle\AccountBundle\Form\Account\ResetPasswordFormType;
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
     * @var \Shopware\Bundle\AccountBundle\Service\CustomerServiceInterface
     */
    protected $customerService;

    /**
     * Init controller method
     */
    public function init()
    {
        $this->admin = Shopware()->Modules()->Admin();
        $this->customerService = Shopware()->Container()->get('shopware_account.customer_service');
    }

    /**
     * Pre dispatch method
     */
    public function preDispatch()
    {
        $this->View()->setScope(Enlight_Template_Manager::SCOPE_PARENT);
        if (!in_array($this->Request()->getActionName(), array('login', 'logout', 'password', 'resetPassword'))
            && !$this->admin->sCheckUser()) {
            return $this->forward('index', 'register');
        }
        $userData = $this->admin->sGetUserData();

        $activeBillingAddressId = $userData['additional']['user']['default_billing_address_id'];
        $activeShippingAddressId = $userData['additional']['user']['default_shipping_address_id'];

        $this->View()->assign('activeBillingAddressId', $activeBillingAddressId);
        $this->View()->assign('activeShippingAddressId', $activeShippingAddressId);
        $this->View()->assign('sUserData', $userData);
        $this->View()->assign('sUserLoggedIn', $this->admin->sCheckUser());
        $this->View()->assign('sAction', $this->Request()->getActionName());
    }

    /**
     * Index action method
     */
    public function indexAction()
    {
        if (
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
            $data = $paymentClass->getCurrentPaymentDataAsArray($this->get('session')->get('sUserId'));
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
                                            ->findOneBy(array('customerId' => $this->get('session')->get('sUserId')));
        if (!empty($partnerModel)) {
            $this->View()->partnerId = $partnerModel->getId();
            $this->get('session')->partnerId = $partnerModel->getId();
        }
    }

    /**
     * Partner Statistic action method
     * This action returns all data for the partner statistic page
     *
     */
    public function partnerStatisticAction()
    {
        $partnerId = $this->get('session')->partnerId;

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
        $repository = Shopware()->Models()->getRepository(\Shopware\Models\Partner\Partner::class);

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

        $this->forward('index', 'register', 'frontend', [
            'sTarget' => $this->Request()->getParam('sTarget')
        ]);
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
                    Shopware()->Db()->query($deleteSQL, array($this->get('session')->get('sUserId')));
                }

                $this->admin->sUpdatePayment();

                if ($checkData['sPaymentObject'] instanceof \ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod) {
                    $checkData['sPaymentObject']->savePaymentData($this->get('session')->get('sUserId'), $this->Request());
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
            $this->get('session')->set('sNewsletter', $status);
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
            AND oe.userID=?
            AND oe.orderdetailsID=?
        ';
        $download = Shopware()->Db()->fetchRow($sql, array($this->get('session')->get('sUserId'), $esdID));

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
            $download = Shopware()->Db()->fetchRow($sql, array($this->get('session')->get('sUserId'), $esdID));
        }

        if (empty($download['file'])) {
            $this->View()->sErrorCode = 1;
            return $this->forward('downloads');
        }

        $file = 'files/'.Shopware()->Config()->get('sESDKEY').'/'.$download['file'];

        $filePath = Shopware()->DocPath() . $file;

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

        $userID = Shopware()->Modules()->Admin()->sGetUserByMail($email);
        if (empty($userID)) {
            return;
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
        $hash = $this->Request()->getParam('hash');
        $this->View()->assign('hash', $hash);

        try {
            $customer = $this->getCustomerByResetHash($hash);
        } catch (\Exception $ex) {
            $this->View()->assign('invalidToken', true);
            $this->View()->assign('sErrorMessages', [$ex->getMessage()]);
        }

        if (!$this->Request()->isPost()) {
            return;
        }

        $form = $this->createForm(ResetPasswordFormType::class, $customer);
        $form->handleRequest($this->Request());

        if (!$form->isValid()) {
            $errors = ['sErrorFlag' => [], 'sErrorMessages' => []];

            foreach ($form->getErrors(true) as $error) {
                $errors['sErrorFlag'][$error->getOrigin()->getName()] = true;
                $errors['sErrorMessages'][]= $this->View()->fetch('string:' . $error->getMessage());
            }

            $this->View()->assign($errors);
            return;
        }

        $customer->setEncoderName($this->get('PasswordEncoder')->getDefaultPasswordEncoderName());

        $this->customerService->update($customer);

        // Perform a login for the user and redirect him to his account
        $this->Request()->setPost(['email' => $customer->getEmail(), 'password' => $form->get('password')->getData()]);
        $this->admin->sLogin();

        if (!$target = $this->Request()->getParam('sTarget')) {
            $target = 'account';
        }

        $this->redirect(['controller' => $target, 'action' => 'index', 'success' => 'resetPassword']);
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
     *
     */
    protected function refreshBasket()
    {
        Shopware()->Modules()->Basket()->sRefreshBasket();
    }

    /**
     * Profile forms for main data, password and email
     */
    public function profileAction()
    {
        $errorFlags = [];
        $errorMessages = [];
        $postData = $this->Request()->getPost() ?: [];

        $defaultData = [
            'profile' => [
                'salutation' => $this->View()->sUserData['additional']['user']['salutation'],
                'title' => $this->View()->sUserData['additional']['user']['title'],
                'firstname' => $this->View()->sUserData['additional']['user']['firstname'],
                'lastname' => $this->View()->sUserData['additional']['user']['lastname'],
                'birthday' => [
                    'day' => null,
                    'month' => null,
                    'year' => null
                ]
            ]
        ];

        if (!empty($this->View()->sUserData['additional']['user']['birthday'])) {
            $datetime = new \DateTime($this->View()->sUserData['additional']['user']['birthday']);
            $defaultData['profile']['birthday']['year'] = $datetime->format('Y');
            $defaultData['profile']['birthday']['month'] = $datetime->format('m');
            $defaultData['profile']['birthday']['day'] = $datetime->format('d');
        }

        $formData = array_merge($defaultData, $postData);

        if ($this->Request()->getParam('errors')) {
            foreach ($this->Request()->getParam('errors') as $error) {
                $message = $this->View()->fetch('string:'.$error->getMessage());
                $errorFlags[$error->getOrigin()->getName()] = true;
                $errorMessages[] = $message;
            }

            $errorMessages = array_unique($errorMessages);
        }

        $this->View()->assign('form_data', $formData);
        $this->View()->assign('errorFlags', $errorFlags);
        $this->View()->assign('errorMessages', $errorMessages);
        $this->View()->assign('success', $this->Request()->getParam('success'));
        $this->View()->assign('section', $this->Request()->getParam('section'));
    }

    /**
     * Endpoint for changing the main profile data
     */
    public function saveProfileAction()
    {
        $userId = $this->get('session')->get('sUserId');

        /** @var Customer $customer */
        $customer = $this->get('models')->find(Customer::class, $userId);

        $form = $this->createForm(ProfileUpdateFormType::class, $customer);
        $form->handleRequest($this->Request());

        if ($form->isValid()) {
            $this->customerService->update($customer);
            $this->redirect(['controller' => 'account', 'action' => 'profile', 'success' => true, 'section' => 'profile']);
            return;
        }

        $this->forward('profile', 'account', 'frontend', ['section' => 'profile', 'errors' => $form->getErrors(true)]);
    }

    /**
     * Endpoint for changing the email
     */
    public function saveEmailAction()
    {
        $userId = $this->get('session')->get('sUserId');

        /** @var Customer $customer */
        $customer = $this->get('models')->find(Customer::class, $userId);

        $form = $this->createForm(EmailUpdateFormType::class, $customer);
        $form->handleRequest($this->Request());

        if ($form->isValid()) {
            $this->customerService->update($customer);
            $this->get('session')->set('sUserMail', $customer->getEmail());

            $this->redirect(['controller' => 'account', 'action' => 'profile', 'success' => true, 'section' => 'email']);
            return;
        }

        $this->forward('profile', 'account', 'frontend', ['section' => 'email', 'errors' => $form->getErrors(true)]);
    }

    /**
     * Endpoint for changing the password
     */
    public function savePasswordAction()
    {
        $userId = $this->get('session')->get('sUserId');
        /** @var Customer $customer */
        $customer = $this->get('models')->find(Customer::class, $userId);

        $form = $this->createForm(PasswordUpdateFormType::class, $customer);
        $form->handleRequest($this->Request());

        if ($form->isValid()) {
            $this->customerService->update($customer);
            $this->get('session')->set('sUserPassword', $customer->getPassword());

            $this->redirect(['controller' => 'account', 'action' => 'profile', 'success' => true, 'section' => 'password']);
            return;
        }

        $this->forward('profile', 'account', 'frontend', ['section' => 'password', 'errors' => $form->getErrors(true)]);
    }

    /**
     * @param string $hash
     * @return Customer
     * @throws Exception
     */
    private function getCustomerByResetHash($hash)
    {
        $resetPasswordNamespace = $this->get('snippets')->getNamespace('frontend/account/reset_password');

        $this->deleteExpiredOptInItems();

        /** @var $confirmModel \Shopware\Models\CommentConfirm\CommentConfirm */
        $confirmModel = $this->get('models')
            ->getRepository('Shopware\Models\CommentConfirm\CommentConfirm')
            ->findOneBy(['hash' => $hash, 'type' => 'password']);

        if (!$confirmModel) {
            throw new Exception(
                $resetPasswordNamespace->get(
                    'PasswordResetNewLinkError',
                    'Confirmation link not found. Please check the spelling. Note that the confirmation link is only valid for 2 hours. After that you have to require a new confirmation link.'
                )
            );
        }

        /** @var $customer Customer */
        $customer = $this->get('models')->find('Shopware\Models\Customer\Customer', $confirmModel->getData());
        if (!$customer) {
            throw new Exception($resetPasswordNamespace->get(
                sprintf('PasswordResetNewMissingId', $confirmModel->getData()),
                sprintf('Could not find the user with the ID "%s".', $confirmModel->getData())
            ));
        }

        return $customer;
    }
}
