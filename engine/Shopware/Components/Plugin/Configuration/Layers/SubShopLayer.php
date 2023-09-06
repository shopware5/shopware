<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components\Plugin\Configuration\Layers;

use Doctrine\DBAL\Query\QueryBuilder;

class SubShopLayer extends AbstractShopConfigurationLayer
{
    public function readValues(string $pluginName, ?int $shopId): array
    {
        if ($shopId === null) {
            return $this->getParent()->readValues($pluginName, $shopId);
        }

        return parent::readValues($pluginName, $shopId);
    }

    protected function configureQuery(QueryBuilder $builder, ?int $shopId, string $pluginName): QueryBuilder
    {
        return $builder
            ->innerJoin(
                'coreConfigValues',
                's_core_shops',
                'coreShops',
                'coreConfigValues.shop_id = coreShops.main_id'
            )
            ->andWhere($builder->expr()->eq('corePlugins.name', $builder->createNamedParameter($pluginName)))
            ->andWhere($builder->expr()->eq('coreShops.id', $builder->createNamedParameter($shopId)))
        ;
    }

    protected function isLayerResponsible(?int $shopId): bool
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        return $queryBuilder->select('1')
                ->from('s_core_shops')
                ->andWhere($queryBuilder->expr()->eq('id', ':id'))
                ->andWhere($queryBuilder->expr()->isNull('main_id'))
                ->andWhere($queryBuilder->expr()->lt('`default`', 1))
                ->setParameter('id', $shopId)
                ->execute()->fetchColumn() !== false
        ;
    }
}
