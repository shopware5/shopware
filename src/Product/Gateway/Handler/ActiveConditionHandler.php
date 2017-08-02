<?php

namespace Shopware\Product\Gateway\Handler;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Context\TranslationContext;
use Shopware\Search\Condition\ActiveCondition;
use Shopware\Search\Criteria;
use Shopware\Search\CriteriaPartInterface;
use Shopware\Search\HandlerInterface;

class ActiveConditionHandler implements HandlerInterface
{
    public function supports(CriteriaPartInterface $criteriaPart): bool
    {
        return $criteriaPart instanceof ActiveCondition;
    }

    public function handle(
        CriteriaPartInterface $criteriaPart,
        QueryBuilder $builder,
        Criteria $criteria,
        TranslationContext $context
    ) {
        $builder->andWhere('product.active = :active');
        $builder->andWhere('variant.active = :active');

        /** @var ActiveCondition $criteriaPart */
        $builder->setParameter(':active', $criteriaPart->isActive());
    }
}