<?php

namespace Shopware\Search;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Search\Criteria;
use Shopware\Search\CriteriaPartInterface;
use Shopware\Bundle\StoreFrontBundle\Context\TranslationContext;

interface HandlerInterface
{
    public function supports(CriteriaPartInterface $criteriaPart): bool;

    public function handle(
        CriteriaPartInterface $criteriaPart,
        QueryBuilder $builder,
        Criteria $criteria,
        TranslationContext $context
    );
}