<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator;
use Shopware\Struct;

/**
 * The country gateway is used to select areas, countries and
 * states.
 *
 * It supports for each resource a single get function to select
 * single struct elements and additionally a getList function for
 * each resource to select a list of the resources.
 *
 * @package Shopware\Gateway\DBAL
 */
class Country extends Gateway
{
    /**
     * @var Hydrator\Country
     */
    private $countryHydrator;

    /**
     * @param ModelManager $entityManager
     * @param Hydrator\Country $countryHydrator
     */
    function __construct(
        ModelManager $entityManager,
        Hydrator\Country $countryHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->countryHydrator = $countryHydrator;
    }

    /**
     * Returns a single area struct, identified over the unique id property.
     *
     * @param int $id
     * @return Struct\Country\Area
     */
    public function getArea($id)
    {
        $areas = $this->getAreas(array($id));

        return array_shift($areas);
    }

    /**
     * Returns a single country struct which identified over the passed id.
     * @param int $id
     * @return \Shopware\Struct\Country
     */
    public function getCountry($id)
    {
        $countries = $this->getCountries(array($id));

        return array_shift($countries);
    }

    /**
     * Returns a single state struct which identified over the passed id.
     *
     * @param $id
     * @return Struct\Country\State
     */
    public function getState($id)
    {
        $states = $this->getStates(array($id));

        return array_shift($states);
    }


    /**
     * Returns a list of area structs which identified over
     * the passed ids.
     *
     * @param array $ids
     * @return Struct\Country\Area[]
     */
    public function getAreas(array $ids)
    {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select($this->getAreaFields());
        $query->from('s_core_countries_areas', 'area');
        $query->where('area.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $areas = array();
        foreach ($data as $row) {
            $areas[] = $this->countryHydrator->hydrateArea($row);
        }

        return $areas;
    }

    /**
     * Returns a list of Country structs.
     * The countries are identified over the passed id array.
     *
     * @param array $ids
     * @return Struct\Country[]
     */
    public function getCountries(array $ids)
    {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select($this->getCountryFields())
            ->addSelect($this->getTableFields('s_core_countries_attributes', 'attribute'));

        $query->from('s_core_countries', 'country')
            ->leftJoin('country', 's_core_countries_attributes', 'attribute', 'attribute.countryID = country.id');

        $query->where('country.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $countries = array();
        foreach ($data as $row) {
            $countries[] = $this->countryHydrator->hydrateCountry($row);
        }

        return $countries;
    }

    /**
     * Returns a list of country state structs.
     * The states are identified over the passed unique id array
     *
     * @param array $ids
     * @return Struct\Country\State[]
     */
    public function getStates(array $ids)
    {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select($this->getStateFields())
            ->addSelect($this->getTableFields('s_core_countries_states_attributes', 'attribute'));

        $query->from('s_core_countries_states', 'state')
            ->leftJoin('state', 's_core_countries_states_attributes', 'attribute', 'attribute.stateID = state.id');

        $query->where('state.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $states = array();
        foreach ($data as $row) {
            $states[] = $this->countryHydrator->hydrateState($row);
        }

        return $states;
    }

    private function getAreaFields()
    {
        return array(
            'area.id',
            'area.name',
            'area.active'
        );
    }

    private function getCountryFields()
    {
        return array(
            'country.id',
            'country.countryname',
            'country.countryiso',
            'country.areaID',
            'country.countryen',
            'country.position',
            'country.notice',
            'country.shippingfree',
            'country.taxfree',
            'country.taxfree_ustid',
            'country.taxfree_ustid_checked',
            'country.active',
            'country.iso3',
            'country.display_state_in_registration',
            'country.force_state_in_registration'
        );
    }

    private function getStateFields()
    {
        return array(
            'state.id',
            'state.countryID',
            'state.name',
            'state.shortcode',
            'state.position',
            'state.active'
        );
    }


}