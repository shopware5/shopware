<?php

namespace Shopware\Gateway\ORM;

use Shopware\Hydrator\ORM as Hydrator;
use Shopware\Struct as Struct;

/**
 * Class Product
 * @package Shopware\Gateway\ORM
 */
class Product
{
    /**
     * @var Hydrator\Product
     */
    private $hydrator;

    /**
     * @param Hydrator\Product $hydrator
     */
    function __construct(Hydrator\Product $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    /**
     * Returns a minified product variant which contains only
     * simplify data of a variant.
     *
     * This product type is normally used for product overviews
     * like listings or sliders.
     *
     * To get the whole product data you can use the `get` function.
     *
     * @param string $number
     * @return Struct\ProductMini
     */
    public function getMini($number)
    {
        $data = array(
            'name' => 'Test product',
            'number' => $number,
            'inStock' => 200,
            'mainProduct' => array(
                'name' => 'Test product',
                'number' => $number . '.1',
                'inStock' => 30,
                'prices' => array(
                    array('from' => 0, 'to' => 20, 'value' => 400.99),
                    array('from' => 21, 'to' => 'beliebig', 'value' => 300.99),
                )
            ),
            'prices' => array(
                array('from' => 0, 'to' => 20, 'value' => 200.99),
                array('from' => 21, 'to' => 'beliebig', 'value' => 100.99),
            )
        );

        return $this->hydrator->hydrateMini($data);
    }

    /**
     * @param $number
     */
    public function get($number)
    {

    }
}