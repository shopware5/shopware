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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Gateway;
use Shopware\Bundle\StoreFrontBundle\Struct;

class CountryGateway implements Gateway\CountryGatewayInterface
{
    /**
     * @var Hydrator\CountryHydrator
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
     * @var Connection
     */
    private $connection;

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        Hydrator\CountryHydrator $countryHydrator
    ) {
        $this->connection = $connection;
        $this->countryHydrator = $countryHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getArea($id, Struct\ShopContextInterface $context)
    {
        $areas = $this->getAreas([$id], $context);

        return array_shift($areas);
    }

    /**
     * {@inheritdoc}
     */
    public function getCountry($id, Struct\ShopContextInterface $context)
    {
        $countries = $this->getCountries([$id], $context);

        return array_shift($countries);
    }

    /**
     * {@inheritdoc}
     */
    public function getState($id, Struct\ShopContextInterface $context)
    {
        $states = $this->getStates([$id], $context);

        return array_shift($states);
    }

    /**
     * {@inheritdoc}
     */
    public function getAreas(array $ids, Struct\ShopContextInterface $context)
    {
        if (empty($ids)) {
            return [];
        }

        $query = $this->connection->createQueryBuilder();

        $query->select($this->fieldHelper->getAreaFields());

        $query->from('s_core_countries_areas', 'countryArea')
            ->where('countryArea.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $areas = [];
        foreach ($data as $row) {
            $area = $this->countryHydrator->hydrateArea($row);
            $areas[$area->getId()] = $area;
        }

        return $this->sortByIds($ids, $areas);
    }

    /**
     * {@inheritdoc}
     */
    public function getCountries(array $ids, Struct\ShopContextInterface $context)
    {
        if (empty($ids)) {
            return [];
        }

        $query = $this->connection->createQueryBuilder();

        $query->select($this->fieldHelper->getCountryFields());
        $query->from('s_core_countries', 'country')
            ->leftJoin('country', 's_core_countries_attributes', 'countryAttribute', 'countryAttribute.countryID = country.id')
            ->where('country.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addCountryTranslation($query, $context);

        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $countries = [];
        foreach ($data as $row) {
            $country = $this->countryHydrator->hydrateCountry($row);
            $countries[$country->getId()] = $country;
        }

        return $this->sortByIds($ids, $countries);
    }

    /**
     * {@inheritdoc}
     */
    public function getStates(array $ids, Struct\ShopContextInterface $context)
    {
        if (empty($ids)) {
            return [];
        }

        $query = $this->createStateQuery($context);

        $query->where('countryState.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $states = [];
        foreach ($data as $row) {
            $state = $this->countryHydrator->hydrateState($row);
            $states[$state->getId()] = $state;
        }

        return $this->sortByIds($ids, $states);
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryStates($countryIds, Struct\ShopContextInterface $context)
    {
        if (empty($countryIds)) {
            return [];
        }

        $query = $this->createStateQuery($context);

        $query->where('countryState.countryID IN (:ids)')
            ->setParameter(':ids', $countryIds, Connection::PARAM_INT_ARRAY);

        $data = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

        $states = [];
        foreach ($data as $row) {
            $countryId = (int) $row['__countryState_countryID'];
            $state = $this->countryHydrator->hydrateState($row);
            $states[$countryId][$state->getId()] = $state;
        }

        return $states;
    }

    /**
     * @param int[] $ids
     * @param array $data
     *
     * @return array
     */
    private function sortByIds($ids, $data)
    {
        $sorted = [];
        foreach ($ids as $id) {
            if (isset($data[$id])) {
                $sorted[$id] = $data[$id];
            }
        }

        return $sorted;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function createStateQuery(Struct\ShopContextInterface $context)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select($this->fieldHelper->getStateFields());

        $query->from('s_core_countries_states', 'countryState')
            ->leftJoin('countryState', 's_core_countries_states_attributes', 'countryStateAttribute', 'countryStateAttribute.stateID = countryState.id');

        $this->fieldHelper->addCountryStateTranslation($query, $context);

        return $query;
    }
}
