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

use Shopware\Bundle\SearchBundle\Condition\SearchTermCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\SearchBundleDBAL\SearchTermQueryBuilderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class SearchTermConditionHandler implements ConditionHandlerInterface
{
    public const STATE_INCLUDES_RANKING = 'ranking';

    private SearchTermQueryBuilderInterface $searchTermQueryBuilder;

    public function __construct(SearchTermQueryBuilderInterface $searchTermQueryBuilder)
    {
        $this->searchTermQueryBuilder = $searchTermQueryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return $condition instanceof SearchTermCondition;
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

    private function addCondition(SearchTermCondition $condition, QueryBuilder $query): void
    {
        $searchQuery = $this->searchTermQueryBuilder->buildQuery($condition->getTerm());

        // no matching products found by the search query builder.
        // add condition that the result contains no product.
        if ($searchQuery === null) {
            $query->andWhere('0 = 1');

            return;
        }

        $queryString = $searchQuery->getSQL();

        $query->addSelect('searchTable.*');
        $query->addState(self::STATE_INCLUDES_RANKING);

        $query->innerJoin(
            'product',
            '(' . $queryString . ')',
            'searchTable',
            'searchTable.product_id = product.id'
        );
    }
}
