<?php

namespace Shopware\Gateway\DBAL\QueryGenerator;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\Search\Condition;
use Shopware\Gateway\Search\Sorting;

abstract class DBAL
{
    public function supportsCondition(Condition $condition)
    {
        return false;
    }

    public function generateCondition(Condition $condition, QueryBuilder $query)
    {
    }

    public function supportsSorting(Sorting $sorting)
    {
        return false;
    }

    public function generateSorting(Sorting $sorting, QueryBuilder $query)
    {
    }
}
