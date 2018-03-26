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

namespace Shopware\Bundle\ESIndexingBundle;

use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Class LastIdQuery
 */
class LastIdQuery
{
    /**
     * @var QueryBuilder
     */
    private $query;

    /**
     * @param QueryBuilder $query
     */
    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
    }

    /**
     * @return array
     */
    public function fetch()
    {
        $data = $this->query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);
        $keys = array_keys($data);
        $this->query->setParameter(':lastId', array_pop($keys));

        return array_values($data);
    }

    /**
     * @return int
     */
    public function fetchCount()
    {
        /** @var $query QueryBuilder */
        $query = clone $this->query;

        //get first column for distinct selection
        $select = $query->getQueryPart('select');

        $query->resetQueryPart('orderBy');
        $query->select('COUNT(DISTINCT ' . array_shift($select) . ')');

        return $query->execute()->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * @return QueryBuilder
     */
    public function getQuery()
    {
        return $this->query;
    }
}
