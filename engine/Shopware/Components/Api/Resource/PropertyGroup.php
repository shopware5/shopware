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

use Exception;
use Shopware\Components\Api\Exception\CustomValidationException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Models\Property\Group;
use Shopware\Models\Property\Repository;

/**
 * Property API Resource
 */
class PropertyGroup extends Resource
{
    /**
     * @return Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository(Group::class);
    }

    /**
     * @param int $id
     *
     * @throws ParameterMissingException
     * @throws NotFoundException
     *
     * @return array|Group
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $filters = [['property' => 'groups.id', 'expression' => '=', 'value' => $id]];
        $query = $this->getRepository()->getListGroupsQuery($filters);

        /** @var Group|null $property */
        $property = $query->getOneOrNullResult($this->getResultMode());

        if (!$property) {
            throw new NotFoundException(sprintf('PropertyGroup by id %d not found', $id));
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
        $propertyGroups = iterator_to_array($paginator);

        return ['data' => $propertyGroups, 'total' => $totalResult];
    }

    /**
     * @throws ValidationException
     * @throws Exception
     *
     * @return Group
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $params = $this->preparePropertyData($params);

        $property = new Group();
        $property->fromArray($params);

        $violations = $this->getManager()->validate($property);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->getManager()->persist($property);
        $this->flush();

        return $property;
    }

    /**
     * @param int $id
     *
     * @throws ValidationException
     * @throws NotFoundException
     * @throws ParameterMissingException
     *
     * @return Group
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        /** @var Group|null $propertyGroup */
        $propertyGroup = $this->getRepository()->find($id);

        if (!$propertyGroup) {
            throw new NotFoundException(sprintf('PropertyGroup by id %d not found', $id));
        }

        $params = $this->preparePropertyData($params, $propertyGroup);
        $propertyGroup->fromArray($params);

        $violations = $this->getManager()->validate($propertyGroup);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->flush();

        return $propertyGroup;
    }

    /**
     * @param int $id
     *
     * @throws ParameterMissingException
     * @throws NotFoundException
     *
     * @return Group
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        /** @var Group|null $propertyGroup */
        $propertyGroup = $this->getRepository()->find($id);

        if (!$propertyGroup) {
            throw new NotFoundException(sprintf('PropertyGroup by id %d not found', $id));
        }

        $this->getManager()->remove($propertyGroup);
        $this->flush();

        return $propertyGroup;
    }

    /**
     * @param Group|null $propertyGroup
     *
     * @throws CustomValidationException
     *
     * @return array
     */
    private function preparePropertyData(array $params, $propertyGroup = null)
    {
        // If property group is created, we need to set some default values
        if (!$propertyGroup) {
            if (empty($params['name'])) {
                throw new CustomValidationException('A name is required');
            }

            if (empty($params['position'])) {
                $params['position'] = 0;
            }

            if (empty($params['comparable'])) {
                // Set comparable
                $params['comparable'] = 0;
            }

            if (empty($params['sortmode'])) {
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
                throw new CustomValidationException('Name must not be empty');
            }
        }

        return $params;
    }
}
