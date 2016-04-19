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

/**
 * Register controller
 */
class Shopware_Controllers_Frontend_Register extends Enlight_Controller_Action
{
    /**
     * @var Enlight_Components_Session_Namespace
     */
    protected $session;

    /**
     * @var sAdmin
     */
    protected $admin;

    /**
     * @var sSystem
     */
    protected $system;

    protected $post;
    protected $error;

    /**
     * Calls when the controller will be initialized
     *
     * @return void
     */
    public function init()
    {
        $this->session = Shopware()->Session();
        $this->admin = Shopware()->Modules()->Admin();
        $this->system = Shopware()->Modules()->System();
        $this->post = $this->request->getParam('register');
    }

    /**
     * Will be called from the dispatcher before an action is processed
     *
     * @return void
     */
    public function preDispatch()
    {
        $this->View()->setScope(Enlight_Template_Manager::SCOPE_PARENT);

        if (!isset($this->View()->register)) {
            $this->View()->register = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
        }

        if (!isset($this->session['sRegister'])) {
            $this->session['sRegister'] = array();
        }

        if (in_array($this->Request()->getActionName(), array('ajax_validate_password', 'ajax_validate_billing', 'ajax_validate_email'))) {
            Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
        }
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
        $this->View()->showNoAccount = $this->Request()->getParam('showNoAccount', false);
        $this->View()->sEsd = Shopware()->Modules()->Basket()->sCheckForESD();
        $this->View()->sTarget = $sTarget;
        $this->View()->sTargetAction = $sTargetAction;

        if (!empty($this->session['sUserId'])) {
            if ($this->request->getParam('sValidation') || !Shopware()->Modules()->Basket()->sCountBasket()) {
                return $this->forward('index', 'account');
            } else {
                // If using the new template, the 'GET' action will be handled
                // in the Register controller (unified login/register page)
                if (empty($this->session['sRegisterFinished'])) {
                    return $this->redirect(array(
                        'controller' => $sTarget,
                        'action' => $sTargetAction
                    ));
                } else {
                    return $this->forward('confirm', 'checkout');
                }
            }
        }
        $skipLogin = $this->request->getParam('skipLogin');
        if ($skipLogin == "1") {
            $this->View()->skipLogin = $skipLogin;
        }

        if ($this->Request()->has('sValidation')) {
            $this->View()->assign('sValidation', $this->Request()->getParam('sValidation'));
        }

        $this->personalAction();
        $this->billingAction();
        $this->shippingAction();
        $this->paymentAction();
    }

    /**
     * Checks the registration
     *
     * @return void
     */
    public function saveRegisterAction()
    {
        if ($this->request->isPost()) {
            $this->savePersonalAction();
            $this->saveBillingAction();

            if (!empty($this->post['billing']['shippingAddress'])) {
                $this->saveShippingAction();
            }
            if (isset($this->post['payment'])) {
                $this->savePaymentAction();
            }
            if (empty($this->error)) {
                $this->saveRegister();

                // If using the new template, we need to check the target page
                // If its the checkout page, we need to stop by the shippingPayment page
                $sTarget = $this->Request()->getParam('sTarget', 'account');
                $sTargetAction = $this->Request()->getParam('sTargetAction', 'index');
                if ($sTarget == 'checkout' && $sTargetAction == 'confirm') {
                    $sTargetAction = 'shippingPayment';
                }
                return $this->redirect(array(
                    'action' => $sTargetAction,
                    'controller' => $sTarget,
                ));
            }
        }
        $this->forward('index');
    }

    /**
     * Saves the registration
     *
     * @return void
     */
    public function saveRegister()
    {
        $paymentData = isset($this->session['sRegister']['payment']['object']) ? $this->session['sRegister']['payment']['object'] : false;

        $this->admin->sSaveRegister();

        $userId = $this->admin->sSYSTEM->_SESSION["sUserId"];

        if (!empty($paymentData) && $userId) {
            $paymentObject = $this->admin->sInitiatePaymentClass($paymentData);
            if ($paymentObject instanceof \ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod) {
                $paymentObject->savePaymentData($userId, $this->request);
            }
        }
    }

    /**
     * Returns the personal information and validates it
     *
     * @throws Enlight_Exception
     * @return void
     */
    public function personalAction()
    {
        if (!isset($this->View()->register->personal)) {
            $this->View()->register->personal = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
        }
        if (!isset($this->View()->register->personal->form_data)) {
            $this->View()->register->personal->form_data = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
        }

        if (!empty($this->session['sRegister']['auth'])) {
            foreach ($this->session['sRegister']['auth'] as $key => $value) {
                if (!isset($this->View()->register->personal->form_data->$key)) {
                    $this->View()->register->personal->form_data->$key = $value;
                }
            }
        }

        if (!empty($this->session['sRegister']['billing'])) {
            foreach ($this->session['sRegister']['billing'] as $key => $value) {
                if (!isset($this->View()->register->personal->form_data->$key)) {
                    $this->View()->register->personal->form_data->$key = $value;
                }
            }
        }

        if ($this->request->getParam('sValidation')) {
            // For new b2bessentials plugin (replacement for customergroup module), do validation of this parameter
            $sValidation = $this->request->getParam('sValidation');
            // Simply check if this customergroup is valid
            if (Shopware()->Db()->fetchOne("SELECT id FROM s_core_customergroups WHERE `groupkey` = ? ", array($sValidation))) {
                // New event to do further validations in b2b customergroup plugin
                if (!Shopware()->Events()->notifyUntil('Shopware_Controllers_Frontend_Register_CustomerGroupRegister', array('subject'=>$this, 'sValidation'=>$sValidation))) {
                    $this->View()->register->personal->form_data->sValidation = $sValidation;
                }
            } else {
                throw new Enlight_Exception("Invalid customergroup");
            }
        }
    }

    /**
     * Saves and validates the personal information
     *
     * @return void
     */
    public function savePersonalAction()
    {
        if (!isset($this->View()->register->personal)) {
            $this->View()->register->personal = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
        }

        if (!empty($this->post['personal'])) {
            $this->View()->register->personal->form_data = new ArrayObject($this->post['personal'], ArrayObject::ARRAY_AS_PROPS);
        }

        $checkData = $this->validatePersonal();
        if (!empty($checkData['sErrorMessages'])) {
            foreach ($checkData['sErrorMessages'] as $key=>$error_message) {
                $checkData['sErrorMessages'][$key] = $this->View()->fetch('string:'.$error_message);
            }
            $this->error = true;
            $this->View()->register->personal->error_flags = new ArrayObject($checkData['sErrorFlag'], ArrayObject::ARRAY_AS_PROPS);
            $this->View()->register->personal->error_messages = new ArrayObject($checkData['sErrorMessages'], ArrayObject::ARRAY_AS_PROPS);
        }
    }

    /**
     * Returns the billing information and validates it
     *
     * @return void
     */
    public function billingAction()
    {
        if (!isset($this->View()->register->billing)) {
            $this->View()->register->billing = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
        }
        if (!isset($this->View()->register->billing->form_data)) {
            $this->View()->register->billing->form_data = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
        }

        $getCountryList =  $this->admin->sGetCountryList();

        $this->View()->register->billing->country_list = $getCountryList;

        if (!empty($this->session['sRegister']['billing'])) {
            foreach ($this->session['sRegister']['billing'] as $key => $value) {
                if (!isset($this->View()->register->billing->form_data->$key)) {
                    $this->View()->register->billing->form_data->$key = $value;
                }
            }
        }

        // setting the country and the states from the session
        if (!empty($this->session['sCountry']) && empty($this->View()->register->billing->form_data->country)) {
            $this->View()->register->billing->form_data->country = $this->session['sCountry'];
        }

        $countryStateName = "country_state_" . $this->View()->register->billing->form_data->country;
        if (!empty($this->session['sState']) && empty($this->View()->register->billing->form_data->$countryStateName)) {
            $this->View()->register->billing->form_data->$countryStateName = $this->session['sState'];
        }
    }

    /**
     * Saves and validates the billing information
     *
     * @return void
     */
    public function saveBillingAction()
    {
        if (!isset($this->View()->register->billing)) {
            $this->View()->register->billing = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
        }
        if (!empty($this->post['billing'])) {
            $this->View()->register->billing->form_data = new ArrayObject($this->post['billing'], ArrayObject::ARRAY_AS_PROPS);
            if (!empty($this->View()->register->billing->form_data['ustid'])) {
                $this->View()->register->billing->form_data['ustid'] = preg_replace('#[^0-9A-Z\+\*\.]#', '', strtoupper($this->View()->register->billing->form_data['ustid']));
            }
        }

        $checkData = $this->validateBilling();

        if (!empty($checkData['sErrorMessages'])) {
            $this->error = true;
            $this->View()->register->billing->error_flags = new ArrayObject($checkData['sErrorFlag'], ArrayObject::ARRAY_AS_PROPS);
            $this->View()->register->billing->error_messages = new ArrayObject($checkData['sErrorMessages'], ArrayObject::ARRAY_AS_PROPS);
        }
    }

    /**
     * Returns the shipping information and validates it
     *
     * @return void
     */
    public function shippingAction()
    {
        if (!isset($this->View()->register->shipping)) {
            $this->View()->register->shipping = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
        }
        if (!isset($this->View()->register->shipping->form_data)) {
            $this->View()->register->shipping->form_data = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
        }

        $this->View()->register->shipping->country_list = $this->admin->sGetCountryList();
        if (!empty($this->session['sRegister']['shipping'])) {
            foreach ($this->session['sRegister']['shipping'] as $key => $value) {
                $this->View()->register->shipping->form_data[$key] = $value;
            }
        }
    }

    /**
     * Saves and validates the shipping information
     *
     * @return void
     */
    public function saveShippingAction()
    {
        if (!isset($this->View()->register->shipping)) {
            $this->View()->register->shipping = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
        }
        if (!empty($this->post['shipping'])) {
            $this->View()->register->shipping->form_data = new ArrayObject($this->post['shipping'], ArrayObject::ARRAY_AS_PROPS);
        }

        $checkData = $this->validateShipping();

        if (!empty($checkData['sErrorMessages'])) {
            $this->error = true;
            $this->View()->register->shipping->error_flags = new ArrayObject($checkData['sErrorFlag'], ArrayObject::ARRAY_AS_PROPS);
            $this->View()->register->shipping->error_messages = new ArrayObject($checkData['sErrorMessages'], ArrayObject::ARRAY_AS_PROPS);
        }
    }

    /**
     * Returns the payment information and validates it
     *
     * @return void
     */
    public function paymentAction()
    {
        if (!isset($this->View()->register->payment)) {
            $this->View()->register->payment = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
        }

        if (!isset($this->View()->register->payment->form_data)) {
            if (!empty($this->session['sPayment'])) {
                $this->View()->register->payment->form_data = array('payment'=>$this->session['sPayment']);
            } else {
                $this->View()->register->payment->form_data = array('payment'=>Shopware()->Config()->get('DefaultPayment'));
            }
        }

        $this->View()->register->payment->payment_means = $this->admin->sGetPaymentMeans();

        if (!empty($this->session['sRegister']['shipping'])) {
            foreach ($this->session['sRegister']['shipping'] as $key => $value) {
                $this->View()->form_data->register['shipping'][$key] = $value;
            }
        }
    }

    /**
     * Saves and validates the payment information
     *
     * @return void
     */
    public function savePaymentAction()
    {
        if (!isset($this->View()->register->payment)) {
            $this->View()->register->payment = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
        }
        if (!empty($this->post['payment'])) {
            $this->View()->register->payment->form_data = $this->request->getPost();
            $this->View()->register->payment->form_data['payment'] = $this->post['payment'];
        }
        $checkData = $this->validatePayment();
        if (!empty($checkData['sErrorMessages'])) {
            $this->error = true;
            $this->View()->register->payment->error_flags = new ArrayObject($checkData['sErrorFlag'], ArrayObject::ARRAY_AS_PROPS);
            $this->View()->register->payment->error_messages = new ArrayObject($checkData['sErrorMessages'], ArrayObject::ARRAY_AS_PROPS);
        } else {
            $this->session['sRegister']['payment'] = array('object'=>$checkData['paymentData']);
        }
    }

    /**
     * Validates the personal informations
     *
     * @return array - personal data with error flags and msg
     */
    public function validatePersonal()
    {
        $errorData = ['sErrorFlag' => [], 'sErrorMessages' => []];

        $data = $this->Request()->getPost();
        $personalData = $data['register']['personal'];

        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->get('shopware.form.factory')->create(PersonalFormType::class, [], ['allow_extra_fields' => true]);
        $form->submit($personalData);

        if ($form->isValid() === false) {
            $errorData['sErrorMessages'][] = $this->get('snippets')
                ->getNamespace('frontend/account/internalMessages')
                ->get('ErrorFillIn', 'Please fill in all red fields');

            foreach ($form->getErrors(true) as $error) {
                $field = $error->getOrigin()->getName();
                $errorData['sErrorFlag'][$field] = $error->getMessage();

                if (in_array($field, ['email', 'password'])) {
                    $errorData['sErrorMessages'][] = $error->getMessage();
                }
            }

            $register = $this->session->offsetGet('sRegister');
            unset($register['auth']["email"]);
            unset($register['auth']["password"]);
            unset($register['auth']["encoderName"]);
            $this->session->offsetSet('sRegister', $register);
        }

        $formData = $form->getData();
        $extraData = $form->getExtraData();

        $this->setRegisterData($formData, $extraData);

        return $errorData;
    }

    /**
     * Set register related array values
     *
     * @param array $formData
     * @param array $extraData
     */
    private function setRegisterData(array $formData, array $extraData = [])
    {
        $register = $this->session->offsetGet('sRegister');

        // Email
        $register['auth']['email'] = $formData['email'];

        // Validation required
        $register['billing']['sValidation'] = $extraData['sValidation'];

        // Receive Newsletter yes / no
        $register['auth']["receiveNewsletter"] = $extraData['receiveNewsletter'];

        // personal data
        $register['auth']['birthday'] = $formData['birthday'];
        $register['auth']['salutation'] = $formData['salutation'];
        $register['auth']['title'] = $formData['title'];
        $register['auth']['firstname'] = $formData['firstname'];
        $register['auth']['lastname'] = $formData['lastname'];

        // Password
        if ($formData['password']) {
            $register['auth']['encoderName'] = Shopware()->Container()->get('PasswordEncoder')->getDefaultPasswordEncoderName();
            $register['auth']['password'] = Shopware()->Container()->get('PasswordEncoder')->encodePassword(
                $formData["password"],
                $register['auth']['encoderName']
            );
        } else {
            unset($register['auth']["password"]);
            unset($register['auth']["encoderName"]);
        }

        // Skip account creation
        if (!$extraData['skipLogin']) {
            $register['auth']['accountmode'] = '0'; // Setting account mode to ACCOUNT
        } else {
            // Enforce the creation of an md5 hashed password for anonymous accounts
            $register['auth']["password"] = md5(uniqid(rand()));
            $register['auth']['encoderName'] = 'md5';
            $register["auth"]["accountmode"] = "1"; // Setting account mode to NO_ACCOUNT
        }

        $register['billing']['salutation'] = $formData['salutation'];
        $register['billing']['firstname'] = $formData['firstname'];
        $register['billing']['lastname'] = $formData['lastname'];
        $register['billing']['customer_type'] = $extraData['customer_type'];
        $register['billing']['phone'] = $extraData['phone'];

        $this->session->offsetSet('sRegister', $register);
    }

    /**
     * Validates the billing informations
     *
     * @return array - billing data with error flags and msg
     */
    public function validateBilling()
    {
        $errorData = ['sErrorFlag' => [], 'sErrorMessages' => []];

        $data = $this->Request()->getPost();
        $register = $this->session->offsetGet('sRegister');
        $billingData = array_merge($register['billing'], $data['register']['billing']);
        $billingData['customer_type'] = $data['register']['personal']['customer_type'];

        // force business validation
        if (!empty($data['register']['personal']['sValidation'])) {
            $billingData['customer_type'] = 'business';
        }

        if (!empty($billingData['country_state_' . $billingData['country']])) {
            $billingData['state'] = $billingData['country_state_' . $billingData['country']];
            $billingData['stateID'] = $billingData['country_state_' . $billingData['country']];
        }

        $address = new \Shopware\Models\Customer\Address();

        $form = Shopware()->Container()->get('shopware.form.factory')->create(AddressFormType::class, $address, ['allow_extra_fields' =>  true]);
        $form->submit($billingData);

        $register = $this->session->offsetGet('sRegister');
        if ($form->isValid() === false) {
            $errorData['sErrorMessages'][] = $this->get('snippets')
                ->getNamespace('frontend/account/internalMessages')
                ->get('ErrorFillIn', 'Please fill in all red fields');

            foreach ($form->getErrors(true) as $error) {
                $errorData['sErrorFlag'][$error->getOrigin()->getName()] = $error->getMessage();
            }

            foreach ($register['billing'] as $key => $value) {
                unset($register['billing'][$key]);
            }
        } else {
            foreach ($billingData as $key => $value) {
                $register['billing'][$key] = $value;
            }
        }
        $this->session->offsetSet('sRegister', $register);

        if (empty($errorData['sErrorMessages'])) {
            $this->session['sCountry'] = $address->getCountry()->getId();
        }

        return $errorData;
    }

    /**
     * Validates the shipping informations
     *
     * @return array - shipping data with error flags and msg
     */
    public function validateShipping()
    {
        $errorData = ['sErrorFlag' => [], 'sErrorMessages' => []];
        $data = $this->Request()->getPost();

        if (empty($data['register']['billing']['shippingAddress'])) {
            return $errorData;
        }

        $shippingData = $data['register']['shipping'];
        $address = new \Shopware\Models\Customer\Address();

        if (!empty($shippingData['country_shipping_state_' . $shippingData['country']])) {
            $shippingData['state'] = $shippingData['country_shipping_state_' . $shippingData['country']];
            $shippingData['stateID'] = $shippingData['country_shipping_state_' . $shippingData['country']];
        }

        $form = Shopware()->Container()->get('shopware.form.factory')->create(AddressFormType::class, $address, ['allow_extra_fields' =>  true]);
        $form->submit($shippingData);

        $register = $this->session->offsetGet('sRegister');
        if ($form->isValid() === false) {
            $errorData['sErrorMessages'][] = Shopware()->Container()->get('snippets')
                ->getNamespace('frontend/account/internalMessages')
                ->get('ErrorFillIn', 'Please fill in all red fields');

            foreach ($form->getErrors(true) as $error) {
                $errorData['sErrorFlag'][$error->getOrigin()->getName()] = $error->getMessage();
            }

            foreach ($register['shipping'] as $key => $value) {
                unset($register['shipping'][$key]);
            }
        } else {
            foreach ($shippingData as $key => $value) {
                $register['shipping'][$key] = $value;
            }
        }
        $this->session->offsetSet('sRegister', $register);

        return $errorData;
    }

    /**
     * Validates the payment informations
     *
     * @return array - payment data with error flags and msg
     */
    public function validatePayment()
    {
        if (empty($this->post['payment'])) {
            return array(
                'sErrorFlag' => array('payment'),
                'sErrorMessages' => array(Shopware()->Snippets()->getNamespace()->get('sErrorBillingAdress')),
            );
        }
        $this->admin->sSYSTEM->_POST['sPayment'] = $this->post['payment'];

        $checkData = $this->admin->sValidateStep3();

        if (!empty($checkData['checkPayment']['sErrorMessages'])) {
            return array(
                'sErrorFlag' => $checkData['checkPayment']['sErrorFlag'],
                'sErrorMessages' => $checkData['checkPayment']['sErrorMessages'],
            );
        }
        return $checkData;
    }
}
