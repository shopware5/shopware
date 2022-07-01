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

namespace Shopware\Components\Api\Resource;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Shopware\Bundle\AccountBundle\Service\AddressServiceInterface;
use Shopware\Bundle\AccountBundle\Service\RegisterServiceInterface;
use Shopware\Bundle\AccountBundle\Service\Validator\AddressValidatorInterface;
use Shopware\Bundle\AccountBundle\Service\Validator\CustomerValidatorInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Components\Api\Exception\CustomValidationException;
use Shopware\Components\Api\Exception\NonUniqueIdentifierUsedException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Components\ShopRegistrationServiceInterface;
use Shopware\Models\Attribute\Customer as CustomerAttribute;
use Shopware\Models\Country\Country as CountryModel;
use Shopware\Models\Country\State as StateModel;
use Shopware\Models\Customer\Address as AddressModel;
use Shopware\Models\Customer\Customer as CustomerModel;
use Shopware\Models\Customer\Group;
use Shopware\Models\Customer\PaymentData;
use Shopware\Models\Customer\PriceGroup;
use Shopware\Models\Customer\Repository as CustomerRepository;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Shop\Shop as ShopModel;

/**
 * Customer API Resource
 */
class Customer extends Resource
{
    /**
     * @return CustomerRepository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository(CustomerModel::class);
    }

    /**
     * Little helper function for the ...ByNumber methods
     *
     * @param string $number
     *
     * @throws ParameterMissingException
     * @throws NonUniqueIdentifierUsedException
     * @throws NotFoundException
     *
     * @return int
     */
    public function getIdFromNumber($number)
    {
        if (empty($number)) {
            throw new ParameterMissingException('id');
        }

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(['customer.id'])
                ->from(CustomerModel::class, 'customer')
                ->where('customer.number = ?1')
                ->setParameter(1, $number);

        try {
            $id = $builder->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $nonUniqueResultException) {
            $ids = $builder->getQuery()->getArrayResult();
            throw new NonUniqueIdentifierUsedException('number', (string) $number, CustomerModel::class, array_column($ids, 'id'));
        }

        if (!$id) {
            throw new NotFoundException(sprintf('Customer by number %s not found', $number));
        }

        return $id['id'];
    }

    /**
     * @param string $number
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     *
     * @return array<string, mixed>|CustomerModel
     */
    public function getOneByNumber($number)
    {
        $id = $this->getIdFromNumber($number);

        return $this->getOne($id);
    }

    /**
     * @param int $id
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     *
     * @return array<string, mixed>|CustomerModel
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $builder = $this->getRepository()->createQueryBuilder('customer');

        $builder->select([
            'customer',
            'attribute',
            'billing',
            'billingAttribute',
            'shipping',
            'shippingAttribute',
            'paymentData',
            'billingCountry',
            'shippingCountry',
            'billingState',
            'shippingState',
        ]);
        $builder->leftJoin('customer.attribute', 'attribute')
            ->leftJoin('customer.defaultBillingAddress', 'billing')
            ->leftJoin('customer.paymentData', 'paymentData', Join::WITH, 'paymentData.paymentMean = customer.paymentId')
            ->leftJoin('billing.attribute', 'billingAttribute')
            ->leftJoin('customer.defaultShippingAddress', 'shipping')
            ->leftJoin('shipping.attribute', 'shippingAttribute')
            ->leftJoin('billing.country', 'billingCountry')
            ->leftJoin('shipping.country', 'shippingCountry')
            ->leftJoin('billing.state', 'billingState')
            ->leftJoin('shipping.state', 'shippingState')
            ->where('customer.id = ?1')
            ->setParameter(1, $id);

        $customer = $builder->getQuery()->getOneOrNullResult($this->getResultMode());

        if (!$customer) {
            throw new NotFoundException(sprintf('Customer by id %d not found', $id));
        }

        return $customer;
    }

    /**
     * @param int                                                                                     $offset
     * @param int                                                                                     $limit
     * @param array<string, string>|array<array{property: string, value: mixed, expression?: string}> $criteria
     * @param array<array{property: string, direction: string}>                                       $orderBy
     *
     * @return array{data: array<array<string, mixed>|CustomerModel>, total: int}
     */
    public function getList($offset = 0, $limit = 25, array $criteria = [], array $orderBy = [])
    {
        $this->checkPrivilege('read');

        $builder = $this->getListQuery();

        $builder->addFilter($criteria);
        $builder->addOrderBy($orderBy);
        $builder->setFirstResult($offset)
                ->setMaxResults($limit);

        $query = $builder->getQuery();

        $query->setHydrationMode($this->getResultMode());

        $paginator = $this->getManager()->createPaginator($query);

        // Returns the total count of the query
        $totalResult = $paginator->count();

        // Returns the customer data
        $customers = $paginator->getIterator()->getArrayCopy();

        return ['data' => $customers, 'total' => $totalResult];
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return CustomerModel
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');
        $this->setupContext($params['shopId']);

        // Create models
        $customer = new CustomerModel();
        $customer->setAttribute(new CustomerAttribute());

        // Normalize call between create and update to allow same parameters
        if (isset($params['defaultBillingAddress'])) {
            $params['billing'] = $params['defaultBillingAddress'];
            unset($params['defaultBillingAddress']);
        }

        if (isset($params['defaultShippingAddress'])) {
            $params['shipping'] = $params['defaultShippingAddress'];
            unset($params['defaultShippingAddress']);
        }

        $params = $this->prepareCustomerData($params, $customer);
        $params = $this->prepareAssociatedData($params, $customer);
        $customer->fromArray($params);

        $billing = $this->createAddress($params['billing']) ?? new AddressModel();
        $shipping = $this->createAddress($params['shipping']);

        $registerService = $this->getContainer()->get(RegisterServiceInterface::class);
        $context = $this->getContainer()->get(ContextServiceInterface::class)->getShopContext()->getShop();

        $context->addAttribute('sendOptinMail', new Attribute([
            'sendOptinMail' => $params['sendOptinMail'] === true,
        ]));

        $registerService->register($context, $customer, $billing, $shipping);

        return $customer;
    }

    /**
     * @param string               $number
     * @param array<string, mixed> $params
     *
     * @throws ParameterMissingException
     * @throws CustomValidationException
     * @throws NotFoundException
     *
     * @return CustomerModel
     */
    public function updateByNumber($number, array $params)
    {
        $id = $this->getIdFromNumber($number);

        return $this->update($id, $params);
    }

    /**
     * @param int                  $id
     * @param array<string, mixed> $params
     *
     * @throws CustomValidationException
     * @throws NotFoundException
     * @throws ParameterMissingException
     *
     * @return CustomerModel
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $customer = $this->getRepository()->find($id);

        if (!$customer instanceof CustomerModel) {
            throw new NotFoundException(sprintf('Customer by id %d not found', $id));
        }

        $this->setupContext($customer->getShop()->getId());

        $params = $this->prepareCustomerData($params, $customer);
        $params = $this->prepareAssociatedData($params, $customer);
        $params = $this->applyAddressData($params, $customer);

        $customer->fromArray($params);

        $customerValidator = $this->getContainer()->get(CustomerValidatorInterface::class);
        $addressValidator = $this->getContainer()->get(AddressValidatorInterface::class);
        $addressService = $this->getContainer()->get(AddressServiceInterface::class);

        $customerValidator->validate($customer);
        $defaultBillingAddress = $customer->getDefaultBillingAddress();
        if ($defaultBillingAddress === null) {
            throw new CustomValidationException('Default billing address not set');
        }
        $defaultShippingAddress = $customer->getDefaultShippingAddress();
        if ($defaultShippingAddress === null) {
            throw new CustomValidationException('Default shipping address not set');
        }
        $addressValidator->validate($defaultBillingAddress);
        $addressValidator->validate($defaultShippingAddress);

        $addressService->update($defaultBillingAddress);
        $addressService->update($defaultShippingAddress);

        $this->flush();

        return $customer;
    }

    /**
     * @param string $number
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     *
     * @return CustomerModel
     */
    public function deleteByNumber($number)
    {
        $id = $this->getIdFromNumber($number);

        return $this->delete($id);
    }

    /**
     * @param int $id
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     *
     * @return CustomerModel
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $customer = $this->getRepository()->find($id);

        if (!$customer) {
            throw new NotFoundException(sprintf('Customer by id %d not found', $id));
        }

        $this->getManager()->remove($customer);
        $this->flush();

        return $customer;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function prepareAssociatedData($data, CustomerModel $customer)
    {
        return $this->prepareCustomerPaymentData($data, $customer);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws CustomValidationException
     *
     * @return array<string, mixed>
     */
    protected function prepareCustomerPaymentData($data, CustomerModel $customer)
    {
        if (!\array_key_exists('paymentData', $data) && !\array_key_exists('debit', $data)) {
            return $data;
        }

        if (\array_key_exists('debit', $data) && !\array_key_exists('paymentData', $data)) {
            $debitPaymentMean = $this->getManager()->getRepository(Payment::class)->findOneBy(['name' => 'debit']);

            if ($debitPaymentMean) {
                $data['paymentData'] = [
                    [
                        'accountNumber' => $data['debit']['account'],
                        'bankCode' => $data['debit']['bankCode'],
                        'bankName' => $data['debit']['bankName'],
                        'accountHolder' => $data['debit']['accountHolder'],
                        'paymentMeanId' => $debitPaymentMean->getId(),
                    ],
                ];
            }
        }

        $paymentDataInstances = $this->checkDataReplacement(
            $customer->getPaymentData(),
            $data,
            'paymentData',
            false
        );

        foreach ($data['paymentData'] as &$paymentDataData) {
            try {
                $paymentData = $this->getOneToManySubElement(
                    $paymentDataInstances,
                    $paymentDataData,
                    PaymentData::class,
                    ['id', 'paymentMeanId']
                );
            } catch (CustomValidationException $cve) {
                $paymentData = new PaymentData();
                $this->getManager()->persist($paymentData);
                $paymentDataInstances->add($paymentData);
            }

            if (isset($paymentDataData['paymentMeanId'])) {
                $paymentMean = $this->getManager()->getRepository(Payment::class)->find($paymentDataData['paymentMeanId']);
                if ($paymentMean === null) {
                    throw new CustomValidationException(sprintf('%s by %s %s not found', Payment::class, 'id', $paymentDataData['paymentMeanId']));
                }
                $paymentData->setPaymentMean($paymentMean);
                unset($paymentDataData['paymentMeanId']);
            }

            if ($paymentData->getCustomer() == null) {
                $paymentData->setCustomer($customer);
            }

            $paymentData->fromArray($paymentDataData);
        }

        $data['paymentData'] = $paymentDataInstances;

        return $data;
    }

    /**
     * @return QueryBuilder
     */
    protected function getListQuery()
    {
        return $this->getRepository()->createQueryBuilder('customer');
    }

    /**
     * @param array<string, mixed> $params
     *
     * @throws CustomValidationException
     *
     * @return array<string, mixed>
     */
    private function prepareCustomerData(array $params, CustomerModel $customer): array
    {
        if (\array_key_exists('groupKey', $params)) {
            $params['group'] = Shopware()->Models()->getRepository(Group::class)->findOneBy(['key' => $params['groupKey']]);
            if (!$params['group']) {
                throw new CustomValidationException(sprintf('CustomerGroup by key %s not found', $params['groupKey']));
            }
        }

        if (\array_key_exists('shopId', $params)) {
            $params['shop'] = Shopware()->Models()->find(ShopModel::class, $params['shopId']);
            if (!$params['shop']) {
                throw new CustomValidationException(sprintf('Shop by id %s not found', $params['shopId']));
            }
        }

        if (\array_key_exists('priceGroupId', $params)) {
            $priceGroupId = (int) $params['priceGroupId'];
            if ($priceGroupId > 0) {
                $params['priceGroup'] = Shopware()->Models()->find(PriceGroup::class, $params['priceGroupId']);
            } else {
                $params['priceGroup'] = null;
            }
        }

        // If a different payment method is selected, it must also be placed in the "paymentPreset" so that the risk management that does not reset.
        if ($customer->getId() && $customer->getPaymentId() !== $params['paymentId']) {
            $params['paymentPreset'] = $params['paymentId'];
        }

        return $params;
    }

    /**
     * Sets the correct context for e.g. validation
     *
     * @throws CustomValidationException
     */
    private function setupContext(int $shopId = null): void
    {
        $shopRepository = $this->getContainer()->get(ModelManager::class)->getRepository(ShopModel::class);

        if ($shopId) {
            $shop = $shopRepository->getActiveById($shopId);
            if (!$shop) {
                throw new CustomValidationException(sprintf('Shop by id %s not found', $shopId));
            }
        } else {
            $shop = $shopRepository->getActiveDefault();
        }

        $this->getContainer()->get(ShopRegistrationServiceInterface::class)->registerShop($shop);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws CustomValidationException
     */
    private function createAddress(array $data = null): ?AddressModel
    {
        if (empty($data)) {
            return null;
        }

        if (!$data['country']) {
            throw new CustomValidationException('A country is required.');
        }

        $data = $this->prepareAddressData($data);

        $address = new AddressModel();
        $address->fromArray($data);

        return $address;
    }

    /**
     * Resolves ids to models
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function prepareAddressData(array $data, bool $filter = false): array
    {
        $data['country'] = !empty($data['country']) ? $this->getContainer()->get(ModelManager::class)->find(CountryModel::class, (int) $data['country']) : null;
        $data['state'] = !empty($data['state']) ? $this->getContainer()->get(ModelManager::class)->find(StateModel::class, $data['state']) : null;

        return $filter ? array_filter($data) : $data;
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    private function applyAddressData(array $params, CustomerModel $customer): array
    {
        $billingData = [];
        $shippingData = [];

        if (\array_key_exists('billing', $params)) {
            $billingData = $this->prepareAddressData($params['billing'], true);
        }

        if (\array_key_exists('shipping', $params)) {
            $shippingData = $this->prepareAddressData($params['shipping'], true);
        }

        if (\array_key_exists('defaultBillingAddress', $params)) {
            $billingData = array_merge($billingData, $this->prepareAddressData($params['defaultBillingAddress'], true));
        }

        if (\array_key_exists('defaultShippingAddress', $params)) {
            $shippingData = array_merge($shippingData, $this->prepareAddressData($params['defaultShippingAddress'], true));
        }

        unset($params['billing'], $params['shipping'], $params['defaultBillingAddress'], $params['defaultShippingAddress']);

        if (!$customer->getDefaultBillingAddress() instanceof AddressModel) {
            throw new CustomValidationException(sprintf('Customer with ID "%s" has no default billing address', $customer->getId()));
        }
        $customer->getDefaultBillingAddress()->fromArray($billingData);
        if (!$customer->getDefaultShippingAddress() instanceof AddressModel) {
            throw new CustomValidationException(sprintf('Customer with ID "%s" has no default shipping address', $customer->getId()));
        }
        $customer->getDefaultShippingAddress()->fromArray($shippingData);

        return $params;
    }
}
