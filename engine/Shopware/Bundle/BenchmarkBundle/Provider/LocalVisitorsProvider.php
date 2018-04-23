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

namespace Shopware\Bundle\BenchmarkBundle\Provider;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\BenchmarkBundle\BenchmarkProviderInterface;

class LocalVisitorsProvider implements BenchmarkProviderInterface
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function getName()
    {
        return 'visitors';
    }

    public function getBenchmarkData()
    {
        return $this->getVisitorsByDay();
    }

    /**
     * @return array
     */
    private function getVisitorsByDay()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        $result = $queryBuilder->select([
                'DATE(visitors.datum) as day',
                'SUM(visitors.uniquevisits) as visitors',
            ])
            ->from('s_statistics_visitors', 'visitors')
            ->orderBy('visitors.datum', 'asc')
            ->groupBy('DATE(visitors.datum)')
            ->setMaxResults(365)
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        $result = array_map('intval', $result);

        return $result;
    }
}
