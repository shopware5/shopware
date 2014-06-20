<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 20.06.14
 * Time: 09:54
 */
namespace Shopware\Service;

use Shopware\Struct;

interface Category
{
    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\Category::getList()
     *
     * @param $ids
     * @param Struct\Context $context
     * @return Struct\Category[]
     */
    public function getList($ids, Struct\Context $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\Category::get()
     *
     * @param $id
     * @param Struct\Context $context
     * @return Struct\Category
     */
    public function get($id, Struct\Context $context);
}