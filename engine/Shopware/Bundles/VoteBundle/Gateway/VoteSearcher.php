<?php

namespace VoteBundle\Gateway;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Search\Search;

class VoteSearcher extends Search
{
    protected function createQuery(): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['*']);
        $query->from('s_articles_vote', 'vote');

        return $query;
    }
}