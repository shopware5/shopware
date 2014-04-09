<?php

namespace Shopware\Gateway;

interface Search
{
    /**
     * @param Condition $condition
     * @return Result
     */
    public function search(Condition $condition);
}