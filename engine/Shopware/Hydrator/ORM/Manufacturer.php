<?php

namespace Shopware\Hydrator\ORM;
use Shopware\Struct as Struct;

class Manufacturer
{
    /**
     * @param array $data
     * @return \Shopware\Struct\Manufacturer
     */
    public function hydrate(array $data)
    {
        $manufacturer = new Struct\Manufacturer();

        $manufacturer->setId($data['id']);

        $manufacturer->setName($data['name']);

        return $manufacturer;
    }
}