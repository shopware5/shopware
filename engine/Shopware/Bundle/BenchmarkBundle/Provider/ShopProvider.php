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
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ShopProvider implements BenchmarkProviderInterface
{
    private const NAME = 'shop';

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
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $this->shopId = $shopContext->getShop()->getId();

        return [
            'id' => $this->getUniqueShopHash(),
            'industry' => $this->getIndustry(),
            'type' => $this->getType(),
            'datetime' => $now->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return string
     */
    private function getUniqueShopHash()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        $shopHash = \Ramsey\Uuid\Uuid::fromBytes(
            (string) $queryBuilder->select('config.id')
            ->from('s_benchmark_config', 'config')
            ->where('config.shop_id = :shopId')
            ->setParameter(':shopId', $this->shopId)
            ->execute()
            ->fetchColumn()
        );

        return $shopHash->toString();
    }

    /**
     * @return string
     */
    private function getIndustry()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return (string) $queryBuilder->select('industry')
            ->from('s_benchmark_config', 'config')
            ->where('config.shop_id = :shopId')
            ->setParameter(':shopId', $this->shopId)
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return string
     */
    private function getType()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        $type = (string) $queryBuilder->select('type')
            ->from('s_benchmark_config', 'config')
            ->where('config.shop_id = :shopId')
            ->setParameter(':shopId', $this->shopId)
            ->execute()
            ->fetchColumn();

        if (!in_array($type, ['b2b', 'b2c'])) {
            $type = 'b2c';
        }

        return $type;
    }
}
