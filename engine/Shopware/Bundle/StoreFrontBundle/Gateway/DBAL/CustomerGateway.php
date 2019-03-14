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
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\CustomerGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\CustomerHydrator;

class CustomerGateway implements CustomerGatewayInterface
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
     * @var CustomerHydrator
     */
    private $hydrator;

    public function __construct(Connection $connection, FieldHelper $fieldHelper, CustomerHydrator $hydrator)
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
        $ids = array_keys(array_flip($ids));

        $data = $this->fetchCustomers($ids);

        $customers = [];
        foreach ($data as $row) {
            $customer = $this->hydrator->hydrate($row);
            $customers[$customer->getId()] = $customer;
        }

        return $customers;
    }

    /**
     * @param int[] $ids
     *
     * @return array
     */
    private function fetchCustomers($ids)
    {
        $query = $this->connection->createQueryBuilder();
        $query->addSelect($this->fieldHelper->getCustomerFields());
        $query->addSelect($this->fieldHelper->getCustomerGroupFields());
        $query->addSelect($this->fieldHelper->getPaymentFields());
        $query->addSelect('(SELECT 1 FROM s_campaigns_mailaddresses campaign_mail WHERE campaign_mail.email = customer.email LIMIT 1) as __active_campaign');
        $query->from('s_user', 'customer');
        $query->where('customer.id IN (:ids)');
        $query->leftJoin('customer', 's_core_customergroups', 'customerGroup', 'customerGroup.groupkey = customer.customergroup');
        $query->leftJoin('customerGroup', 's_core_customergroups_attributes', 'customerGroupAttribute', 'customerGroupAttribute.customerGroupID = customerGroup.id');
        $query->leftJoin('customer', 's_core_paymentmeans', 'payment', 'payment.id = customer.paymentID');
        $query->leftJoin('payment', 's_core_paymentmeans_attributes', 'paymentAttribute', 'payment.id = paymentAttribute.paymentmeanID');
        $query->leftJoin('customer', 's_user_attributes', 'customerAttribute', 'customer.id = customerAttribute.userID');
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
    }
}
