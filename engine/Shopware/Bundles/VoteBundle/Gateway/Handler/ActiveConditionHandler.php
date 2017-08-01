<?php

namespace VoteBundle\Gateway\Handler;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Search\Condition\ActiveCondition;
use Shopware\Search\HandlerInterface;
use Shopware\Search\Criteria;
use Shopware\Search\CriteriaPartInterface;
use Shopware\Bundle\StoreFrontBundle\Context\TranslationContext;

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
        $key = ':' . self::class;

        $builder->andWhere('vote.active = ' . $key);

        /** @var ActiveCondition $criteriaPart */
        $builder->setParameter($key, $criteriaPart);
    }
}