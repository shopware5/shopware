<?php

namespace Shopware\Hydrator\ORM;
use Shopware\Struct as Struct;

class Unit
{
    /**
     * @param array $data
     * @return \Shopware\Struct\Unit
     */
    public function hydrate(array $data)
    {
        $unit = new Struct\Unit();

        $unit->setId($data['id']);

        $unit->setName($data['name']);

        $unit->setUnit($data['unit']);

        return $unit;
    }
}