<?php

namespace Shopware\Service\Core;

use Shopware\Struct;
use Shopware\Service;
use Shopware\Gateway;

class ProductLink implements Service\ProductLink
{
    /**
     * @var Gateway\Link
     */
    private $gateway;

    /**
     * @param Gateway\Link $gateway
     */
    function __construct(Gateway\Link $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\Link::get()
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return mixed
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
     * @see \Shopware\Service\ProductLink::get()
     *
     * @param array $products
     * @param Struct\Context $context
     * @return array Indexed by the product order number, each array element contains a list of \Shopware\Struct\Product\Link classes.
     */
    public function getList(array $products, Struct\Context $context)
    {
        return $this->gateway->getList($products, $context);
    }

}
