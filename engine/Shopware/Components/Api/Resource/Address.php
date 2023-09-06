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

namespace Shopware\Components\Api\Resource;

use Shopware\Bundle\AccountBundle\Service\AddressServiceInterface;
use Shopware\Components\Api\Exception\CustomValidationException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Model\Exception\ModelNotFoundException;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\ShopRegistrationServiceInterface;
use Shopware\Models\Country\Country;
use Shopware\Models\Country\State;
use Shopware\Models\Customer\Address as AddressModel;
use Shopware\Models\Customer\AddressRepository;
use Shopware\Models\Customer\Customer as CustomerModel;
use Shopware\Models\Shop\Shop as ShopModel;

/**
 * Address API Resource
 */
class Address extends Resource
{
    /**
     * @var AddressServiceInterface
     */
    private $addressService;

    public function __construct()
    {
        $this->addressService = $this->getContainer()->get(AddressServiceInterface::class);
    }

    /**
     * @return AddressRepository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository(AddressModel::class);
    }

    /**
     * @param int $id
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     *
     * @return array|AddressModel
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $query = $this->getRepository()->getOne($id);

        $address = $query->getOneOrNullResult($this->getResultMode());

        if (!$address) {
            throw new NotFoundException(sprintf('Address by id %d not found', $id));
        }

        return $address;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getList($offset = 0, $limit = 25, array $criteria = [], array $orderBy = [])
    {
        $this->checkPrivilege('read');

        $query = $this->getRepository()->getListQuery($criteria, $orderBy, $limit, $offset);
        $query->setHydrationMode(self::HYDRATE_ARRAY);

        $paginator = $this->getManager()->createPaginator($query);

        // Returns the total count of the query
        $totalResult = $paginator->count();

        // Returns the address data
        $addresses = iterator_to_array($paginator);

        return ['data' => $addresses, 'total' => $totalResult];
    }

    /**
     * @throws NotFoundException
     * @throws CustomValidationException
     *
     * @return AddressModel
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $customerId = !empty($params['customer']) ? (int) $params['customer'] : 0;
        unset($params['customer']);

        $customer = $this->getContainer()->get(ModelManager::class)->find(CustomerModel::class, $customerId);
        if (!$customer) {
            throw new NotFoundException(sprintf('Customer by id %s not found', $customerId));
        }

        $this->setupContext($customer->getShop()->getId());

        if (!$params['country']) {
            throw new CustomValidationException('A country is required.');
        }

        $params = $this->prepareAddressData($params);

        $address = new AddressModel();
        $address->fromArray($params);

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
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     * @throws ValidationException
     *
     * @return AddressModel
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $address = $this->getRepository()->findOneBy(['id' => $id]);

        if (!$address) {
            throw new NotFoundException(sprintf('Address by id %d not found', $id));
        }

        $this->setupContext($address->getCustomer()->getShop()->getId());

        $params = $this->prepareAddressData($params, $address->getCustomer()->getId(), true);
        $address->fromArray($params);

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
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     *
     * @return AddressModel
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $address = $this->getRepository()->findOneBy(['id' => $id]);

        if (!$address) {
            throw new NotFoundException(sprintf('Address by id %d not found', $id));
        }

        $this->addressService->delete($address);

        return $address;
    }

    /**
     * Sets the correct context for e.g. validation
     *
     * @throws ModelNotFoundException
     */
    private function setupContext(int $shopId): void
    {
        $shopRepository = $this->getContainer()->get(ModelManager::class)->getRepository(ShopModel::class);

        $shop = $shopRepository->getActiveById($shopId);
        if (!$shop instanceof ShopModel) {
            throw new ModelNotFoundException(ShopModel::class, $shopId);
        }

        $this->getContainer()->get(ShopRegistrationServiceInterface::class)->registerShop($shop);
    }

    /**
     * Resolves ids to models
     *
     * @throws CustomValidationException when attempting to change a customer id on an address
     * @throws NotFoundException         if the given customer id in the data array is invalid
     */
    private function prepareAddressData(array $data, ?int $customerId = null, bool $filter = false): array
    {
        /*
         * Check if the API user tries to set an address to a *different* customer
         * if the customer is the same as the owner of the address, then no exception will be thrown
         * if it is different, depending on the case, an \InvalidArgumentException or a \LogicException will be thrown
         */
        if (isset($data['customer'])) {
            $data['customer'] = (int) $data['customer'];

            if ($data['customer'] <= 0) {
                throw new NotFoundException('Invalid customer id');
            }

            if ($customerId !== null && $data['customer'] !== $customerId) {
                throw new CustomValidationException('Changing a customer id on addresses is not supported');
            }

            unset($data['customer']);
        }

        $data['country'] = !empty($data['country']) ? $this->getContainer()->get(ModelManager::class)->find(Country::class, (int) $data['country']) : null;
        $data['state'] = !empty($data['state']) ? $this->getContainer()->get(ModelManager::class)->find(State::class, $data['state']) : null;

        return $filter ? array_filter($data) : $data;
    }
}
