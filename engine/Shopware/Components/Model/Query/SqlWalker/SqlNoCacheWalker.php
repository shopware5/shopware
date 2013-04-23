<?php

namespace Shopware\Components\Model\Query\SqlWalker;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Quick hack to allow adding a FORCE INDEX on the query
 */
class SqlNoCacheWalker extends SqlWalker
{
    const HINT_SQL_NO_CACHE = 'SqlNoCacheWalker.SqlNoCache';

    public function walkSelectClause($selectClause)
    {
        $sql = parent::walkSelectClause($selectClause);

        if ($this->getQuery()->getHint(self::HINT_SQL_NO_CACHE) === true) {
            if ($selectClause->isDistinct) {
                $sql = str_replace('SELECT DISTINCT', 'SELECT DISTINCT SQL_NO_CACHE', $sql);
            } else {
                $sql = str_replace('SELECT', 'SELECT SQL_NO_CACHE ', $sql);
            }
        }

        return $sql;
    }

}


