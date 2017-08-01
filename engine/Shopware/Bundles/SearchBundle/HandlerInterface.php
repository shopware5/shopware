<?php

namespace SearchBundle;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
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