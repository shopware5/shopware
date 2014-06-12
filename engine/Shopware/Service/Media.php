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
     * @var Gateway\ProductMedia
     */
    private $productMediaGateway;

    /**
     * @var Gateway\VariantMedia
     */
    private $variantMediaGateway;

    /**
     * @var \Shopware_Components_Config
     */
    private $shopwareConfig;

    /**
     * @param \Shopware\Gateway\DBAL\ProductMedia $productMedia
     * @param \Shopware\Gateway\DBAL\VariantMedia $variantMedia
     * @param \Shopware_Components_Config $shopwareConfig
     */
    function __construct(
        Gateway\ProductMedia $productMedia,
        Gateway\VariantMedia $variantMedia,
        \Shopware_Components_Config $shopwareConfig
    ) {
        $this->productMediaGateway = $productMedia;
        $this->variantMediaGateway = $variantMedia;
        $this->shopwareConfig = $shopwareConfig;
    }

    /**
     * @param Struct\ListProduct[] $products
     * @param \Shopware\Struct\Context $context
     * @return \Shopware\Struct\Media[] Indexed by product number
     */
    public function getCovers(array $products, Struct\Context $context)
    {
        if ($this->shopwareConfig->get('forceArticleMainImageInListing')) {
            return $this->productMediaGateway->getCovers(
                $products,
                $context
            );
        }

        $covers = $this->variantMediaGateway->getCovers(
            $products,
            $context
        );

        $fallback = array();
        foreach ($products as $product) {
            if (!array_key_exists($product->getNumber(), $covers)) {
                $fallback[] = $product;
            }
        }

        $fallback = $this->productMediaGateway->getCovers($fallback, $context);

        return array_merge($covers, $fallback);
    }

    /**
     * @param Struct\ListProduct[] $products
     * @param Context $context
     * @return array Contains a list of Struct\Media[] classes which indexed with the product order number.
     */
    public function getMedia(array $products, Context $context)
    {
        $specifyMedia = $this->variantMediaGateway->getList($products, $context);

        $globalMedia = $this->productMediaGateway->getList($products, $context);

        $result = array();

        foreach ($products as $product) {

            $variantMedia = array();

            if (array_key_exists($product->getNumber(), $specifyMedia)) {
                $variantMedia = $specifyMedia[$product->getNumber()];
            }

            $productMedia = $globalMedia[$product->getNumber()];

            $result[$product->getNumber()] = array_merge(
                $variantMedia,
                array_diff_key($productMedia, $variantMedia)
            );
        }

        return $result;
    }
}
