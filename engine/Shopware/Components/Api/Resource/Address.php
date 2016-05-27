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

namespace Shopware\Components\Api\Resource;

use Shopware\Bundle\AccountBundle\Form\Account\AddressFormType;
use Shopware\Bundle\AccountBundle\Service\AddressServiceInterface;
use Shopware\Components\Api\Exception as ApiException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Models\Customer\Customer;

/**
 * Address API Resource
 *
 * @category  Shopware
 * @package   Shopware\Components\Api\Resource
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Address extends Resource
{
    /**
     * @var AddressServiceInterface
     */
    private $addressService;

    public function __construct()
    {
        $this->addressService = $this->getContainer()->get('shopware_account.address_service');
    }

    /**
     * @return \Shopware\Models\Customer\AddressRepository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository('Shopware\Models\Customer\Address');
    }


    /**
     * @param int $id
     * @return array|\Shopware\Models\Customer\Address
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        $query = $this->getRepository()->getOne($id);

        /** @var $address \Shopware\Models\Customer\Address */
        $address = $query->getOneOrNullResult($this->getResultMode());

        if (!$address) {
            throw new ApiException\NotFoundException("Address by id $id not found");
        }

        return $address;
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param array $criteria
     * @param array $orderBy
     * @return array
     */
    public function getList($offset = 0, $limit = 25, array $criteria = array(), array $orderBy = array())
    {
        $this->checkPrivilege('read');

        $query = $this->getRepository()->getListQuery($criteria, $orderBy, $limit, $offset);
        $query->setHydrationMode(self::HYDRATE_ARRAY);

        $paginator = $this->getManager()->createPaginator($query);

        //returns the total count of the query
        $totalResult = $paginator->count();

        //returns the address data
        $addresses = $paginator->getIterator()->getArrayCopy();

        return array('data' => $addresses, 'total' => $totalResult);
    }

    /**
     * @param array $params
     * @return \Shopware\Models\Customer\Address
     * @throws ApiException\NotFoundException
     * @throws ApiException\PrivilegeException
     * @throws ApiException\ValidationException
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $customerId = !empty($params['customer']) ? (int) $params['customer'] : 0;
        $customer = $this->getContainer()->get('models')->find(Customer::class, $customerId);
        if (!$customer) {
            throw new ApiException\NotFoundException("Customer by id $customerId not found");
        }

        $address = new \Shopware\Models\Customer\Address();

        $form = $this->getContainer()
            ->get('shopware.form.factory')
            ->create(AddressFormType::class, $address);

        $form->submit($params, true);

        if (!$form->isValid()) {
            throw ValidationException::createFromFormError($form->getErrors(true));
        }

        $this->addressService->create($address, $customer);

        if (!empty($params['__options_set_default_billing_address'])) {
            $this->addressService->setDefaultBillingAddress($address);
        }

        if (!empty($params['__options_set_default_shipping_address'])) {
            $this->addressService->setDefaultShippingAddress($address);
        }

        return $address;
    }

    /**
     * @param int $id
     * @param array $params
     * @return \Shopware\Models\Customer\Address
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $address \Shopware\Models\Customer\Address */
        $address = $this->getRepository()->findOneBy(['id' => $id]);

        if (!$address) {
            throw new ApiException\NotFoundException("Address by id $id not found");
        }

        $form = $this->getContainer()
            ->get('shopware.form.factory')
            ->create(AddressFormType::class, $address);

        $form->submit($params, false);

        if (!$form->isValid()) {
            throw ValidationException::createFromFormError($form->getErrors(true));
        }

        $this->addressService->update($address);

        if (!empty($params['__options_set_default_billing_address'])) {
            $this->addressService->setDefaultBillingAddress($address);
        }

        if (!empty($params['__options_set_default_shipping_address'])) {
            $this->addressService->setDefaultShippingAddress($address);
        }

        return $address;
    }

    /**
     * @param int $id
     * @return \Shopware\Models\Customer\Address
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $address \Shopware\Models\Customer\Address */
        $address = $this->getRepository()->findOneBy(['id' => $id]);

        if (!$address) {
            throw new ApiException\NotFoundException("Address by id $id not found");
        }

        $this->addressService->delete($address);

        return $address;
    }
}
