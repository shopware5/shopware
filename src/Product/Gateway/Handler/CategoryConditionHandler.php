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

namespace Shopware\Product\Gateway\Handler;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Context\TranslationContext;
use Shopware\Search\Criteria;
use Shopware\Search\CriteriaPartInterface;
use Shopware\Search\HandlerInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CategoryConditionHandler implements HandlerInterface
{
    /**
     * @var int
     */
    private $counter = 0;

    public function supports(CriteriaPartInterface $criteriaPart): bool
    {
        return $criteriaPart instanceof CategoryCondition;
    }

    public function handle(
        CriteriaPartInterface $criteriaPart,
        \Doctrine\DBAL\Query\QueryBuilder $builder,
        Criteria $criteria,
        TranslationContext $context
    ) {
        if ($this->counter++ === 0) {
            $suffix = '';
        } else {
            $suffix = $this->counter;
        }

        $builder->innerJoin(
            'product',
            's_articles_categories_ro',
            "productCategory{$suffix}",
            "productCategory{$suffix}.articleID = product.id
            AND productCategory{$suffix}.categoryID IN (:category{$suffix})"
        );

        /* @var CategoryCondition $condition */
        $builder->setParameter(
            ":category{$suffix}",
            $condition->getCategoryIds(),
            Connection::PARAM_INT_ARRAY
        );
    }
}
