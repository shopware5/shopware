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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class CategoryConditionHandler implements ConditionHandlerInterface
{
    public const STATE_NAME = 'productCategory';

    /**
     * {@inheritdoc}
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return $condition instanceof CategoryCondition;
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

    private function addCondition(CategoryCondition $condition, QueryBuilder $query): void
    {
        $joinName = self::STATE_NAME;
        $counter = 1;

        while ($query->hasState($joinName)) {
            ++$counter;
            $joinName = self::STATE_NAME . $counter;
        }

        $query->addState($joinName);

        $query->innerJoin(
            'product',
            's_articles_categories_ro',
            $joinName,
            $joinName . ".articleID = product.id
            AND {$joinName}.categoryID IN (:{$joinName})"
        );

        $query->setParameter(
            ':' . $joinName,
            $condition->getCategoryIds(),
            Connection::PARAM_INT_ARRAY
        );
    }
}
