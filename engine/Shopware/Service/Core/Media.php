<?php

namespace Shopware\Service\Core;

use Shopware\Struct;
use Shopware\Service;
use Shopware\Gateway;

/**
 * @package Shopware\Service
 */
class Media implements Service\Media
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
     * @param Gateway\ProductMedia $productMedia
     * @param Gateway\VariantMedia $variantMedia
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
     * If the forceArticleMainImageInListing configuration is activated,
     * the function try to selects the first product media which has a configurator configuration
     * for the provided product.
     *
     * If no configurator image exist, the function returns the fallback main image of the product.
     *
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\VariantMedia::getCover()
     * @see \Shopware\Gateway\ProductMedia::getCover()
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return Struct\Media
     */
    public function getCover(Struct\ListProduct $product, Struct\Context $context)
    {
        $covers = $this->getCovers(array($product), $context);
        return array_shift($covers);
    }

    /**
     * @see \Shopware\Service\Media::getCover()
     *
     * @param Struct\ListProduct[] $products
     * @param \Shopware\Struct\Context $context
     * @return Struct\Media[] Indexed by product number
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
     * Selects first the media structs which have a configurator configuration for the provided product variant.
     * The normal product media structs which has no configuration, are appended to the configurator media structs.
     *
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\ProductMedia::get()
     * @see \Shopware\Gateway\VariantMedia::get()
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return Struct\Media[]
     */
    public function getProductMedia(Struct\ListProduct $product, Struct\Context $context)
    {
        $media = $this->getProductsMedia(array($product), $context);
        return array_shift($media);
    }

    /**
     * @see \Shopware\Service\Media::getProductMedia()
     *
     * @param Struct\ListProduct[] $products
     * @param Context $context
     * @return array Indexed by the product order number, each array element contains a \Shopware\Struct\Media array.
     */
    public function getProductsMedia(array $products, Context $context)
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
