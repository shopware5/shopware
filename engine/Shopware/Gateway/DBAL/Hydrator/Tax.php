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

        $tax->setId((int)$data['__tax_id']);
        $tax->setName($data['__tax_description']);
        $tax->setTax((float)$data['__tax_tax']);

        return $tax;
    }

    /**
     * Creates a new tax struct and assigns the passed
     * data array.
     *
     * @param array $data
     * @return \Shopware\Struct\Tax
     */
    public function hydrateRule(array $data)
    {
        $tax = new Struct\Tax();

        $tax->setId((int)$data['__taxRule_groupID']);
        $tax->setName($data['__taxRule_name']);
        $tax->setTax((float)$data['__taxRule_tax']);

        return $tax;
    }

}