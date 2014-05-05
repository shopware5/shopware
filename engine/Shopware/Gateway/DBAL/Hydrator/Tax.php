<?php

namespace Shopware\Gateway\DBAL\Hydrator;

use Shopware\Struct as Struct;

class Tax extends Hydrator
{
    /**
     * Creates a new tax struct and assigns the passed
     * data array.
     *
     * @param array $data
     * @return \Shopware\Struct\Tax
     */
    public function hydrate(array $data)
    {
        $tax = new Struct\Tax();

        $tax->setId($data['id']);

        $tax->setName($data['name']);

        $tax->setTax($data['tax']);

        return $tax;
    }

}