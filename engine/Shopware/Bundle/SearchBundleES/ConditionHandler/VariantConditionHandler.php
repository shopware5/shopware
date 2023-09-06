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

namespace Shopware\Bundle\SearchBundleES\ConditionHandler;

use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundleES\PartialConditionHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class VariantConditionHandler implements PartialConditionHandlerInterface
{
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return $criteriaPart instanceof VariantCondition;
    }

    public function handleFilter(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        $this->handle($criteriaPart, $criteria, $search);
    }

    public function handlePostFilter(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        $this->handle($criteriaPart, $criteria, $search);
    }

    private function handle(VariantCondition $criteriaPart, Criteria $criteria, Search $search): void
    {
        $groupBy = $this->buildGroupBy($criteria);

        if ($groupBy) {
            $search->addPostFilter(new TermQuery($groupBy, true));

            $search->addPostFilter(
                new TermsQuery(
                    'filterConfiguration.options.id',
                    $criteriaPart->getOptionIds()
                )
            );

            return;
        }

        $search->addPostFilter(new TermQuery('isMainVariant', true));

        $search->addPostFilter(
            new TermsQuery(
                'filterConfiguration.options.id',
                $criteriaPart->getOptionIds()
            )
        );
    }

    private function buildGroupBy(Criteria $criteria): ?string
    {
        $conditions = $criteria->getConditionsByClass(VariantCondition::class);

        $conditions = array_filter($conditions, function (VariantCondition $condition) {
            return $condition->expandVariants();
        });

        $groups = array_map(function (VariantCondition $condition) {
            return $condition->getGroupId();
        }, $conditions);

        if (empty($conditions)) {
            return null;
        }

        sort($groups, SORT_NUMERIC);

        return 'visibility.g' . implode('-', $groups);
    }
}
