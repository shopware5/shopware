<?php

namespace Shopware\Gateway\Search\Condition;

use Shopware\Gateway\Search\Condition;

class Property extends Condition
{
    public $values = array();

    function __construct(array $values)
    {
        $this->values = $values;
    }

    public function getName()
    {
        return 'property';
    }
}