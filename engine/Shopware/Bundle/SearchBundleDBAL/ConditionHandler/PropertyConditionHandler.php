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

namespace Shopware\Bundle\SearchBundleDBAL\ConditionHandler;

use Shopware\Bundle\SearchBundle\Condition\PropertyCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class PropertyConditionHandler implements ConditionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return $condition instanceof PropertyCondition;
    }

    /**
     * {@inheritdoc}
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        $this->addCondition($condition, $query);
    }

    private function addCondition(PropertyCondition $condition, QueryBuilder $query): void
    {
        $tableKey = $condition->getName();

        $suffix = md5(json_encode($condition, JSON_THROW_ON_ERROR));

        if ($query->hasState('property_' . $tableKey)) {
            return;
        }
        $query->addState('property_' . $tableKey);

        $where = [];
        foreach ($condition->getValueIds() as $valueId) {
            $valueKey = ':' . $tableKey . '_' . $valueId . '_' . $suffix;
            $where[] = $tableKey . '.valueID = ' . $valueKey;
            $query->setParameter($valueKey, $valueId);
        }

        $where = implode(' OR ', $where);

        $query->innerJoin(
            'product',
            's_filter_articles',
            $tableKey,
            'product.id = ' . $tableKey . '.articleID
             AND (' . $where . ')'
        );
    }
}
