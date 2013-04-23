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

    public function walkFromClause($fromClause)
    {
        $result = parent::walkFromClause($fromClause);

        if ($index = $this->getQuery()->getHint(self::HINT_FORCE_INDEX)) {
            $result = preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 FORCE INDEX (' . $index . ')', $result);
        }

        return $result;
    }

    public function walkSelectClause($selectClause)
    {
        $sql = parent::walkSelectClause($selectClause);

        if ($this->getQuery()->getHint(self::HINT_STRAIGHT_JOIN) === true) {
            $sql = str_replace('SELECT', 'SELECT STRAIGHT_JOIN ', $sql);
        }

        return $sql;
    }

}