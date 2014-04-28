<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;
use Shopware\Gateway\DBAL as Gateway;

/**
 * @package Shopware\Service
 */
class Media
{
    /**
     * @var Gateway\Media
     */
    private $mediaGateway;

    /**
     * @var \Shopware_Components_Config
     */
    private $shopwareConfig;

    /**
     * @param Gateway\Media $mediaGateway
     * @param \Shopware_Components_Config $shopwareConfig
     */
    function __construct(
        Gateway\Media $mediaGateway,
        \Shopware_Components_Config $shopwareConfig
    ) {
        $this->mediaGateway = $mediaGateway;
        $this->shopwareConfig = $shopwareConfig;
    }

    /**
     * @param Struct\ListProduct[] $products
     * @return \Shopware\Struct\Media[]
     */
    public function getCovers(array $products)
    {
        return $this->mediaGateway->getCovers($products);
    }

    /**
     * @param Struct\ListProduct $product
     * @return Struct\Media
     */
    public function getCover(Struct\ListProduct $product)
    {
        if ($this->shopwareConfig->get('forceArticleMainImageInListing')) {
            $cover = $this->mediaGateway->getCover($product);
        } else {
            $cover = $this->mediaGateway->getCover($product);
        }

        return $cover;
    }
}