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

namespace Shopware\Components\Model\DBAL;

use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Class Result which allows to paginate dbal queries.
 */
class Result
{
    /**
     * Contains the data result of the pdo statement.
     *
     * @var array
     */
    protected $data;

    /**
     * Contains the executed pdo statement of the passed query builder.
     *
     * @var \Doctrine\DBAL\Driver\ResultStatement
     */
    protected $statement;

    /**
     * Contains the total count of the executed query, if no max result would be set.
     * Use this value for pagination.
     *
     * @var int
     */
    protected $totalCount;

    /**
     * The fetch mode which is used for the PDOStatement->fetch() function.
     *
     * @var int
     */
    protected $fetchMode;

    /**
     * Class constructor which expects the DBAL query builder object.
     *
     * @param QueryBuilder $builder       the DBAL\Query builder object
     * @param int          $fetchMode     Allows to define the data result structure
     * @param bool         $useCountQuery allows to disable or enable the total count query
     *
     * @internal param array $data
     */
    public function __construct(QueryBuilder $builder, $fetchMode = \PDO::FETCH_ASSOC, $useCountQuery = true)
    {
        $builder = clone $builder;

        $this->fetchMode = $fetchMode;

        if ($useCountQuery) {
            $this->addTotalCountSelect($builder);
        }

        $this->statement = $builder->execute();

        if ($useCountQuery) {
            $this->totalCount = $builder->getConnection()->fetchColumn(
                'SELECT FOUND_ROWS() as count'
            );
        }
    }

    /**
     * Returns the data result of the statement
     *
     * @return array
     */
    public function getData()
    {
        if ($this->data === null) {
            $this->data = $this->statement->fetchAll($this->fetchMode);
        }

        return $this->data;
    }

    /**
     * Returns the total count of the statement
     * without using the LIMIT condition.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * Modifies the passed DBAL query builder object to calculate
     * the total count.
     *
     * @return $this
     */
    private function addTotalCountSelect(QueryBuilder $builder)
    {
        $select = $builder->getQueryPart('select');
        $select[0] = ' SQL_CALC_FOUND_ROWS ' . $select[0];
        $builder->select($select);

        return $this;
    }
}
