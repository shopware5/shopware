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
use Shopware\Bundle\SearchBundle\Condition\PriceCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\Facet\PriceFacet;
use Shopware\Bundle\SearchBundle\FacetResult\RangeFacetResult;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\SearchBundleES\PriceFieldMapper;
use Shopware\Bundle\SearchBundleES\ResultHydratorInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;
use Shopware_Components_Snippet_Manager;

class PriceFacetHandler implements HandlerInterface, ResultHydratorInterface
{
    private Shopware_Components_Snippet_Manager $snippetManager;

    private QueryAliasMapper $queryAliasMapper;

    private PriceFieldMapper $priceFieldMapper;

    public function __construct(
        Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper,
        PriceFieldMapper $priceFieldMapper
    ) {
        $this->snippetManager = $snippetManager;
        $this->queryAliasMapper = $queryAliasMapper;
        $this->priceFieldMapper = $priceFieldMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return $criteriaPart instanceof PriceFacet;
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
        $aggregation = new StatsAggregation('price');
        $field = $this->priceFieldMapper->getPriceField($criteria, $context);
        $aggregation->setField($field);
        $search->addAggregation($aggregation);
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
        if (!isset($elasticResult['aggregations']['price'])) {
            return;
        }
        $data = $elasticResult['aggregations']['price'];

        if ($data['count'] <= 0) {
            return;
        }
        if ($data['min'] == $data['max']) {
            return;
        }

        $criteriaPart = $this->createFacet(
            $criteria,
            round((float) $data['min'], 2),
            round((float) $data['max'], 2)
        );
        $result->addFacet($criteriaPart);
    }

    private function createFacet(Criteria $criteria, float $min, float $max): RangeFacetResult
    {
        $activeMin = $min;
        $activeMax = $max;

        $condition = $criteria->getCondition('price');
        if ($condition instanceof PriceCondition) {
            $activeMin = $condition->getMinPrice();
            $activeMax = $condition->getMaxPrice();
        }
        $minFieldName = $this->queryAliasMapper->getShortAlias('priceMin') ?? 'priceMin';
        $maxFieldName = $this->queryAliasMapper->getShortAlias('priceMax') ?? 'priceMax';

        $facet = $criteria->getFacet('price');
        if ($facet instanceof PriceFacet && !empty($facet->getLabel())) {
            $label = $facet->getLabel();
        } else {
            $label = $this->snippetManager
                ->getNamespace('frontend/listing/facet_labels')
                ->get('price', 'Price');
        }

        return new RangeFacetResult(
            'price',
            $criteria->hasCondition('price'),
            $label,
            $min,
            $max,
            $activeMin,
            $activeMax,
            $minFieldName,
            $maxFieldName,
            [],
            null,
            2,
            'frontend/listing/filter/facet-currency-range.tpl'
        );
    }
}
