<?php

namespace Shopware\Hydrator\ORM;
use Shopware\Struct as Struct;

class Price
{
    /**
     * @param array $data
     * @return \Shopware\Struct\ProductMini
     */
    public function hydrate(array $data)
    {
        $price = new Struct\Price();

        $price->setId($data['id']);

        $price->setFrom($data['from']);

        if (strtolower($data['to']) == 'beliebig') {
            $price->setTo(null);
        } else {
            $price->setTo($data['to']);
        }

        return $price;
    }
}