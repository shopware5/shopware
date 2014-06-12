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
class Country
{
    /**
     * @var Hydrator\Country
     */
    private $countryHydrator;

    /**
     * The FieldHelper class is used for the
     * different table column definitions.
     *
     * This class helps to select each time all required
     * table data for the store front.
     *
     * Additionally the field helper reduce the work, to
     * select in a second step the different required
     * attribute tables for a parent table.
     *
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @param ModelManager $entityManager
     * @param FieldHelper $fieldHelper
     * @param Hydrator\Country $countryHydrator
     */
    function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\Country $countryHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->countryHydrator = $countryHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * Returns a single area struct, identified over the unique id property.
     *
     * @param int $id
     * @param \Shopware\Struct\Context $context
     * @return Struct\Country\Area
     */
    public function getArea($id, Struct\Context $context)
    {
        $areas = $this->getAreas(array($id), $context);

        return array_shift($areas);
    }

    /**
     * Returns a single country struct which identified over the passed id.
     *
     * @param int $id
     * @param \Shopware\Struct\Context $context
     * @return \Shopware\Struct\Country
     */
    public function getCountry($id, Struct\Context $context)
    {
        $countries = $this->getCountries(array($id), $context);

        return array_shift($countries);
    }

    /**
     * Returns a single state struct which identified over the passed id.
     *
     * @param $id
     * @param \Shopware\Struct\Context $context
     * @return Struct\Country\State
     */
    public function getState($id, Struct\Context $context)
    {
        $states = $this->getStates(array($id), $context);

        return array_shift($states);
    }

    /**
     * Returns a list of area structs which identified over
     * the passed ids.
     *
     * @param array $ids
     * @param \Shopware\Struct\Context $context
     * @return Struct\Country\Area[]
     */
    public function getAreas(array $ids, Struct\Context $context)
    {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select($this->fieldHelper->getAreaFields());
        $query->from('s_core_countries_areas', 'countryArea');
        $query->where('countryArea.id IN (:ids)')
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
     * @param \Shopware\Struct\Context $context
     * @return Struct\Country[]
     */
    public function getCountries(array $ids, Struct\Context $context)
    {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select($this->fieldHelper->getCountryFields());

        $query->from('s_core_countries', 'country')
            ->leftJoin('country', 's_core_countries_attributes', 'countryAttribute', 'countryAttribute.countryID = country.id');

        $this->fieldHelper->addCountryTranslation($query);

        $query->where('country.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY)
            ->setParameter(':language', $context->getShop()->getId());


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
     * @param \Shopware\Struct\Context $context
     * @return Struct\Country\State[]
     */
    public function getStates(array $ids, Struct\Context $context)
    {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select($this->fieldHelper->getStateFields());

        $query->from('s_core_countries_states', 'countryState')
            ->leftJoin('countryState', 's_core_countries_states_attributes', 'countryStateAttribute', 'countryStateAttribute.stateID = countryState.id');

        $this->fieldHelper->addCountryStateTranslation($query);

        $query->where('countryState.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY)
            ->setParameter(':language', $context->getShop()->getId())
        ;

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $states = array();
        foreach ($data as $row) {
            $states[] = $this->countryHydrator->hydrateState($row);
        }

        return $states;
    }
}
