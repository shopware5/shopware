<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 20.06.14
 * Time: 09:55
 */
namespace Shopware\Service;

use Shopware\Struct;

interface Manufacturer
{
    /**
     * @see \Shopware\Service\Manufacturer::get()
     *
     * @param array $ids
     * @param Struct\Context $context
     * @return Struct\Product\Manufacturer[] Indexed by the manufacturer id
     */
    public function getList(array $ids, Struct\Context $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\Manufacturer::get()
     *
     * @param $id
     * @param Struct\Context $context
     * @return Struct\Product\Manufacturer[]
     */
    public function get($id, Struct\Context $context);
}