<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 21.01.15
 * Time: 10:17
 */
namespace Shopware\Bundle\StoreFrontBundle\Service;

use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;

/**
 * Class BaseProductFactory
 * @package Shopware\Bundle\StoreFrontBundle\Struct
 */
interface BaseProductFactoryServiceInterface
{
    /**
     * Creates a single base product struct with all required identifier fields
     * for other store front services
     *
     * @param $number
     * @return BaseProduct
     */
    public function createBaseProduct($number);

    /**
     * Creates a list of base product structs with all required identifier fields
     * for other store front services
     *
     * @param $numbers
     * @return BaseProduct[]
     */
    public function createBaseProducts($numbers);
}
