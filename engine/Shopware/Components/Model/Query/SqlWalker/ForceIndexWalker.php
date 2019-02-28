<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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

namespace Shopware\Components\Model\Query\SqlWalker;

use Doctrine\ORM\Query\SqlWalker;

/**
 * Quick hack to allow adding a SQL specified commands
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
