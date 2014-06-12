<?php

namespace Shopware\Gateway\Search;

use Shopware\Struct\Extendable;

class Product extends Extendable
{
    /**
     * @var string
     */
    private $number;

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }
}
