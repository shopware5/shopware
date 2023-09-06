<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Shopware\Bundle\StoreFrontBundle\Gateway\ProductMediaGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\VariantMediaGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\VariantCoverServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class VariantCoverService implements VariantCoverServiceInterface
{
    private ProductMediaGatewayInterface $productMediaGateway;

    private VariantMediaGatewayInterface $variantMediaGateway;

    public function __construct(
        ProductMediaGatewayInterface $productMedia,
        VariantMediaGatewayInterface $variantMedia
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
            $context
        );

        $fallback = [];
        foreach ($products as $product) {
            if (!\array_key_exists($product->getNumber(), $covers)) {
                $fallback[] = $product;
            }
        }

        if (empty($fallback)) {
            return $covers;
        }

        $fallback = $this->productMediaGateway->getCovers($fallback, $context);

        return $covers + $fallback;
    }
}
