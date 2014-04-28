<?php

namespace Shopware\Gateway\Search\Facet;

use Shopware\Gateway\Search\Facet;

class Price extends Facet
{
    /**
     * @var array
     */
    public $prices;

    /**
     * @var string
     */
    public $customerGroupKey;

    function __construct($customerGroupKey)
    {
        $this->customerGroupKey = $customerGroupKey;
    }

    public function getName()
    {
        return 'price';
    }

}