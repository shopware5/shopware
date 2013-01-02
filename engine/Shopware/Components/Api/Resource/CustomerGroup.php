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

/**
 * CustomerGroup API Resource
 *
 * @category  Shopware
 * @package   Shopware\Components\Api\Resource
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class CustomerGroup extends Resource
{
    /**
     * @return \Shopware\Models\Customer\Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository('Shopware\Models\Customer\Group');
    }


    /**
     * @param int $id
     * @return array|\Shopware\Models\Customer\Group
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function getOne($id)
    {

        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        $builder = $this->getRepository()->createQueryBuilder('customerGroup')
                ->select('customerGroup', 'd')
                ->leftJoin('customerGroup.discounts', 'd')
                ->where('customerGroup.id = :id')
                ->setParameter(':id', $id);

        $query = $builder->getQuery();
        $query->setHydrationMode($this->getResultMode());

        /** @var $category \Shopware\Models\Customer\Group*/
        $result = $query->getOneOrNullResult($this->getResultMode());

        if (!$result) {
            throw new ApiException\NotFoundException("CustomerGroup by id $id not found");
        }

        return $result;
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

        $builder = $this->getRepository()->createQueryBuilder('customerGroup')
                ->select('customerGroup', 'd')
                ->leftJoin('customerGroup.discounts', 'd');

        $builder->addFilter($criteria)
                ->addOrderBy($orderBy)
                ->setFirstResult($offset)
                ->setMaxResults($limit);
        $query = $builder->getQuery();
        $query->setHydrationMode($this->resultMode);

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

        //returns the total count of the query
        $totalResult = $paginator->count();

        //returns the category data
        $results = $paginator->getIterator()->getArrayCopy();

        return array('data' => $results, 'total' => $totalResult);
    }

    /**
     * @param array $params
     * @return \Shopware\Models\Customer\Group
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Exception
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $params = $this->prepareCustomerGroupData($params);

        $result = new \Shopware\Models\Customer\Group();

        $discounts = $params['discounts'];
        unset($params['discounts']);

        $result->fromArray($params);

        $violations = $this->getManager()->validate($result);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->getManager()->persist($result);

        $this->saveDiscounts($discounts, $result);


        $this->flush();

        return $result;
    }

    /**
     * @param int $id
     * @param array $params
     * @return \Shopware\Models\Customer\Group
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

        /** @var $result \Shopware\Models\Customer\Group */
        $result = $this->getRepository()->find($id);

        if (!$result) {
            throw new ApiException\NotFoundException("CustomerGroup by id $id not found");
        }

        $params = $this->prepareCustomerGroupData($params, $result);

        $discounts = $params['discounts'];
        unset($params['discounts']);

        $result->fromArray($params);

        $violations = $this->getManager()->validate($result);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->saveDiscounts($discounts, $result);

        $this->flush();

        return $result;
    }

    /**
     * Helper function to save discounts for a given group.
     * @param array $discounts
     * @param \Shopware\Models\Customer\Group $group
     */
    private function saveDiscounts($discounts, $group)
    {

        $oldDiscounts = $group->getDiscounts();
        foreach ($oldDiscounts as $oldDiscount) {
            if (!in_array($oldDiscount, $discounts)) {
                Shopware()->Models()->remove($oldDiscount);
            }
        }
        Shopware()->Models()->flush();
        /** @var \Shopware\Models\Customer\Discount $discount */
        foreach ($discounts as $discount) {
            $discount->setGroup($group);
            Shopware()->Models()->persist($discount);
        }
    }

    /**
     * @param int $id
     * @return \Shopware\Models\Customer\Group
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $result \Shopware\Models\Customer\Group */
        $result = $this->getRepository()->find($id);

        if (!$result) {
            throw new ApiException\NotFoundException("CustomerGroup by id $id not found");
        }

        $this->getManager()->remove($result);
        $this->flush();

        return $result;
    }

    private function prepareCustomerGroupData($params, $customerGroup = null)
    {
        $defaults = array(
            'taxInput' => 1,
            'tax' => 1,
            'mode' => 0
        );

        $requiredParams = array('name', 'key', 'tax', 'taxInput', 'mode');
        foreach ($requiredParams as $param) {
            if (!$customerGroup) {
                if ((!isset($params[$param]) || empty($params[$param])) && !array_key_exists($param, $defaults)) {
                    throw new ApiException\ParameterMissingException($param);
                }if (array_key_exists($param, $defaults)) {
                    $params[$param] = $defaults[$param];
                }
            } else {
                if (isset($params[$param]) && empty($params[$param])) {
                    throw new \Exception('param $param may not be empty');
                }
            }
        }

        $discountRepository = Shopware()->Models()->getRepository('\Shopware\Models\Customer\Discount');

        if (isset($params['discounts'])) {
            $discounts = array();
            foreach ($params['discounts'] as $discount) {
                $discountModel = null;
                if ($customerGroup) {
                    $discountModel = $discountRepository->findOneBy(array("group"=>$customerGroup, "discount" => $discount['discount'], "value" => $discount['value']));
                }
                if ($discountModel === null) {
                    $discountModel = new \Shopware\Models\Customer\Discount();
                }
                $discountModel->setDiscount($discount['discount']);
                $discountModel->setValue($discount['value']);
                $discounts[] = $discountModel;
            }
            $params['discounts'] = $discounts;
        }

        return $params;
    }
}
