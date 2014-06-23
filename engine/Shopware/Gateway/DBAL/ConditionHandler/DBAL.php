<?php

namespace Shopware\Gateway\DBAL\ConditionHandler;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\Search\Condition;
use Shopware\Gateway\Search\Sorting;
use Shopware\Struct\Context;

interface DBAL
{
    /**
     * Checks if the passed condition can be handled by this class.
     *
     * @param Condition $condition
     * @return bool
     */
    public function supportsCondition(Condition $condition);

    /**
     * Checks if the passed sorting can be handled by this class
     * @param Sorting $sorting
     * @return bool
     */
    public function supportsSorting(Sorting $sorting);

    /**
     * Handles the passed condition object.
     * Extends the provided query builder with the specify conditions.
     * Should use the andWhere function, otherwise other conditions would be overwritten.
     *
     * @param Condition $condition
     * @param QueryBuilder $query
     * @param Context $context
     * @return void
     */
    public function generateCondition(
        Condition $condition,
        QueryBuilder $query,
        Context $context
    );

    /**
     * Handles the passed sorting object.
     * Extends the passed query builder with the specify sorting.
     * Should use the addOrderBy function, otherwise other sortings would be overwritten.
     *
     * @param Sorting $sorting
     * @param QueryBuilder $query
     * @param Context $context
     * @return void
     */
    public function generateSorting(
        Sorting $sorting,
        QueryBuilder $query,
        Context $context
    );
}
