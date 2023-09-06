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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\CustomerGroupGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\CustomerGroupHydrator;

class CustomerGroupGateway implements CustomerGroupGatewayInterface
{
    private CustomerGroupHydrator $customerGroupHydrator;

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
     */
    private FieldHelper $fieldHelper;

    private Connection $connection;

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        CustomerGroupHydrator $customerGroupHydrator
    ) {
        $this->customerGroupHydrator = $customerGroupHydrator;
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $groups = $this->getList([$key]);

        return array_shift($groups);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $keys)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select($this->fieldHelper->getCustomerGroupFields());

        $query->from('s_core_customergroups', 'customerGroup')
            ->leftJoin('customerGroup', 's_core_customergroups_attributes', 'customerGroupAttribute', 'customerGroupAttribute.customerGroupID = customerGroup.id')
            ->where('customerGroup.groupkey IN (:keys)')
            ->setParameter(':keys', $keys, Connection::PARAM_STR_ARRAY);

        $data = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

        $customerGroups = [];
        foreach ($data as $group) {
            $key = (string) $group['__customerGroup_groupkey'];
            $customerGroups[$key] = $this->customerGroupHydrator->hydrate($group);
        }

        return $customerGroups;
    }
}
