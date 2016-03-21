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
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Gateway;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Gateway\DBAL
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
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
     * @param Connection $connection
     * @param FieldHelper $fieldHelper
     * @param Hydrator\CountryHydrator $countryHydrator
     */
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
     * @inheritdoc
     */
    public function getArea($id, Struct\ShopContextInterface $context)
    {
        $areas = $this->getAreas([$id], $context);

        return array_shift($areas);
    }

    /**
     * @inheritdoc
     */
    public function getCountry($id, Struct\ShopContextInterface $context)
    {
        $countries = $this->getCountries([$id], $context);

        return array_shift($countries);
    }

    /**
     * @inheritdoc
     */
    public function getState($id, Struct\ShopContextInterface $context)
    {
        $states = $this->getStates([$id], $context);

        return array_shift($states);
    }

    /**
     * @inheritdoc
     */
    public function getAreas(array $ids, Struct\ShopContextInterface $context)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select($this->fieldHelper->getAreaFields());

        $query->from('s_core_countries_areas', 'countryArea')
            ->where('countryArea.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $areas = [];
        foreach ($data as $row) {
            $areas[] = $this->countryHydrator->hydrateArea($row);
        }

        return $areas;
    }

    /**
     * @inheritdoc
     */
    public function getCountries(array $ids, Struct\ShopContextInterface $context)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select($this->fieldHelper->getCountryFields());
        $query->from('s_core_countries', 'country')
            ->leftJoin('country', 's_core_countries_attributes', 'countryAttribute', 'countryAttribute.countryID = country.id')
            ->where('country.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addCountryTranslation($query, $context);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $countries = [];
        foreach ($data as $row) {
            $countries[] = $this->countryHydrator->hydrateCountry($row);
        }

        return $countries;
    }

    /**
     * @inheritdoc
     */
    public function getStates(array $ids, Struct\ShopContextInterface $context)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select($this->fieldHelper->getStateFields());

        $query->from('s_core_countries_states', 'countryState')
            ->leftJoin('countryState', 's_core_countries_states_attributes', 'countryStateAttribute', 'countryStateAttribute.stateID = countryState.id')
            ->where('countryState.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addCountryStateTranslation($query, $context);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $states = [];
        foreach ($data as $row) {
            $states[] = $this->countryHydrator->hydrateState($row);
        }

        return $states;
    }
}
