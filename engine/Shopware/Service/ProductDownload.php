<?php

namespace Shopware\Service;

use Shopware\Gateway\DBAL as Gateway;
use Shopware\Struct;

class ProductDownload
{
    /**
     * @var Gateway\Download
     */
    private $gateway;

    /**
     * @param Gateway\Download $gateway
     */
    function __construct(Gateway\Download $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @param array $products
     * @param Struct\Context $context
     * @return array
     */
    public function getList(array $products, Struct\Context $context)
    {
        return $this->gateway->getList($products, $context);
    }

}
