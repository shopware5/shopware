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
 * Country API Resource
 */
class Country extends Resource
{
    /**
     * @return \Shopware\Models\Country\Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository(\Shopware\Models\Country\Country::class);
    }

    /**
     * Returns the data of the Country with the given ID.
     *
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return array|\Shopware\Models\Country\Country
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        $filters = [
            [
                'property' => 'countries.id',
                'expression' => '=',
                'value' => $id,
            ],
        ];
        $builder = $this->getRepository()->getCountriesWithStatesQueryBuilder($filters);
        /** @var \Shopware\Models\Country\Country|null $country */
        $country = $builder->getQuery()->getOneOrNullResult($this->getResultMode());
        if (!$country) {
            throw new ApiException\NotFoundException(sprintf('Country by id %d not found', $id));
        }

        return $country;
    }

    /**
     * Returns an array containing the total count of existing countries as well as the data of countries
     * that match the given criteria.
     *
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getList($offset = 0, $limit = 25, array $criteria = [], array $orderBy = [])
    {
        $this->checkPrivilege('read');

        // Build query
        $builder = $this->getRepository()->getCountriesWithStatesQueryBuilder();
        $builder->addFilter($criteria)
                ->addOrderBy($orderBy)
                ->setFirstResult($offset)
                ->setMaxResults($limit);

        // Set hydration mode
        $query = $builder->getQuery();
        $query->setHydrationMode($this->getResultMode());

        // Get result
        $paginator = $this->getManager()->createPaginator($query);
        $totalResult = $paginator->count();
        $countries = $paginator->getIterator()->getArrayCopy();

        return [
            'data' => $countries,
            'total' => $totalResult,
        ];
    }

    /**
     * Creates a new Country entity using the passed params.
     *
     * @throws \Shopware\Components\Api\Exception\ValidationException
     *
     * @return \Shopware\Models\Country\Country
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $params = $this->prepareCountryData($params);

        $country = new \Shopware\Models\Country\Country();
        $country->fromArray($params);

        $violations = $this->getManager()->validate($country);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->getManager()->persist($country);
        $this->flush();

        return $country;
    }

    /**
     * Updates the Country entity with the given ID using the passed params.
     *
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ValidationException
     *
     * @return \Shopware\Models\Country\Country
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        /** @var \Shopware\Models\Country\Country|null $country */
        $country = $this->getRepository()->find($id);
        if (!$country) {
            throw new ApiException\NotFoundException(sprintf('Country by id %d not found', $id));
        }

        $params = $this->prepareCountryData($params, $country);
        $country->fromArray($params);

        $violations = $this->getManager()->validate($country);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->flush();

        return $country;
    }

    /**
     * Deletes the Country entity with the given ID.
     *
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return \Shopware\Models\Country\Country
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        /** @var \Shopware\Models\Country\Country|null $country */
        $country = $this->getRepository()->find($id);
        if (!$country) {
            throw new ApiException\NotFoundException(sprintf('Country by id %d not found', $id));
        }

        $this->getManager()->remove($country);
        $this->flush();

        return $country;
    }

    /**
     * @param \Shopware\Models\Country\Country $country
     *
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return array
     */
    private function prepareCountryData(array $params, \Shopware\Models\Country\Country $country = null)
    {
        $requiredParams = [
            'name',
            'iso',
            'iso3',
            'isoName',
        ];
        foreach ($requiredParams as $param) {
            if (!$country) {
                if (!isset($params[$param]) || empty($params[$param])) {
                    throw new ApiException\ParameterMissingException($param);
                }
            } else {
                if (isset($params[$param]) && empty($params[$param])) {
                    throw new ApiException\CustomValidationException(sprintf('Param "%s" may not be empty', $param));
                }
            }
        }

        if (isset($params['area'])) {
            $areaId = (int) $params['area'];
            if ($areaId > 0) {
                $area = $this->getManager()->find('\Shopware\Models\Country\Area', $areaId);
                if ($area) {
                    $params['area'] = $area;
                } else {
                    throw new ApiException\NotFoundException(sprintf('Area by id %d not found', $areaId));
                }
            } else {
                $params['area'] = null;
            }
        }

        $params = $this->prepareCountryStatesData($params);

        return $params;
    }

    /**
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return array
     */
    private function prepareCountryStatesData(array $params)
    {
        if (!isset($params['states']) || !is_array($params['states'])) {
            return $params;
        }

        foreach ($params['states'] as &$state) {
            if (!isset($state['id']) || empty($state['id'])) {
                throw new ApiException\CustomValidationException('You need to specify the id of the state you want to modify');
            }

            /** @var \Shopware\Models\Country\State|null $stateModel */
            $stateModel = $this->getManager()->find(\Shopware\Models\Country\State::class, $state['id']);
            if (!$stateModel) {
                throw new ApiException\NotFoundException(sprintf('State by id %d not found', (int) $state['id']));
            }

            // Update state
            $stateModel->fromArray($state);
            $state = $stateModel;
        }

        return $params;
    }
}
