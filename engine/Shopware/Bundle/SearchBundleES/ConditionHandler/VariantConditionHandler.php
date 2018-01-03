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

use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MatchQuery;
use ONGR\ElasticsearchDSL\Query\Joining\NestedQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundleDBAL\VariantHelperInterface;
use Shopware\Bundle\SearchBundleES\PartialConditionHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class VariantConditionHandler implements PartialConditionHandlerInterface
{
    private $variantHelper;

    public function __construct(VariantHelperInterface $variantHelper)
    {
        $this->variantHelper = $variantHelper;
    }

    /**
     * Validates if the criteria part can be handled by this handler
     *
     * @param CriteriaPartInterface $criteriaPart
     *
     * @return bool
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return $criteriaPart instanceof VariantCondition;
    }

    /**
     * Handles the criteria part and adds the provided condition as post filter.
     *
     * @param CriteriaPartInterface $criteriaPart
     * @param Criteria              $criteria
     * @param Search                $search
     * @param ShopContextInterface  $context
     */
    public function handleFilter(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        /**
         * @var VariantCondition
         */
        $boolQuery = new BoolQuery();
        $boolQuery->add(new MatchQuery('groupByGroups.key', $criteria->getAttribute('swagVariantFilter')->get('groupKey')));
        $boolQuery->add(new MatchQuery('groupByGroups.shouldDisplay', true));

        $nested = new NestedQuery('groupByGroups', $boolQuery);
        $search->addFilter($nested);

        $search->addFilter(
            new TermsQuery(
                'configuration.options.id',
                $criteriaPart->getOptionIds()
            )
        );
    }

    /**
     * Handles the criteria part and extends the provided search.
     *
     * @param CriteriaPartInterface $criteriaPart
     * @param Criteria              $criteria
     * @param Search                $search
     * @param ShopContextInterface  $context
     */
    public function handlePostFilter(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        /**
         * @var VariantCondition
         */
        $boolQuery = new BoolQuery();
        $boolQuery->add(new MatchQuery('groupByGroups.key', $criteria->getAttribute('swagVariantFilter')->get('groupKey')));
        $boolQuery->add(new MatchQuery('groupByGroups.shouldDisplay', true));

        $nested = new NestedQuery('groupByGroups', $boolQuery);
        $search->addPostFilter($nested);

        $search->addPostFilter(
            new TermsQuery(
                'configuration.options.id',
                $criteriaPart->getOptionIds()
            )
        );
    }
}
