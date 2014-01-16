<?php

namespace Shopware\Components\Model\DBAL;

use Doctrine\DBAL\Query\QueryBuilder;

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
     * @var \PDOStatement
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
     * Class constructor which expects the executed pdo statement.
     *
     * @param QueryBuilder $builder
     * @internal param array $data
     */
    function __construct(QueryBuilder $builder)
    {
        $builder = clone $builder;

        $builder = $this->getCountQuery($builder);

        $this->statement = $builder->execute();

        $this->totalCount = $builder->getConnection()->fetchColumn(
            "SELECT FOUND_ROWS() as count"
        );
    }

    /**
     * Modifies the passed DBAL query builder object to calculate
     * the total count.
     *
     * @param QueryBuilder $builder
     * @return QueryBuilder
     */
    private function getCountQuery(QueryBuilder $builder)
    {
        $select = $builder->getQueryPart('select');
        $select[0] = ' SQL_CALC_FOUND_ROWS ' . $select[0];

        return $builder->select($select);
    }

    /**
     * Returns the data result of the statement
     * @return array
     */
    public function getData()
    {
        if ($this->data === null) {
            $this->data = $this->statement->fetchAll(
                \PDO::FETCH_ASSOC
            );
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

}
