<?php

namespace Shopware\Gateway\Search\Facet;

use Shopware\Gateway\Search\Facet;

class Property extends Facet
{
    /**
     * @var array
     */
    public $properties;

    public function getName()
    {
        return 'property';
    }

}