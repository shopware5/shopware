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
use ONGR\ElasticsearchDSL\Aggregation\Metric\ValueCountAggregation;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\Facet\ShippingFreeFacet;
use Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\SearchBundleES\ResultHydratorInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;
use Shopware_Components_Snippet_Manager;

class ShippingFreeFacetHandler implements HandlerInterface, ResultHydratorInterface
{
    /**
     * @var Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var QueryAliasMapper
     */
    private $queryAliasMapper;

    public function __construct(
        Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper
    ) {
        $this->snippetManager = $snippetManager;
        $this->queryAliasMapper = $queryAliasMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return $criteriaPart instanceof ShippingFreeFacet;
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
        $aggregation = new ValueCountAggregation('shipping_free_count');
        $aggregation->setField('shippingFree');

        $filterAgg = new FilterAggregation('shipping_free_filter');
        $filterAgg->setFilter(new TermQuery('shippingFree', true));
        $filterAgg->addAggregation($aggregation);

        $search->addAggregation($filterAgg);
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
        if (!isset($elasticResult['aggregations']['shipping_free_filter'])) {
            return;
        }

        $data = $elasticResult['aggregations']['shipping_free_filter']['shipping_free_count'];

        if ($data['value'] <= 0) {
            return;
        }

        $criteriaPart = $this->createFacet($criteria);

        $result->addFacet($criteriaPart);
    }

    /**
     * @return BooleanFacetResult
     */
    private function createFacet(Criteria $criteria)
    {
        if (!$fieldName = $this->queryAliasMapper->getShortAlias('shippingFree')) {
            $fieldName = 'shippingFree';
        }

        /** @var ShippingFreeFacet|null $facet */
        $facet = $criteria->getFacet('shipping_free');
        if ($facet && !empty($facet->getLabel())) {
            $label = $facet->getLabel();
        } else {
            $label = $this->snippetManager
                ->getNamespace('frontend/listing/facet_labels')
                ->get('shipping_free', 'Shipping free');
        }

        $criteriaPart = new BooleanFacetResult(
            'shipping_free',
            $fieldName,
            $criteria->hasCondition('shipping_free'),
            $label
        );

        return $criteriaPart;
    }
}
