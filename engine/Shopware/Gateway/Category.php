<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 18.06.14
 * Time: 14:40
 */
namespace Shopware\Gateway;

use Shopware\Struct\Context;


/**
 * @package Shopware\Gateway\DBAL
 */
interface Category
{
    /**
     * To get detailed information about the structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\DBAL\Category::get()
     *
     * @param array $ids
     * @param Context $context
     * @return \Shopware\Struct\Category[] Indexed by the category id
     */
    public function getList(array $ids, Context $context);

    /**
     * The \Shopware\Struct\Category requires the following data:
     * - Category base data
     * - Core attribute
     * - Assigned media object
     * - Core attribute of the media object
     *
     * @param $id
     * @param Context $context
     * @return \Shopware\Struct\Category
     */
    public function get($id, Context $context);
}