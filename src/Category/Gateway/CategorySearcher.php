<?php

namespace Shopware\Category\Gateway;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Search\Search;
use Shopware\Search\SearchResult;

class CategorySearcher extends Search
{
    protected function createQuery(): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();

        $query->select([
            'category.id',
            'category.parent as parent_id',
            'category.path',
            'category.description as name'
        ]);

        $query->from('s_categories', 'category');

        return $query;
    }

    protected function createResult(array $rows, int $total): SearchResult
    {
        $rows = array_map(function (array $row) {
            $row['path'] = array_filter(explode('|', $row['path']));
            return $row;
        }, $rows);

        return new SearchResult($rows, $total);
    }
}