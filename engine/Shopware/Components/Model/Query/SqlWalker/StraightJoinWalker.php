<?php

namespace Shopware\Components\Model\Query\SqlWalker;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Quick hack to allow adding a FORCE INDEX on the query
 */
class StraightJoinWalker extends SqlWalker
{
    const HINT_STRAIGHT_JOIN = 'StraightJoinWalker.StraightJoin';

    public function walkSelectClause($selectClause)
    {
        $sql = parent::walkSelectClause($selectClause);

        if ($this->getQuery()->getHint(self::HINT_STRAIGHT_JOIN) === true) {
            $sql = str_replace('SELECT', 'SELECT STRAIGHT_JOIN ', $sql);
        }

        return $sql;
    }

}

