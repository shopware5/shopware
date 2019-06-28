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

use ONGR\ElasticsearchDSL\BuilderInterface;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MatchQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\ExistsQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\PrefixQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\WildcardQuery;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\SearchBundle\Condition\ProductAttributeCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundleES\PartialConditionHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductAttributeConditionHandler implements PartialConditionHandlerInterface
{
    /**
     * @var CrudService
     */
    private $attributeService;

    public function __construct(CrudService $attributeService)
    {
        $this->attributeService = $attributeService;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return $criteriaPart instanceof ProductAttributeCondition;
    }

    /**
     * {@inheritdoc}
     */
    public function handleFilter(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        /* @var ProductAttributeCondition $criteriaPart */
        $search->addQuery(
            $this->createQuery($criteriaPart),
            BoolQuery::FILTER
        );
    }

    /**
     * {@inheritdoc}
     */
    public function handlePostFilter(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        /* @var ProductAttributeCondition $criteriaPart */
        $search->addPostFilter(
            $this->createQuery($criteriaPart)
        );
    }

    /**
     * @return BuilderInterface
     */
    private function createQuery(ProductAttributeCondition $criteriaPart)
    {
        $field = 'attributes.core.' . $criteriaPart->getField();

        $type = 'string';
        try {
            $attribute = $this->attributeService->get('s_articles_attributes', $criteriaPart->getField());
            $type = $attribute->getElasticSearchType()['type'];
        } catch (\Exception $e) {
        }

        switch ($criteriaPart->getOperator()) {
            case ProductAttributeCondition::OPERATOR_EQ:
                if ($type === 'string') {
                    $field .= '.raw';
                }
                if ($criteriaPart->getValue() === null) {
                    $filter = new BoolQuery();
                    $filter->add(new ExistsQuery($field), BoolQuery::MUST_NOT);

                    return $filter;
                }

                return new TermQuery($field, $criteriaPart->getValue());

            case ProductAttributeCondition::OPERATOR_NEQ:
                if ($criteriaPart->getValue() === null) {
                    return new ExistsQuery($field);
                }
                $filter = new BoolQuery();
                $filter->add(new TermQuery($field, $criteriaPart->getValue()), BoolQuery::MUST_NOT);

                return $filter;

            case ProductAttributeCondition::OPERATOR_LT:
                return new RangeQuery($field, ['lt' => $criteriaPart->getValue()]);

            case ProductAttributeCondition::OPERATOR_LTE:
                return new RangeQuery($field, ['lte' => $criteriaPart->getValue()]);

            case ProductAttributeCondition::OPERATOR_BETWEEN:
                $value = $criteriaPart->getValue();

                return new RangeQuery($field, ['gte' => $value['min'], 'lte' => $value['max']]);

            case ProductAttributeCondition::OPERATOR_GT:
                return new RangeQuery($field, ['gt' => $criteriaPart->getValue()]);

            case ProductAttributeCondition::OPERATOR_GTE:
                return new RangeQuery($field, ['gte' => $criteriaPart->getValue()]);

            case ProductAttributeCondition::OPERATOR_CONTAINS:
                return new MatchQuery($field, $criteriaPart->getValue());

            case ProductAttributeCondition::OPERATOR_NOT_IN:
                if ($type === 'string') {
                    $field .= '.raw';
                }
                $filter = new BoolQuery();
                $filter->add(new TermsQuery($field, $criteriaPart->getValue()), BoolQuery::MUST_NOT);

                return $filter;

            case ProductAttributeCondition::OPERATOR_IN:
                if ($type === 'string') {
                    $field .= '.raw';
                }

                return new TermsQuery($field, $criteriaPart->getValue());

            case ProductAttributeCondition::OPERATOR_STARTS_WITH:
                if ($type === 'string') {
                    $field .= '.raw';
                }

                return new PrefixQuery($field, $criteriaPart->getValue());

            case ProductAttributeCondition::OPERATOR_ENDS_WITH:
                if ($type === 'string') {
                    $field .= '.raw';
                }

                return new WildcardQuery($field, '*' . $criteriaPart->getValue());

            default:
                throw new \RuntimeException(sprintf('Operator %s is not supported in elastic search', $criteriaPart->getOperator()));
        }
    }
}
