<?php

namespace Shopware\Hydrator\ORM;

use Shopware\Struct as Struct;

class Product
{
    /**
     * Used to hydrate the price data and converts the array
     * into a Struct\Price
     *
     * @var Price
     */
    private $priceHydrator;

    /**
     * @param Price $priceHydrator
     */
    function __construct(Price $priceHydrator)
    {
        $this->priceHydrator = $priceHydrator;
    }

    /**
     * Hydrates the passed data and converts the ORM
     * array values into a Struct\ProductMini class.
     *
     * @param array $data
     * @return Struct\ProductMini
     */
    public function hydrateMini(array $data)
    {
        $product = new Struct\ProductMini();

        $product->setId($data['id']);

        $product->setName($data['id']);

        $product->setInStock($data['inStock']);

        if (isset($data['mainProduct'])) {
            $product->setMainProduct(
                $this->hydrateMini(
                    $data['mainProduct']
                )
            );
        }

        if (isset($data['prices'])) {
            $product->setPrices(
                $this->iteratePrices($data['prices'])
            );
        }
        return $product;
    }

    /**
     * Iterates the price array and creates for each
     * array element a price struct.
     *
     * @param $data
     * @return array
     */
    private function iteratePrices($data)
    {
        $prices = array();

        foreach($data as $price) {
            $prices[] = $this->priceHydrator->hydrate(
                $price
            );
        }

        return $prices;
    }

}