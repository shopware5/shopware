<?php

namespace Shopware\Category\Gateway\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Context\TranslationContext;
use Shopware\Search\Condition\ShopCondition;
use Shopware\Search\Criteria;
use Shopware\Search\CriteriaPartInterface;
use Shopware\Search\HandlerInterface;

class ShopConditionHandler implements HandlerInterface
{
    public function supports(CriteriaPartInterface $criteriaPart): bool
    {
        return $criteriaPart instanceof ShopCondition;
    }

    public function handle(
        CriteriaPartInterface $criteriaPart,
        QueryBuilder $builder,
        Criteria $criteria,
        TranslationContext $context
    ) {
        $builder->innerJoin(
            'category',
            's_core_shops',
            'shop',
            "shop.id IN (:shopIds) 
            AND category.path LIKE CONCAT('%|', shop.category_id, '|')"
        );

        /** @var ShopCondition $criteriaPart */
        $builder->setParameter(':shopIds', $criteriaPart->getIds(), Connection::PARAM_INT_ARRAY);
    }
}