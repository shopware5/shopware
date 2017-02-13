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

namespace Shopware\Bundle\CustomerSearchBundle;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;

class CustomerNumberSearch
{
    /**
     * @var HandlerRegistry
     */
    private $handlerRegistry;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param HandlerRegistry $handlerRegistry
     * @param Connection $connection
     */
    public function __construct(HandlerRegistry $handlerRegistry, Connection $connection)
    {
        $this->handlerRegistry = $handlerRegistry;
        $this->connection = $connection;
    }

    /**
     * @param Criteria $criteria
     * @return CustomerNumberSearchResult
     */
    public function search(Criteria $criteria)
    {
        $query = $this->buildQuery($criteria);

        $customers = $this->fetchCustomers($criteria, $query);

        $total = null;
        if ($criteria->fetchTotal()) {
            $total = $this->fetchTotal($query);
        }

        return new CustomerNumberSearchResult(
            $this->hydrate($customers),
            (int) $total
        );
    }

    /**
     * @param array[] $result
     * @return array
     */
    private function hydrate(array $result)
    {
        $rows = [];
        foreach ($result as $row) {
            $rows[] = new CustomerNumberRow(
                (int) $row['__customer_id'],
                $row['__customer_number'],
                $row['__customer_email'],
                ['core' => new Attribute($row)]
            );
        }

        return $rows;
    }

    /**
     * @param Criteria $criteria
     * @return QueryBuilder
     */
    private function buildQuery(Criteria $criteria)
    {
        $query = new QueryBuilder($this->connection);

        $query->from('s_user', 'user');
        $query->leftJoin('user', 's_customer_search_index', 'customer', 'user.id = customer.id');
        $query->leftJoin('user', 's_user_attributes', 'customerAttribute', 'customerAttribute.userID = user.id');

        foreach ($criteria->getConditions() as $condition) {
            $handler = $this->handlerRegistry->getConditionHandler($condition);
            $handler->handle($condition, $query);
        }
        return $query;
    }

    /**
     * @param Criteria $criteria
     * @param QueryBuilder $query
     * @return array[]
     */
    private function fetchCustomers(Criteria $criteria, QueryBuilder $query)
    {
        if ($criteria->getOffset() !== null) {
            $query->setFirstResult($criteria->getOffset());
        }
        if ($criteria->getLimit() !== null) {
            $query->setMaxResults($criteria->getLimit());
        }

        $query->addSelect(['user.*']);

        return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function fetchTotal(QueryBuilder $query)
    {
        $query->select('COUNT(DISTINCT customer.id)');
        $query->resetQueryPart('groupBy');
        $query->setFirstResult(0);
        $query->setMaxResults(1);

        return $query->execute()->fetch(\PDO::FETCH_COLUMN);
    }
}
