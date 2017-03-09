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

use Shopware\Bundle\StoreFrontBundle\Gateway;
use Shopware\Bundle\StoreFrontBundle\Service\VariantCoverServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class VariantCoverService implements VariantCoverServiceInterface
{
    /**
     * @var Gateway\ProductMediaGateway
     */
    private $productMediaGateway;

    /**
     * @var Gateway\VariantMediaGateway
     */
    private $variantMediaGateway;

    /**
     * @param Gateway\ProductMediaGateway $productMedia
     * @param Gateway\VariantMediaGateway $variantMedia
     */
    public function __construct(
        Gateway\ProductMediaGateway $productMedia,
        Gateway\VariantMediaGateway $variantMedia
    ) {
        $this->productMediaGateway = $productMedia;
        $this->variantMediaGateway = $variantMedia;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($products, Struct\ShopContextInterface $context)
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
