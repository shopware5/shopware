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

use ONGR\ElasticsearchDSL\Query\BoolQuery;
use ONGR\ElasticsearchDSL\Query\ExistsQuery;
use ONGR\ElasticsearchDSL\Query\MatchQuery;
use ONGR\ElasticsearchDSL\Query\MissingQuery;
use ONGR\ElasticsearchDSL\Query\PrefixQuery;
use ONGR\ElasticsearchDSL\Query\RangeQuery;
use ONGR\ElasticsearchDSL\Query\TermQuery;
use ONGR\ElasticsearchDSL\Query\TermsQuery;
use ONGR\ElasticsearchDSL\Query\WildcardQuery;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\SearchBundle\Condition\ProductAttributeCondition;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductAttributeConditionHandler implements HandlerInterface
{
    /**
     * @var CrudService
     */
    private $attributeService;

    /**
     * ProductAttributeConditionHandler constructor.
     * @param CrudService $attributeService
     */
    public function __construct(CrudService $attributeService)
    {
        $this->attributeService = $attributeService;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return ($criteriaPart instanceof ProductAttributeCondition);
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
        /** @var ProductAttributeCondition $criteriaPart */
        $field = 'attributes.core.' . $criteriaPart->getField();

        $type = 'string';
        try {
            $attribute = $this->attributeService->get('s_articles_attributes', $criteriaPart->getField());
            $type = $attribute->getElasticSearchType()['type'];
        } catch (\Exception $e) {
        }

        switch ($criteriaPart->getOperator()) {
            case ProductAttributeCondition::OPERATOR_EQ:
                if ($criteriaPart->getValue() === null) {
                    $filter = new MissingQuery($field);
                } else {
                    $filter = new TermQuery($field, $criteriaPart->getValue());
                }
                break;

            case ProductAttributeCondition::OPERATOR_NEQ:
                if ($criteriaPart->getValue() === null) {
                    $filter = new ExistsQuery($field);
                } else {
                    $filter = new BoolQuery();
                    $filter->add(new TermQuery($field, $criteriaPart->getValue()), BoolQuery::MUST_NOT);
                }
                break;

            case ProductAttributeCondition::OPERATOR_LT:
                $filter = new RangeQuery($field, ['lt' => $criteriaPart->getValue()]);
                break;

            case ProductAttributeCondition::OPERATOR_LTE:
                $filter = new RangeQuery($field, ['lte' => $criteriaPart->getValue()]);
                break;

            case ProductAttributeCondition::OPERATOR_BETWEEN:
                $value = $criteriaPart->getValue();
                $filter = new RangeQuery($field, ['gte' => $value['min'], 'lte' => $value['max']]);
                break;

            case ProductAttributeCondition::OPERATOR_GT:
                $filter = new RangeQuery($field, ['gt' => $criteriaPart->getValue()]);
                break;

            case ProductAttributeCondition::OPERATOR_GTE:
                $filter = new RangeQuery($field, ['gte' => $criteriaPart->getValue()]);
                break;

            case ProductAttributeCondition::OPERATOR_CONTAINS:
                $filter = new MatchQuery($field, $criteriaPart->getValue());
                break;

            case ProductAttributeCondition::OPERATOR_IN:
                if ($type === 'string') {
                    $field .= '.raw';
                }
                $filter = new TermsQuery($field, $criteriaPart->getValue());
                break;

            case ProductAttributeCondition::OPERATOR_STARTS_WITH:
                if ($type === 'string') {
                    $field .= '.raw';
                }
                $filter = new PrefixQuery($field, $criteriaPart->getValue());
                break;

            case ProductAttributeCondition::OPERATOR_ENDS_WITH:
                if ($type === 'string') {
                    $field .= '.raw';
                }
                $filter = new WildcardQuery($field, '*'. $criteriaPart->getValue());
                break;

            default:
                return;
        }

        if ($criteria->hasBaseCondition($criteriaPart->getName())) {
            $search->addFilter($filter);
        } else {
            $search->addPostFilter($filter);
        }
    }
}
