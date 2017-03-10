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

use Shopware\Bundle\StoreFrontBundle\Gateway\ProductMediaGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\VariantMediaGateway;
use Shopware\Bundle\StoreFrontBundle\Service\VariantCoverServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class VariantCoverService implements VariantCoverServiceInterface
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
     * @param ProductMediaGateway $productMedia
     * @param VariantMediaGateway $variantMedia
     */
    public function __construct(
        ProductMediaGateway $productMedia,
        VariantMediaGateway $variantMedia
    ) {
        $this->productMediaGateway = $productMedia;
        $this->variantMediaGateway = $variantMedia;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($products, ShopContextInterface $context)
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
}
