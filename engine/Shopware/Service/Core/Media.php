<?php
/**
 * Shopware 4
 * Copyright © shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */
namespace Shopware\Service\Core;

use Shopware\Struct;
use Shopware\Service;
use Shopware\Gateway;

/**
 * @package Shopware\Service\Core
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
     * @inheritdoc
     */
    public function getCover(Struct\ListProduct $product, Struct\Context $context)
    {
        $covers = $this->getCovers(array($product), $context);
        return array_shift($covers);
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getProductMedia(Struct\ListProduct $product, Struct\Context $context)
    {
        $media = $this->getProductsMedia(array($product), $context);
        return array_shift($media);
    }

    /**
     * @inheritdoc
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
