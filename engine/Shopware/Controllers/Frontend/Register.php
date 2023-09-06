<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

use Doctrine\DBAL\Connection;
use Shopware\Bundle\AccountBundle\Form\Account\AddressFormType;
use Shopware\Bundle\AccountBundle\Form\Account\PersonalFormType;
use Shopware\Bundle\AccountBundle\Service\RegisterServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\CountryGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Captcha\CaptchaValidator;
use Shopware\Components\Captcha\Exception\CaptchaNotFoundException;
use Shopware\Components\Model\Exception\ModelNotFoundException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

class Shopware_Controllers_Frontend_Register extends Enlight_Controller_Action
{
    /**
     * Will be called from the dispatcher before an action is processed
     */
    public function preDispatch()
    {
        $this->View()->setScope(Enlight_Template_Manager::SCOPE_PARENT);
    }

    /**
     * Will be called when no action is supplied
     */
    public function indexAction()
    {
        $sTarget = $this->Request()->getParam('sTarget', 'account');
        $sTargetAction = $this->Request()->getParam('sTargetAction', 'index');

        $this->View()->assign([
            'sTarget' => $sTarget,
            'sTargetAction' => $sTargetAction,
            'sEsd' => Shopware()->Modules()->Basket()->sCheckForESD(),
            'showNoAccount' => $this->Request()->getParam('showNoAccount', false),
            'accountMode' => $this->Request()->getParam('skipLogin'),
        ]);

        if ($this->shouldRedirectToAccount()) {
            $this->forward('index', 'account');

            return;
        }

        if ($this->shouldRedirectToCheckout()) {
            $this->forward('confirm', 'checkout');

            return;
        }

        if ($this->shouldRedirectToTarget()) {
            $this->redirect(['controller' => $sTarget, 'action' => $sTargetAction]);

            return;
        }

        $this->View()->assign('isAccountless', $this->get('session')->get('isAccountless'));
        $this->View()->assign('register', $this->getRegisterData());
        $this->View()->assign('countryList', $this->get('modules')->Admin()->sGetCountryList());
    }

    /**
     * Checks the registration
     */
    public function saveRegisterAction()
    {
        if (!$this->request->isPost()) {
            $this->forward('index');

            return;
        }

        /** @var ShopContextInterface $context */
        $context = $this->get(ContextServiceInterface::class)->getShopContext();

        /** @var Enlight_Components_Session_Namespace $session */
        $session = $this->get('session');

        /** @var RegisterServiceInterface $registerService */
        $registerService = $this->get(RegisterServiceInterface::class);

        $data = $this->getPostData();

        $customerForm = $this->createCustomerForm($data['register']['personal']);
        $billingForm = $this->createBillingForm($data['register']['billing']);

        $errors = [
            'personal' => $this->getFormErrors($customerForm),
            'billing' => $this->getFormErrors($billingForm),
            'shipping' => [],
            'captcha' => [],
        ];

        $shipping = null;
        if ($this->isShippingProvided($data)) {
            $shippingForm = $this->createShippingForm($data['register']['shipping']);
            $shipping = $shippingForm->getData();
            $errors['shipping'] = $this->getFormErrors($shippingForm);
        } else {
            /** @var Address $billing */
            $billing = $billingForm->getData();

            $billingCountry = $billing->getCountry();

            if ($billingCountry === null) {
                throw new RuntimeException('Billing address needs a country');
            }

            $country = $this->get(CountryGatewayInterface::class)->getCountry($billingCountry->getId(), $context);

            if (!$country->allowShipping()) {
                $errors['billing']['country'] = $this->get('snippets')->getNamespace('frontend/register/index')->get('CountryNotAvailableForShipping');
            }
        }

        $validCaptcha = $this->validateCaptcha($this->get(Shopware_Components_Config::class)->get('registerCaptcha'), $this->request);
        if (!$validCaptcha) {
            $errors['captcha'] = [
                $this->get('snippets')
                    ->getNamespace('widgets/captcha/custom_captcha')
                    ->get('invalidCaptchaMessage'),
            ];
        }

        $errors['occurred'] = !empty($errors['personal'])
            || !empty($errors['shipping'])
            || !empty($errors['billing'])
            || !empty($errors['captcha']);

        if ($errors['occurred']) {
            $this->handleRegisterError($data, $errors);

            return;
        }

        /** @var Customer $customer */
        $customer = $customerForm->getData();

        /** @var Address $billing */
        $billing = $billingForm->getData();

        $config = $this->container->get(Shopware_Components_Config::class);

        $accountMode = (int) $customer->getAccountMode();
        $doubleOptinWithAccount = ($accountMode === 0) && $config->get('optinregister');
        $doubleOptInAccountless = ($accountMode === 1) && $config->get('optinaccountless');

        $doubleOptinRegister = $doubleOptinWithAccount || $doubleOptInAccountless;
        $shop = $context->getShop();
        $shop->addAttribute('sendOptinMail', new Attribute([
            'sendOptinMail' => $doubleOptinRegister,
        ]));

        $customer->setReferer((string) $session->offsetGet('sReferer'));
        $customer->setValidation((string) ($data['register']['personal']['sValidation'] ?? ''));
        $customer->setAffiliate((int) $session->offsetGet('sPartner'));
        $customer->setPaymentId((int) $session->offsetGet('sPaymentID'));
        $customer->setDoubleOptinRegister($doubleOptinRegister);
        $customer->setDoubleOptinConfirmDate(null);

        /** @var Enlight_Event_EventManager $eventManager */
        $eventManager = $this->get('events');

        $errors = ['occurred' => false];
        $errors = $eventManager->filter(
            'Shopware_Modules_Admin_SaveRegister_BeforeRegister',
            $errors,
            [
                'customer' => $customer,
                'billing' => $billing,
                'shipping' => $shipping,
            ]
        );

        if ($errors['occurred']) {
            $this->handleRegisterError($data, $errors);

            return;
        }

        $registerService->register(
            $shop,
            $customer,
            $billing,
            $shipping
        );

        /*
         * Remove sensitive data before writing to the session
         */
        unset(
            $data['register']['personal']['password'],
            $data['register']['personal']['passwordConfirmation'],
            $data['register']['billing']['password'],
            $data['register']['billing']['passwordConfirmation']
        );

        if ($doubleOptinRegister) {
            $eventManager->notify(
                'Shopware_Modules_Admin_SaveRegister_DoubleOptIn_Waiting',
                [
                    'id' => $customer->getId(),
                    'billingID' => $customer->getDefaultBillingAddress()->getId(),
                    'shippingID' => $customer->getDefaultShippingAddress()->getId(),
                ]
            );

            $session->offsetSet('isAccountless', $accountMode === Customer::ACCOUNT_MODE_FAST_LOGIN);

            $this->redirectCustomer([
                'location' => 'register',
                'optinsuccess' => true,
            ]);

            return;
        }

        $this->saveRegisterSuccess($data, $customer);
        $this->redirectCustomer();
    }

    public function confirmValidationAction()
    {
        $connection = $this->container->get(Connection::class);

        $modelManager = $this->container->get(ModelManager::class);

        $hash = $this->Request()->get('sConfirmation');

        $sql = "SELECT `data` FROM `s_core_optin` WHERE `hash` = ? AND type = 'swRegister'";
        $result = $connection->fetchColumn($sql, [$hash]);

        // Triggers an Error-Message, which tells the customer that his confirmation link was invalid
        if (empty($result)) {
            $this->redirectCustomer([
                'optinhashinvalid' => true,
            ]);

            return;
        }

        try {
            $data = unserialize($result, ['allowed_classes' => false]);
        } catch (Throwable $e) {
            $data = false;
        }
        if ($data === false || !isset($data['customerId'])) {
            throw new InvalidArgumentException(sprintf('The data for hash \'%s\' is corrupted.', $hash));
        }
        $customerId = (int) $data['customerId'];

        $date = new DateTime();

        $customer = $modelManager->find(Customer::class, $customerId);
        if (!$customer instanceof Customer) {
            throw new ModelNotFoundException(Customer::class, $customerId);
        }

        // One-Time-Account
        if ($data['fromCheckout'] === true || $customer->getAccountMode() === 1) {
            $redirection = [
                'controller' => 'checkout',
                'action' => 'confirm',
            ];
        } else {
            $redirection = [
                'controller' => 'account',
                'action' => 'index',
            ];
        }

        $customer->setFirstLogin($date);
        $customer->setDoubleOptinConfirmDate($date);
        $customer->setActive(true);
        $customer->setRegisterOptInId(null);

        $modelManager->persist($customer);
        $modelManager->flush();

        $sql = "DELETE FROM `s_core_optin` WHERE `hash` = ?  AND type = 'swRegister'";
        $connection->executeQuery($sql, [$this->Request()->get('sConfirmation')]);

        $this->saveRegisterSuccess($data, $customer);
        $this->redirectCustomer(
            array_merge(['optinconfirmed' => true], $redirection)
        );
    }

    /**
     * @throws Exception
     *
     * @deprecated since 5.7.4. Will be removed in Shopware 5.8 without replacement
     */
    public function ajaxValidateEmailAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Response()->setHttpResponseCode(Response::HTTP_GONE);
    }

    public function ajaxValidatePasswordAction()
    {
        Shopware()->Front()->Plugins()->ViewRenderer()->setNoRender();

        $data = $this->getPostData();
        $customerForm = $this->createCustomerForm($data['register']['personal']);

        $errors = $this->getFormErrors($customerForm);
        $errors = [
            'password' => $errors['password'] ?: false,
            'passwordConfirmation' => $errors['passwordConfirmation'] ?: false,
        ];

        $this->Response()->headers->set('content-type', 'application/json', true);
        $this->Response()->setContent(json_encode($errors));
    }

    private function saveRegisterSuccess(array $data, Customer $customer): void
    {
        /** @var Enlight_Event_EventManager $eventManager */
        $eventManager = $this->get('events');

        $this->writeSession($data, $customer);
        $this->loginCustomer($customer);

        if ($customer->getAccountMode() == Customer::ACCOUNT_MODE_CUSTOMER) {
            $this->sendRegistrationMail($customer);
        }

        $eventManager->notify(
            'Shopware_Modules_Admin_SaveRegister_Successful',
            [
                'id' => $customer->getId(),
                'billingID' => $customer->getDefaultBillingAddress()->getId(),
                'shippingID' => $customer->getDefaultShippingAddress()->getId(),
            ]
        );
    }

    /**
     * Validates the captcha in the request
     */
    private function validateCaptcha(string $captchaName, Enlight_Controller_Request_Request $request): bool
    {
        /** @var CaptchaValidator $captchaValidator */
        $captchaValidator = $this->container->get('shopware.captcha.validator');

        try {
            $isValid = $captchaValidator->validateByName($captchaName, $request);
        } catch (CaptchaNotFoundException $exception) {
            $this->container->get('corelogger')->error($exception->getMessage());
            $isValid = $captchaValidator->validateByName('nocaptcha', $request);
        }

        if ($isValid) {
            return true;
        }

        return false;
    }

    private function isUserLoggedIn(): bool
    {
        return !empty($this->get('session')->offsetGet('sUserId'));
    }

    private function shouldRedirectToAccount(): bool
    {
        if (!$this->isUserLoggedIn()) {
            return false;
        }

        return $this->Request()->getParam('sValidation') || !Shopware()->Modules()->Basket()->sCountBasket();
    }

    private function shouldRedirectToCheckout(): bool
    {
        return $this->isUserLoggedIn();
    }

    private function shouldRedirectToTarget(): bool
    {
        return $this->isUserLoggedIn() && empty($this->get('session')->offsetGet('sRegisterFinished'));
    }

    /**
     * @param FormInterface<Customer>|FormInterface<Address> $form
     *
     * @return array<string, string>
     */
    private function getFormErrors(FormInterface $form): array
    {
        if ($form->isSubmitted() && $form->isValid()) {
            return [];
        }
        $errors = [
            '' => $this->get('snippets')
                ->getNamespace('frontend/account/internalMessages')
                ->get('ErrorFillIn', 'Please fill in all red fields'),
        ];

        foreach ($form->getErrors(true) as $error) {
            $errors[$error->getOrigin()->getName()] = $this->View()->fetch('string:' . $error->getMessage());
        }

        return $errors;
    }

    private function isShippingProvided(array $data): bool
    {
        return \array_key_exists('shippingAddress', $data['register']['billing']);
    }

    private function getPostData(): array
    {
        $data = $this->request->getPost();

        $countryStateName = 'country_state_' . $data['register']['billing']['country'];
        $data['register']['billing']['state'] = $data['register']['billing'][$countryStateName] ?? null;

        $countryStateName = 'country_shipping_state_' . ($data['register']['shipping']['country'] ?? null);
        $data['register']['shipping']['state'] = $data['register']['shipping'][$countryStateName] ?? null;
        $data['register']['billing'] += $data['register']['personal'];
        $data['register']['shipping']['phone'] = $data['register']['personal']['phone'] ?? null;

        if (!$data['register']['personal']['accountmode']) {
            $data['register']['personal']['accountmode'] = Customer::ACCOUNT_MODE_CUSTOMER;
        }

        $data['register']['billing']['additional']['customer_type'] = $data['register']['personal']['customer_type'];

        return $data;
    }

    private function getCustomerGroupKey(): ?string
    {
        $customerGroupKey = $this->request->getParam('sValidation');
        $customerGroupId = $this->get(Connection::class)->fetchColumn(
            'SELECT id FROM s_core_customergroups WHERE `groupkey` = ?',
            [$customerGroupKey]
        );

        if ($customerGroupKey && !$customerGroupId) {
            throw new Enlight_Exception('Invalid customergroup');
        }

        $event = Shopware()->Events()->notifyUntil(
            'Shopware_Controllers_Frontend_Register_CustomerGroupRegister',
            ['subject' => $this, 'sValidation' => $customerGroupId]
        );

        if ($event) {
            return $this->get(Shopware_Components_Config::class)->get('defaultCustomerGroup', 'EK');
        }

        return $customerGroupKey;
    }

    private function getRegisterData(): array
    {
        $register = $this->View()->getAssign('register');
        if (!$register) {
            $register = [];
        }

        $session = $this->get('session');
        $register = array_replace_recursive([
            'personal' => [
                'sValidation' => $this->getCustomerGroupKey(),
            ],
            'billing' => [
                'country' => $session->offsetGet('sCountry'),
                'state' => $session->offsetGet('sState'),
            ],
        ], $register);

        return $register;
    }

    private function writeSession(array $data, Customer $customer): void
    {
        $shippingCountry = $customer->getDefaultShippingAddress()->getCountry();

        if ($shippingCountry === null) {
            throw new RuntimeException('Invalid customer shipping address');
        }

        /** @var Enlight_Components_Session_Namespace $session */
        $session = $this->get('session');
        $session->offsetSet('sRegister', $data['register']);
        $session->offsetSet('sOneTimeAccount', false);
        $session->offsetSet('sRegisterFinished', true);
        $session->offsetSet('sCountry', $shippingCountry->getId());
        $session->offsetSet('sArea', $shippingCountry->getArea() !== null ? $shippingCountry->getArea()->getId() : 0);

        if ($customer->getAccountMode() === Customer::ACCOUNT_MODE_FAST_LOGIN) {
            $session->offsetSet('sOneTimeAccount', true);
        }
    }

    private function loginCustomer(Customer $customer): void
    {
        $this->front->Request()->setPost('email', $customer->getEmail());
        $this->front->Request()->setPost('passwordMD5', $customer->getPassword());
        Shopware()->Modules()->Admin()->sLogin(true);
    }

    /**
     * Redirects after registration to the corresponding controllers and actions
     */
    private function redirectCustomer(array $params = []): void
    {
        $location = [
            'controller' => $this->Request()->getParam('sTarget', 'account'),
            'action' => $this->Request()->getParam('sTargetAction', 'index'),
        ];

        if ($location === ['controller' => 'checkout', 'action' => 'confirm']) {
            $location = ['controller' => 'checkout', 'action' => 'shippingPayment'];
        }

        $this->redirect(array_merge($location, $params));
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return FormInterface<Customer>
     */
    private function createCustomerForm(array $data): FormInterface
    {
        $customer = new Customer();
        $form = $this->createForm(PersonalFormType::class, $customer);
        $form->submit($data);

        return $form;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return FormInterface<Address>
     */
    private function createBillingForm(array $data): FormInterface
    {
        $address = new Address();
        $form = $this->createForm(AddressFormType::class, $address);
        $form->submit($data);

        return $form;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return FormInterface<Address>
     */
    private function createShippingForm(array $data): FormInterface
    {
        $address = new Address();
        $form = $this->createForm(AddressFormType::class, $address);
        $form->submit($data);

        return $form;
    }

    private function sendRegistrationMail(Customer $customer): void
    {
        try {
            Shopware()->Modules()->Admin()->sSaveRegisterSendConfirmation($customer->getEmail());
        } catch (Exception $e) {
            $message = sprintf('Could not send user registration email to address %s', $customer->getEmail());
            $this->get('corelogger')->error($message, ['exception' => $e->getMessage()]);
        }
    }

    private function handleRegisterError(array $data, array $errors): void
    {
        unset(
            $data['register']['personal']['password'],
            $data['register']['personal']['passwordConfirmation'],
            $data['register']['personal']['emailConfirmation']
        );

        $this->View()->assign('errors', $errors);
        $this->View()->assign($data);
        $this->forward('index');
    }
}
