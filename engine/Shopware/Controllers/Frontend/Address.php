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

/**
 * Address controller
 */
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
        $this->addressRepository = $this->get('models')->getRepository(Address::class);
        $this->addressService = $this->get('shopware_account.address_service');

        $this->View()->assign('sUserLoggedIn', $this->admin->sCheckUser());

        if (!$this->View()->getAssign('sUserLoggedIn')) {
            $this->forward('index', 'register');
            return;
        }

        $this->View()->assign('sUserData', $this->admin->sGetUserData());
        $this->View()->assign('sAction', $this->Request()->getActionName());
    }

    /**
     * Address listing
     */
    public function indexAction()
    {
        $addresses = $this->addressRepository->getListArray($this->get('session')->get('sUserId'));

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
        $form = $this->createForm(AddressFormType::class, $address, ['allow_extra_fields' => true]);
        $form->handleRequest($this->Request());

        if ($form->isValid()) {
            $userId = $this->get('session')->get('sUserId');
            $customer = $this->get('models')->find(Customer::class, $userId);

            $this->addressService->create($address, $customer);

            $extraData = $form->getExtraData();

            if (!empty($extraData['set_default_billing'])) {
                $this->addressService->setDefaultBillingAddress($address);
            }

            if (!empty($extraData['set_default_shipping'])) {
                $this->addressService->setDefaultShippingAddress($address);
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
        $addressId = $this->Request()->getParam('id', null);
        $address = $this->addressRepository->getOneByUser($addressId, $userId);

        $form = $this->createForm(AddressFormType::class, $address, ['allow_extra_fields' => true]);
        $form->handleRequest($this->Request());

        if ($form->isValid()) {
            $this->addressService->update($address);

            $extraData = $form->getExtraData();

            if (!empty($extraData['set_default_billing'])) {
                $this->addressService->setDefaultBillingAddress($address);
            }

            if (!empty($extraData['set_default_shipping'])) {
                $this->addressService->setDefaultShippingAddress($address);
            }

            $this->redirect(['action' => 'index', 'success' => 'update']);
            return;
        }

        $this->View()->assign($this->getFormViewData($form));
    }

    /**
     * Delete confirm action
     */
    public function deleteAction()
    {
        $userId = $this->get('session')->get('sUserId');
        $addressId = $this->Request()->getParam('id', null);

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
     * @param FormInterface $form
     * @return array
     */
    private function getFormViewData(FormInterface $form)
    {
        $errorFlags = [];
        $errorMessages = [];
        $viewData = [];

        foreach ($form->getErrors(true) as $error) {
            $errorFlags[$error->getOrigin()->getName()] = true;
            $errorMessages[] = $this->get('snippets')->getNamespace('frontend/account/internalMessages')
                ->get('ErrorFillIn', 'Please fill in all red fields');
        }

        $errorMessages = array_unique($errorMessages);

        $formData = array_merge(
            $this->get('models')->toArray($form->getViewData()),
            ['attribute' => $this->get('models')->toArray($form->getViewData()->getAttribute())],
            $form->getExtraData()
        );

        $viewData['error_flags'] = $errorFlags;
        $viewData['error_messages'] = $errorMessages;
        $viewData['countryList'] = $this->admin->sGetCountryList();
        $viewData['formData'] = $formData;

        return $viewData;
    }

    /**
     * Sets the default shipping address
     */
    public function setDefaultShippingAddressAction()
    {
        $userId = $this->get('session')->get('sUserId');
        $addressId = $this->Request()->getParam('addressId', null);

        $address = $this->addressRepository->getOneByUser($addressId, $userId);

        if (!$this->Request()->isPost()) {
            $this->redirect(['action' => 'index']);
            return;
        }

        $this->addressService->setDefaultShippingAddress($address);

        $this->redirect(['action' => 'index', 'success' => 'default_shipping']);
    }

    /**
     * Sets the default shipping address
     */
    public function setDefaultBillingAddressAction()
    {
        $userId = $this->get('session')->get('sUserId');
        $addressId = $this->Request()->getParam('addressId', null);

        $address = $this->addressRepository->getOneByUser($addressId, $userId);

        if (!$this->Request()->isPost()) {
            $this->redirect(['action' => 'index']);
            return;
        }

        $this->addressService->setDefaultBillingAddress($address);

        $this->redirect(['action' => 'index', 'success' => 'default_billing']);
    }
}
