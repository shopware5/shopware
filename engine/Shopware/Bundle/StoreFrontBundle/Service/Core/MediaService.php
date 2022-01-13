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

namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Shopware\Bundle\StoreFrontBundle\Gateway\MediaGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\ProductMediaGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\VariantMediaGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\VariantCoverServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Media;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware_Components_Config;

class MediaService implements MediaServiceInterface
{
    private ProductMediaGatewayInterface $productMediaGateway;

    private VariantMediaGatewayInterface $variantMediaGateway;

    private Shopware_Components_Config $shopwareConfig;

    private MediaGatewayInterface $mediaGateway;

    private VariantCoverServiceInterface $variantCoverService;

    public function __construct(
        MediaGatewayInterface $mediaGateway,
        ProductMediaGatewayInterface $productMedia,
        VariantMediaGatewayInterface $variantMedia,
        Shopware_Components_Config $shopwareConfig,
        VariantCoverServiceInterface $variantCoverService
    ) {
        $this->productMediaGateway = $productMedia;
        $this->variantMediaGateway = $variantMedia;
        $this->shopwareConfig = $shopwareConfig;
        $this->mediaGateway = $mediaGateway;
        $this->variantCoverService = $variantCoverService;
    }

    /**
     * @param int $id
     *
     * @return Media|null
     */
    public function get($id, ShopContextInterface $context)
    {
        return $this->mediaGateway->get($id, $context);
    }

    /**
     * @param int[] $ids
     *
     * @return Media[] Indexed by the media id
     */
    public function getList($ids, ShopContextInterface $context)
    {
        return $this->mediaGateway->getList($ids, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getCover(BaseProduct $product, ShopContextInterface $context)
    {
        $covers = $this->getCovers([$product], $context);

        return array_shift($covers);
    }

    /**
     * {@inheritdoc}
     */
    public function getCovers($products, ShopContextInterface $context)
    {
        if ($this->shopwareConfig->get('forceArticleMainImageInListing')) {
            return $this->productMediaGateway->getCovers(
                $products,
                $context
            );
        }

        return $this->variantCoverService->getList($products, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductMedia(BaseProduct $product, ShopContextInterface $context)
    {
        $media = $this->getProductsMedia([$product], $context);

        return array_shift($media);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsMedia($products, ShopContextInterface $context)
    {
        $specifyMedia = $this->variantMediaGateway->getList($products, $context);

        $globalMedia = $this->productMediaGateway->getList($products, $context);

        $result = [];

        foreach ($products as $product) {
            $variantMedia = [];

            if (\array_key_exists($product->getNumber(), $specifyMedia)) {
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
