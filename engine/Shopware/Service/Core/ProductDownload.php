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
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\Download::get()
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return Struct\Product\Download[]
     */
    public function get(Struct\ListProduct $product, Struct\Context $context)
    {
        $downloads = $this->getList(array($product), $context);
        return array_shift($downloads);
    }

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Service\ProductDownload::get()
     *
     * @param array $products
     * @param Struct\Context $context
     * @return array Indexed by the product order number, each array element contains a list of \Shopware\Struct\Product\Download classes.
     */
    public function getList(array $products, Struct\Context $context)
    {
        return $this->gateway->getList($products, $context);
    }

}
