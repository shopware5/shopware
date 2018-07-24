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
            'list' => $this->getVisitsList(),
            'listByDevice' => $this->getVisitsPerDevice(),
        ];
    }

    /**
     * @return array
     */
    private function getVisitsList()
    {
        $queryBuilder = $this->getVisitsListQueryBuilder();

        return $queryBuilder->execute()->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @return array
     */
    private function getVisitsPerDevice()
    {
        $queryBuilder = $this->getVisitsPerDeviceQueryBuilder();

        $visitsPerDevice = $queryBuilder->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        return $visitsPerDevice;
    }

    /**
     * @return QueryBuilder
     */
    private function getVisitsPerDeviceQueryBuilder()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder
            ->addSelect('visitors.datum as date')
            ->addSelect('visitors.deviceType')
            ->addSelect('SUM(visitors.pageimpressions) as totalImpressions')
            ->addSelect('SUM(visitors.uniquevisits) as totalUniqueVisits')
            ->from('s_statistics_visitors', 'visitors')
            ->andWhere('visitors.shopID = :shopId')
            ->setParameter(':shopId', $this->shopId)
            ->groupBy('visitors.datum, visitors.deviceType');
    }

    /**
     * @return QueryBuilder
     */
    private function getVisitsListQueryBuilder()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->addSelect('visitors.datum as date')
            ->addSelect('SUM(visitors.pageimpressions) as totalImpressions')
            ->addSelect('SUM(visitors.uniquevisits) as totalUniqueVisits')
            ->from('s_statistics_visitors', 'visitors')
            ->where('visitors.shopID = :shopId')
            ->setParameter(':shopId', $this->shopId)
            ->groupBy('visitors.datum');
    }
}
