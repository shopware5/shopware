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

use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Service;
use Shopware\Bundle\StoreFrontBundle\Gateway;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Service\Core
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class MediaService implements Service\MediaServiceInterface
{
    /**
     * @var Gateway\ProductMediaGatewayInterface
     */
    private $productMediaGateway;

    /**
     * @var Gateway\VariantMediaGatewayInterface
     */
    private $variantMediaGateway;

    /**
     * @var \Shopware_Components_Config
     */
    private $shopwareConfig;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Gateway\MediaGatewayInterface
     */
    private $mediaGateway;

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Gateway\MediaGatewayInterface $mediaGateway
     * @param Gateway\ProductMediaGatewayInterface $productMedia
     * @param Gateway\VariantMediaGatewayInterface $variantMedia
     * @param \Shopware_Components_Config $shopwareConfig
     */
    public function __construct(
        Gateway\MediaGatewayInterface $mediaGateway,
        Gateway\ProductMediaGatewayInterface $productMedia,
        Gateway\VariantMediaGatewayInterface $variantMedia,
        \Shopware_Components_Config $shopwareConfig
    ) {
        $this->productMediaGateway = $productMedia;
        $this->variantMediaGateway = $variantMedia;
        $this->shopwareConfig = $shopwareConfig;
        $this->mediaGateway = $mediaGateway;
    }

    /**
     * @param $id
     * @param Struct\ShopContextInterface $context
     * @return Struct\Media
     */
    public function get($id, Struct\ShopContextInterface $context)
    {
        return $this->mediaGateway->get($id, $context);
    }

    /**
     * @param $ids
     * @param Struct\ShopContextInterface $context
     * @return Struct\Media[] Indexed by the media id
     */
    public function getList($ids, Struct\ShopContextInterface $context)
    {
        return $this->mediaGateway->getList($ids, $context);
    }

    /**
     * @inheritdoc
     */
    public function getCover(Struct\BaseProduct $product, Struct\ShopContextInterface $context)
    {
        $covers = $this->getCovers([$product], $context);

        return array_shift($covers);
    }

    /**
     * @inheritdoc
     */
    public function getCovers($products, Struct\ShopContextInterface $context)
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

        $fallback = [];
        foreach ($products as $product) {
            if (!array_key_exists($product->getNumber(), $covers)) {
                $fallback[] = $product;
            }
        }

        $fallback = $this->productMediaGateway->getCovers($fallback, $context);

        return $covers + $fallback;
    }

    /**
     * @inheritdoc
     */
    public function getProductMedia(Struct\BaseProduct $product, Struct\ShopContextInterface $context)
    {
        $media = $this->getProductsMedia([$product], $context);

        return array_shift($media);
    }

    /**
     * @inheritdoc
     */
    public function getProductsMedia($products, Struct\ShopContextInterface $context)
    {
        $specifyMedia = $this->variantMediaGateway->getList($products, $context);

        $globalMedia = $this->productMediaGateway->getList($products, $context);

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
