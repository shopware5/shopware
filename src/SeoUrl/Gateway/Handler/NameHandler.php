<?php

namespace Shopware\SeoUrl\Gateway\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Context\TranslationContext;
use Shopware\Search\Condition\NameCondition;
use Shopware\Search\Criteria;
use Shopware\Search\CriteriaPartInterface;
use Shopware\Search\HandlerInterface;

class NameHandler implements HandlerInterface
{
    public function supports(CriteriaPartInterface $criteriaPart): bool
    {
        return $criteriaPart instanceof NameCondition;
    }

    public function handle(
        CriteriaPartInterface $criteriaPart,
        QueryBuilder $builder,
        Criteria $criteria,
        TranslationContext $context
    ): void {
        $builder->andWhere('seoUrl.name IN (:names)');

        /** @var NameCondition $criteriaPart */
        $builder->setParameter(':names', $criteriaPart->getNames(), Connection::PARAM_STR_ARRAY);
    }
}