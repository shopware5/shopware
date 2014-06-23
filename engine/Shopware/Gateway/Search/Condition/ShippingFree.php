<?php

namespace Shopware\Gateway\Search\Condition;

use Shopware\Gateway\Search\Condition;

class ShippingFree extends Condition
{
    public function getName()
    {
        return 'shipping_free';
    }
}