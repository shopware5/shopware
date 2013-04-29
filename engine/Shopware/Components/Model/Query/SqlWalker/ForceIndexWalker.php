<?php

namespace Shopware\Components\Model\Query\SqlWalker;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Quick hack to allow adding a FORCE INDEX on the query
 */
class ForceIndexWalker extends SqlWalker
{
    const HINT_FORCE_INDEX = 'ForceIndexWalker.ForceIndex';

    const HINT_STRAIGHT_JOIN = 'StraightJoinWalker.StraightJoin';

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

        if ($this->getQuery()->getHint(self::HINT_STRAIGHT_JOIN) === true) {
            $sql = str_replace('SELECT', 'SELECT STRAIGHT_JOIN ', $sql);
        }

        return $sql;
    }

    public function walkFromClause($fromClause)
    {
        $result = parent::walkFromClause($fromClause);

        if ($index = $this->getQuery()->getHint(self::HINT_FORCE_INDEX)) {
            $result = preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 FORCE INDEX (' . $index . ')', $result);
        }

        return $result;
    }

}