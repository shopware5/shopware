<?php

namespace Shopware\Service\Core;

use Shopware\Struct;
use Shopware\Service;
use Shopware\Gateway;

class ProductDownload implements Service\ProductDownload
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
     * @inheritdoc
     */
    public function get(Struct\ListProduct $product, Struct\Context $context)
    {
        $downloads = $this->getList(array($product), $context);
        return array_shift($downloads);
    }

    /**
     * @inheritdoc
     */
    public function getList(array $products, Struct\Context $context)
    {
        return $this->gateway->getList($products, $context);
    }

}
