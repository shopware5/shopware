<?php

namespace Shopware\Gateway\Search\Facet;

use Shopware\Gateway\Search\Facet;

class Manufacturer extends Facet
{
    public $manufacturers;

    public function getName()
    {
        return 'manufacturer';
    }
}