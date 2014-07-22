<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Bundle\SearchBundle\DBAL;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundle\DBAL
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class QueryBuilder extends \Doctrine\DBAL\Query\QueryBuilder
{
    /**
     * @var string[]
     */
    private $states = array();

    /**
     * @return string[]
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * @param string $state
     */
    public function addState($state)
    {
        $this->states[] = $state;
    }

    /**
     * @param string $state
     * @return bool
     */
    public function hasState($state)
    {
        return in_array($state, $this->states);
    }

    /**
     * @param $table
     * @return array|bool
     */
    public function includesTable($table)
    {
        foreach ($this->getQueryPart('from') as $from) {
            if ($from['table'] == $table) {
                return array(
                    'type'  => 'from',
                    'table' => $from['table'],
                    'alias' => $from['alias']
                );
            }
        }

        foreach ($this->getQueryPart('join') as $joinFrom) {
            foreach ($joinFrom as $join) {
                if ($join['joinTable'] == $table) {
                    return array(
                        'type'  => $join['joinType'],
                        'table' => $join['joinTable'],
                        'alias' => $join['joinAlias'],
                        'condition' => $join['joinCondition']
                    );
                }
            }
        }

        return false;
    }

    /**
     * @param $table
     */
    public function removeTableInclude($table)
    {
        $fromParts = $this->getQueryPart('from');

        foreach ($fromParts as $key => $from) {
            if ($from['table'] == $table) {
                unset($fromParts[$key]);
            }
        }

        $joinParts = $this->getQueryPart('join');

        foreach ($joinParts as $group => $joinFrom) {
            foreach ($joinFrom as $key => $join) {
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

        foreach ($fromParts as $from) {
            $this->from($from['table'], $from['alias']);
        }

        foreach ($joinParts as $joinFrom => $joinGroup) {
            foreach ($joinGroup as $join) {
                switch ($join['joinType']) {
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
