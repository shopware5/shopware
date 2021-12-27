<?php

declare(strict_types=1);

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

use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use League\Flysystem\FileNotFoundException;
use Shopware\Bundle\AccountBundle\Form\Account\EmailUpdateFormType;
use Shopware\Bundle\AccountBundle\Form\Account\PasswordUpdateFormType;
use Shopware\Bundle\AccountBundle\Form\Account\ProfileUpdateFormType;
use Shopware\Bundle\AccountBundle\Form\Account\ResetPasswordFormType;
use Shopware\Bundle\AccountBundle\Service\CustomerServiceInterface;
use Shopware\Bundle\StaticContentBundle\Exception\EsdNotFoundException;
use Shopware\Bundle\StoreFrontBundle\Gateway\CountryGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Random;
use Shopware\Models\CommentConfirm\CommentConfirm;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Partner\Partner;
use ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

class Shopware_Controllers_Frontend_Account extends Enlight_Controller_Action
{
    /**
     * @deprecated - Will be private in Shopware 5.8
     *
     * @var sAdmin
     */
    protected $admin;

    /**
     * @deprecated - Will be private in Shopware 5.8
     *
     * @var CustomerServiceInterface
     */
    protected $customerService;

    /**
     * @return void
     */
    public function init()
    {
        $this->admin = Shopware()->Modules()->Admin();
        $this->customerService = Shopware()->Container()->get(CustomerServiceInterface::class);
    }

    public function preDispatch()
    {
        $this->View()->setScope(Smarty::SCOPE_PARENT);
        if ($this->shouldForwardToRegister()) {
            $this->forward('index', 'register', 'frontend', $this->getForwardParameters());

            return;
        }
        $customerData = $this->admin->sGetUserData();
        if (!\is_array($customerData)) {
            $this->forward('index', 'register', 'frontend', $this->getForwardParameters());

            return;
        }

        $activeBillingAddressId = $customerData['additional']['user']['default_billing_address_id'];
        $activeShippingAddressId = $customerData['additional']['user']['default_shipping_address_id'];

        if (!empty($customerData['shippingaddress']['country']['id'])) {
            $country = $this->get(CountryGatewayInterface::class)->getCountry($customerData['shippingaddress']['country']['id'], $this->get(ContextServiceInterface::class)->getContext());
            $customerData['shippingaddress']['country'] = $this->get(LegacyStructConverter::class)->convertCountryStruct($country);
        }

        $this->View()->assign('activeBillingAddressId', $activeBillingAddressId);
        $this->View()->assign('activeShippingAddressId', $activeShippingAddressId);
        $this->View()->assign('sUserData', $customerData);
        $this->View()->assign('userInfo', $this->get('shopware_account.store_front_greeting_service')->fetch());
        $this->View()->assign('sUserLoggedIn', $this->admin->sCheckUser());
        $this->View()->assign('sAction', $this->request->getActionName());

        if ($this->isOneTimeAccount() && !\in_array($this->request->getActionName(), ['abort', 'login', 'register'])) {
            $this->logoutAction();
            $this->redirect(['controller' => 'register']);
        }
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        if ($this->Request()->getParam('success')) {
            $this->View()->assign('sSuccessAction', $this->Request()->getParam('success'));
        }
    }

    /**
     * Read and change payment mean and payment data
     *
     * @return void
     */
    public function paymentAction()
    {
        $this->View()->assign('sPaymentMeans', $this->admin->sGetPaymentMeans());
        $this->View()->assign('sFormData', ['payment' => $this->View()->getAssign('sUserData')['additional']['user']['paymentID']]);
        $this->View()->assign('sTarget', $this->Request()->getParam('sTarget', $this->Request()->getControllerName()));
        $this->View()->assign('sTargetAction', $this->Request()->getParam('sTargetAction', 'index'));

        $getPaymentDetails = $this->admin->sGetPaymentMeanById($this->View()->getAssign('sFormData')['payment']);

        $paymentClass = $this->admin->sInitiatePaymentClass($getPaymentDetails);
        if ($paymentClass instanceof BasePaymentMethod) {
            $data = $paymentClass->getCurrentPaymentDataAsArray(Shopware()->Session()->get('sUserId'));
            if (!empty($data)) {
                $this->View()->assign('sFormData', $this->View()->getAssign('sFormData') + $data);
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
     * Read last orders
     *
     * @return void
     */
    public function ordersAction()
    {
        $destinationPage = (int) $this->Request()->get('sPage');
        $orderData = $this->admin->sGetOpenOrderData($destinationPage);
        $orderData = $this->applyTrackingUrl($orderData);

        $this->View()->assign('sOpenOrders', $orderData['orderData']);
        $this->View()->assign('sNumberPages', $orderData['numberOfPages']);
        $this->View()->assign('sPages', $orderData['pages']);

        // This has to be assigned here because the config method in smarty can't handle array structures
        $this->View()->assign('sDownloadAvailablePaymentStatus', Shopware()->Config()->get('downloadAvailablePaymentStatus'));
    }

    /**
     * Read last downloads
     *
     * @return void
     */
    public function downloadsAction()
    {
        $destinationPage = (int) $this->Request()->get('sPage');

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
     * The partner statistic menu item action displays
     * the menu item in the account menu
     *
     * @return void
     */
    public function partnerStatisticMenuItemAction()
    {
        // Show partner statistic menu
        $partnerModel = $this->get('models')->getRepository(Partner::class)->findOneBy(['customerId' => Shopware()->Session()->get('sUserId')]);
        if (!empty($partnerModel)) {
            $this->View()->assign('partnerId', $partnerModel->getId());
            Shopware()->Session()->offsetSet('partnerId', $partnerModel->getId());
        }
    }

    /**
     * This action returns all data for the partner statistic page
     *
     * @return void
     */
    public function partnerStatisticAction()
    {
        $partnerId = Shopware()->Session()->get('partnerId');

        if (empty($partnerId)) {
            $this->forward('index');

            return;
        }

        $toDate = $this->Request()->get('toDate');
        $fromDate = $this->Request()->get('fromDate');

        // If a "fromDate" is passed, format it over the \DateTime object. Otherwise, create a new date with today
        if (empty($fromDate) || !Zend_Date::isDate($fromDate, 'Y-m-d')) {
            $fromDate = new DateTime();
            $fromDate = $fromDate->sub(new DateInterval('P1M'));
        } else {
            $fromDate = new DateTime($fromDate);
        }

        // If a "toDate" is passed, format it over the \DateTime object. Otherwise, create a new date with today
        if (empty($toDate) || !Zend_Date::isDate($toDate, 'Y-m-d')) {
            $toDate = new DateTime();
        } else {
            $toDate = new DateTime($toDate);
        }

        $this->View()->assign('partnerStatisticToDate', $toDate->format('Y-m-d'));
        $this->View()->assign('partnerStatisticFromDate', $fromDate->format('Y-m-d'));

        // To get the right value cause 2012-02-02 is smaller than 2012-02-02 15:33:12
        $toDate = $toDate->add(new DateInterval('P1D'));

        $repository = $this->get('models')->getRepository(Partner::class);

        // Get the information of the partner chart
        $customerCurrencyFactor = Shopware()->Shop()->getCurrency()->getFactor();

        $dataQuery = $repository->getStatisticChartQuery($partnerId, $fromDate, $toDate, $customerCurrencyFactor);
        $this->View()->assign('sPartnerOrderChartData', $dataQuery->getArrayResult());

        $dataQuery = $repository->getStatisticListQuery(null, null, null, $partnerId, false, $fromDate, $toDate, $customerCurrencyFactor);
        $this->View()->assign('sPartnerOrders', $dataQuery->getArrayResult());

        $dataQuery = $repository->getStatisticListQuery(null, null, null, $partnerId, true, $fromDate, $toDate, $customerCurrencyFactor);
        $this->View()->assign('sTotalPartnerAmount', $dataQuery->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY));
    }

    /**
     * Logout account and delete session
     *
     * @return void
     */
    public function logoutAction()
    {
        $this->admin->logout();
    }

    /**
     * Abort one time order and delete session
     *
     * @return void
     */
    public function abortAction()
    {
        $this->admin->logout();
    }

    /**
     * Login account and show login errors
     *
     * @return void
     */
    public function loginAction()
    {
        $this->View()->assign('sTarget', $this->Request()->getParam('sTarget'));

        if ($this->Request()->isPost()) {
            $checkCustomer = $this->admin->sLogin();
            if (\is_array($checkCustomer) && !empty($checkCustomer['sErrorMessages'])) {
                $this->View()->assign('sFormData', $this->Request()->getPost());
                $this->View()->assign('sErrorFlag', $checkCustomer['sErrorFlag']);
                $this->View()->assign('sErrorMessages', $checkCustomer['sErrorMessages']);
            } else {
                $this->refreshBasket();
            }
        }

        if (empty($this->View()->getAssign('sErrorMessages')) && $this->admin->sCheckUser()) {
            $this->redirect(
                [
                    'controller' => $this->Request()->getParam('sTarget', 'account'),
                    'action' => $this->Request()->getParam('sTargetAction', 'index'),
                ]
            );

            return;
        }

        $this->forward('index', 'register', 'frontend', [
            'sTarget' => $this->Request()->getParam('sTarget'),
        ]);
    }

    /**
     * @return void
     */
    public function savePaymentAction()
    {
        if ($this->Request()->isPost()) {
            $sourceIsCheckoutConfirm = $this->Request()->getParam('sourceCheckoutConfirm');
            $values = $this->Request()->getPost('register');
            $frontRequest = $this->front->Request();
            if ($frontRequest === null) {
                throw new RuntimeException('Front controller has no request set');
            }
            $frontRequest->setPost('sPayment', $values['payment']);
            $checkData = $this->admin->sValidateStep3();

            if (!empty($checkData['checkPayment']['sErrorMessages']) || empty($checkData['sProcessed'])) {
                if (empty($sourceIsCheckoutConfirm)) {
                    $this->View()->assign('sErrorFlag', $checkData['checkPayment']['sErrorFlag']);
                    $this->View()->assign('sErrorMessages', $checkData['checkPayment']['sErrorMessages']);
                }

                $this->forward('payment');

                return;
            }
            $customerData = $this->admin->sGetUserData();
            $previousPayment = \is_array($customerData) ? $customerData['additional']['user']['paymentID'] : 0;

            $previousPaymentData = $this->admin->sGetPaymentMeanById($previousPayment);
            if ($previousPaymentData['paymentTable']) {
                $deleteSQL = 'DELETE FROM ' . $previousPaymentData['paymentTable'] . ' WHERE userID=?';
                Shopware()->Db()->query($deleteSQL, [Shopware()->Session()->get('sUserId')]);
            }

            $this->admin->sUpdatePayment();

            if ($checkData['sPaymentObject'] instanceof BasePaymentMethod) {
                $checkData['sPaymentObject']->savePaymentData(Shopware()->Session()->get('sUserId'), $this->Request());
            }
        }

        $target = $this->Request()->getParam('sTarget');
        if (!$target) {
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
     * Save newsletter address data
     *
     * @return void
     */
    public function saveNewsletterAction()
    {
        if ($this->Request()->isPost()) {
            $status = (bool) $this->Request()->getPost('newsletter');
            $customerMail = $this->admin->sGetUserMailById();
            if (!\is_string($customerMail)) {
                return;
            }

            $this->admin->sUpdateNewsletter($status, $customerMail, true);
            $successMessage = $status ? 'newsletter' : 'deletenewsletter';
            if (Shopware()->Config()->get('optinnewsletter') && $status) {
                $successMessage = 'optinnewsletter';
            }
            $this->View()->assign('sSuccessAction', $successMessage);
            $this->container->get('session')->offsetSet('sNewsletter', $status);
        }
        $this->forward('index');
    }

    /**
     * Read and test download file
     *
     * @return void
     */
    public function downloadAction()
    {
        $esdService = $this->container->get('shopware_static_content.service.esd_service');
        $downloadService = $this->container->get('shopware_static_content.service.download_service');
        $filesystem = $this->container->get('shopware.filesystem.private');
        $esdID = (int) $this->request->getParam('esdID', 0);

        if ($esdID === 0) {
            $this->forward('downloads');

            return;
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
        } catch (FileNotFoundException $exception) {
            $this->forwardDownloadError(2);
        }
    }

    /**
     * Send new account password
     *
     * @return void
     */
    public function passwordAction()
    {
        $this->View()->assign('sTarget', $this->Request()->getParam('sTarget'));

        if ($this->Request()->isPost()) {
            $checkCustomer = $this->sendResetPasswordConfirmationMail($this->Request()->getParam('email'));
            if (!empty($checkCustomer['sErrorMessages']) && !empty($checkCustomer['sErrorFlag'])) {
                $this->View()->assign('sFormData', $this->Request()->getPost());
                $this->View()->assign('sErrorFlag', $checkCustomer['sErrorFlag']);
                $this->View()->assign('sErrorMessages', $checkCustomer['sErrorMessages']);
            } else {
                $this->View()->assign('sSuccess', true);
            }
        }
    }

    /**
     * @deprecated - Will be private in Shopware 5.8
     * Send a mail asking the customer, if he actually wants to reset his password
     *
     * @param string $email
     *
     * @return array{sErrorMessages?: array<string>, sErrorFlag?: array{email: true}}
     */
    public function sendResetPasswordConfirmationMail($email)
    {
        $snippets = Shopware()->Snippets()->getNamespace('frontend/account/password');

        if (empty($email)) {
            return [
                'sErrorMessages' => [$snippets->get('ErrorForgotMail')],
                'sErrorFlag' => ['email' => true],
            ];
        }

        $customerId = Shopware()->Modules()->Admin()->sGetUserByMail($email);
        if (empty($customerId)) {
            return [];
        }

        $hash = Random::getAlphanumericString(32);

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

        $customer = $this->get(Connection::class)->fetchAssociative($sql, [$customerId]);
        $email = $customer['email'];
        $customer['attributes'] = $this->get(Connection::class)->fetchAssociative('SELECT * FROM s_user_attributes WHERE userID = ?', [$customerId]);

        $context['user'] = $customer;

        // Send mail
        $mail = Shopware()->TemplateMail()->createMail('sCONFIRMPASSWORDCHANGE', $context);
        $mail->addTo($email);
        $mail->send();

        // Add the hash to the optin table
        $sql = "INSERT INTO `s_core_optin` (`type`, `datum`, `hash`, `data`) VALUES ('swPassword', NOW(), ?, ?)";
        Shopware()->Db()->query($sql, [$hash, $customerId]);

        return [];
    }

    /**
     * Shows the reset password form and triggers password reset on submit
     *
     * @return void
     */
    public function resetPasswordAction()
    {
        $hash = (string) $this->Request()->getParam('hash');
        $this->View()->assign('hash', $hash);
        $customer = null;

        try {
            $customer = $this->getCustomerByResetHash($hash);
        } catch (Exception $ex) {
            $this->View()->assign('invalidToken', true);
            $this->View()->assign('sErrorMessages', [$ex->getMessage()]);
        }

        if (!$customer instanceof Customer) {
            $this->View()->assign('sErrorMessages', ['Customer not found']);

            return;
        }

        if (!$this->Request()->isPost()) {
            return;
        }

        $form = $this->createForm(ResetPasswordFormType::class, $customer);
        $form->handleRequest($this->Request());

        if ($form->isSubmitted() && !$form->isValid()) {
            $errors = ['sErrorFlag' => [], 'sErrorMessages' => []];

            foreach ($form->getErrors(true) as $error) {
                if (!$error instanceof FormError) {
                    continue;
                }
                if ($error->getOrigin() instanceof FormInterface) {
                    $errors['sErrorFlag'][$error->getOrigin()->getName()] = true;
                }
                $errors['sErrorMessages'][] = $this->View()->fetch('string:' . $error->getMessage());
            }

            $this->View()->assign($errors);

            return;
        }

        $customer->setEncoderName($this->get('passwordencoder')->getDefaultPasswordEncoderName());

        $this->get('models')->persist($customer);
        $this->get('models')->flush($customer);

        // Perform a login for customer and redirect to account
        $this->Request()->setPost(['email' => $customer->getEmail(), 'password' => $form->get('password')->getData()]);
        $this->admin->sLogin();

        $target = $this->Request()->getParam('sTarget');
        if (!$target) {
            $target = 'account';
        }

        $this->get(Connection::class)->executeQuery(
            'DELETE FROM s_core_optin WHERE hash = ? AND type = ?',
            [$hash, 'swPassword']
        );

        $this->redirect(['controller' => $target, 'action' => 'index', 'success' => 'resetPassword']);
    }

    /**
     * Profile forms for main data, password and email
     *
     * @return void
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
            $datetime = new DateTime($this->View()->getAssign('sUserData')['additional']['user']['birthday']);
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
     *
     * @return void
     */
    public function saveProfileAction()
    {
        $customerId = $this->get('session')->get('sUserId');

        $customer = $this->get(ModelManager::class)->find(Customer::class, $customerId);

        $form = $this->createForm(ProfileUpdateFormType::class, $customer);
        $form->handleRequest($this->Request());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->customerService->update($customer);
            $this->container->get('session')->offsetSet('userInfo', null);
            $this->redirect(['controller' => 'account', 'action' => 'profile', 'success' => true, 'section' => 'profile']);

            return;
        }

        $this->forward('profile', 'account', 'frontend', ['section' => 'profile', 'errors' => $form->getErrors(true)]);
    }

    /**
     * Endpoint for changing the email
     *
     * @return void
     */
    public function saveEmailAction()
    {
        $customerId = $this->get('session')->get('sUserId');

        $customer = $this->get(ModelManager::class)->find(Customer::class, $customerId);

        $form = $this->createForm(EmailUpdateFormType::class, $customer);
        $form->handleRequest($this->Request());

        if ($form->isSubmitted() && $form->isValid()) {
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
     *
     * @return void
     */
    public function savePasswordAction()
    {
        $customerId = $this->get('session')->get('sUserId');
        $customer = $this->get(ModelManager::class)->find(Customer::class, $customerId);

        $form = $this->createForm(PasswordUpdateFormType::class, $customer);
        $form->handleRequest($this->Request());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->customerService->update($customer);

            /*
             * Formatting the date as 'Y-m-d H:i:s' is needed, since it is read
             * directly from the DB in sAdmin::loginUser.
             */
            $this->get('session')->offsetSet('sUserPasswordChangeDate', $customer->getPasswordChangeDate()->format('Y-m-d H:i:s'));

            $this->redirect(['controller' => 'account', 'action' => 'profile', 'success' => true, 'section' => 'password']);

            return;
        }

        $this->forward('profile', 'account', 'frontend', ['section' => 'password', 'errors' => $form->getErrors(true)]);
    }

    /**
     * @deprecated - Will be private in Shopware 5.8
     *
     * @return void
     */
    protected function refreshBasket()
    {
        $modules = $this->container->get('modules');
        $customerData = $modules->Admin()->sGetUserData();
        $session = $this->container->get('session');

        if (\is_array($customerData)) {
            $session->offsetSet('sCountry', (int) $customerData['additional']['countryShipping']['id']);
            $session->offsetSet('sArea', (int) $customerData['additional']['countryShipping']['areaID']);
        }

        $this->container->get(ContextServiceInterface::class)->initializeContext();

        $modules->Basket()->sRefreshBasket();
    }

    private function forwardDownloadError(int $errorCode): void
    {
        $this->View()->assign('sErrorCode', $errorCode);

        $this->forward('downloads');
    }

    /**
     * @param array<string, mixed> $orderData
     *
     * @return array<string, mixed>
     */
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

    private function renderTrackingLink(string $link, string $trackingCode): ?string
    {
        $regEx = '/(\{\$offerPosition.trackingcode\})/';

        return preg_replace($regEx, $trackingCode, $link);
    }

    /**
     * Delete old expired password-hashes after two hours
     */
    private function deleteExpiredOptInItems(): void
    {
        $connection = $this->get(Connection::class);

        $connection->executeStatement(
            "DELETE FROM s_core_optin WHERE datum <= (NOW() - INTERVAL 2 HOUR) AND type = 'swPassword'"
        );
    }

    /**
     * @throws RuntimeException
     */
    private function getCustomerByResetHash(string $hash): Customer
    {
        $resetPasswordNamespace = $this->container->get('snippets')->getNamespace('frontend/account/reset_password');

        $this->deleteExpiredOptInItems();

        $confirmModel = $this->get(ModelManager::class)
            ->getRepository(CommentConfirm::class)
            ->findOneBy(['hash' => $hash, 'type' => 'swPassword']);

        if (!$confirmModel) {
            throw new RuntimeException($resetPasswordNamespace->get('PasswordResetNewLinkError', 'Confirmation link not found. Please check the spelling. Note that the confirmation link is only valid for 2 hours. After that you have to require a new confirmation link.'));
        }

        $customer = $this->get(ModelManager::class)->find(Customer::class, $confirmModel->getData());
        if (!$customer) {
            throw new RuntimeException($resetPasswordNamespace->get('PasswordResetNewMissingId', 'Your account could not be found. Please contact us to fix this problem.'));
        }

        return $customer;
    }

    private function shouldForwardToRegister(): bool
    {
        return !\in_array($this->Request()->getActionName(), ['login', 'logout', 'password', 'resetPassword'])
            && !$this->admin->sCheckUser();
    }

    /**
     * @return array{sTarget: string, sTargetAction: string}
     */
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
            || (int) $this->View()->getAssign('sUserData')['additional']['user']['accountmode'] === Customer::ACCOUNT_MODE_FAST_LOGIN;
    }
}
