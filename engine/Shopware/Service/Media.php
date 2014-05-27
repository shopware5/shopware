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
     * @var Gateway\Cover
     */
    private $coverGateway;

    /**
     * @var Gateway\Media
     */
    private $mediaGateway;

    /**
     * @var \Shopware_Components_Config
     */
    private $shopwareConfig;

    /**
     * @param Gateway\Cover $coverGateway
     * @param Gateway\Media $mediaGateway
     * @param \Shopware_Components_Config $shopwareConfig
     */
    function __construct(
        Gateway\Cover $coverGateway,
        Gateway\Media $mediaGateway,
        \Shopware_Components_Config $shopwareConfig
    ) {
        $this->coverGateway = $coverGateway;
        $this->mediaGateway = $mediaGateway;
        $this->shopwareConfig = $shopwareConfig;
    }

    /**
     * @param Struct\ListProduct[] $products
     * @return \Shopware\Struct\Media[] Indexed by product number
     */
    public function getProductsCovers(array $products)
    {
        return $this->coverGateway->getList($products);
    }

    /**
     * @param Struct\ListProduct[] $products
     * @param Context $context
     * @return array Contains a list of Struct\Media[] classes which indexed with the product order number.
     */
    public function getProductsMedia(array $products, Context $context)
    {
        $specifyMedia = $this->mediaGateway->getVariantsMedia($products);

        $globalMedia = $this->mediaGateway->getProductsMedia($products);

        $result = array();

        foreach ($products as $product) {

            $variantMedia = array();

            if (array_key_exists($product->getNumber(), $specifyMedia)) {
                $variantMedia = $specifyMedia[$product->getNumber()];
            }

            $productMedia = $globalMedia[$product->getId()];

            $result[$product->getNumber()] = array_merge(
                $variantMedia,
                array_diff_key($productMedia, $variantMedia)
            );
        }

        return $result;
    }
}