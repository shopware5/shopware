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
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\BenchmarkBundle\BenchmarkProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class AnalyticsProvider implements BenchmarkProviderInterface
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var int
     */
    private $shopId;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function getName()
    {
        return 'analytics';
    }

    /**
     * {@inheritdoc}
     */
    public function getBenchmarkData(ShopContextInterface $shopContext)
    {
        $this->shopId = $shopContext->getShop()->getId();

        return [
            'totalVisitsYesterday' => $this->getVisitsYesterday(),
            'totalViewsYesterday' => $this->getViewsYesterday(),
            'visitsByDeviceYesterday' => $this->getVisitsYesterdayPerDevice(),
            'totalVisitsByDevice' => $this->getTotalVisitsByDevice(),
            'totalVisits' => $this->getTotalVisits(),
        ];
    }

    /**
     * @return int
     */
    private function getVisitsYesterday()
    {
        $queryBuilder = $this->getVisitsYesterdayQueryBuilder();

        return (int) $queryBuilder->groupBy('visitors.datum')
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return int
     */
    private function getViewsYesterday()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return (int) $queryBuilder->select('SUM(visitors.pageimpressions) as pageImpressions')
            ->from('s_statistics_visitors', 'visitors')
            ->where('visitors.datum = CURDATE() - INTERVAL 1 DAY')
            ->andWhere('visitors.shopID = :shopId')
            ->setParameter(':shopId', $this->shopId)
            ->groupBy('visitors.datum')
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return array
     */
    private function getVisitsYesterdayPerDevice()
    {
        $queryBuilder = $this->getVisitsYesterdayQueryBuilder();

        $visitsPerDevice = $queryBuilder->select('visitors.deviceType, SUM(visitors.uniquevisits) as uniqueVisits')
            ->groupBy('visitors.datum, visitors.deviceType')
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        $visitsPerDevice = array_map('intval', $visitsPerDevice);

        return $visitsPerDevice;
    }

    /**
     * @return QueryBuilder
     */
    private function getVisitsYesterdayQueryBuilder()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->select('SUM(visitors.uniquevisits) as uniqueVisits')
            ->from('s_statistics_visitors', 'visitors')
            ->where('visitors.datum = CURDATE() - INTERVAL 1 DAY')
            ->andWhere('visitors.shopID = :shopId')
            ->setParameter(':shopId', $this->shopId);
    }

    /**
     * @return int
     */
    private function getTotalVisits()
    {
        $queryBuilder = $this->getTotalVisitsQueryBuilder();

        return (int) $queryBuilder->execute()->fetchColumn();
    }

    /**
     * @return array
     */
    private function getTotalVisitsByDevice()
    {
        $queryBuilder = $this->getTotalVisitsQueryBuilder();

        $visitsPerDevice = $queryBuilder->select('visitors.deviceType, SUM(visitors.uniquevisits) as uniqueVisits')
            ->groupBy('visitors.deviceType')
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        $visitsPerDevice = array_map('intval', $visitsPerDevice);

        return $visitsPerDevice;
    }

    /**
     * @return QueryBuilder
     */
    private function getTotalVisitsQueryBuilder()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->select('SUM(visitors.uniquevisits) as uniqueVisits')
            ->from('s_statistics_visitors', 'visitors')
            ->where('visitors.shopID = :shopId')
            ->setParameter(':shopId', $this->shopId);
    }
}
