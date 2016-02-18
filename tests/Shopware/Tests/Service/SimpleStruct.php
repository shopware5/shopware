<?php

namespace Shopware\Tests\Service;

use Shopware\Bundle\StoreFrontBundle\Struct\Struct;

class SimpleStruct extends Struct
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
