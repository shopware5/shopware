<?php

namespace Shopware\Gateway\DBAL\Hydrator;
use Shopware\Struct as Struct;

class Attribute
{
    public function hydrate(array $data)
    {
        $attribute = new Struct\Attribute();

        foreach($data as $key => $value) {
            $attribute->set($key, $value);
        }

        return $attribute;
    }
}