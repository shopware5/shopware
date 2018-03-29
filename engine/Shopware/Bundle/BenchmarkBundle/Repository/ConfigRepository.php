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

namespace Shopware\Bundle\BenchmarkBundle\Repository;

use Doctrine\DBAL\Connection;

class ConfigRepository implements ConfigRepositoryInterface
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
     * {@inheritdoc}
     */
    public function loadSettings()
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        return $queryBuilder->select([
                'settings.active',
                'settings.last_sent as lastSent',
                'settings.last_received as lastReceived',
                'settings.last_order_id as lastOrderId',
                'settings.orders_batch_size as ordersBatchSize',
                'settings.business',
                'settings.terms_accepted as termsAccepted',
            ])
            ->from('s_benchmark_config', 'settings')
            ->execute()
            ->fetch();
    }

    /**
     * {@inheritdoc}
     */
    public function saveSettings($ordersBatchSize)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->update('s_benchmark_config', 'config')
            ->set('config.orders_batch_size', ':ordersBatchSize')
            ->setParameter(':ordersBatchSize', $ordersBatchSize)
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function saveBusiness($business)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->update('s_benchmark_config', 'config')
            ->set('config.business', ':business')
            ->setParameter(':business', $business)
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function setActive($active)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->update('s_benchmark_config', 'config')
            ->set('config.active', ':active')
            ->setParameter(':active', $active)
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function acceptTerms()
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->update('s_benchmark_config')
            ->set('terms_accepted', 1)
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        return $queryBuilder->select('config.cached_template')
            ->from('s_benchmark_config', 'config')
            ->execute()
            ->fetchColumn();
    }
}
