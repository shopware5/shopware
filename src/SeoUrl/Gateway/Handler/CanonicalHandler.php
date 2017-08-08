<?php

namespace Shopware\SeoUrl\Gateway\Handler;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Context\Struct\TranslationContext;
use Shopware\Search\Condition\CanonicalCondition;
use Shopware\Search\Criteria;
use Shopware\Search\CriteriaPartInterface;
use Shopware\Search\HandlerInterface;

class CanonicalHandler implements HandlerInterface
{
    public function supports(CriteriaPartInterface $criteriaPart): bool
    {
        return $criteriaPart instanceof CanonicalCondition;
    }

    public function handle(
        CriteriaPartInterface $criteriaPart,
        QueryBuilder $builder,
        Criteria $criteria,
        TranslationContext $context
    ): void {
        $builder->andWhere('seoUrl.is_canonical = :canonical');

        /** @var CanonicalCondition $criteriaPart */
        $builder->setParameter(':canonical', $criteriaPart->isCanonical());
    }
}