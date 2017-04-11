<?php
declare(strict_types=1);
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

namespace Shopware\Bundle\CartBundle\Infrastructure\View;

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Product\CalculatedProduct;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\Media\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Product\ProductGateway;

class ViewProductTransformer implements ViewLineItemTransformerInterface
{
    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Product\ProductGateway
     */
    private $productGateway;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    public function __construct(
        ProductGateway $listProductGateway,
        MediaServiceInterface $mediaService
    ) {
        $this->productGateway = $listProductGateway;
        $this->mediaService = $mediaService;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(
        CalculatedCart $calculatedCart,
        ViewCart $templateCart,
        ShopContextInterface $context
    ): void {
        $collection = $calculatedCart->getCalculatedLineItems()->filterInstance(CalculatedProduct::class);

        if ($collection->count() === 0) {
            return;
        }

        $listProducts = $this->productGateway->getList($collection->getIdentifiers(), $context);

        $covers = $this->mediaService->getVariantCovers($listProducts, $context);

        foreach ($listProducts as $listProduct) {
            /** @var CalculatedProduct $calculated */
            $calculated = $collection->get($listProduct->getNumber());

            if (isset($covers[$listProduct->getNumber()])) {
                $listProduct->setCover($covers[$listProduct->getNumber()]);
            }

            $template = ViewProduct::createFromProducts($listProduct, $calculated);

            $templateCart->getViewLineItems()->add($template);
        }
    }
}
