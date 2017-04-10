<?php
declare(strict_types=1);
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

namespace Shopware\Bundle\StoreFrontBundle\Gateway;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Gateway\Hydrator\CustomerHydrator;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer;
use Shopware\Bundle\CartBundle\Infrastructure\SortArrayByKeysTrait;
use Shopware\Bundle\StoreFrontBundle\Gateway\FieldHelper;
use Shopware\Bundle\StoreFrontBundle\Struct\TranslationContext;

class CustomerGateway
{
    use SortArrayByKeysTrait;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var CustomerHydrator
     */
    private $hydrator;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param FieldHelper      $fieldHelper
     * @param CustomerHydrator $hydrator
     * @param Connection       $connection
     */
    public function __construct(FieldHelper $fieldHelper, CustomerHydrator $hydrator, Connection $connection)
    {
        $this->fieldHelper = $fieldHelper;
        $this->hydrator = $hydrator;
        $this->connection = $connection;
    }

    /**
     * @param int[]              $ids
     * @param TranslationContext $context
     *
     * @return Customer[]
     */
    public function getList(array $ids, TranslationContext $context): array
    {
        if (0 === count($ids)) {
            return [];
        }
        $query = $this->connection->createQueryBuilder();
        $query->select('customer.id as arrayKey');
        $query->addSelect($this->fieldHelper->getCustomerFields());
        $query->addSelect($this->fieldHelper->getCustomerGroupFields());
        $query->addSelect($this->fieldHelper->getPaymentMethodFields());

        $query->from('s_user', 'customer');
        $query->leftJoin('customer', 's_user_attributes', 'customerAttribute', 'customer.id = customerAttribute.userID');
        $query->leftJoin('customer', 's_core_customergroups', 'customerGroup', 'customerGroup.groupkey = customer.customergroup');
        $query->leftJoin('customerGroup', 's_core_customergroups_attributes', 'customerGroupAttribute', 'customerGroupAttribute.customerGroupID = customerGroup.id');
        $query->leftJoin('customer', 's_core_paymentmeans', 'paymentMethod', 'paymentMethod.id = customer.paymentpreset');
        $query->leftJoin('paymentMethod', 's_core_paymentmeans_attributes', 'paymentMethodAttribute', 'paymentMethodAttribute.paymentmeanID = paymentMethod.id');

        $query->where('customer.id IN (:ids)');
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addCustomerTranslation($query, $context);

        $data = $query->execute()->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);
        $customers = [];
        foreach ($data as $id => $row) {
            $customers[$id] = $this->hydrator->hydrate($row);
        }

        return $this->sortIndexedArrayByKeys($ids, $customers);
    }
}
