<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
use Shopware\Components\Model\ModelManager;
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
     * @param ModelManager $entityManager
     * @param FieldHelper $fieldHelper
     * @param Hydrator\CountryHydrator $countryHydrator
     */
    public function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\CountryHydrator $countryHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->countryHydrator = $countryHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * @inheritdoc
     */
    public function getArea($id, Struct\ShopContextInterface $context)
    {
        $areas = $this->getAreas(array($id), $context);

        return array_shift($areas);
    }

    /**
     * @inheritdoc
     */
    public function getCountry($id, Struct\ShopContextInterface $context)
    {
        $countries = $this->getCountries(array($id), $context);

        return array_shift($countries);
    }

    /**
     * @inheritdoc
     */
    public function getState($id, Struct\ShopContextInterface $context)
    {
        $states = $this->getStates(array($id), $context);

        return array_shift($states);
    }

    /**
     * @inheritdoc
     */
    public function getAreas(array $ids, Struct\ShopContextInterface $context)
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
     * @inheritdoc
     */
    public function getCountries(array $ids, Struct\ShopContextInterface $context)
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
     * @inheritdoc
     */
    public function getStates(array $ids, Struct\ShopContextInterface $context)
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
