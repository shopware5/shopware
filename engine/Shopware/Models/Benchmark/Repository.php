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

namespace Shopware\Models\Benchmark;

use Doctrine\ORM\EntityRepository;

/**
 * Repository
 */
class Repository extends EntityRepository
{
    /**
     * @param int $shopId
     *
     * @return BenchmarkConfig
     */
    public function getConfigForShop($shopId)
    {
        return $this->findOneBy(['shopId' => $shopId]);
    }

    /**
     * @param array $newConfigs
     */
    public function saveShopConfigs(array $newConfigs)
    {
        $savedConfigs = $this->getShopConfigs();

        $allShopIds = $this->getAllViableShopIds();

        foreach ($allShopIds as $shopId => $shopName) {
            // Config for this shop is already in database, skip
            if (array_key_exists($shopId, $savedConfigs)) {
                continue;
            }

            $uuid = \Ramsey\Uuid\Uuid::uuid4();
            $configModel = new BenchmarkConfig($uuid->getBytes());
            $configModel->setShopId($shopId);

            $configModel->setIndustry(0);
            $configModel->setType('b2c');
            $configModel->setActive(0);

            // Config for this shop was sent from on-boarding
            if (array_key_exists($shopId, $newConfigs)) {
                $config = $newConfigs[$shopId];

                // Config is also complete, both type and industry were set
                if ($config['industry'] && $config['type']) {
                    $configModel->setIndustry($config['industry']);
                    $configModel->setType($config['type']);
                    $configModel->setActive(1);
                }
            }

            $this->getEntityManager()->persist($configModel);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @param BenchmarkConfig $config
     */
    public function save(BenchmarkConfig $config)
    {
        $em = $this->getEntityManager();

        $em->merge($config);
        $em->persist($config);
        $em->flush($config);
    }

    /**
     * @return array
     */
    public function getShopsWithValidTemplate()
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        return $queryBuilder->select([
                'configs.shop_id as arrayKey',
                'configs.shop_id as shopId',
                'shops.name as shopName',
            ])
            ->from('s_benchmark_config', 'configs')
            ->innerJoin('configs', 's_core_shops', 'shops', 'shops.id = configs.shop_id')
            ->where('configs.last_received > NOW() - INTERVAL 7 DAY')
            ->andWhere('configs.cached_template IS NOT NULL')
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
    }

    /**
     * @param bool $addShopName
     *
     * @return array
     */
    public function getShopConfigs($addShopName = false)
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $queryBuilder->select([
                'configs.shop_id as arrayKey',
                'configs.shop_id as shopId',
                'configs.active',
                'configs.last_sent as lastSent',
                'configs.last_received as lastReceived',
                'configs.last_order_id as lastOrderId',
                'configs.last_customer_id as lastCustomerId',
                'configs.last_product_id as lastProductId',
                'configs.batch_size as batchSize',
                'configs.industry',
                'configs.type',
                'configs.response_token as responseToken',
            ])
            ->from('s_benchmark_config', 'configs');

        if ($addShopName) {
            $queryBuilder->addSelect('shops.name as shopName')
                ->innerJoin('configs', 's_core_shops', 'shops', 'shops.id = configs.shop_id')
                ->orderBy('configs.shop_id', 'ASC');
        }

        return $queryBuilder->execute()->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
    }

    /**
     * @return int
     */
    public function getConfigsCount()
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        return (int) $queryBuilder->select('COUNT(configs.id)')
            ->from('s_benchmark_config', 'configs')
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return BenchmarkConfig|null
     */
    public function getNextTransmissionShopConfig()
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $yesterday = new \DateTime('now', new \DateTimeZone('UTC'));
        $yesterday = $yesterday->modify('-1 day');

        $lastHour = new \DateTime('now', new \DateTimeZone('UTC'));
        $lastHour = $lastHour->modify('-1 hour');

        return $queryBuilder->select('configs')
            ->from(BenchmarkConfig::class, 'configs')
            ->where('configs.lastSent < :yesterday')
            ->andWhere('configs.active = 1')
            ->andWhere('configs.industry != 0')
            ->andWhere('configs.locked IS NULL OR configs.locked < :lastHour')
            ->setParameter(':yesterday', $yesterday->format('Y-m-d H:i:s'))
            ->setParameter(':lastHour', $lastHour->format('Y-m-d H:i:s'))
            ->orderBy('configs.shopId', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return BenchmarkConfig|null
     */
    public function getNextReceivingShopConfig()
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $now = $now->modify('-1 day');

        return $queryBuilder->select('configs')
            ->from(BenchmarkConfig::class, 'configs')
            ->where('configs.lastReceived < :dateNow')
            ->andWhere('configs.lastSent > configs.lastReceived')
            ->andWhere('configs.active = 1')
            ->andWhere('configs.industry != 0')
            ->andWhere('configs.token IS NOT NULL')
            ->setParameter(':dateNow', $now->format('Y-m-d H:i:s'))
            ->orderBy('configs.shopId')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $shopId
     */
    public function lockShop($shopId)
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $queryBuilder->update('s_benchmark_config')
            ->set('locked', ':now')
            ->where('shop_id = :shopId')
            ->setParameter(':shopId', $shopId)
            ->setParameter(':now', $now->format('Y-m-d H:i:s'))
            ->execute();
    }

    /**
     * @param int $shopId
     */
    public function unlockShop($shopId)
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $queryBuilder->update('s_benchmark_config')
            ->set('locked', 'NULL')
            ->where('shop_id = :shopId')
            ->setParameter(':shopId', $shopId)
            ->execute();
    }

    /**
     * @return array
     */
    private function getAllViableShopIds()
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        return $queryBuilder->select('shops.id, shops.name')
            ->from('s_core_shops', 'shops')
            ->where('shops.main_id IS NULL')
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
}
