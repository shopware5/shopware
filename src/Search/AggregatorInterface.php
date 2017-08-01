<?php

namespace Shopware\Search;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Search\Criteria;
use Shopware\Search\CriteriaPartInterface;
use Shopware\Bundle\StoreFrontBundle\Context\TranslationContext;

interface AggregatorInterface
{
    public function supports(CriteriaPartInterface $criteriaPart): bool;

    public function aggregate(
        CriteriaPartInterface $criteriaPart,
        QueryBuilder $builder,
        Criteria $criteria,
        TranslationContext $context
    );
}