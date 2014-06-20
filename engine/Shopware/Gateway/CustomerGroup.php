<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 18.06.14
 * Time: 14:46
 */
namespace Shopware\Gateway;


/**
 * @package Shopware\Gateway\DBAL
 */
interface CustomerGroup
{
    /**
     * To get detailed information about the structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\DBAL\CustomerGroup::get()
     *
     * @param array $keys
     * @return \Shopware\Struct\Customer\Group[] Indexed by the customer group key
     */
    public function getList(array $keys);

    /**
     * The \Shopware\Struct\Customer\Group requires the following data:
     * - Customer group base data
     * - Core attribute of the customer group
     *
     * @param $key
     * @return \Shopware\Struct\Customer\Group
     */
    public function get($key);
}