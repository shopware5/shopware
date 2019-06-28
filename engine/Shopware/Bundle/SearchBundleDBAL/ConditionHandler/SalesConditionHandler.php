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

namespace Shopware\Bundle\SearchBundleDBAL\ConditionHandler;

use Shopware\Bundle\SearchBundle\Condition\SalesCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class SalesConditionHandler implements ConditionHandlerInterface
{
    const STATE_INCLUDES_TOPSELLER_TABLE = 'topseller';

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
        QueryBuilder  $query,
        ShopContextInterface $context
    ) {
        if (!$query->hasState(self::STATE_INCLUDES_TOPSELLER_TABLE)) {
            $query->leftJoin(
                'product',
                's_articles_top_seller_ro',
                'topSeller',
                'topSeller.article_id = product.id'
            );
            $query->addState(self::STATE_INCLUDES_TOPSELLER_TABLE);
        }

        $key = ':sales' . md5(json_encode($condition));
        $query->andWhere('topSeller.sales > ' . $key);

        /* @var SalesCondition $condition */
        $query->setParameter($key, $condition->getMinSales());
    }
}
