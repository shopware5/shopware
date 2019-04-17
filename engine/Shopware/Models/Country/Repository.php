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

namespace Shopware\Models\Country;

use Shopware\Components\Model\ModelRepository;

class Repository extends ModelRepository
{
    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all defined countries.
     *
     * @param array|null                                         $filter
     * @param string|array|\Doctrine\ORM\Query\Expr\OrderBy|null $order
     * @param int|null                                           $offset
     * @param int|null                                           $limit
     *
     * @return \Doctrine\ORM\Query
     * @return \Doctrine\ORM\Query
     */
    public function getCountriesQuery($filter = null, $order = null, $offset = null, $limit = null)
    {
        $builder = $this->getCountriesQueryBuilder($filter, $order);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getCountriesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param array|null                                         $filter
     * @param string|array|\Doctrine\ORM\Query\Expr\OrderBy|null $order
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCountriesQueryBuilder($filter = null, $order = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select([
            'countries.id',
            'countries.name',
            'countries.iso',
            'countries.position',
            'countries.active as active',
            'countries.forceStateInRegistration',
            'countries.displayStateInRegistration',
            'area.id as areaId',
        ]);
        $builder->from(\Shopware\Models\Country\Country::class, 'countries')
        ->leftJoin('countries.area', 'area');

        if ($filter[0]['property'] === 'areaId') {
            $builder->where('area.id = :areaId');
            $builder->setParameter('areaId', $filter[0]['value']);
        } elseif ($filter !== null) {
            $builder->addFilter($filter);
        }

        if ($order !== null) {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * @param array|null                                         $filter
     * @param string|array|\Doctrine\ORM\Query\Expr\OrderBy|null $order
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCountriesWithStatesQueryBuilder($filter = null, $order = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder
            ->select(['countries', 'states', 'area'])
            ->from(\Shopware\Models\Country\Country::class, 'countries')
            ->leftJoin('countries.states', 'states')
            ->leftJoin('countries.area', 'area');

        if ($filter !== null) {
            $builder->addFilter($filter);
        }

        if ($order !== null) {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects a
     * list of countries for the passed area id.
     *
     * @param int $areaId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getCountriesByAreaIdQuery($areaId)
    {
        $builder = $this->getCountriesByAreaIdQueryBuilder($areaId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getCountriesByAreaIdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $areaId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCountriesByAreaIdQueryBuilder($areaId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select(['countries'])
                ->from(\Shopware\Models\Country\Country::class, 'countries')
                ->where('countries.areaId = ?1')
                ->setParameter(1, $areaId);
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects a
     * list of areas.
     *
     * @return \Doctrine\ORM\Query
     */
    public function getAreasQuery()
    {
        $builder = $this->getAreasQueryBuilder();

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAreasQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAreasQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select(['areas'])
                ->from(\Shopware\Models\Country\Area::class, 'areas');
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects all country data for the passed country id.
     *
     * @param int $countryId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getCountryQuery($countryId)
    {
        $builder = $this->getCountryQueryBuilder($countryId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getCountryQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $countryId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCountryQueryBuilder($countryId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select(['countries', 'attribute'])
                       ->from(\Shopware\Models\Country\Country::class, 'countries')
                       ->leftJoin('countries.attribute', 'attribute')
                       ->where('countries.id = ?1')
                       ->setParameter(1, $countryId);
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects a
     * list of countries for the passed area id.
     *
     * @param int $countryId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getCountryAttributesQuery($countryId)
    {
        $builder = $this->getCountryAttributesQueryBuilder($countryId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getShopsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $countryId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCountryAttributesQueryBuilder($countryId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select(['attribute'])
                         ->from(\Shopware\Models\Attribute\Country::class, 'attribute')
                         ->where('attribute.countryId = ?1')
                         ->setParameter(1, $countryId);
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects a
     * list of countries for the passed area id.
     *
     * @param int $countryId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getStatesByCountryIdQuery($countryId)
    {
        $builder = $this->getStatesByCountryIdQueryBuilder($countryId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getShopsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $countryId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getStatesByCountryIdQueryBuilder($countryId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select(['states', 'attribute'])
                ->from(\Shopware\Models\Country\State::class, 'states')
                ->leftJoin('states.attribute', 'attribute')
                ->where('states.countryId = ?1')
                ->setParameter(1, $countryId);
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects a
     * list of countries for the passed area id.
     *
     * @param int $stateId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getStateAttributesQuery($stateId)
    {
        return $this->getStateAttributesQueryBuilder($stateId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getShopsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $stateId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getStateAttributesQueryBuilder($stateId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select(['attribute'])
                         ->from(\Shopware\Models\Attribute\CountryState::class, 'attribute')
                         ->where('attribute.countryStateId = ?1')
                         ->setParameter(1, $stateId);
    }
}
