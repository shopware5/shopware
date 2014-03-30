<?php

namespace Shopware\Hydrator\ORM;
use Shopware\Struct as Struct;

class Tax
{
    /**
     * @param array $data
     * @return \Shopware\Struct\Tax
     */
    public function hydrate(array $data)
    {
        $tax = new Struct\Tax();

        $tax->setId($data['id']);

        $tax->setTax($data['tax']);

        return $tax;
    }
}