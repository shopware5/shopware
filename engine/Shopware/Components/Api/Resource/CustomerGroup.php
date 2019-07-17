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

/**
 * CustomerGroup API Resource
 */
class CustomerGroup extends Resource
{
    /**
     * @return \Shopware\Models\Customer\Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository(\Shopware\Models\Customer\Group::class);
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return array|\Shopware\Models\Customer\Group
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        $builder = $this->getRepository()->createQueryBuilder('customerGroup')
                ->select('customerGroup', 'd')
                ->leftJoin('customerGroup.discounts', 'd')
                ->where('customerGroup.id = :id')
                ->setParameter(':id', $id);

        $query = $builder->getQuery();
        $query->setHydrationMode($this->getResultMode());

        /** @var \Shopware\Models\Customer\Group $category */
        $result = $query->getOneOrNullResult($this->getResultMode());

        if (!$result) {
            throw new ApiException\NotFoundException(sprintf('CustomerGroup by id %d not found', $id));
        }

        return $result;
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

        $builder = $this->getRepository()->createQueryBuilder('customerGroup')
                ->select('customerGroup', 'd')
                ->leftJoin('customerGroup.discounts', 'd');

        $builder->addFilter($criteria)
                ->addOrderBy($orderBy)
                ->setFirstResult($offset)
                ->setMaxResults($limit);
        $query = $builder->getQuery();
        $query->setHydrationMode($this->resultMode);

        $paginator = $this->getManager()->createPaginator($query);

        // Returns the total count of the query
        $totalResult = $paginator->count();

        // Returns the category data
        $results = $paginator->getIterator()->getArrayCopy();

        return ['data' => $results, 'total' => $totalResult];
    }

    /**
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Exception
     *
     * @return \Shopware\Models\Customer\Group
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
     *
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     *
     * @return \Shopware\Models\Customer\Group
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        /** @var \Shopware\Models\Customer\Group|null $result */
        $result = $this->getRepository()->find($id);

        if (!$result) {
            throw new ApiException\NotFoundException(sprintf('CustomerGroup by id %d not found', $id));
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
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return \Shopware\Models\Customer\Group
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        /** @var \Shopware\Models\Customer\Group|null $result */
        $result = $this->getRepository()->find($id);

        if (!$result) {
            throw new ApiException\NotFoundException(sprintf('CustomerGroup by id %d not found', $id));
        }

        $this->getManager()->remove($result);
        $this->flush();

        return $result;
    }

    /**
     * Helper function to save discounts for a given group.
     *
     * @param \Shopware\Models\Customer\Group $group
     */
    private function saveDiscounts(array $discounts, $group)
    {
        $oldDiscounts = $group->getDiscounts();
        foreach ($oldDiscounts as $oldDiscount) {
            if (!in_array($oldDiscount, $discounts)) {
                $this->getManager()->remove($oldDiscount);
            }
        }
        $this->getManager()->flush();
        /** @var \Shopware\Models\Customer\Discount $discount */
        foreach ($discounts as $discount) {
            $discount->setGroup($group);
            $this->getManager()->persist($discount);
        }
    }

    /**
     * @param \Shopware\Models\Customer\Group|null $customerGroup
     *
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     *
     * @return array
     */
    private function prepareCustomerGroupData(array $params, $customerGroup = null)
    {
        $defaults = [
            'taxInput' => 1,
            'tax' => 1,
            'mode' => 0,
        ];

        if ($customerGroup === null) {
            if (!isset($params['taxInput'])) {
                $params['taxInput'] = $defaults['taxInput'];
            }

            if (!isset($params['tax'])) {
                $params['tax'] = $defaults['tax'];
            }

            if (!isset($params['mode'])) {
                $params['mode'] = $defaults['mode'];
            }

            if (empty($params['name'])) {
                throw new ApiException\CustomValidationException(sprintf("Parameter '%s' is missing", 'name'));
            }

            if (empty($params['key'])) {
                throw new ApiException\CustomValidationException(sprintf("Parameter '%s' is missing", 'key'));
            }
        }

        if (isset($params['name']) && empty($params['name'])) {
            throw new ApiException\CustomValidationException(sprintf("Parameter '%s' is missing", 'name'));
        }

        if (isset($params['key']) && empty($params['key'])) {
            throw new ApiException\CustomValidationException(sprintf("Parameter '%s' is missing", 'key'));
        }

        $discountRepository = $this->getManager()->getRepository(\Shopware\Models\Customer\Discount::class);

        if (isset($params['discounts'])) {
            $discounts = [];
            foreach ($params['discounts'] as $discount) {
                $discountModel = null;
                if ($customerGroup) {
                    $discountModel = $discountRepository->findOneBy([
                        'group' => $customerGroup,
                        'discount' => $discount['discount'],
                        'value' => $discount['value'],
                    ]);
                }

                if ($discountModel === null) {
                    $discountModel = new \Shopware\Models\Customer\Discount();
                }
                $discountModel->setDiscount($discount['discount']);
                $discountModel->setValue($discount['value']);
                $discounts[] = $discountModel;
            }
            $params['discounts'] = $discounts;
        } else {
            $params['discounts'] = [];
        }

        return $params;
    }
}
