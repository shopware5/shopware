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

use Shopware\Bundle\AccountBundle\Form\Account\AddressFormType;
use Shopware\Bundle\AccountBundle\Form\Account\PersonalFormType;
use Shopware\Bundle\AccountBundle\Service\RegisterServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;

/**
 * Register controller
 */
class Shopware_Controllers_Frontend_Register extends Enlight_Controller_Action
{
    /**
     * @var sAdmin
     */
    protected $admin;

    /**
     * Will be called from the dispatcher before an action is processed
     *
     * @return void
     */
    public function preDispatch()
    {
        $this->View()->setScope(Enlight_Template_Manager::SCOPE_PARENT);
    }

    /**
     * Will be called when no action is supplied
     *
     * @return void
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
            'accountMode' => $this->Request()->getParam('skipLogin')
        ]);

        if ($this->shouldRedirectToAccount()) {
            $this->forward('index', 'account');
            return;
        } elseif ($this->shouldRedirectToCheckout()) {
            $this->forward('confirm', 'checkout');
            return;
        } elseif ($this->shouldRedirectToTarget()) {
            $this->redirect(['controller' => $sTarget, 'action' => $sTargetAction]);
            return;
        }

        $this->View()->assign('register', $this->getRegisterData());
        $this->View()->assign('countryList', $this->getCountries());
    }

    /**
     * Checks the registration
     *
     * @return void
     */
    public function saveRegisterAction()
    {
        if (!$this->request->isPost()) {
            $this->forward('index');
            return;
        }

        /** @var ShopContextInterface $context */
        $context = $this->get('shopware_storefront.context_service')->getShopContext();

        /** @var Enlight_Components_Session_Namespace $session */
        $session = $this->get('session');

        /** @var Enlight_Event_EventManager $eventManager */
        $eventManager = $this->get('events');

        /** @var RegisterServiceInterface $registerService */
        $registerService = $this->get('shopware_account.register_service');

        $data = $this->getPostData();

        $customerForm = $this->createCustomerForm($data['register']['personal']);
        $billingForm = $this->createBillingForm($data['register']['billing']);

        $errors = [
            'personal' => $this->getFormErrors($customerForm),
            'billing' => $this->getFormErrors($billingForm),
            'shipping' => []
        ];

        $shipping = null;
        if ($this->isShippingProvided($data)) {
            $shippingForm = $this->createShippingForm($data['register']['shipping']);
            $shipping = $shippingForm->getData();
            $errors['shipping'] = $this->getFormErrors($shippingForm);
        }

        $errors['occurred'] = (
            !empty($errors['personal']) ||
            !empty($errors['shipping']) ||
            !empty($errors['billing'])
        );

        if ($errors['occurred']) {
            unset($data['register']['personal']['password']);
            unset($data['register']['personal']['passwordConfirmation']);
            unset($data['register']['personal']['emailConfirmation']);

            $this->View()->assign('errors', $errors);
            $this->View()->assign($data);
            $this->forward('index');
            return;
        }

        /** @var Customer $customer */
        $customer = $customerForm->getData();

        /** @var Address $billing */
        $billing = $billingForm->getData();

        $customer->setReferer((string) $session->offsetGet('sReferer'));
        $customer->setValidation((string) $data['register']['personal']['sValidation']);
        $customer->setAffiliate((int) $session->offsetGet('sPartner'));
        $customer->setPaymentId((int) $session->offsetGet("sPaymentID"));

        $registerService->register(
            $context->getShop(),
            $customer,
            $billing,
            $shipping
        );

        $this->writeSession($data, $customer);

        if ($customer->getAccountMode() == Customer::ACCOUNT_MODE_CUSTOMER) {
            $this->sendRegistrationMail($customer);
        }

        $this->loginCustomer($customer);

        $eventManager->notify(
            'Shopware_Modules_Admin_SaveRegister_Successful',
            [
                'id' => $customer->getId(),
                'billingID' => $customer->getDefaultBillingAddress()->getId(),
                'shippingID' => $customer->getDefaultShippingAddress()->getId()
            ]
        );

        $this->redirectCustomer();
    }

    public function ajaxValidateEmailAction()
    {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();

        $data = $this->getPostData();
        $customerForm = $this->createCustomerForm($data['register']['personal']);

        $errors = $this->getFormErrors($customerForm);
        $errors = [
            'email' => $errors['email'] ?: false,
            'emailConfirmation' => $errors['emailConfirmation'] ?: false
        ];

        $this->Response()->setHeader('Content-type', 'application/json', true);
        $this->Response()->setBody(json_encode($errors));
    }

    public function ajaxValidatePasswordAction()
    {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();

        $data = $this->getPostData();
        $customerForm = $this->createCustomerForm($data['register']['personal']);

        $errors = $this->getFormErrors($customerForm);
        $errors = [
            'password' => $errors['password'] ?: false,
            'passwordConfirmation' => $errors['passwordConfirmation'] ?: false
        ];

        $this->Response()->setHeader('Content-type', 'application/json', true);
        $this->Response()->setBody(json_encode($errors));
    }

    /**
     * @return bool
     */
    private function isUserLoggedIn()
    {
        return !empty($this->get('session')->offsetGet('sUserId'));
    }

    /**
     * @return bool
     */
    private function shouldRedirectToAccount()
    {
        if (!$this->isUserLoggedIn()) {
            return false;
        }
        return ($this->Request()->getParam('sValidation') || !Shopware()->Modules()->Basket()->sCountBasket());
    }

    /**
     * @return bool
     */
    private function shouldRedirectToCheckout()
    {
        return $this->isUserLoggedIn();
    }

    /**
     * @return bool
     */
    private function shouldRedirectToTarget()
    {
        return $this->isUserLoggedIn() && empty($this->get('session')->offsetGet('sRegisterFinished'));
    }

    /**
     * @param FormInterface $form
     * @return array
     */
    private function getFormErrors(FormInterface $form)
    {
        if ($form->isValid()) {
            return [];
        }
        $errors = [
            '' => $this->get('snippets')
                ->getNamespace('frontend/account/internalMessages')
                ->get('ErrorFillIn', 'Please fill in all red fields')
        ];

        foreach ($form->getErrors(true) as $error) {
            $errors[$error->getOrigin()->getName()] = $this->View()->fetch('string:' . $error->getMessage());
        }
        return $errors;
    }

    /**
     * @param array $data
     * @return bool
     */
    private function isShippingProvided(array $data)
    {
        return array_key_exists('shippingAddress', $data['register']['billing']);
    }

    /**
     * @return array
     */
    private function getPostData()
    {
        $data = $this->request->getPost();

        $countryStateName = "country_state_" . $data['register']['billing']['country'];
        $data['register']['billing']['state'] = $data['register']['billing'][$countryStateName];

        $countryStateName = "country_shipping_state_" . $data['register']['shipping']['country'];
        $data['register']['shipping']['state'] = $data['register']['shipping'][$countryStateName];
        $data['register']['billing'] += $data['register']['personal'];
        $data['register']['shipping']['phone'] = $data['register']['personal']['phone'];

        if (!$data['register']['personal']['accountmode']) {
            $data['register']['personal']['accountmode'] = Customer::ACCOUNT_MODE_CUSTOMER;
        }

        $data['register']['billing']['additional']['customer_type'] = $data['register']['personal']['customer_type'];

        return $data;
    }

    /**
     * @throws Enlight_Exception
     */
    private function getCustomerGroupKey()
    {
        $customerGroupKey = $this->request->getParam('sValidation', null);
        $customerGroupId = $this->get('dbal_connection')->fetchColumn(
            'SELECT id FROM s_core_customergroups WHERE `groupkey` = ?',
            [$customerGroupKey]
        );

        if ($customerGroupKey && !$customerGroupId) {
            throw new Enlight_Exception("Invalid customergroup");
        }

        $event = Shopware()->Events()->notifyUntil(
            'Shopware_Controllers_Frontend_Register_CustomerGroupRegister',
            ['subject' => $this, 'sValidation' => $customerGroupId]
        );

        if ($event) {
            return $this->get('config')->get('defaultCustomerGroup', 'EK');
        }

        return $customerGroupKey;
    }

    /**
     * @return array
     * @throws Enlight_Exception
     */
    private function getRegisterData()
    {
        $register = $this->View()->getAssign('register');
        if (!$register) {
            $register = [];
        }

        $session = $this->get('session');
        $register = array_replace_recursive([
            'personal' => [
                'sValidation' => $this->getCustomerGroupKey()
            ],
            'billing' => [
                'country' => $session->offsetGet('sCountry'),
                'state' => $session->offsetGet('sState')
            ]
        ], $register);

        return $register;
    }

    /**
     * @param array $data
     * @param Customer $customer
     */
    private function writeSession(array $data, Customer $customer)
    {
        /** @var Enlight_Components_Session_Namespace $session */
        $session = $this->get('session');
        $session->offsetSet('sRegister', $data['register']);
        $session->offsetSet('sOneTimeAccount', false);
        $session->offsetSet('sRegisterFinished', true);
        if ($customer->getAccountMode() === Customer::ACCOUNT_MODE_FAST_LOGIN) {
            $session->offsetSet('sOneTimeAccount', true);
        }
        $session->offsetSet('sCountry', $customer->getDefaultBillingAddress()->getCountry()->getId());
    }


    /**
     * @param Customer $customer
     * @throws Exception
     */
    private function loginCustomer(Customer $customer)
    {
        $this->front->Request()->setPost('email', $customer->getEmail());
        $this->front->Request()->setPost('passwordMD5', $customer->getPassword());
        Shopware()->Modules()->Admin()->sLogin(true);
    }

    /**
     * Redirects after registration to the corresponding controllers and actions
     */
    private function redirectCustomer()
    {
        $location = [
            'controller' => $this->Request()->getParam('sTarget', 'account'),
            'action' => $this->Request()->getParam('sTargetAction', 'index')
        ];

        if ($location === ['controller' => 'checkout', 'action' => 'confirm']) {
            $location = ['controller' => 'checkout', 'action' => 'shippingPayment'];
        }

        $this->redirect($location);
    }

    /**
     * @param array $data
     * @return Form
     */
    private function createCustomerForm(array $data)
    {
        $customer = new Customer();
        $form = $this->createForm(get_class($this->container->get('shopware_account.form.personalform')), $customer);
        $form->submit($data);
        return $form;
    }

    /**
     * @param array $data
     * @return Form
     */
    private function createBillingForm(array $data)
    {
        $address = new Address();
        $form = $this->createForm(get_class($this->container->get('shopware_account.form.addressform')), $address);
        $form->submit($data);
        return $form;
    }

    /**
     * @param array $data
     * @return Form
     */
    private function createShippingForm(array $data)
    {
        $address = new Address();
        $form = $this->createForm(get_class($this->container->get('shopware_account.form.addressform')), $address);
        $form->submit($data);
        return $form;
    }

    /**
     * @return array
     */
    private function getCountries()
    {
        $context = $this->get('shopware_storefront.context_service')->getShopContext();
        $service = $this->get('shopware_storefront.location_service');
        $countries = $service->getCountries($context);
        return $this->get('legacy_struct_converter')->convertCountryStructList($countries);
    }

    /**
     * @param Customer $customer
     */
    private function sendRegistrationMail(Customer $customer)
    {
        try {
            Shopware()->Modules()->Admin()->sSaveRegisterSendConfirmation($customer->getEmail());
        } catch (\Exception $e) {
            $message = sprintf("Could not send user registration email to address %s", $customer->getEmail());
            Shopware()->Container()->get('corelogger')->error($message, ['exception' => $e]);
        }
    }
}
