<?php

namespace Shopware\Bundle\CustomerSearchBundle\Gateway;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\FieldHelper;

class AddressGateway
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var AddressHydrator
     */
    private $hydrator;

    /**
     * @param Connection $connection
     * @param FieldHelper $fieldHelper
     * @param AddressHydrator $hydrator
     */
    public function __construct(Connection $connection, FieldHelper $fieldHelper, AddressHydrator $hydrator)
    {
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
        $this->hydrator = $hydrator;
    }

    /**
     * @param int[] $ids
     * @return array
     */
    public function getList($ids)
    {
        $ids = array_keys(array_flip($ids));

        $query = $this->createQuery();

        $query->where('address.id IN (:ids)');
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $data = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $addresses = [];
        foreach ($data as $row) {
            $id = $row['__address_id'];
            $addresses[$id] = $this->hydrator->hydrate($row);
        }

        return $addresses;
    }

    private function createQuery()
    {
        $query = $this->connection->createQueryBuilder();
        $query->addSelect($this->fieldHelper->getAddressFields());
        $query->addSelect($this->fieldHelper->getCountryFields());
        $query->addSelect($this->fieldHelper->getStateFields());

        $query->from('s_user_addresses', 'address');
        $query->leftJoin('address', 's_user_addresses_attributes', 'addressAttribute', 'addressAttribute.address_id = address.id');

        $query->leftJoin('address', 's_core_countries', 'country', 'country.id = address.country_id');
        $query->leftJoin('country', 's_core_countries_attributes', 'countryAttribute', 'countryAttribute.countryID = country.id');

        $query->leftJoin('address', 's_core_countries_states', 'countryState', 'countryState.id = address.state_id');
        $query->leftJoin('countryState', 's_core_countries_states_attributes', 'countryStateAttribute', 'countryStateAttribute.stateID = countryState.id');

        return $query;
    }
}
