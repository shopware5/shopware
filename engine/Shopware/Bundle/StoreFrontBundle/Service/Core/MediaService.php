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

use Shopware\Bundle\StoreFrontBundle\Gateway\MediaGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\ProductMediaGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\VariantMediaGateway;
use Shopware\Bundle\StoreFrontBundle\Service\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\VariantCoverServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class MediaService implements MediaServiceInterface
{
    /**
     * @var ProductMediaGateway
     */
    private $productMediaGateway;

    /**
     * @var VariantMediaGateway
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
     * @var VariantCoverServiceInterface
     */
    private $variantCoverService;

    /**
     * @param MediaGateway                 $mediaGateway
     * @param ProductMediaGateway          $productMedia
     * @param VariantMediaGateway          $variantMedia
     * @param \Shopware_Components_Config  $shopwareConfig
     * @param VariantCoverServiceInterface $variantCoverService
     */
    public function __construct(
        MediaGateway $mediaGateway,
        ProductMediaGateway $productMedia,
        VariantMediaGateway $variantMedia,
        \Shopware_Components_Config $shopwareConfig,
        VariantCoverServiceInterface $variantCoverService
    ) {
        $this->productMediaGateway = $productMedia;
        $this->variantMediaGateway = $variantMedia;
        $this->shopwareConfig = $shopwareConfig;
        $this->mediaGateway = $mediaGateway;
        $this->variantCoverService = $variantCoverService;
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

        return $this->variantCoverService->getList($products, $context);
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
