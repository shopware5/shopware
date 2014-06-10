<?php

namespace Shopware\Gateway\DBAL\QueryGenerator;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\Search\Condition;
use Shopware\Gateway\Search\Sorting;
use Shopware\Struct\Context;

interface DBAL
{
    public function supportsCondition(Condition $condition);

    public function supportsSorting(Sorting $sorting);

    public function generateCondition(
        Condition $condition,
        QueryBuilder $query,
        Context $context
    );

    public function generateSorting(
        Sorting $sorting,
        QueryBuilder $query,
        Context $context
    );
}
