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

use ONGR\ElasticsearchDSL\Filter\NotFilter;
use ONGR\ElasticsearchDSL\Filter\QueryFilter;
use ONGR\ElasticsearchDSL\Filter\RangeFilter;
use ONGR\ElasticsearchDSL\Filter\TermFilter;
use ONGR\ElasticsearchDSL\Filter\TermsFilter;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\SearchBundle\Condition\ProductAttributeCondition;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductAttributeConditionHandler implements HandlerInterface
{
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

        switch ($criteriaPart->getOperator()) {
            case ProductAttributeCondition::OPERATOR_EQ:
                $filter = new TermFilter($field, $criteriaPart->getValue());
                break;

            case ProductAttributeCondition::OPERATOR_NEQ:
                $filter = new NotFilter(new TermFilter($field, $criteriaPart->getValue()));
                break;

            case ProductAttributeCondition::OPERATOR_LT:
                $filter = new RangeFilter($field, ['lt' => $criteriaPart->getValue()]);
                break;

            case ProductAttributeCondition::OPERATOR_LTE:
                $filter = new RangeFilter($field, ['lte' => $criteriaPart->getValue()]);
                break;

            case ProductAttributeCondition::OPERATOR_BETWEEN:
                $value = $criteriaPart->getValue();
                $filter = new RangeFilter($field, ['gte' => $value['min'], 'lte' => $value['max']]);
                break;

            case ProductAttributeCondition::OPERATOR_GT:
                $filter = new RangeFilter($field, ['gt' => $criteriaPart->getValue()]);
                break;

            case ProductAttributeCondition::OPERATOR_GTE:
                $filter = new RangeFilter($field, ['gte' => $criteriaPart->getValue()]);
                break;

            case ProductAttributeCondition::OPERATOR_IN:
                $filter = new TermsFilter($field, $criteriaPart->getValue());
                break;

            case ProductAttributeCondition::OPERATOR_STARTS_WITH:
            case ProductAttributeCondition::OPERATOR_ENDS_WITH:
            case ProductAttributeCondition::OPERATOR_CONTAINS:
                $filter = new QueryFilter(
                    new TermFilter($field, $criteriaPart->getValue())
                );
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
