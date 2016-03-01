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

use Shopware\Bundle\FormBundle\Forms\Account\AddressFormType;
use \Enlight_Controller_Request_Request as Request;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\AddressRepository;
use Shopware\Models\Customer\Customer;

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
     * Pre dispatch method
     */
    public function preDispatch()
    {
        $this->admin = Shopware()->Modules()->Admin();
        $this->addressRepository = $this->get('models')->getRepository(Address::class);

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
        $addresses = $this->addressRepository
            ->getByUserQuery($this->get('session')->get('sUserId'))
            ->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $this->View()->assign('error', $this->Request()->getParam('error'));
        $this->View()->assign('success', $this->Request()->getParam('success'));
        $this->View()->assign('addresses', $addresses);
    }

    /**
     * Shortcut action for more fluent urls
     */
    public function createAction()
    {
        $this->forward('form');
    }

    /**
     * Shortcut action for more fluent urls
     */
    public function editAction()
    {
        $this->forward('form');
    }

    /**
     * Form to create and edit addresses
     */
    public function formAction()
    {
        $address = $this->getAddressByRequest($this->Request());

        $form = $this->createForm(AddressFormType::class, $address, ['allow_extra_fields' => true]);
        $form->handleRequest($this->Request());

        if ($form->isValid()) {
            $this->get('models')->flush($address);

            $this->redirect(['action' => 'index', 'success' => 'save']);

            return;
        } else {
            $errorFlags = [];
            $errorMessages = [];

            foreach ($form->getErrors(true) as $error) {
                $errorFlags[$error->getOrigin()->getName()] = true;
                $errorMessages[] = $this->get('snippets')->getNamespace('frontend/account/internalMessages')
                    ->get('ErrorFillIn', 'Please fill in all red fields');
            }

            $errorMessages = array_unique($errorMessages);

            $this->View()->assign('error_flags', $errorFlags);
            $this->View()->assign('error_messages', $errorMessages);
        }

        $formData = array_merge(
            $this->get('models')->toArray($form->getViewData()),
            $form->getExtraData()
        );

        $this->View()->assign('countryList', $this->admin->sGetCountryList());
        $this->View()->assign('formData', $formData);
    }

    /**
     * @param Request $request
     * @return Address
     */
    private function getAddressByRequest(Request $request)
    {
        $userId = $this->get('session')->get('sUserId');
        $addressId = $request->getParam('id', null);

        if ($addressId) {
            $address = $this->addressRepository->getDetailByUserQuery($addressId, $userId)->getSingleResult();
        } else {
            $address = new Address();
            $address->setCustomer(
                $this->get('models')->find(Customer::class, $userId)
            );

            $this->get('models')->persist($address);
        }

        return $address;
    }

    /**
     * Delete confirm action
     */
    public function deleteAction()
    {
        $userId = $this->get('session')->get('sUserId');
        $addressId = $this->Request()->getParam('id', null);

        $address = $this->addressRepository->getDetailByUserQuery($addressId, $userId)->getSingleResult();

        if ($this->Request()->isPost()) {
            $this->get('models')->remove($address);
            $this->get('models')->flush($address);

            $this->redirect(['action' => 'index', 'success' => 'delete']);

            return;
        }

        $addressView = $this->get('models')->toArray($address);
        $addressView['country'] = $this->get('models')->toArray($address->getCountry());
        $addressView['state'] = $this->get('models')->toArray($address->getState());

        $this->View()->assign('address', $addressView);
    }
}
