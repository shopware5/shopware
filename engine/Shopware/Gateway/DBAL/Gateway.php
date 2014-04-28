<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Components\Model\ModelManager;

abstract class Gateway
{
    /**
     * Contains the selection for the s_articles_attributes table.
     * This table contains dynamically columns.
     *
     * @var array
     */
    private $attributeFields = array();

    /**
     * @var ModelManager
     */
    protected $entityManager;

    /**
     * Helper function which generates an array with table column selections
     * for the passed table.
     *
     * @param $table
     * @param $alias
     * @return array
     */
    protected function getTableFields($table, $alias)
    {
        $key = $table . '_' . $alias;

        if ($this->attributeFields[$key] !== null) {
            return $this->attributeFields[$key];
        }

        $schemaManager = $this->entityManager->getConnection()->getSchemaManager();

        $tableColumns = $schemaManager->listTableColumns($table);
        $columns = array();

        foreach ($tableColumns as $column) {
            $columns[] = $alias . '.' . $column->getName() . ' as __' . $alias . '_' . $column->getName();
        }

        $this->attributeFields[$key] = $columns;

        return $this->attributeFields[$key];
    }

    /**
     * Helper function which uses the first column as array key.
     *
     * @param QueryBuilder $query
     * @return array
     */
    protected function fetchAssociatedArray(QueryBuilder $query)
    {
        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_GROUP);

        $data = array_combine(
            array_keys($data),
            array_column($data, 0)
        );
        return $data;
    }
}