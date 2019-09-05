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
use Shopware\Bundle\StaticContentBundle\Exception\EsdNotFoundException;
use Shopware\Models\Customer\Customer;

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
        if ($this->shouldForwardToRegister()) {
            return $this->forward('index', 'register', 'frontend', $this->getForwardParameters());
        }
        $userData = $this->admin->sGetUserData();

        $activeBillingAddressId = $userData['additional']['user']['default_billing_address_id'];
        $activeShippingAddressId = $userData['additional']['user']['default_shipping_address_id'];

        if (!empty($userData['shippingaddress']['country']['id'])) {
            $country = $this->get('shopware_storefront.country_gateway')->getCountry($userData['shippingaddress']['country']['id'], $this->get('shopware_storefront.context_service')->getContext());
            $userData['shippingaddress']['country'] = $this->get('legacy_struct_converter')->convertCountryStruct($country);
        }

        $this->View()->assign('activeBillingAddressId', $activeBillingAddressId);
        $this->View()->assign('activeShippingAddressId', $activeShippingAddressId);
        $this->View()->assign('sUserData', $userData);
        $this->View()->assign('userInfo', $this->get('shopware_account.store_front_greeting_service')->fetch());
        $this->View()->assign('sUserLoggedIn', $this->admin->sCheckUser());
        $this->View()->assign('sAction', $this->request->getActionName());

        if ($this->isOneTimeAccount() && !in_array($this->request->getActionName(), ['abort', 'login', 'register'])) {
            $this->logoutAction();
            $this->redirect(['controller' => 'register']);
        }
    }

    /**
     * Index action method
     */
    public function indexAction()
    {
        if ($this->Request()->getParam('success')) {
            $this->View()->assign('sSuccessAction', $this->Request()->getParam('success'));
        }
    }

    /**
     * Payment action method
     *
     * Read and change payment mean and payment data
     */
    public function paymentAction()
    {
        $this->View()->assign('sPaymentMeans', $this->admin->sGetPaymentMeans());
        $this->View()->assign('sFormData', ['payment' => $this->View()->sUserData['additional']['user']['paymentID']]);
        $this->View()->assign('sTarget', $this->Request()->getParam('sTarget', $this->Request()->getControllerName()));
        $this->View()->assign('sTargetAction', $this->Request()->getParam('sTargetAction', 'index'));

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
            $this->View()->assign('sFormData', $values);
        }
    }

    /**
     * Orders action method
     *
     * Read last orders
     */
    public function ordersAction()
    {
        $destinationPage = (int) $this->Request()->sPage;
        $orderData = $this->admin->sGetOpenOrderData($destinationPage);
        $orderData = $this->applyTrackingUrl($orderData);

        $this->View()->assign('sOpenOrders', $orderData['orderData']);
        $this->View()->assign('sNumberPages', $orderData['numberOfPages']);
        $this->View()->assign('sPages', $orderData['pages']);

        // This has to be assigned here because the config method in smarty can't handle array structures
        $this->View()->assign('sDownloadAvailablePaymentStatus', Shopware()->Config()->get('downloadAvailablePaymentStatus'));
    }

    /**
     * Downloads action method
     *
     * Read last downloads
     */
    public function downloadsAction()
    {
        $destinationPage = (int) $this->Request()->sPage;

        if (empty($destinationPage)) {
            $destinationPage = 1;
        }

        $orderData = $this->admin->sGetDownloads($destinationPage);
        $this->View()->assign('sDownloads', $orderData['orderData']);
        $this->View()->assign('sNumberPages', $orderData['numberOfPages']);
        $this->View()->assign('sPages', $orderData['pages']);

        // This has to be assigned here because the config method in smarty can't handle array structures
        $this->View()->assign('sDownloadAvailablePaymentStatus', Shopware()->Config()->get('downloadAvailablePaymentStatus'));
    }

    /**
     * PartnerStatisticMenuItem action method
     *
     * The partner statistic menu item action displays
     * the menu item in the account menu
     */
    public function partnerStatisticMenuItemAction()
    {
        // Show partner statistic menu
        $partnerModel = Shopware()->Models()->getRepository('Shopware\Models\Partner\Partner')
                                            ->findOneBy(['customerId' => Shopware()->Session()->sUserId]);
        if (!empty($partnerModel)) {
            $this->View()->assign('partnerId', $partnerModel->getId());
            Shopware()->Session()->offsetSet('partnerId', $partnerModel->getId());
        }
    }

    /**
     * Partner Statistic action method
     * This action returns all data for the partner statistic page
     */
    public function partnerStatisticAction()
    {
        $partnerId = Shopware()->Session()->get('partnerId');

        if (empty($partnerId)) {
            return $this->forward('index');
        }

        $toDate = $this->Request()->get('toDate');
        $fromDate = $this->Request()->get('fromDate');

        // If a to date passed, format it over the \DateTime object. Otherwise create a new date with today
        if (empty($fromDate) || !Zend_Date::isDate($fromDate, 'Y-m-d')) {
            $fromDate = new \DateTime();
            $fromDate = $fromDate->sub(new DateInterval('P1M'));
        } else {
            $fromDate = new \DateTime($fromDate);
        }

        // If a to date passed, format it over the \DateTime object. Otherwise create a new date with today
        if (empty($toDate) || !Zend_Date::isDate($toDate, 'Y-m-d')) {
            $toDate = new \DateTime();
        } else {
            $toDate = new \DateTime($toDate);
        }

        $this->View()->assign('partnerStatisticToDate', $toDate->format('Y-m-d'));
        $this->View()->assign('partnerStatisticFromDate', $fromDate->format('Y-m-d'));

        // To get the right value cause 2012-02-02 is smaller than 2012-02-02 15:33:12
        $toDate = $toDate->add(new DateInterval('P1D'));

        /** @var \Shopware\Models\Partner\Repository $repository */
        $repository = Shopware()->Models()->getRepository(\Shopware\Models\Partner\Partner::class);

        // Get the information of the partner chart
        $userCurrencyFactor = Shopware()->Shop()->getCurrency()->getFactor();

        $dataQuery = $repository->getStatisticChartQuery($partnerId, $fromDate, $toDate, $userCurrencyFactor);
        $this->View()->assign('sPartnerOrderChartData', $dataQuery->getArrayResult());

        $dataQuery = $repository->getStatisticListQuery(null, null, null, $partnerId, false, $fromDate, $toDate, $userCurrencyFactor);
        $this->View()->assign('sPartnerOrders', $dataQuery->getArrayResult());

        $dataQuery = $repository->getStatisticListQuery(null, null, null, $partnerId, true, $fromDate, $toDate, $userCurrencyFactor);
        $this->View()->assign('sTotalPartnerAmount', $dataQuery->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY));
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
     * Abort action method
     *
     * Abort one time order and delete session
     */
    public function abortAction()
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
        $this->View()->assign('sTarget', $this->Request()->getParam('sTarget'));

        if ($this->Request()->isPost()) {
            $checkUser = $this->admin->sLogin();
            if (!empty($checkUser['sErrorMessages'])) {
                $this->View()->assign('sFormData', $this->Request()->getPost());
                $this->View()->assign('sErrorFlag', $checkUser['sErrorFlag']);
                $this->View()->assign('sErrorMessages', $checkUser['sErrorMessages']);
            } else {
                $this->refreshBasket();
            }
        }

        if (empty($this->View()->getAssign('sErrorMessages')) && $this->admin->sCheckUser()) {
            return $this->redirect(
                [
                    'controller' => $this->Request()->getParam('sTarget', 'account'),
                    'action' => $this->Request()->getParam('sTargetAction', 'index'),
                ]
            );
        }

        $this->forward('index', 'register', 'frontend', [
            'sTarget' => $this->Request()->getParam('sTarget'),
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
                    $this->View()->assign('sErrorFlag', $checkData['checkPayment']['sErrorFlag']);
                    $this->View()->assign('sErrorMessages', $checkData['checkPayment']['sErrorMessages']);
                }

                return $this->forward('payment');
            }
            $previousPayment = $this->admin->sGetUserData();
            $previousPayment = $previousPayment['additional']['user']['paymentID'];

            $previousPayment = $this->admin->sGetPaymentMeanById($previousPayment);
            if ($previousPayment['paymentTable']) {
                $deleteSQL = 'DELETE FROM ' . $previousPayment['paymentTable'] . ' WHERE userID=?';
                Shopware()->Db()->query($deleteSQL, [Shopware()->Session()->sUserId]);
            }

            $this->admin->sUpdatePayment();

            if ($checkData['sPaymentObject'] instanceof \ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod) {
                $checkData['sPaymentObject']->savePaymentData(Shopware()->Session()->sUserId, $this->Request());
            }
        }

        if (!$target = $this->Request()->getParam('sTarget')) {
            $target = 'account';
        }
        $targetAction = $this->Request()->getParam('sTargetAction', 'index');
        $this->redirect([
            'controller' => $target,
            'action' => $targetAction,
            'success' => 'payment',
        ]);
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
            $successMessage = $status ? 'newsletter' : 'deletenewsletter';
            if (Shopware()->Config()->optinnewsletter && $status) {
                $successMessage = 'optinnewsletter';
            }
            $this->View()->assign('sSuccessAction', $successMessage);
            $this->container->get('session')->offsetSet('sNewsletter', $status);
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
        $esdService = $this->container->get('shopware_static_content.service.esd_service');
        $downloadService = $this->container->get('shopware_static_content.service.download_service');
        $filesystem = $this->container->get('shopware.filesystem.private');
        $esdID = $this->request->getParam('esdID');

        if (empty($esdID)) {
            return $this->forward('downloads');
        }

        try {
            $download = $esdService->loadEsdOfCustomer($this->container->get('session')->offsetGet('sUserId'), $esdID);
        } catch (EsdNotFoundException $exception) {
            $this->forwardDownloadError(1);

            return;
        }

        if (empty($download->getFile())) {
            $this->forwardDownloadError(1);

            return;
        }

        $filePath = $esdService->getLocation($download);

        if ($filesystem->has($filePath) === false) {
            $this->forwardDownloadError(2);

            return;
        }

        try {
            $downloadService->send($filePath, $filesystem);
        } catch (\League\Flysystem\FileNotFoundException $exception) {
            $this->forwardDownloadError(2);
        }

        if ($this->isNotInUnitTestMode()) {
            exit;
        }

        if ($this->isNotInUnitTestMode()) {
            exit;
        }
    }

    /**
     * Send new account password
     */
    public function passwordAction()
    {
        $this->View()->assign('sTarget', $this->Request()->getParam('sTarget'));

        if ($this->Request()->isPost()) {
            $checkUser = $this->sendResetPasswordConfirmationMail($this->Request()->getParam('email'));
            if (!empty($checkUser['sErrorMessages'])) {
                $this->View()->assign('sFormData', $this->Request()->getPost());
                $this->View()->assign('sErrorFlag', $checkUser['sErrorFlag']);
                $this->View()->assign('sErrorMessages', $checkUser['sErrorMessages']);
            } else {
                $this->View()->assign('sSuccess', true);
            }
        }
    }

    /**
     * Send a mail asking the customer, if he actually wants to reset his password
     *
     * @param string $email
     *
     * @return array
     */
    public function sendResetPasswordConfirmationMail($email)
    {
        $snippets = Shopware()->Snippets()->getNamespace('frontend/account/password');

        if (empty($email)) {
            return ['sErrorMessages' => [$snippets->get('ErrorForgotMail')]];
        }

        $userID = Shopware()->Modules()->Admin()->sGetUserByMail($email);
        if (empty($userID)) {
            return [];
        }

        $hash = \Shopware\Components\Random::getAlphanumericString(32);

        $context = [
            'sUrlReset' => $this->Front()->Router()->assemble(['controller' => 'account', 'action' => 'resetPassword', 'hash' => $hash]),
            'sUrl' => $this->Front()->Router()->assemble(['controller' => 'account', 'action' => 'resetPassword']),
            'sKey' => $hash,
        ];

        $sql = 'SELECT 
          s_user.accountmode,
          s_user.active,
          s_user.affiliate,
          s_user.birthday,
          s_user.confirmationkey,
          s_user.customergroup,
          s_user.customernumber,
          s_user.email,
          s_user.failedlogins,
          s_user.firstlogin,
          s_user.lastlogin,
          s_user.language,
          s_user.internalcomment,
          s_user.lockeduntil,
          s_user.subshopID,
          s_user.title,
          s_user.salutation,
          s_user.firstname,
          s_user.lastname,
          s_user.lastlogin,
          s_user.newsletter
          FROM s_user
          WHERE id = ?';

        $user = $this->get('dbal_connection')->fetchAssoc($sql, [$userID]);
        $email = $user['email'];
        $user['attributes'] = $this->get('dbal_connection')->fetchAssoc('SELECT * FROM s_user_attributes WHERE userID = ?', [$userID]);

        $context['user'] = $user;

        // Send mail
        $mail = Shopware()->TemplateMail()->createMail('sCONFIRMPASSWORDCHANGE', $context);
        $mail->addTo($email);
        $mail->send();

        // Add the hash to the optin table
        $sql = "INSERT INTO `s_core_optin` (`type`, `datum`, `hash`, `data`) VALUES ('swPassword', NOW(), ?, ?)";
        Shopware()->Db()->query($sql, [$hash, $userID]);
    }

    /**
     * Shows the reset password form and triggers password reset on submit
     */
    public function resetPasswordAction()
    {
        $hash = $this->Request()->getParam('hash');
        $this->View()->assign('hash', $hash);
        $customer = null;

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
                $errors['sErrorMessages'][] = $this->View()->fetch('string:' . $error->getMessage());
            }

            $this->View()->assign($errors);

            return;
        }

        $customer->setEncoderName($this->get('passwordencoder')->getDefaultPasswordEncoderName());

        $this->get('models')->persist($customer);
        $this->get('models')->flush($customer);

        // Perform a login for the user and redirect him to his account
        $this->Request()->setPost(['email' => $customer->getEmail(), 'password' => $form->get('password')->getData()]);
        $this->admin->sLogin();

        if (!$target = $this->Request()->getParam('sTarget')) {
            $target = 'account';
        }

        $this->get('dbal_connection')->executeQuery(
            'DELETE FROM s_core_optin WHERE hash = ? AND type = ?',
            [$hash, 'swPassword']
        );

        $this->redirect(['controller' => $target, 'action' => 'index', 'success' => 'resetPassword']);
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
                'salutation' => $this->View()->getAssign('sUserData')['additional']['user']['salutation'],
                'title' => $this->View()->getAssign('sUserData')['additional']['user']['title'],
                'firstname' => $this->View()->getAssign('sUserData')['additional']['user']['firstname'],
                'lastname' => $this->View()->getAssign('sUserData')['additional']['user']['lastname'],
                'birthday' => [
                    'day' => null,
                    'month' => null,
                    'year' => null,
                ],
            ],
        ];

        if (!empty($this->View()->getAssign('sUserData')['additional']['user']['birthday'])) {
            $datetime = new \DateTime($this->View()->getAssign('sUserData')['additional']['user']['birthday']);
            $defaultData['profile']['birthday']['year'] = $datetime->format('Y');
            $defaultData['profile']['birthday']['month'] = $datetime->format('m');
            $defaultData['profile']['birthday']['day'] = $datetime->format('d');
        }

        $formData = array_merge($defaultData, $postData);

        if ($this->Request()->getParam('errors')) {
            foreach ($this->Request()->getParam('errors') as $error) {
                $message = $this->View()->fetch('string:' . $error->getMessage());
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
            $this->container->get('session')->offsetSet('userInfo', null);
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
            $this->get('session')->offsetSet('sUserMail', $customer->getEmail());
            $this->get('session')->offsetSet('userInfo', null);
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
            $this->get('session')->offsetSet('sUserPassword', $customer->getPassword());

            $this->redirect(['controller' => 'account', 'action' => 'profile', 'success' => true, 'section' => 'password']);

            return;
        }

        $this->forward('profile', 'account', 'frontend', ['section' => 'password', 'errors' => $form->getErrors(true)]);
    }

    protected function refreshBasket()
    {
        /** @var Shopware_Components_Modules $modules */
        $modules = $this->container->get('modules');
        $userData = $modules->Admin()->sGetUserData();
        $session = $this->container->get('session');

        $session->offsetSet('sCountry', (int) $userData['additional']['countryShipping']['id']);
        $session->offsetSet('sArea', (int) $userData['additional']['countryShipping']['areaID']);

        $this->container->get('shopware_storefront.context_service')->initializeContext();

        $modules->Basket()->sRefreshBasket();
    }

    private function forwardDownloadError(int $errorCode): void
    {
        $this->View()->assign('sErrorCode', $errorCode);

        $this->forward('downloads');
    }

    private function applyTrackingUrl(array $orderData): array
    {
        foreach ($orderData['orderData'] as &$order) {
            if (!empty($order['trackingcode']) && !empty($order['dispatch']) && !empty($order['dispatch']['status_link'])) {
                $order['dispatch']['status_link'] = $this->renderTrackingLink(
                    $order['dispatch']['status_link'],
                    $order['trackingcode']
                );
            }
        }

        return $orderData;
    }

    private function renderTrackingLink(string $link, string $trackingCode): string
    {
        $regEx = '/(\{\$offerPosition.trackingcode\})/';

        return preg_replace($regEx, $trackingCode, $link);
    }

    /**
     * Delete old expired password-hashes after two hours
     */
    private function deleteExpiredOptInItems()
    {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $this->get('dbal_connection');

        $connection->executeUpdate(
            'DELETE FROM s_core_optin WHERE datum <= (NOW() - INTERVAL 2 HOUR) AND type = "swPassword"'
        );
    }

    /**
     * @throws Exception
     */
    private function getCustomerByResetHash(string $hash): Customer
    {
        $resetPasswordNamespace = $this->container->get('snippets')->getNamespace('frontend/account/reset_password');

        $this->deleteExpiredOptInItems();

        /** @var \Shopware\Models\CommentConfirm\CommentConfirm|null $confirmModel */
        $confirmModel = $this->get('models')
            ->getRepository(\Shopware\Models\CommentConfirm\CommentConfirm::class)
            ->findOneBy(['hash' => $hash, 'type' => 'swPassword']);

        if (!$confirmModel) {
            throw new Exception(
                $resetPasswordNamespace->get(
                    'PasswordResetNewLinkError',
                    'Confirmation link not found. Please check the spelling. Note that the confirmation link is only valid for 2 hours. After that you have to require a new confirmation link.'
                )
            );
        }

        /** @var Customer|null $customer */
        $customer = $this->get('models')->find(\Shopware\Models\Customer\Customer::class, $confirmModel->getData());
        if (!$customer) {
            throw new Exception(
                $resetPasswordNamespace->get(
                    'PasswordResetNewMissingId',
                    'Your account could not be found. Please contact us to fix this problem.'
                )
            );
        }

        return $customer;
    }

    private function shouldForwardToRegister(): bool
    {
        return !in_array($this->Request()->getActionName(), ['login', 'logout', 'password', 'resetPassword'])
            && !$this->admin->sCheckUser();
    }

    private function getForwardParameters(): array
    {
        if (!$this->Request()->getParam('sTarget') && !$this->Request()->getParam('sTargetAction')) {
            return [
                'sTarget' => $this->Request()->getControllerName(),
                'sTargetAction' => $this->Request()->getActionName(),
            ];
        }

        return [
            'sTarget' => $this->Request()->getParam('sTarget'),
            'sTargetAction' => $this->Request()->getParam('sTargetAction'),
        ];
    }

    private function isOneTimeAccount(): bool
    {
        return $this->container->get('session')->offsetGet('sOneTimeAccount')
            || $this->View()->getAssign('sUserData')['additional']['user']['accountmode'] == 1;
    }

    private function isNotInUnitTestMode(): bool
    {
        return !$this->container->hasParameter('shopware.session.unitTestEnabled') || !$this->container->getParameter('shopware.session.unitTestEnabled');
    }
}
