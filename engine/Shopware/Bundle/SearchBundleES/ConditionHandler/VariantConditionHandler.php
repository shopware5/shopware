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
    /**
     * Validates if the criteria part can be handled by this handler
     *
     * @return bool
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return $criteriaPart instanceof VariantCondition;
    }

    /**
     * Handles the criteria part and adds the provided condition as post filter.
     */
    public function handleFilter(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        $this->handle($criteriaPart, $criteria, $search);
    }

    /**
     * Handles the criteria part and extends the provided search.
     */
    public function handlePostFilter(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        $this->handle($criteriaPart, $criteria, $search);
    }

    private function handle(CriteriaPartInterface $criteriaPart, Criteria $criteria, Search $search)
    {
        $groupBy = $this->buildGroupBy($criteria);

        if ($groupBy) {
            $search->addPostFilter(new TermQuery($groupBy, true));

            /* @var VariantCondition $criteriaPart */
            $search->addPostFilter(
                new TermsQuery(
                    'filterConfiguration.options.id',
                    $criteriaPart->getOptionIds()
                )
            );

            return;
        }

        $search->addPostFilter(new TermQuery('isMainVariant', true));

        /* @var VariantCondition $criteriaPart */
        $search->addPostFilter(
            new TermsQuery(
                'filterConfiguration.options.id',
                $criteriaPart->getOptionIds()
            )
        );
    }

    private function buildGroupBy(Criteria $criteria)
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
