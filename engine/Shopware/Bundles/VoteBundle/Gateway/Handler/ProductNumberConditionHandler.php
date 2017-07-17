<?php

namespace VoteBundle\Gateway\Handler;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\StoreFrontBundle\Context\TranslationContext;
use SearchBundle\Condition\ProductNumberCondition;
use SearchBundle\HandlerInterface;

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