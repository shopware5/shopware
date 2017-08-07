<?php

namespace Shopware\SeoUrl\Gateway\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Context\TranslationContext;
use Shopware\Search\Condition\ForeignKeyCondition;
use Shopware\Search\Criteria;
use Shopware\Search\CriteriaPartInterface;
use Shopware\Search\HandlerInterface;

class ForeignKeyHandler implements HandlerInterface
{
    public function supports(CriteriaPartInterface $criteriaPart): bool
    {
        return $criteriaPart instanceof ForeignKeyCondition;
    }

    public function handle(
        CriteriaPartInterface $criteriaPart,
        QueryBuilder $builder,
        Criteria $criteria,
        TranslationContext $context
    ): void {
        $builder->andWhere('seoUrl.foreign_key IN (:foreignKeys)');

        /** @var ForeignKeyCondition $criteriaPart */
        $builder->setParameter(':foreignKeys', $criteriaPart->getForeignKeys(), Connection::PARAM_INT_ARRAY);
    }
}