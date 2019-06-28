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
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\ValueCountAggregation;
use ONGR\ElasticsearchDSL\Query\TermLevel\ExistsQuery;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\AttributeBundle\Service\ConfigurationStruct;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Bundle\SearchBundle\Condition\ProductAttributeCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\Facet\ProductAttributeFacet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\RadioFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\RangeFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListItem;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundle\TemplateSwitchable;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\SearchBundleES\ResultHydratorInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductAttributeFacetHandler implements HandlerInterface, ResultHydratorInterface
{
    const AGGREGATION_SIZE = 5000;

    /**
     * @var FacetInterface[]|CriteriaPartInterface[]
     */
    private $criteriaParts = [];

    /**
     * @var CrudService
     */
    private $crudService;

    public function __construct(CrudService $crudService)
    {
        $this->crudService = $crudService;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return $criteriaPart instanceof ProductAttributeFacet;
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
        $type = null;

        try {
            $attribute = $this->crudService->get('s_articles_attributes', $criteriaPart->getField());
            $type = $attribute->getElasticSearchType()['type'];
        } catch (\Exception $e) {
        }

        $this->criteriaParts[] = $criteriaPart;

        switch ($criteriaPart->getMode()) {
            case ProductAttributeFacet::MODE_VALUE_LIST_RESULT:
            case ProductAttributeFacet::MODE_RADIO_LIST_RESULT:
                if ($type === 'string') {
                    $field .= '.raw';
                }
                $aggregation = new TermsAggregation($criteriaPart->getName());
                $aggregation->setField($field);
                $aggregation->addParameter('size', self::AGGREGATION_SIZE);
                break;

            case ProductAttributeFacet::MODE_BOOLEAN_RESULT:
                $count = new ValueCountAggregation($criteriaPart->getName() . '_count');
                $count->setField($field);

                $aggregation = new FilterAggregation($criteriaPart->getName());
                $aggregation->setFilter(new ExistsQuery($field));
                $aggregation->addAggregation($count);
                break;

            case ProductAttributeFacet::MODE_RANGE_RESULT:
                $aggregation = new TermsAggregation($criteriaPart->getName());
                $aggregation->setField($field);
                $aggregation->addParameter('size', self::AGGREGATION_SIZE);
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

            /** @var ConfigurationStruct|null $attribute */
            $attribute = $this->crudService->get('s_articles_attributes', $criteriaPart->getField());

            $type = $attribute ? $attribute->getColumnType() : null;

            if (in_array($type, [TypeMapping::TYPE_DATE, TypeMapping::TYPE_DATETIME])) {
                $aggregations[$key] = $this->formatDates($aggregations[$key]);
            }

            $criteriaPartResult = null;
            switch ($criteriaPart->getMode()) {
                case ProductAttributeFacet::MODE_VALUE_LIST_RESULT:
                case ProductAttributeFacet::MODE_RADIO_LIST_RESULT:
                    $criteriaPartResult = $this->createItemListResult($criteriaPart, $aggregations[$key], $criteria);
                    break;
                case ProductAttributeFacet::MODE_BOOLEAN_RESULT:
                    $criteriaPartResult = $this->createBooleanResult($criteriaPart, $aggregations[$key], $criteria);
                    break;
                case ProductAttributeFacet::MODE_RANGE_RESULT:
                    $criteriaPartResult = $this->createRangeResult($criteriaPart, $aggregations[$key], $criteria);

                    if ($criteriaPartResult->getMax() === $criteriaPartResult->getMin()) {
                        $criteriaPartResult = null;
                    }

                    break;
                default:
                    break;
            }
            if ($criteriaPartResult === null) {
                continue;
            }

            $this->switchTemplate($type, $criteriaPartResult, $criteriaPart);

            $result->addFacet($criteriaPartResult);
        }
    }

    /**
     * @param string $type
     *
     * @return FacetResultInterface
     */
    private function switchTemplate($type, FacetResultInterface $result, ProductAttributeFacet $facet)
    {
        if (!$result instanceof TemplateSwitchable) {
            return $result;
        }

        if ($facet->getTemplate()) {
            $result->setTemplate($facet->getTemplate());

            return $result;
        }

        $result->setTemplate(
            $this->getTypeTemplate($type, $facet->getMode(), $result->getTemplate())
        );

        return $result;
    }

    /**
     * @param string $type
     * @param string $mode
     * @param string $defaultTemplate
     *
     * @return string
     */
    private function getTypeTemplate($type, $mode, $defaultTemplate)
    {
        switch (true) {
            case $type === TypeMapping::TYPE_DATE && $mode === ProductAttributeFacet::MODE_RANGE_RESULT:
                return 'frontend/listing/filter/facet-date-range.tpl';

            case $type === TypeMapping::TYPE_DATE && $mode === ProductAttributeFacet::MODE_VALUE_LIST_RESULT:
                return 'frontend/listing/filter/facet-date-multi.tpl';

            case $type === TypeMapping::TYPE_DATE && $mode !== ProductAttributeFacet::MODE_BOOLEAN_RESULT:
                return 'frontend/listing/filter/facet-date.tpl';

            case $type === TypeMapping::TYPE_DATETIME && $mode === ProductAttributeFacet::MODE_RANGE_RESULT:
                return 'frontend/listing/filter/facet-datetime-range.tpl';

            case $type === TypeMapping::TYPE_DATETIME && $mode === ProductAttributeFacet::MODE_VALUE_LIST_RESULT:
                return 'frontend/listing/filter/facet-datetime-multi.tpl';

            case $type === TypeMapping::TYPE_DATETIME && $mode !== ProductAttributeFacet::MODE_BOOLEAN_RESULT:
                return 'frontend/listing/filter/facet-datetime.tpl';

            default:
                return $defaultTemplate;
        }
    }

    /**
     * @param array $data
     *
     * @return RadioFacetResult|ValueListFacetResult|null
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

        /** @var ProductAttributeCondition $condition */
        if ($condition = $criteria->getCondition($criteriaPart->getName())) {
            $actives = $condition->getValue();

            // $condition->getValue() can return a string
            if (!is_array($actives)) {
                $actives = [$actives];
            }
        }

        $items = array_map(function ($row) use ($actives) {
            return new ValueListItem($row, $row, in_array($row, $actives));
        }, $values);

        if ($criteriaPart->getMode() == ProductAttributeFacet::MODE_RADIO_LIST_RESULT) {
            return new RadioFacetResult(
                $criteriaPart->getName(),
                $criteria->hasCondition($criteriaPart->getName()),
                $criteriaPart->getLabel(),
                $items,
                $criteriaPart->getFormFieldName()
            );
        }

        return new ValueListFacetResult(
            $criteriaPart->getName(),
            $criteria->hasCondition($criteriaPart->getName()),
            $criteriaPart->getLabel(),
            $items,
            $criteriaPart->getFormFieldName()
        );
    }

    /**
     * @param array $data
     *
     * @return BooleanFacetResult|null
     */
    private function createBooleanResult(ProductAttributeFacet $criteriaPart, $data, Criteria $criteria)
    {
        $count = $data[$criteriaPart->getName() . '_count'];
        $count = $count['value'];

        if ($count <= 0) {
            return null;
        }

        return new BooleanFacetResult(
            $criteriaPart->getName(),
            $criteriaPart->getFormFieldName(),
            $criteria->hasCondition($criteriaPart->getName()),
            $criteriaPart->getLabel()
        );
    }

    /**
     * @param array $data
     *
     * @return RangeFacetResult
     */
    private function createRangeResult(ProductAttributeFacet $criteriaPart, $data, Criteria $criteria)
    {
        $values = array_column($data['buckets'], 'key');
        $min = min($values);
        $max = max($values);

        $activeMin = $min;
        $activeMax = $max;

        /** @var ProductAttributeCondition $condition */
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
            $criteriaPart->getSuffix(),
            $criteriaPart->getDigits()
        );
    }

    /**
     * @return array
     */
    private function formatDates(array $aggregation)
    {
        $aggregation['buckets'] = array_map(function ($bucket) {
            $bucket['key'] = $bucket['key_as_string'];

            return $bucket;
        }, $aggregation['buckets']);

        return $aggregation;
    }
}
