<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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


/**
 * Customer API Resource
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
                ->select('customer', 'attribute', 'billing', 'billingAttribute', 'shipping', 'shippingAttribute', 'debit')
                ->leftJoin('customer.attribute', 'attribute')
                ->leftJoin('customer.billing', 'billing')
                ->leftJoin('billing.attribute', 'billingAttribute')
                ->leftJoin('customer.shipping', 'shipping')
                ->leftJoin('shipping.attribute', 'shippingAttribute')
                ->leftJoin('customer.debit', 'debit')
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

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

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

        if (isset($params['email']) && !$this->isEmailUnique($params['email'], null, $params['shopId'])) {
            throw new ApiException\CustomValidationException(sprintf("Emailaddress %s is not unique", $params['email']));
        }

        $params = $this->prepareCustomerData($params);

        $customer = new CustomerModel();
        $customer->fromArray($params);

        $violations = $this->getManager()->validate($customer);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->getManager()->persist($customer);
        $this->flush();

        return $customer;
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
            throw new ApiException\NotFoundException("Customer by id $id not found");
        }

        $params = $this->prepareCustomerData($params, $customer);
        $customer->fromArray($params);

        $violations = $this->getManager()->validate($customer);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->flush();

        return $customer;
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
        if ($customer === null && !isset($params['active'])) {
            $params['active'] = true;
        }

        if (isset($params['email']) && !$this->isEmailUnique($params['email'], $customer)) {
            throw new ApiException\CustomValidationException(sprintf("Emailaddress %s is not unique", $params['email']));
        }

        if (!empty($params['groupKey'])) {
            $params['group'] = Shopware()->Models()->getRepository('Shopware\Models\Customer\Group')->findOneBy(array('key' => $params['groupKey']));
            if (!$params['group']) {
                throw new ApiException\CustomValidationException(sprintf("CustomerGroup by key %s not found", $params['groupKey']));
            }
        } else {
            unset($params['group']);
        }

        if (!empty($params['shopId'])) {
            $params['shop'] = Shopware()->Models()->find('Shopware\Models\Shop\Shop', $params['shopId']);
        } else {
            unset($params['shop']);
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
     * @param $mail
     * @param null|int $cutomerId
     * @param null|int $shopId
     * @return bool
     */
    public function isEmailUnique($mail, $customer = null, $shopId = null)
    {
        $cutomerId = null;
        if ($customer) {
            $cutomerId = $customer->getId();

            if ($customer->getShop()) {
                $shopId = $customer->getShop()->getId();
            }
        }

        $query = $this->getRepository()->getValidateEmailQuery($mail, $cutomerId, $shopId);

        $customer = $query->getArrayResult();

        return empty($customer);
    }
}
