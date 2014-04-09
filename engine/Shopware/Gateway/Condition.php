<?php

namespace Shopware\Gateway;

class Condition
{
    public $offset;

    public $limit;

    public $sort;

    public $properties;

    public $supplier;

    public $category;

    /**
     * The price condition requires a from and to value
     * to define the price range.
     *
     * Example: array(1, 100)
     *
     * @var array
     */
    public $price;
}