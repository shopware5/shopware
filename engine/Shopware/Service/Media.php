<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;
use Shopware\Gateway as Gateway;

/**
 * @package Shopware\Service
 */
class Media
{
    /**
     * @var \Shopware\Gateway\Media
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
     * @param Struct\ListProduct $product
     * @return Struct\Media
     */
    public function getProductCover(Struct\ListProduct $product)
    {
        if ($this->shopwareConfig->get('forceArticleMainImageInListing')) {
            $cover = $this->mediaGateway->getProductCover($product);
        } else {
            $cover = $this->mediaGateway->getProductCover($product);
        }

        return $cover;
    }
}