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
use Shopware\Bundle\AccountBundle\Service\AddressServiceInterface;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\AddressRepository;
use Shopware\Models\Customer\Customer;
use Symfony\Component\Form\FormInterface;

class Shopware_Controllers_Frontend_Address extends Enlight_Controller_Action
{
    /**
     * @var sAdmin
     */
    protected $admin;

    /**
     * @var AddressRepository
     */
    protected $addressRepository;

    /**
     * @var AddressServiceInterface
     */
    protected $addressService;

    /**
     * Pre dispatch method
     */
    public function preDispatch()
    {
        $this->admin = Shopware()->Modules()->Admin();

        $session = $this->container->get('session');
        if ($session->get('sOneTimeAccount') && $this->Request()->has('sidebar')) {
            $this->admin->logout();
        }

        $this->addressRepository = $this->get('models')->getRepository(Address::class);
        $this->addressService = $this->get('shopware_account.address_service');

        $this->View()->assign('sUserLoggedIn', $this->admin->sCheckUser());

        if (!$this->View()->getAssign('sUserLoggedIn')) {
            return $this->forward('index', 'register', 'frontend', $this->getForwardParameters());
        }

        $this->View()->assign('userInfo', $this->get('shopware_account.store_front_greeting_service')->fetch());
        $this->View()->assign('sUserData', $this->admin->sGetUserData());
        $this->View()->assign('sAction', $this->Request()->getActionName());
    }

    /**
     * Address listing
     */
    public function indexAction()
    {
        $addresses = $this->addressRepository->getListArray(
            $this->container->get('session')->get('sUserId')
        );

        // Create a list of ids of occurring countries and states
        $countryIds = array_unique(array_filter(array_column($addresses, 'countryId')));
        $stateIds = array_unique(array_filter(array_column($addresses, 'stateId')));

        $countryRepository = $this->container->get('shopware_storefront.country_gateway');
        $context = $this->container->get('shopware_storefront.context_service')->getShopContext();

        $countries = $countryRepository->getCountries($countryIds, $context);
        $states = $countryRepository->getStates($stateIds, $context);

        // Apply translations for countries and states to address array, converting them from structs to arrays in the process
        foreach ($addresses as &$address) {
            if (array_key_exists($address['countryId'], $countries)) {
                $address['country'] = json_decode(json_encode($countries[$address['countryId']]), true);
            }
            if (array_key_exists($address['stateId'], $states)) {
                $address['state'] = json_decode(json_encode($states[$address['stateId']]), true);
            }
        }
        unset($address);

        $this->View()->assign('error', $this->Request()->getParam('error'));
        $this->View()->assign('success', $this->Request()->getParam('success'));
        $this->View()->assign('addresses', $addresses);
    }

    /**
     * Shortcut action for more fluent urls
     */
    public function createAction()
    {
        $address = new Address();
        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($this->Request());

        if ($form->isValid()) {
            $userId = $this->get('session')->get('sUserId');
            $customer = $this->get('models')->find(Customer::class, $userId);

            $this->addressService->create($address, $customer);

            if (!empty($address->getAdditional()['setDefaultBillingAddress'])) {
                $this->addressService->setDefaultBillingAddress($address);
            }

            if ($this->isValidShippingAddress($address) && !empty($address->getAdditional()['setDefaultShippingAddress'])) {
                $this->addressService->setDefaultShippingAddress($address);
            }

            if ($this->Request()->getParam('sTarget')) {
                $action = $this->Request()->getParam('sTargetAction', 'index') ?: 'index';
                $this->redirect([
                    'controller' => $this->Request()->getParam('sTarget'),
                    'action' => $action,
                    'success' => 'address',
                ]);

                return;
            }

            $this->redirect(['action' => 'index', 'success' => 'create']);

            return;
        }

        $this->View()->assign($this->getFormViewData($form));
    }

    /**
     * Shortcut action for more fluent urls
     */
    public function editAction()
    {
        $userId = $this->get('session')->get('sUserId');
        $addressId = $this->Request()->getParam('id');
        $address = $this->addressRepository->getOneByUser($addressId, $userId);

        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($this->Request());

        if ($form->isValid() && $this->isValidShippingAddress($address)) {
            $this->addressService->update($address);

            if (!empty($address->getAdditional()['setDefaultBillingAddress'])) {
                $this->addressService->setDefaultBillingAddress($address);
            }

            if (!empty($address->getAdditional()['setDefaultShippingAddress'])) {
                $this->addressService->setDefaultShippingAddress($address);
            }

            if ($this->Request()->getParam('sTarget')) {
                $action = $this->Request()->getParam('sTargetAction', 'index') ?: 'index';
                $this->redirect([
                    'controller' => $this->Request()->getParam('sTarget'),
                    'action' => $action,
                    'success' => 'address',
                ]);

                return;
            }

            $this->redirect(['action' => 'index', 'success' => 'update']);

            return;
        }

        $this->View()->assign('countryNotAvailableForShipping', !$this->isValidShippingAddress($address));
        $this->View()->assign($this->getFormViewData($form));
        $this->View()->assign('isShipping', $address->getId() === $address->getCustomer()->getDefaultShippingAddress()->getId());
    }

    /**
     * Delete confirm action
     */
    public function deleteAction()
    {
        $userId = $this->get('session')->get('sUserId');
        $addressId = $this->Request()->getParam('id');

        $address = $this->addressRepository->getOneByUser($addressId, $userId);

        if ($this->Request()->isPost()) {
            $this->addressService->delete($address);

            $this->redirect(['action' => 'index', 'success' => 'delete']);

            return;
        }

        $addressView = $this->get('models')->toArray($address);
        $addressView['country'] = $this->get('models')->toArray($address->getCountry());
        $addressView['state'] = $this->get('models')->toArray($address->getState());
        $addressView['attribute'] = $this->get('models')->toArray($address->getAttribute());

        $this->View()->assign('address', $addressView);
    }

    /**
     * Sets the default shipping address
     */
    public function setDefaultShippingAddressAction()
    {
        $userId = $this->get('session')->get('sUserId');
        $addressId = $this->Request()->getParam('addressId');

        $address = $this->addressRepository->getOneByUser($addressId, $userId);

        if (!$this->Request()->isPost()) {
            $this->redirect(['action' => 'index']);

            return;
        }

        $additional = $address->getAdditional();
        $additional['setDefaultShippingAddress'] = 1;
        $address->setAdditional($additional);

        if ($this->isValidShippingAddress($address)) {
            $this->addressService->setDefaultShippingAddress($address);
        }

        $this->redirect(['action' => 'index', 'success' => 'default_shipping']);
    }

    /**
     * Sets the default shipping address
     */
    public function setDefaultBillingAddressAction()
    {
        $userId = $this->get('session')->get('sUserId');
        $addressId = $this->Request()->getParam('addressId');

        $address = $this->addressRepository->getOneByUser($addressId, $userId);

        if (!$this->Request()->isPost()) {
            $this->redirect(['action' => 'index']);

            return;
        }

        $this->addressService->setDefaultBillingAddress($address);

        $this->redirect(['action' => 'index', 'success' => 'default_billing']);
    }

    /**
     * Selection of addresses for the current logged-in customer
     */
    public function ajaxSelectionAction()
    {
        $extraData = $this->Request()->getParam('extraData', []);
        $addresses = $this->getModelManager()
            ->getRepository(Address::class)
            ->getListArray($this->get('session')->get('sUserId'));
        $activeAddressId = $this->Request()->getParam('id');

        if (!empty($activeAddressId)) {
            foreach ($addresses as $key => $address) {
                if ($address['id'] == $activeAddressId) {
                    unset($addresses[$key]);
                }
            }
        }

        $addresses = $this->translateCountries($addresses);

        /** @var string $data */
        $extraData = array_map(function ($data) {
            // only allow alphanumeric characters, commas and spaces
            return preg_replace('/[^A-Za-z0-9 ,]/', '', $data);
        }, $extraData);

        $this->View()->assign('addresses', $addresses);
        $this->View()->assign('activeAddressId', $activeAddressId);
        $this->View()->assign('extraData', $extraData);
        $this->View()->assign('isShippingRequest', $this->isShippingRequest());
    }

    /**
     * Show address form
     */
    public function ajaxEditorAction()
    {
        $userId = $this->get('session')->get('sUserId');
        $addressId = $this->Request()->getParam('id');

        if ($addressId) {
            $address = $this->addressRepository->getOneByUser($addressId, $userId);
        } else {
            $address = new Address();
        }

        $form = $this->createForm(AddressFormType::class, $address);
        $this->View()->assign($this->getFormViewData($form));
        $this->View()->assign('isShipping', $this->isShippingRequest());
    }

    /**
     * Saves a new address and returns an envelope containing success and error indicators
     *
     * In addition, extraData[] can be send to do various actions after saving. See handleExtraData() for more
     * information.
     */
    public function ajaxSaveAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $response = ['success' => true, 'errors' => [], 'data' => []];

        $userId = $this->get('session')->get('sUserId');
        $addressId = $this->Request()->getPost('id');
        $extraData = $this->Request()->getParam('extraData', []);

        if ($this->Request()->getParam('saveAction') === 'update') {
            $address = $this->addressRepository->getOneByUser($addressId, $userId);
        } else {
            $address = new Address();
        }

        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($this->Request());

        if ($form->isValid()) {
            if ($address->getId()) {
                $this->addressService->update($address);
            } else {
                $customer = $this->get('models')->find(Customer::class, $userId);
                $this->addressService->create($address, $customer);
            }

            $this->handleExtraData($extraData, $address);

            $addressView = $this->get('models')->toArray($address);
            $addressView['country'] = $this->get('models')->toArray($address->getCountry());
            $addressView['state'] = $this->get('models')->toArray($address->getState());
            $addressView['attribute'] = $this->get('models')->toArray($address->getAttribute());
            $response['data'] = $addressView;
        } else {
            foreach ($form->getErrors(true) as $error) {
                $response['errors'][$error->getOrigin()->getName()] = $error->getMessage();
            }
        }

        $response['success'] = empty($response['errors']);

        $this->Response()->headers->set('content-type', 'application/json', true);
        $this->Response()->setContent(json_encode($response));
    }

    /**
     * Do various actions based on extraData[] parameters
     */
    public function handleExtraAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $address = $this->addressRepository->getOneByUser(
            $this->Request()->getPost('id'),
            $this->get('session')->get('sUserId')
        );

        $data = $this->Request()->getParam('extraData', []);
        $this->handleExtraData($data, $address);
    }

    /**
     * @return array
     */
    private function getFormViewData(FormInterface $form)
    {
        $errorFlags = [];
        $errorMessages = [];
        $viewData = [];

        foreach ($form->getErrors(true) as $error) {
            $errorFlags[$error->getOrigin()->getName()] = true;
            if ($error->getMessage()) {
                $errorMessages[] = $error->getMessage();
            }
        }

        $errorMessages = array_unique($errorMessages);

        if (!empty($errorFlags)) {
            $errorMessage = $this->get('snippets')
                ->getNamespace('frontend/account/internalMessages')
                ->get('ErrorFillIn', 'Please fill in all red fields');
            array_unshift($errorMessages, $errorMessage);
        }

        /** @var Address $address */
        $address = $form->getViewData();

        $formData = array_merge(
            $this->get('models')->toArray($address),
            ['attribute' => $this->get('models')->toArray($address->getAttribute())],
            ['additional' => $address->getAdditional()],
            $form->getExtraData()
        );

        $viewData['error_flags'] = $errorFlags;
        $viewData['error_messages'] = $errorMessages;
        $viewData['countryList'] = $this->admin->sGetCountryList();
        $viewData['formData'] = $formData;
        $viewData['sTarget'] = $this->Request()->getParam('sTarget');
        $viewData['sTargetAction'] = $this->Request()->getParam('sTargetAction');
        $viewData['extraData'] = $this->Request()->getParam('extraData', []);

        return $viewData;
    }

    /**
     * Handle extra data, sent by the api request to do various actions afterwards
     *
     * - sessionKey, set a session variable named the value of the submitted sessionKey containing the address id.
     * - setDefaultBillingAddress, sets the address as new default billing address
     * - setDefaultShippingAddress, sets the address as new default shipping address
     */
    private function handleExtraData(array $extraData, Address $address)
    {
        if (!empty($extraData['sessionKey'])) {
            $keys = explode(',', $extraData['sessionKey']);
            foreach ($keys as $key) {
                if (!$key) {
                    continue;
                }

                if ($key === 'checkoutShippingAddressId') {
                    $this->refreshSession($address);
                }

                $this->get('session')->offsetSet($key, $address->getId());
            }
        }

        if (!empty($extraData['setDefaultBillingAddress'])) {
            $this->addressService->setDefaultBillingAddress($address);
        }

        if (!empty($extraData['setDefaultShippingAddress'])) {
            $additional = $address->getAdditional();
            $additional['setDefaultShippingAddress'] = 1;
            $address->setAdditional($additional);

            if ($this->isValidShippingAddress($address)) {
                $this->addressService->setDefaultShippingAddress($address);
            }
        }
    }

    private function refreshSession(Address $address)
    {
        $countryId = $address->getCountry()->getId();
        $stateId = $address->getState() ? $address->getState()->getId() : null;
        $areaId = $address->getCountry()->getArea() ? $address->getCountry()->getArea()->getId() : null;

        $this->get('session')->offsetSet('sCountry', $countryId);
        $this->get('session')->offsetSet('sState', $stateId);
        $this->get('session')->offsetSet('sArea', $areaId);

        $this->get('shopware_storefront.context_service')->initializeShopContext();
    }

    /**
     * @return array
     */
    private function getForwardParameters()
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

    /**
     * @return bool
     */
    private function isShippingRequest()
    {
        $extraData = $this->Request()->getParam('extraData', []);
        $keys = array_filter(explode(';', $extraData['sessionKey']));

        return $extraData['setDefaultShippingAddress'] === '1'
            || in_array('checkoutShippingAddressId', $keys, true);
    }

    /**
     * @return bool
     */
    private function isValidShippingAddress(Address $address)
    {
        if (empty($address->getAdditional()['setDefaultShippingAddress']) && $address->getId() !== $address->getCustomer()->getDefaultShippingAddress()->getId()) {
            return true;
        }

        $context = $this->get('shopware_storefront.context_service')->getContext();
        $country = $this->get('shopware_storefront.country_gateway')->getCountry($address->getCountry()->getId(), $context);

        return $country->allowShipping();
    }

    /**
     * @return array
     */
    private function translateCountries(array $addresses)
    {
        $countryIds = array_map(function ($address) {
            return $address['country']['id'];
        }, $addresses);

        $context = $this->get('shopware_storefront.context_service')->getContext();
        $countries = $this->get('shopware_storefront.country_gateway')->getCountries($countryIds, $context);
        $countries = $this->get('legacy_struct_converter')->convertCountryStructList($countries);

        foreach ($addresses as &$address) {
            $address['country'] = array_merge($address['country'], $countries[$address['country']['id']]);
        }
        unset($address);

        return $addresses;
    }
}
