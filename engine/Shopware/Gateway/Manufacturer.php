<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 18.06.14
 * Time: 14:47
 */
namespace Shopware\Gateway;

use Shopware\Struct;


/**
 * @package Shopware\Gateway\DBAL
 */
interface Manufacturer
{
    /**
     * To get detailed information about the structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\DBAL\Manufacturer::get()
     *
     * @param array $ids
     * @param \Shopware\Struct\Context $context
     * @return Struct\Product\Manufacturer[] Indexed by the manufacturer id
     */
    public function getList(array $ids, Struct\Context $context);

    /**
     * The \Shopware\Struct\Manufacturer requires the following data:
     * - Manufacturer data
     * - Core attribute of the manufacturer
     *
     * Required translation in the provided context language:
     * - Manufacturer
     *
     * @param $id
     * @param \Shopware\Struct\Context $context
     * @return Struct\Product\Manufacturer
     */
    public function get($id, Struct\Context $context);
}