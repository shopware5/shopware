<?php

namespace Shopware\Gateway;

use Shopware\Struct as Struct;

interface CustomerGroup
{
    /**
     * Returns a single Struct\CustomerGroup object.
     *
     * The customer group should be loaded with the CustomerGroup attributes.
     * Otherwise the customer group data isn't extendable.
     *
     * The passed $key parameter contains the alphanumeric customer group identifier
     * which stored in the s_core_customergroups.groupkey column.
     *
     * @param $key
     * @return \Shopware\Struct\CustomerGroup
     */
    public function getByKey($key);
}