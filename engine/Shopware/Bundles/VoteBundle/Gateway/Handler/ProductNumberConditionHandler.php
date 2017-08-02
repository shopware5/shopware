<?php

namespace VoteBundle\Gateway\Handler;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Search\Criteria;
use Shopware\Search\CriteriaPartInterface;
use Shopware\Context\TranslationContext;
use SearchBundle\Condition\ProductNumberCondition;
use Shopware\Search\HandlerInterface;

class ProductNumberConditionHandler implements HandlerInterface
{
    public function supports(CriteriaPartInterface $criteriaPart): bool
    {
        return $criteriaPart instanceof ProductNumberCondition;
    }

    public function handle(
        CriteriaPartInterface $criteriaPart,
        QueryBuilder $builder,
        Criteria $criteria,
        TranslationContext $context
    ) {
        $key = ':' . self::class;

        $builder->andWhere('vote.articleID IN (' . $key .')');

        $builder->setParameter($key, $criteriaPart);
    }
}