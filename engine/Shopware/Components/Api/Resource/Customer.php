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

use Shopware\Components\Api\Exception as ApiException;
use Shopware\Models\Customer\Customer as CustomerModel;
use Shopware\Models\Customer\PaymentData;

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
        $builder->select(array('customer.id'))
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

        $builder = $this->getRepository()
                ->createQueryBuilder('customer')
                ->select('customer', 'attribute', 'billing', 'billingAttribute', 'shipping', 'shippingAttribute', 'paymentData')
                ->leftJoin('customer.attribute', 'attribute')
                ->leftJoin('customer.billing', 'billing')
                ->leftJoin('customer.paymentData', 'paymentData', \Doctrine\ORM\Query\Expr\Join::WITH, 'paymentData.paymentMean = customer.paymentId')
                ->leftJoin('billing.attribute', 'billingAttribute')
                ->leftJoin('customer.shipping', 'shipping')
                ->leftJoin('shipping.attribute', 'shippingAttribute')
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
    public function getList($offset = 0, $limit = 25, array $criteria = array(), array $orderBy = array())
    {
        $this->checkPrivilege('read');

        $builder = $this->getRepository()->createQueryBuilder('customer');

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

        return array('data' => $customers, 'total' => $totalResult);
    }

    /**
     * @param array $params
     * @return \Shopware\Models\Customer\Customer
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Exception
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $params = $this->prepareCustomerData($params);

        if (isset($params['accountMode']) && $params['accountMode'] == 0) {
            if (isset($params['email']) && !$this->isEmailUnique($params['email'], null, $params['shopId'])) {
                throw new ApiException\CustomValidationException(sprintf("Emailaddress %s for shopId %s is not unique", $params['email'], $params['shopId']));
            }
        }

        $customer = new CustomerModel();
        $params = $this->prepareAssociatedData($params, $customer);

        $customer->fromArray($params);

        $violations = $this->getManager()->validate($customer);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->getManager()->persist($customer);
        $this->flush();

        $addressImportService = $this->getContainer()->get('shopware_account.address_import_service');

        try {
            $addressImportService->importCustomerBilling($customer->getId());
        } catch (\Exception $ex) {
        }

        try {
            $addressImportService->importCustomerShipping($customer->getId());
        } catch (\Exception $ex) {
        }

        $this->getManager()->clear(CustomerModel::class);
        $customer = $this->getManager()->find(CustomerModel::class, $customer->getId());

        return $customer;
    }


    /**
     * @param string $number
     * @param array $params
     * @return \Shopware\Models\Customer\Customer
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     */
    public function updateByNumber($number, $params)
    {
        $id = $this->getIdFromNumber($number);
        return $this->update($id, $params);
    }

    /**
     * @param int $id
     * @param array $params
     * @return \Shopware\Models\Customer\Customer
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
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

        $params = $this->prepareCustomerData($params, $customer);
        $params = $this->prepareAssociatedData($params, $customer);

        $customer->fromArray($params);

        if (!$this->isEmailUnique($customer->getEmail(), $customer)) {
            throw new ApiException\CustomValidationException(sprintf("Email address %s for shopId %s is not unique", $customer->getEmail(), $customer->getShop()->getId()));
        }

        $violations = $this->getManager()->validate($customer);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

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

    private function prepareCustomerData($params, $customer = null)
    {
        if ($customer === null) {
            if (!isset($params['shopId'])) {
                $params['shopId'] = 1;
            }

            if (!isset($params['active'])) {
                $params['active'] = true;
            }

            // if accountmode is not set, set it to be a full user account
            if (!isset($params['accountMode'])) {
                $params['accountMode'] = 0;
            }

            if (!isset($params['groupKey'])) {
                /** @var $shop \Shopware\Models\Shop\Shop */
                $shop = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop')->getActiveDefault();
                $defaultGroupKey = $shop->getCustomerGroup()->getKey();
                $params['groupKey'] = $defaultGroupKey;
            }
        }

        $number = $this->getCustomerNumber($params, $customer);
        if ($number !== null) {
            $params['number'] = $number;
        }

        if (isset($params['groupKey'])) {
            $params['group'] = Shopware()->Models()->getRepository('Shopware\Models\Customer\Group')->findOneBy(array('key' => $params['groupKey']));
            if (!$params['group']) {
                throw new ApiException\CustomValidationException(sprintf("CustomerGroup by key %s not found", $params['groupKey']));
            }
        }

        if (isset($params['shopId'])) {
            $params['shop'] = Shopware()->Models()->find('Shopware\Models\Shop\Shop', $params['shopId']);
            if (!$params['shop']) {
                throw new ApiException\CustomValidationException(sprintf("Shop by id %s not found", $params['shopId']));
            }
        }

        if (!empty($params['priceGroupId'])) {
            $params['priceGroup'] = Shopware()->Models()->find('Shopware\Models\Customer\PriceGroup', $params['priceGroupId']);
        } else {
            $params['priceGroup'] = null;
        }

        //If a different payment method is selected, it must also be placed in the "paymentPreset" so that the risk management that does not reset.
        if ($customer !== null && $customer->getPaymentId() !== $params['paymentId']) {
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
        $data = $this->prepareCustomerAddressData($data);

        return $data;
    }

    /**
     * Legacy support
     * Merges streetNumber into street in billing and shipping addresses
     * If no street is provided, streetNumber is dropped
     *
     * @param array $data
     * @return array
     */
    protected function prepareCustomerAddressData($data)
    {
        if (isset($data['billing']) && isset($data['billing']['streetNumber'])) {
            if (isset($data['billing']['street'])) {
                $data['billing']['street'] .= ' '.$data['billing']['streetNumber'];
            }
            unset($data['billing']['streetNumber']);
        }
        if (isset($data['shipping']) && isset($data['shipping']['streetNumber'])) {
            if (isset($data['shipping']['street'])) {
                $data['shipping']['street'] .= ' '.$data['shipping']['streetNumber'];
            }
            unset($data['shipping']['streetNumber']);
        }

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
        if (!isset($data['paymentData']) && !isset($data['debit'])) {
            return $data;
        }

        if (isset($data['debit']) && !isset($data['paymentData'])) {
            $debitPaymentMean = $this->getManager()->getRepository('Shopware\Models\Payment\Payment')->findOneBy(array('name' => 'debit'));

            if ($debitPaymentMean) {
                $data['paymentData'] = array(
                    array(
                        "accountNumber" => $data['debit']["account"],
                        "bankCode" => $data['debit']["bankCode"],
                        "bankName" => $data['debit']["bankName"],
                        "accountHolder" => $data['debit']["accountHolder"],
                        "paymentMeanId" => $debitPaymentMean->getId()
                    )
                );
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
                    array('id', 'paymentMeanId')
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

            if ($paymentData->getPaymentMean() && $paymentData->getPaymentMean()->getName() == 'debit') {
                $data['debit'] = array(
                    "account"       => $paymentDataData["accountNumber"],
                    "bankCode"      => $paymentDataData["bankCode"],
                    "bankName"      => $paymentDataData["bankName"],
                    "accountHolder" => $paymentDataData["accountHolder"],
                );
            }

            $paymentData->fromArray($paymentDataData);
        }

        $data['paymentData'] = $paymentDataInstances;

        return $data;
    }

    /**
     * @param $mail
     * @param null|\Shopware\Models\Customer\Customer $customer
     * @param null|int $shopId
     * @return bool
     */
    public function isEmailUnique($mail, $customer = null, $shopId = null)
    {
        $customerId = null;
        if ($customer) {
            $customerId = $customer->getId();

            if ($customer->getShop()) {
                $shopId = $customer->getShop()->getId();
            }

            // If accountmode is 1 (no real user account), email is allowed to be non-unique
            if ($customer->getAccountMode() == 1) {
                return true;
            }
        }

        $query = $this->getRepository()->getValidateEmailQuery($mail, $customerId, $shopId);
        $customer = $query->getArrayResult();

        return empty($customer);
    }

    /**
     * @param array $params
     * @param CustomerModel|null $customer
     * @return string
     * @throws \Exception
     */
    private function getCustomerNumber($params, CustomerModel $customer = null)
    {
        if (array_key_exists('number', $params)) {
            return $params['number'];
        }
        if ($customer && $customer->getNumber()) {
            return $customer->getNumber();
        }

        $incrementer = Shopware()->Container()->get('shopware.number_range_incrementer');
        return $incrementer->increment('user');
    }
}
