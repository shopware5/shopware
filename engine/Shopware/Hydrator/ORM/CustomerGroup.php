<?php

namespace Shopware\Hydrator\ORM;
use Shopware\Struct as Struct;

class CustomerGroup
{
    public function hydrate(array $data)
    {
        $customerGroup = new Struct\CustomerGroup();

        return $customerGroup;
    }
}