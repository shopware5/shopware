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
 * Property API Resource
 */
class PropertyGroup extends Resource
{
    /**
     * @return \Shopware\Models\Property\Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository(\Shopware\Models\Property\Group::class);
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return array|\Shopware\Models\Property\Group
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        $filters = [['property' => 'groups.id', 'expression' => '=', 'value' => $id]];
        $query = $this->getRepository()->getListGroupsQuery($filters);

        /** @var \Shopware\Models\Property\Group|null $property */
        $property = $query->getOneOrNullResult($this->getResultMode());

        if (!$property) {
            throw new ApiException\NotFoundException(sprintf('PropertyGroup by id %d not found', $id));
        }

        return $property;
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

        $query = $this->getRepository()->getListGroupsQuery($criteria, $orderBy, $limit, $offset);
        $query->setHydrationMode($this->resultMode);

        $paginator = $this->getManager()->createPaginator($query);

        // Returns the total count of the query
        $totalResult = $paginator->count();

        // Returns the property groups data
        $propertyGroups = $paginator->getIterator()->getArrayCopy();

        return ['data' => $propertyGroups, 'total' => $totalResult];
    }

    /**
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Exception
     *
     * @return \Shopware\Models\Property\Group
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $params = $this->preparePropertyData($params);

        $property = new \Shopware\Models\Property\Group();
        $property->fromArray($params);

        $violations = $this->getManager()->validate($property);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->getManager()->persist($property);
        $this->flush();

        return $property;
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     *
     * @return \Shopware\Models\Property\Group
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        /** @var \Shopware\Models\Property\Group|null $propertyGroup */
        $propertyGroup = $this->getRepository()->find($id);

        if (!$propertyGroup) {
            throw new ApiException\NotFoundException(sprintf('PropertyGroup by id %d not found', $id));
        }

        $params = $this->preparePropertyData($params, $propertyGroup);
        $propertyGroup->fromArray($params);

        $violations = $this->getManager()->validate($propertyGroup);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->flush();

        return $propertyGroup;
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return \Shopware\Models\Property\Group
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        /** @var \Shopware\Models\Property\Group|null $propertyGroup */
        $propertyGroup = $this->getRepository()->find($id);

        if (!$propertyGroup) {
            throw new ApiException\NotFoundException(sprintf('PropertyGroup by id %d not found', $id));
        }

        $this->getManager()->remove($propertyGroup);
        $this->flush();

        return $propertyGroup;
    }

    /**
     * @param \Shopware\Models\Property\Group|null $propertyGroup
     *
     * @throws ApiException\CustomValidationException
     *
     * @return array
     */
    private function preparePropertyData(array $params, $propertyGroup = null)
    {
        // If property group is created, we need to set some default values
        if (!$propertyGroup) {
            if (!isset($params['name']) || empty($params['name'])) {
                throw new ApiException\CustomValidationException('A name is required');
            }

            if (!isset($params['position']) || empty($params['position'])) {
                $params['position'] = 0;
            }

            if (!isset($params['comparable']) || empty($params['comparable'])) {
                // Set comparable
                $params['comparable'] = 0;
            }

            if (!isset($params['sortmode']) || empty($params['sortmode'])) {
                // Set sortmode
                $params['sortmode'] = 0;
            }

            // Sortmode equals the old article_count sorting?
            if ($params['sortmode'] == 2) {
                // Fallback to the default sorting
                $params['sortmode'] = 0;
            }
        } else {
            if (isset($params['name']) && empty($params['name'])) {
                throw new ApiException\CustomValidationException('Name must not be empty');
            }
        }

        return $params;
    }
}
