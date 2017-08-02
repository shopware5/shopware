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
use Shopware\Context\TranslationContext;
use Shopware\Search\Condition\ShopCondition;
use Shopware\Search\Criteria;
use Shopware\Search\CriteriaPartInterface;
use Shopware\Search\HandlerInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ShopConditionHandler implements HandlerInterface
{
    public function supports(CriteriaPartInterface $criteriaPart): bool
    {
        return $criteriaPart instanceof ShopCondition;
    }

    public function handle(
        CriteriaPartInterface $criteriaPart,
        \Doctrine\DBAL\Query\QueryBuilder $builder,
        Criteria $criteria,
        TranslationContext $context
    ) {
        $builder->innerJoin(
            'product',
            's_articles_categories_ro',
            'productCategory',
            'productCategory.articleID = product.id'
        );

        $builder->innerJoin(
            'productCategory',
            's_core_shops',
            'shop',
            'shop.category_id = productCategory.categoryID AND shop.id IN (:shopIds)'
        );

        /** @var ShopCondition $criteriaPart */
        $builder->setParameter(
            ':shopIds',
            $criteriaPart->getIds(),
            Connection::PARAM_INT_ARRAY
        );
    }
}
