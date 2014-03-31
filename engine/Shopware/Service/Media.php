<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;
use Shopware\Gateway\ORM as Gateway;

/**
 * @package Shopware\Service
 */
class Media
{
    private $mediaGateway;

    private $shopwareConfig;

    function __construct(Gateway\Media $mediaGateway, \Shopware_Components_Config $shopwareConfig)
    {
        $this->mediaGateway = $mediaGateway;
        $this->shopwareConfig = $shopwareConfig;
    }

    /**
     * @param Struct\ProductMini $product
     * @return Struct\Media
     */
    public function getProductCover(Struct\ProductMini $product)
    {
        return $this->mediaGateway->getProductCover($product);
    }
}