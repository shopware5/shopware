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
use Shopware\Bundle\StoreFrontBundle\Gateway\AddressGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\AddressHydrator;

class AddressGateway implements AddressGatewayInterface
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

    public function __construct(Connection $connection, FieldHelper $fieldHelper, AddressHydrator $hydrator)
    {
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
        $this->hydrator = $hydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($ids)
    {
        if (empty($ids)) {
            return [];
        }
        $ids = array_filter($ids);
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
