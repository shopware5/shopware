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

namespace Shopware\Bundle\CustomerSearchBundleDBAL;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\CustomerSearchBundle\BaseCustomer;
use Shopware\Bundle\CustomerSearchBundle\CustomerNumberSearchInterface;
use Shopware\Bundle\CustomerSearchBundle\CustomerNumberSearchResult;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;

class CustomerNumberSearch implements CustomerNumberSearchInterface
{
    /**
     * @var HandlerRegistry
     */
    private $handlerRegistry;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        HandlerRegistry $handlerRegistry,
        Connection $connection
    ) {
        $this->handlerRegistry = $handlerRegistry;
        $this->connection = $connection;
    }

    /**
     * @return CustomerNumberSearchResult
     */
    public function search(Criteria $criteria)
    {
        $query = $this->buildQuery($criteria);

        $customers = $this->fetchCustomers($criteria, $query);

        $total = count($customers);
        if ($criteria->fetchCount()) {
            $total = $this->fetchTotal($query);
        }

        return new CustomerNumberSearchResult(
            $this->hydrate($customers),
            (int) $total
        );
    }

    /**
     * @param array[] $result
     *
     * @return array
     */
    private function hydrate(array $result)
    {
        $rows = [];
        foreach ($result as $row) {
            $rows[] = new BaseCustomer(
                (int) $row['id'],
                $row['customernumber'],
                $row['email'],
                ['search' => new Attribute($row)]
            );
        }

        return $rows;
    }

    /**
     * @return QueryBuilder
     */
    private function buildQuery(Criteria $criteria)
    {
        $query = new QueryBuilder($this->connection);

        $query->from('s_customer_search_index', 'customer');
        $query->leftJoin('customer', 's_user_attributes', 'customerAttribute', 'customerAttribute.userID = customer.id');

        foreach ($criteria->getConditions() as $condition) {
            $handler = $this->handlerRegistry->getConditionHandler($condition);
            $handler->handle($condition, $query);
        }

        return $query;
    }

    /**
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

        foreach ($criteria->getSortings() as $sorting) {
            $handler = $this->handlerRegistry->getSortingHandler($sorting);
            $handler->handle($sorting, $query);
        }

        $query->addSelect('customer.id, customer.customernumber, customer.email');

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
