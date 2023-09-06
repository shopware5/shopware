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

namespace Shopware\Bundle\SearchBundleES\FacetHandler;

use ONGR\ElasticsearchDSL\Aggregation\Metric\StatsAggregation;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\Facet\HeightFacet;
use Shopware\Bundle\SearchBundle\Facet\LengthFacet;
use Shopware\Bundle\SearchBundle\Facet\WeightFacet;
use Shopware\Bundle\SearchBundle\Facet\WidthFacet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\FacetResult\RangeFacetResult;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\SearchBundleES\ResultHydratorInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductDimensionsFacetHandler implements HandlerInterface, ResultHydratorInterface
{
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return $criteriaPart instanceof WeightFacet
            || $criteriaPart instanceof WidthFacet
            || $criteriaPart instanceof LengthFacet
            || $criteriaPart instanceof HeightFacet
        ;
    }

    public function handle(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        $search->addAggregation(
            new StatsAggregation($criteriaPart->getName(), $criteriaPart->getName())
        );
    }

    public function hydrate(
        array $elasticResult,
        ProductNumberSearchResult $result,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        if (!isset($elasticResult['aggregations'])) {
            return;
        }

        foreach ($criteria->getFacets() as $criteriaFacet) {
            if (!$criteriaFacet instanceof WeightFacet
                && !$criteriaFacet instanceof WidthFacet
                && !$criteriaFacet instanceof LengthFacet
                && !$criteriaFacet instanceof HeightFacet
            ) {
                continue;
            }

            if (!\array_key_exists($criteriaFacet->getName(), $elasticResult['aggregations'])) {
                continue;
            }

            $data = $elasticResult['aggregations'][$criteriaFacet->getName()];

            $facetResult = $this->createRangeFacet($criteriaFacet, $data, $criteria);
            if (!$facetResult) {
                continue;
            }

            $result->addFacet($facetResult);
        }
    }

    /**
     * @param WeightFacet|WidthFacet|LengthFacet|HeightFacet $facet
     * @param array<string, string>                          $stats
     */
    private function createRangeFacet(FacetInterface $facet, array $stats, Criteria $criteria): ?RangeFacetResult
    {
        $name = $facet->getName();

        $minField = 'min' . ucfirst($name);
        $maxField = 'max' . ucfirst($name);

        $min = (float) $stats['min'];
        $max = (float) $stats['max'];

        $activeMin = $min;
        $activeMax = $max;

        if ($condition = $criteria->getCondition($name)) {
            $method = 'get' . ucfirst($minField);
            $activeMin = $condition->$method();

            $method = 'get' . ucfirst($maxField);
            $activeMax = $condition->$method();
        }

        if ($min === $max) {
            return null;
        }

        $label = $facet->getLabel() ?? '';

        return new RangeFacetResult(
            $name,
            $criteria->hasCondition($name),
            $label,
            $min,
            $max,
            $activeMin,
            $activeMax,
            $minField,
            $maxField,
            [],
            $facet->getSuffix(),
            3,
            'frontend/listing/filter/facet-range.tpl'
        );
    }
}
