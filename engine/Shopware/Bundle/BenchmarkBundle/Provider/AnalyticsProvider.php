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
    private const NAME = 'analytics';

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
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getBenchmarkData(ShopContextInterface $shopContext)
    {
        $this->shopId = $shopContext->getShop()->getId();

        $config = $this->getConfig();

        $returnData = [
            'list' => $this->getVisitsList((int) $config['last_analytics_id']),
            'listByDevice' => $this->getVisitsPerDevice((int) $config['last_analytics_id']),
        ];

        return $returnData;
    }

    /**
     * @param int $lastAnalyticsId
     *
     * @return array
     */
    private function getVisitsList($lastAnalyticsId)
    {
        $queryBuilder = $this->getVisitsListQueryBuilder($lastAnalyticsId);

        $data = $queryBuilder->execute()->fetchAll(\PDO::FETCH_ASSOC);

        $data = array_map(function ($item) {
            $item['totalImpressions'] = (int) $item['totalImpressions'];
            $item['totalUniqueVisits'] = (int) $item['totalUniqueVisits'];

            return $item;
        }, $data);

        return $data;
    }

    /**
     * @param int $lastAnalyticsId
     *
     * @return array
     */
    private function getVisitsPerDevice($lastAnalyticsId)
    {
        $queryBuilder = $this->getVisitsPerDeviceQueryBuilder($lastAnalyticsId);

        $visitsPerDevice = $queryBuilder->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        $visitsPerDevice = array_map(function ($item) {
            $item['totalImpressions'] = (int) $item['totalImpressions'];
            $item['totalUniqueVisits'] = (int) $item['totalUniqueVisits'];

            return $item;
        }, $visitsPerDevice);

        return $visitsPerDevice;
    }

    /**
     * @param int $lastAnalyticsId
     *
     * @return QueryBuilder
     */
    private function getVisitsPerDeviceQueryBuilder($lastAnalyticsId)
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder
            ->addSelect('visitors.datum as date')
            ->addSelect('visitors.deviceType')
            ->addSelect('SUM(visitors.pageimpressions) as totalImpressions')
            ->addSelect('SUM(visitors.uniquevisits) as totalUniqueVisits')
            ->from('s_statistics_visitors', 'visitors')
            ->andWhere('visitors.shopID = :shopId')
            ->andWhere('visitors.id > :lastId')
            ->setParameter(':shopId', $this->shopId)
            ->setParameter(':lastId', $lastAnalyticsId)
            ->groupBy('visitors.datum, visitors.deviceType');
    }

    /**
     * @param int $lastAnalyticsId
     *
     * @return QueryBuilder
     */
    private function getVisitsListQueryBuilder($lastAnalyticsId)
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->addSelect('visitors.datum as date')
            ->addSelect('SUM(visitors.pageimpressions) as totalImpressions')
            ->addSelect('SUM(visitors.uniquevisits) as totalUniqueVisits')
            ->from('s_statistics_visitors', 'visitors')
            ->where('visitors.shopID = :shopId')
            ->andWhere('visitors.id > :lastId')
            ->setParameter(':shopId', $this->shopId)
            ->setParameter(':lastId', $lastAnalyticsId)
            ->groupBy('visitors.datum');
    }

    /**
     * @return array
     */
    private function getConfig()
    {
        $configsQueryBuilder = $this->dbalConnection->createQueryBuilder();

        return $configsQueryBuilder->select('configs.*')
            ->from('s_benchmark_config', 'configs')
            ->where('configs.shop_id = :shopId')
            ->setParameter(':shopId', $this->shopId)
            ->execute()
            ->fetch();
    }
}
