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

namespace Shopware\Models\Snippet;

use Shopware\Components\Model\ModelRepository;

/**
 * Doctrine Repository class for Snippet model
 */
class SnippetRepository extends ModelRepository
{
    public function getDistinctNamespacesQuery($locales = null, $shopIds = null, $limit = null, $offset = null)
    {
        $builder = $this->getDistinctNamespacesQueryBuilder($locales, $shopIds, $limit, $offset);

        return $builder->getQuery();
    }

    public function getDistinctNamespacesQueryBuilder($locales = null, $shopIds = null, $limit = null, $offset = null)
    {
        $builder = $this->createQueryBuilder('snippet')
            ->select([
                'DISTINCT snippet.namespace as namespace',
            ]);
        if ($locales) {
            $builder
                ->andWhere('snippet.localeId IN (:locales)')->setParameter('locales', $locales);
        }
        if ($shopIds) {
            $builder
                ->andWhere('snippet.shopId = (:shopIds)')->setParameter('shopIds', $shopIds);
        }

        $builder->setFirstResult($offset)
            ->setMaxResults($limit);

        return $builder;
    }

    public function getDistinctShopsQueryQuery($localeId)
    {
        $builder = $this->getDistinctShopsQueryBuilder($localeId);

        return $builder->getQuery();
    }

    public function getDistinctShopsQueryBuilder($localeId)
    {
        $builder = $this->createQueryBuilder('snippet')
            ->select([
                'DISTINCT snippet.shopId as shopId',
            ])->where('snippet.localeId = :localeId')->setParameter('localeId', $localeId);

        return $builder;
    }

    public function getDistinctLocalesQuery()
    {
        $builder = $this->getDistinctLocalesQueryBuilder();

        return $builder->getQuery();
    }

    public function getDistinctLocalesQueryBuilder()
    {
        $builder = $this->createQueryBuilder('snippet')
            ->select([
                'DISTINCT snippet.localeId as localeId',
            ]);

        return $builder;
    }
}
