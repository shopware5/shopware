<?php

namespace Shopware\Service;

use Shopware\Gateway\DBAL as Gateway;
use Shopware\Struct;

class ProductLink
{
    /**
     * @var Gateway\Link
     */
    private $gateway;

    function __construct(Gateway\Link $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @param array $products
     * @param Struct\Context $context
     * @return Struct\Product\Link[]
     */
    public function getList(array $products, Struct\Context $context)
    {
        return $this->gateway->getList($products, $context);
    }

}
