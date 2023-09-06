<?php
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

namespace Shopware\Bundle\SearchBundleDBAL\ConditionHandler;

use Shopware\Bundle\SearchBundle\Condition\SalesCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class SalesConditionHandler implements ConditionHandlerInterface
{
    public const STATE_INCLUDES_TOPSELLER_TABLE = 'topseller';

    /**
     * {@inheritdoc}
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return $condition instanceof SalesCondition;
    }

    /**
     * {@inheritdoc}
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        $this->addCondition($condition, $query);
    }

    private function addCondition(SalesCondition $condition, QueryBuilder $query): void
    {
        if (!$query->hasState(self::STATE_INCLUDES_TOPSELLER_TABLE)) {
            $query->leftJoin(
                'product',
                's_articles_top_seller_ro',
                'topSeller',
                'topSeller.article_id = product.id'
            );
            $query->addState(self::STATE_INCLUDES_TOPSELLER_TABLE);
        }

        $key = ':sales' . md5(json_encode($condition, JSON_THROW_ON_ERROR));
        $query->andWhere('topSeller.sales >= ' . $key);

        $query->setParameter($key, $condition->getMinSales());
    }
}
