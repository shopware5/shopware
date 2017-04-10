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

use ONGR\ElasticsearchDSL\Aggregation\StatsAggregation;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\ESIndexingBundle\FieldMappingInterface;
use Shopware\Bundle\SearchBundle\Condition\PriceCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\Facet\PriceFacet;
use Shopware\Bundle\SearchBundle\FacetResult\RangeFacetResult;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\SearchBundleES\ResultHydratorInterface;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;

class PriceFacetHandler implements HandlerInterface, ResultHydratorInterface
{
    /**
     * @var \Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var QueryAliasMapper
     */
    private $queryAliasMapper;

    /**
     * @var FieldMappingInterface
     */
    private $fieldMapping;

    /**
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     * @param QueryAliasMapper                     $queryAliasMapper
     * @param FieldMappingInterface                $fieldMapping
     */
    public function __construct(
        \Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper,
        FieldMappingInterface $fieldMapping
    ) {
        $this->snippetManager = $snippetManager;
        $this->queryAliasMapper = $queryAliasMapper;
        $this->fieldMapping = $fieldMapping;
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
        $field = $this->fieldMapping->getPriceField($context);
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
            (float) $data['min'],
            (float) $data['max']
        );
        $result->addFacet($criteriaPart);
    }

    /**
     * @param Criteria $criteria
     * @param float    $min
     * @param float    $max
     *
     * @return RangeFacetResult
     */
    private function createFacet(Criteria $criteria, $min, $max)
    {
        $activeMin = $min;
        $activeMax = $max;

        /** @var $condition PriceCondition */
        if ($condition = $criteria->getCondition('price')) {
            $activeMin = $condition->getMinPrice();
            $activeMax = $condition->getMaxPrice();
        }

        if (!$minFieldName = $this->queryAliasMapper->getShortAlias('priceMin')) {
            $minFieldName = 'priceMin';
        }
        if (!$maxFieldName = $this->queryAliasMapper->getShortAlias('priceMax')) {
            $maxFieldName = 'priceMax';
        }

        /** @var PriceFacet $facet */
        $facet = $criteria->getFacet('price');
        if ($facet && !empty($facet->getLabel())) {
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
            (float) $min,
            (float) $max,
            (float) $activeMin,
            (float) $activeMax,
            $minFieldName,
            $maxFieldName,
            [],
            'frontend/listing/filter/facet-currency-range.tpl'
        );
    }
}
