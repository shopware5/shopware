<?php

namespace Shopware\Gateway\DBAL\Hydrator;

use Shopware\Struct as Struct;

class Attribute extends Hydrator
{
    public function hydrate(array $data)
    {
        $attribute = new Struct\CoreAttribute();

        foreach ($data as $key => $value) {
            $attribute->set($key, $value);
        }

        return $attribute;
    }
}
