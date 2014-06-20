<?php

namespace Shopware\Service\Core;

use Shopware\Struct;
use Shopware\Service;
use Shopware\Gateway;


class Marketing implements Service\Marketing
{
    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @param \Shopware_Components_Config $config
     */
    function __construct(\Shopware_Components_Config $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getProductAttribute(Struct\ListProduct $product)
    {
        $attribute = new Struct\Product\MarketingAttribute();

        $today = new \DateTime();

        $diff = $today->diff($product->getCreatedAt());

        $marker = (int) $this->config->get('markAsNew');

        $attribute->setIsNew(
            ($diff->days <= $marker || $product->getCreatedAt() > $today)
        );

        $attribute->setComingSoon(
            ($product->getReleaseDate() && $product->getReleaseDate() > $today)
        );

        $attribute->setIsTopSeller(
            ($product->getSales() >= $this->config->get('markAsTopSeller'))
        );

        return $attribute;
    }
}