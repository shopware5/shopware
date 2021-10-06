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

namespace Shopware\Components\Plugin\Configuration\Layers;

use Doctrine\DBAL\Query\QueryBuilder;

class DefaultShopLayer extends AbstractShopConfigurationLayer
{
    public function readValues(string $pluginName, ?int $shopId): array
    {
        return parent::readValues($pluginName, 1);
    }

    protected function configureQuery(QueryBuilder $builder, ?int $shopId, string $pluginName): QueryBuilder
    {
        $shopIdKey = 'shopId' . crc32((string) $shopId);
        $pluginNameKey = 'pluginName' . crc32($pluginName);

        return $builder
            ->andWhere($builder->expr()->eq('corePlugins.name', ':' . $pluginNameKey))
            ->andWhere($builder->expr()->eq('coreConfigValues.shop_id', ':' . $shopIdKey))
            ->setParameter($pluginNameKey, $pluginName)
            ->setParameter($shopIdKey, $shopId)
        ;
    }

    protected function isLayerResponsible(?int $shopId): bool
    {
        if ($shopId === 1 || $shopId === null) {
            return true;
        }

        $queryBuilder = $this->connection->createQueryBuilder();

        return $queryBuilder->select('1')
                ->from('s_core_shops')
                ->andWhere($queryBuilder->expr()->eq('id', ':id'))
                ->andWhere($queryBuilder->expr()->gt('`default`', 0))
                ->setParameter('id', $shopId)
                ->execute()->fetchColumn() !== false
            ;
    }
}
