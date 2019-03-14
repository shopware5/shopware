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

namespace Shopware\Bundle\ESIndexingBundle\Property;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\ESIndexingBundle\LastIdQuery;

class PropertyQueryFactory
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param int|null $limit
     *
     * @return LastIdQuery
     */
    public function createQuery($limit = null)
    {
        return new LastIdQuery($this->createOptionQuery($limit));
    }

    /**
     * @param int|null $limit
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function createOptionQuery($limit = null)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['propertyGroups.id', 'propertyGroups.id'])
            ->from('s_filter_options', 'propertyGroups')
            ->where('propertyGroups.id > :lastId')
            ->setParameter(':lastId', 0);

        if ($limit !== null) {
            $query->setMaxResults($limit);
        }

        return $query;
    }
}
