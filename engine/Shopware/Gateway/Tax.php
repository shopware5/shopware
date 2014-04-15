<?php

namespace Shopware\Gateway;
use Shopware\Struct as Struct;

interface Tax
{
    public function getRules(
        Struct\CustomerGroup $customerGroup,
        Struct\Area $area = null,
        Struct\Country $country = null,
        Struct\State $state = null
    );
}