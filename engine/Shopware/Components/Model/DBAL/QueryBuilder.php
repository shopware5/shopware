<?php

namespace Shopware\Components\Model\DBAL;

class QueryBuilder extends \Doctrine\DBAL\Query\QueryBuilder
{
    public function includesTable($table)
    {
        foreach($this->getQueryPart('from') as $from) {
            if ($from['table'] == $table) {
                return true;
            }
        }

        foreach($this->getQueryPart('join') as $joinFrom) {
            foreach($joinFrom as $join) {
                if ($join['joinTable'] == $table) {
                    return true;
                }
            }
        }

        return false;
    }

    public function removeTableInclude($table)
    {
        $fromParts = $this->getQueryPart('from');

        foreach($fromParts as $key => $from) {
            if ($from['table'] == $table) {
                unset($fromParts[$key]);
            }
        }

        $joinParts = $this->getQueryPart('join');

        foreach($joinParts as $group => $joinFrom) {
            foreach($joinFrom as $key => $join) {
                if ($join['joinTable'] == $table) {
                    unset($joinParts[$group][$key]);
                }
            }

            if (empty($joinFrom)) {
                unset($joinFrom);
            }
        }

        $this->resetQueryPart('from')
            ->resetQueryPart('join');

        foreach($fromParts as $from) {
            $this->from($from['table'], $from['alias']);
        }

        foreach($joinParts as $joinFrom => $joinGroup) {
            foreach($joinGroup as $join) {
                switch($join['joinType']) {
                    case "inner":
                        $this->innerJoin(
                            $joinFrom,
                            $join['joinTable'],
                            $join['joinAlias'],
                            $join['joinCondition']
                        );
                        break;
                    case "left":
                        $this->leftJoin(
                            $joinFrom,
                            $join['joinTable'],
                            $join['joinAlias'],
                            $join['joinCondition']
                        );
                        break;
                }
            }
        }
    }
}