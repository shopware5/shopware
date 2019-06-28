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

namespace Shopware\Bundle\SearchBundleES\FacetHandler;

use ONGR\ElasticsearchDSL\Aggregation\Bucketing\FilterAggregation;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\Facet\CombinedConditionFacet;
use Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundleES\CombinedConditionQueryBuilder;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\SearchBundleES\ResultHydratorInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class CombinedConditionFacetHandler implements HandlerInterface, ResultHydratorInterface
{
    /**
     * @var CombinedConditionQueryBuilder
     */
    private $combinedConditionQueryBuilder;

    public function __construct(CombinedConditionQueryBuilder $combinedConditionQueryBuilder)
    {
        $this->combinedConditionQueryBuilder = $combinedConditionQueryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return $criteriaPart instanceof CombinedConditionFacet;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        /** @var CombinedConditionFacet $criteriaPart */
        $query = $this->combinedConditionQueryBuilder->build(
            $criteriaPart->getConditions(),
            $criteria,
            $context
        );

        $filter = new FilterAggregation($criteriaPart->getName());
        $filter->setFilter($query);

        $search->addAggregation($filter);
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(
        array $elasticResult,
        ProductNumberSearchResult $result,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        if (!isset($elasticResult['aggregations'])) {
            return;
        }

        foreach ($elasticResult['aggregations'] as $key => $aggregation) {
            if (strpos($key, 'combined_facet_') === false) {
                continue;
            }

            if ($aggregation['doc_count'] <= 0) {
                continue;
            }

            if (!$criteria->hasFacet($key)) {
                continue;
            }

            /** @var CombinedConditionFacet $facet */
            $facet = $criteria->getFacet($key);

            $result->addFacet(
                new BooleanFacetResult(
                    $facet->getName(),
                    $facet->getRequestParameter(),
                    $criteria->hasCondition($facet->getName()),
                    $facet->getLabel()
                )
            );
        }
    }
}
