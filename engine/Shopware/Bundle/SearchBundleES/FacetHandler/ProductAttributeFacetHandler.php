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

use ONGR\ElasticsearchDSL\Aggregation\FilterAggregation;
use ONGR\ElasticsearchDSL\Aggregation\TermsAggregation;
use ONGR\ElasticsearchDSL\Aggregation\ValueCountAggregation;
use ONGR\ElasticsearchDSL\Query\ExistsQuery;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\SearchBundle\Condition\ProductAttributeCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\ProductAttributeFacet;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\RadioFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\RangeFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListItem;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundleES\ResultHydratorInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductAttributeFacetHandler implements HandlerInterface, ResultHydratorInterface
{
    const AGGREGATION_SIZE = 5000;

    /**
     * @var ProductAttributeFacet[]
     */
    private $criteriaParts = [];

    /**
     * {@inheritdoc}
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return ($criteriaPart instanceof ProductAttributeFacet);
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
        /** @var ProductAttributeFacet $criteriaPart */
        $field = 'attributes.core.' . $criteriaPart->getField();
        $this->criteriaParts[] = $criteriaPart;

        switch ($criteriaPart->getMode()) {
            case (ProductAttributeFacet::MODE_VALUE_LIST_RESULT):
            case (ProductAttributeFacet::MODE_RADIO_LIST_RESULT):
                $aggregation = new TermsAggregation($criteriaPart->getName());
                $aggregation->setField($field);
                $aggregation->addParameter('size', self::AGGREGATION_SIZE);
                break;

            case (ProductAttributeFacet::MODE_BOOLEAN_RESULT):
                $count = new ValueCountAggregation($criteriaPart->getName() . '_count');
                $count->setField($field);

                $aggregation = new FilterAggregation($criteriaPart->getName());
                $aggregation->setFilter(new ExistsQuery($field));
                $aggregation->addAggregation($count);
                break;

            case (ProductAttributeFacet::MODE_RANGE_RESULT):
                $aggregation = new TermsAggregation($criteriaPart->getName());
                $aggregation->setField($field);
                break;

            default:
                return;
        }
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
        $aggregations = $elasticResult['aggregations'];

        foreach ($this->criteriaParts as $criteriaPart) {
            $key = $criteriaPart->getName();

            if (!isset($aggregations[$key])) {
                continue;
            }

            switch ($criteriaPart->getMode()) {
                case (ProductAttributeFacet::MODE_VALUE_LIST_RESULT):
                case (ProductAttributeFacet::MODE_RADIO_LIST_RESULT):
                    $criteriaPartResult = $this->createItemListResult($criteriaPart, $aggregations[$key], $criteria);
                    break;
                case (ProductAttributeFacet::MODE_BOOLEAN_RESULT):
                    $criteriaPartResult = $this->createBooleanResult($criteriaPart, $aggregations[$key], $criteria);
                    break;
                case (ProductAttributeFacet::MODE_RANGE_RESULT):
                    $criteriaPartResult = $this->createRangeResult($criteriaPart, $aggregations[$key], $criteria);
                    break;
                default:
                    $criteriaPartResult = null;
            }

            if ($criteriaPartResult) {
                $result->addFacet($criteriaPartResult);
            }
        }
    }

    /**
     * @param ProductAttributeFacet $criteriaPart
     * @param $data
     * @param Criteria $criteria
     * @return null|RadioFacetResult|ValueListFacetResult
     */
    private function createItemListResult(
        ProductAttributeFacet $criteriaPart,
        $data,
        Criteria $criteria
    ) {
        $values = array_column($data['buckets'], 'key');
        if (empty($values)) {
            return null;
        }

        $actives = [];

        /**@var $condition ProductAttributeCondition*/
        if ($condition = $criteria->getCondition($criteriaPart->getName())) {
            $actives = $condition->getValue();
        }

        $items = array_map(function ($row) use ($actives) {
            return new ValueListItem($row, $row, in_array($row, $actives));
        }, $values);

        if ($criteriaPart->getMode() == ProductAttributeFacet::MODE_RADIO_LIST_RESULT) {
            $template = $criteriaPart->getTemplate();
            if (!$template) {
                $template = 'frontend/listing/filter/facet-radio.tpl';
            }

            return new RadioFacetResult(
                $criteriaPart->getName(),
                $criteria->hasCondition($criteriaPart->getName()),
                $criteriaPart->getLabel(),
                $items,
                $criteriaPart->getFormFieldName(),
                [],
                $template
            );
        } else {
            $template = $criteriaPart->getTemplate();
            if (!$template) {
                $template = 'frontend/listing/filter/facet-value-list.tpl';
            }

            return new ValueListFacetResult(
                $criteriaPart->getName(),
                $criteria->hasCondition($criteriaPart->getName()),
                $criteriaPart->getLabel(),
                $items,
                $criteriaPart->getFormFieldName(),
                [],
                $template
            );
        }
    }

    /**
     * @param ProductAttributeFacet $criteriaPart
     * @param $data
     * @param Criteria $criteria
     * @return null|BooleanFacetResult
     */
    private function createBooleanResult(ProductAttributeFacet $criteriaPart, $data, Criteria $criteria)
    {
        $count = $data[$criteriaPart->getName() . '_count'];
        $count = $count['value'];

        if ($count <= 0) {
            return null;
        }

        $template = $criteriaPart->getTemplate();
        if (!$template) {
            $template = 'frontend/listing/filter/facet-boolean.tpl';
        }

        return new BooleanFacetResult(
            $criteriaPart->getName(),
            $criteriaPart->getFormFieldName(),
            $criteria->hasCondition($criteriaPart->getName()),
            $criteriaPart->getLabel(),
            [],
            $template
        );
    }

    /**
     * @param ProductAttributeFacet $criteriaPart
     * @param $data
     * @param Criteria $criteria
     * @return RangeFacetResult
     */
    private function createRangeResult(ProductAttributeFacet $criteriaPart, $data, Criteria $criteria)
    {
        $values = array_column($data['buckets'], 'key');
        $min = min($values);
        $max = max($values);

        $template = $criteriaPart->getTemplate();
        if (!$template) {
            $template = 'frontend/listing/filter/facet-range.tpl';
        }

        $activeMin = $min;
        $activeMax = $max;

        /**@var $condition ProductAttributeCondition*/
        if ($condition = $criteria->getCondition($criteriaPart->getName())) {
            $data = $condition->getValue();
            $activeMin = $data['min'];
            $activeMax = $data['max'];
        }

        return new RangeFacetResult(
            $criteriaPart->getName(),
            $criteria->hasCondition($criteriaPart->getName()),
            $criteriaPart->getLabel(),
            $min,
            $max,
            $activeMin,
            $activeMax,
            'min' . $criteriaPart->getFormFieldName(),
            'max' . $criteriaPart->getFormFieldName(),
            [],
            $template
        );
    }
}
