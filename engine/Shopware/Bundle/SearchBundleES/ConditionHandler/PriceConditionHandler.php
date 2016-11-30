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

use ONGR\ElasticsearchDSL\Query\RangeQuery;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\ESIndexingBundle\FieldMappingInterface;
use Shopware\Bundle\SearchBundle\Condition\PriceCondition;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundleES\PartialConditionHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class PriceConditionHandler implements PartialConditionHandlerInterface
{
    /**
     * @var FieldMappingInterface
     */
    private $fieldMapping;

    /**
     * @param FieldMappingInterface $fieldMapping
     */
    public function __construct(FieldMappingInterface $fieldMapping)
    {
        $this->fieldMapping = $fieldMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return ($criteriaPart instanceof PriceCondition);
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
        $search->addFilter(
            $this->createQuery($criteriaPart, $context)
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
        $search->addPostFilter(
            $this->createQuery($criteriaPart, $context)
        );
    }

    /**
     * @param CriteriaPartInterface $criteriaPart
     * @param ShopContextInterface $context
     * @return RangeQuery
     */
    private function createQuery(CriteriaPartInterface $criteriaPart, ShopContextInterface $context)
    {
        $range = [];

        /** @var PriceCondition $criteriaPart */
        if ($criteriaPart->getMinPrice()) {
            $range['gte'] = $criteriaPart->getMinPrice();
        }
        if ($criteriaPart->getMaxPrice()) {
            $range['lte'] = $criteriaPart->getMaxPrice();
        }
        return new RangeQuery($this->fieldMapping->getPriceField($context), $range);
    }
}
