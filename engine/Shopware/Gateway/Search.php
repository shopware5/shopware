<?php

namespace Shopware\Gateway;

use Shopware\Gateway\Search\Criteria;
use Shopware\Gateway\Search\Result;

interface Search
{
    /**
     * @param Search\Criteria $condition
     * @return Result
     */
    public function search(Criteria $condition);
}