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

use Doctrine\ORM\Query\Expr\Join;
use Shopware\Components\Api\Exception as ApiException;
use Shopware\Models\Country\Country as CountryModel;
use Shopware\Models\Country\State as StateModel;
use Shopware\Models\Customer\Customer as CustomerModel;
use Shopware\Models\Customer\Address as AddressModel;
use Shopware\Models\Shop\Shop as ShopModel;
use Shopware\Models\Customer\PaymentData;
use Shopware\Models\Shop\Repository;

/**
 * Customer API Resource
 *
 * @category  Shopware
 * @package   Shopware\Components\Api\Resource
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Customer extends Resource
{
    /**
     * @return \Shopware\Models\Customer\Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository('Shopware\Models\Customer\Customer');
    }

    /**
     * Little helper function for the ...ByNumber methods
     * @param $number
     * @return int
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function getIdFromNumber($number)
    {
        if (empty($number)) {
            throw new ApiException\ParameterMissingException();
        }

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(['customer.id'])
                ->from('\Shopware\Models\Customer\Customer', 'customer')
                ->where('customer.number = ?1')
                ->setParameter(1, $number);

        $id = $builder->getQuery()->getOneOrNullResult();

        if (!$id) {
            throw new ApiException\NotFoundException("Customer by number {$number} not found");
        }

        return $id['id'];
    }

    /**
     * @param string $number
     * @return array|\Shopware\Models\Customer\Customer
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function getOneByNumber($number)
    {
        $id = $this->getIdFromNumber($number);

        return $this->getOne($id);
    }

    /**
     * @param int $id
     * @return array|\Shopware\Models\Customer\Customer
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
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
            'shippingState'
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

        /** @var $customer \Shopware\Models\Customer\Customer */
        $customer = $builder->getQuery()->getOneOrNullResult($this->getResultMode());

        if (!$customer) {
            throw new ApiException\NotFoundException("Customer by id $id not found");
        }

        return $customer;
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param array $criteria
     * @param array $orderBy
     * @return array
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

        //returns the total count of the query
        $totalResult = $paginator->count();

        //returns the customer data
        $customers = $paginator->getIterator()->getArrayCopy();

        return ['data' => $customers, 'total' => $totalResult];
    }

    /**
     * @param array $params
     * @return \Shopware\Models\Customer\Customer
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');
        $this->setupContext($params['shopId']);

        // create models
        $customer = new CustomerModel();
        $params = $this->prepareCustomerData($params, $customer);
        $params = $this->prepareAssociatedData($params, $customer);
        $customer->fromArray($params);

        $billing = $this->createAddress($params['billing']) ?: new AddressModel();
        $shipping = $this->createAddress($params['shipping']);

        $registerService = $this->getContainer()->get('shopware_account.register_service');
        $context = $this->getContainer()->get('shopware_storefront.context_service')->getShopContext()->getShop();

        $registerService->register($context, $customer, $billing, $shipping);

        return $customer;
    }

    /**
     * @param string $number
     * @param array $params
     * @return \Shopware\Models\Customer\Customer
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     */
    public function updateByNumber($number, array $params)
    {
        $id = $this->getIdFromNumber($number);
        return $this->update($id, $params);
    }

    /**
     * @param int $id
     * @param array $params
     * @return \Shopware\Models\Customer\Customer
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $customer \Shopware\Models\Customer\Customer */
        $customer = $this->getRepository()->find($id);

        if (!$customer) {
            throw new ApiException\NotFoundException("Customer with id $id not found");
        }

        $this->setupContext($customer->getShop()->getId());

        $params = $this->prepareCustomerData($params, $customer);
        $params = $this->prepareAssociatedData($params, $customer);
        $params = $this->applyAddressData($params, $customer);

        $customer->fromArray($params);

        $customerValidator = $this->getContainer()->get('shopware_account.customer_validator');
        $addressValidator = $this->getContainer()->get('shopware_account.address_validator');
        $addressService = $this->getContainer()->get('shopware_account.address_service');

        $customerValidator->validate($customer);
        $addressValidator->validate($customer->getDefaultBillingAddress());
        $addressValidator->validate($customer->getDefaultShippingAddress());

        $addressService->update($customer->getDefaultBillingAddress());
        $addressService->update($customer->getDefaultShippingAddress());

        $this->flush();

        return $customer;
    }

    /**
     * @param string $number
     * @return \Shopware\Models\Customer\Customer
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function deleteByNumber($number)
    {
        $id = $this->getIdFromNumber($number);
        return $this->delete($id);
    }

    /**
     * @param int $id
     * @return \Shopware\Models\Customer\Customer
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $customer \Shopware\Models\Customer\Customer */
        $customer = $this->getRepository()->find($id);

        if (!$customer) {
            throw new ApiException\NotFoundException("Customer by id $id not found");
        }

        $this->getManager()->remove($customer);
        $this->flush();

        return $customer;
    }

    private function prepareCustomerData($params, CustomerModel $customer)
    {
        if (array_key_exists('groupKey', $params)) {
            $params['group'] = Shopware()->Models()->getRepository('Shopware\Models\Customer\Group')->findOneBy(['key' => $params['groupKey']]);
            if (!$params['group']) {
                throw new ApiException\CustomValidationException(sprintf("CustomerGroup by key %s not found", $params['groupKey']));
            }
        }

        if (array_key_exists('shopId', $params)) {
            $params['shop'] = Shopware()->Models()->find('Shopware\Models\Shop\Shop', $params['shopId']);
            if (!$params['shop']) {
                throw new ApiException\CustomValidationException(sprintf("Shop by id %s not found", $params['shopId']));
            }
        }

        if (array_key_exists('priceGroupId', $params)) {
            $priceGroupId = (int) $params['priceGroupId'];
            if ($priceGroupId > 0) {
                $params['priceGroup'] = Shopware()->Models()->find('Shopware\Models\Customer\PriceGroup', $params['priceGroupId']);
            } else {
                $params['priceGroup'] = null;
            }
        }

        //If a different payment method is selected, it must also be placed in the "paymentPreset" so that the risk management that does not reset.
        if ($customer->getId() && $customer->getPaymentId() !== $params['paymentId']) {
            $params['paymentPreset'] = $params['paymentId'];
        }

        return $params;
    }

    /**
     * @param array $data
     * @param \Shopware\Models\Customer\Customer $customer
     * @return array
     */
    protected function prepareAssociatedData($data, CustomerModel $customer)
    {
        $data = $this->prepareCustomerPaymentData($data, $customer);

        return $data;
    }

    /**
     * @param array $data
     * @param \Shopware\Models\Customer\Customer $customer
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @return array
     */
    protected function prepareCustomerPaymentData($data, CustomerModel $customer)
    {
        if (!array_key_exists('paymentData', $data) && !array_key_exists('debit', $data)) {
            return $data;
        }

        if (array_key_exists('debit', $data) && !array_key_exists('paymentData', $data)) {
            $debitPaymentMean = $this->getManager()->getRepository('Shopware\Models\Payment\Payment')->findOneBy(['name' => 'debit']);

            if ($debitPaymentMean) {
                $data['paymentData'] = [
                    [
                        "accountNumber" => $data['debit']["account"],
                        "bankCode" => $data['debit']["bankCode"],
                        "bankName" => $data['debit']["bankName"],
                        "accountHolder" => $data['debit']["accountHolder"],
                        "paymentMeanId" => $debitPaymentMean->getId()
                    ]
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
                    '\Shopware\Models\Customer\PaymentData',
                    ['id', 'paymentMeanId']
                );
            } catch (ApiException\CustomValidationException $cve) {
                $paymentData = new PaymentData();
                $this->getManager()->persist($paymentData);
                $paymentDataInstances->add($paymentData);
            }

            if (isset($paymentDataData['paymentMeanId'])) {
                $paymentMean = $this->getManager()->getRepository('Shopware\Models\Payment\Payment')->find($paymentDataData['paymentMeanId']);
                if (is_null($paymentMean)) {
                    throw new ApiException\CustomValidationException(
                        sprintf("%s by %s %s not found", 'Shopware\Models\Payment\Payment', 'id', $paymentDataData['paymentMeanId'])
                    );
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
     * Sets the correct context for e.g. validation
     *
     * @param int $shopId
     * @throws ApiException\CustomValidationException
     */
    private function setupContext($shopId = null)
    {
        /** @var Repository $shopRepository */
        $shopRepository = $this->getContainer()->get('models')->getRepository(ShopModel::class);

        if ($shopId) {
            $shop = $shopRepository->getActiveById($shopId);
            if (!$shop) {
                throw new ApiException\CustomValidationException(sprintf("Shop by id %s not found", $shopId));
            }
        } else {
            $shop = $shopRepository->getActiveDefault();
        }

        $shop->registerResources();
    }

    /**
     * @param array|null $data
     * @return null|AddressModel
     * @throws ApiException\CustomValidationException
     */
    private function createAddress(array $data = null)
    {
        if (empty($data)) {
            return null;
        }

        if (!$data['country']) {
            throw new ApiException\CustomValidationException("A country is required.");
        }

        $data = $this->prepareAddressData($data);

        $address = new AddressModel();
        $address->fromArray($data);

        return $address;
    }

    /**
     * Resolves id's to models
     *
     * @param array $data
     * @param bool $filter
     * @return array
     */
    private function prepareAddressData(array $data, $filter = false)
    {
        $data['country'] = !empty($data['country']) ? $this->getContainer()->get('models')->find(CountryModel::class, (int) $data['country']) : null;
        $data['state'] = !empty($data['state']) ? $this->getContainer()->get('models')->find(StateModel::class, $data['state']) : null;

        return $filter ? array_filter($data) : $data;
    }

    /**
     * @param array $params
     * @param CustomerModel $customer
     * @return array
     */
    private function applyAddressData(array $params, CustomerModel $customer)
    {
        $billingData = [];
        $shippingData = [];

        if (array_key_exists('billing', $params)) {
            $billingData = $this->prepareAddressData($params['billing'], true);
        }

        if (array_key_exists('shipping', $params)) {
            $shippingData = $this->prepareAddressData($params['shipping'], true);
        }

        if (array_key_exists('defaultBillingAddress', $params)) {
            $billingData = array_merge($billingData, $this->prepareAddressData($params['defaultBillingAddress'], true));
        }

        if (array_key_exists('defaultShippingAddress', $params)) {
            $shippingData = array_merge($shippingData, $this->prepareAddressData($params['defaultShippingAddress'], true));
        }

        unset($params['billing'], $params['shipping'], $params['defaultBillingAddress'], $params['defaultShippingAddress']);

        $customer->getDefaultBillingAddress()->fromArray($billingData);
        $customer->getDefaultShippingAddress()->fromArray($shippingData);

        return $params;
    }

    /**
     * @return \Shopware\Components\Model\QueryBuilder
     */
    protected function getListQuery()
    {
        return $this->getRepository()->createQueryBuilder('customer');
    }
}
