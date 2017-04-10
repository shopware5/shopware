<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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

namespace Shopware\Bundle\StoreFrontBundle\Media;

use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class MediaService implements MediaServiceInterface
{
    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Media\ProductMediaGateway
     */
    private $productMediaGateway;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Media\VariantMediaGateway
     */
    private $variantMediaGateway;

    /**
     * @var \Shopware_Components_Config
     */
    private $shopwareConfig;

    /**
     * @var MediaGateway
     */
    private $mediaGateway;

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Media\MediaGateway        $mediaGateway
     * @param ProductMediaGateway                                         $productMedia
     * @param \Shopware\Bundle\StoreFrontBundle\Media\VariantMediaGateway $variantMedia
     * @param \Shopware_Components_Config                                 $shopwareConfig
     */
    public function __construct(
        MediaGateway $mediaGateway,
        ProductMediaGateway $productMedia,
        VariantMediaGateway $variantMedia,
        \Shopware_Components_Config $shopwareConfig
    ) {
        $this->productMediaGateway = $productMedia;
        $this->variantMediaGateway = $variantMedia;
        $this->shopwareConfig = $shopwareConfig;
        $this->mediaGateway = $mediaGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($ids, ShopContextInterface $context)
    {
        return $this->mediaGateway->getList($ids, $context->getTranslationContext());
    }

    /**
     * {@inheritdoc}
     */
    public function getCovers($products, ShopContextInterface $context)
    {
        if ($this->shopwareConfig->get('forceArticleMainImageInListing')) {
            return $this->productMediaGateway->getCovers(
                $products,
                $context->getTranslationContext()
            );
        }

        return $this->getVariantCovers($products, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantCovers($products, ShopContextInterface $context)
    {
        $covers = $this->variantMediaGateway->getCovers(
            $products,
            $context->getTranslationContext()
        );

        $fallback = [];
        foreach ($products as $product) {
            if (!array_key_exists($product->getNumber(), $covers)) {
                $fallback[] = $product;
            }
        }

        if (empty($fallback)) {
            return $covers;
        }

        $fallback = $this->productMediaGateway->getCovers($fallback, $context->getTranslationContext());

        return $covers + $fallback;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsMedia($products, ShopContextInterface $context)
    {
        $specifyMedia = $this->variantMediaGateway->getList($products, $context->getTranslationContext());

        $globalMedia = $this->productMediaGateway->getList($products, $context->getTranslationContext());

        $result = [];

        foreach ($products as $product) {
            $variantMedia = [];

            if (array_key_exists($product->getNumber(), $specifyMedia)) {
                $variantMedia = $specifyMedia[$product->getNumber()];
            }

            if (!isset($globalMedia[$product->getNumber()])) {
                $result[$product->getNumber()] = $variantMedia;
                continue;
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
