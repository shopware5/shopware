<?php

namespace Shopware\Gateway\Search\Condition;

class Category
{
    public $id;

    function __construct($id)
    {
        $this->id = $id;
    }
}