<?php

namespace Shopware\Bundle\CartBundle\Infrastructure\View;

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Product\CalculatedProduct;
use Shopware\Bundle\StoreFrontBundle\Gateway\ListProductGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\VariantCoverServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ViewProductTransformer implements ViewLineItemTransformerInterface
{
    /**
     * @var ListProductGatewayInterface
     */
    private $listProductGateway;

    /**
     * @var VariantCoverServiceInterface
     */
    private $mediaService;

    /**
     * @param ListProductGatewayInterface $listProductGateway
     * @param VariantCoverServiceInterface $mediaService
     */
    public function __construct(
        ListProductGatewayInterface $listProductGateway,
        VariantCoverServiceInterface $mediaService
    ) {
        $this->listProductGateway = $listProductGateway;
        $this->mediaService = $mediaService;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(
        CalculatedCart $cart,
        ViewCart $templateCart,
        ShopContextInterface $context
    ) {
        $collection = $cart->getLineItems()->filterClass(CalculatedProduct::class);

        if ($collection->count() === 0) {
            return;
        }

        $listProducts = $this->listProductGateway->getList(
            $collection->getIdentifiers(),
            $context
        );

        $covers = $this->mediaService->getList($listProducts, $context);

        foreach ($listProducts as $listProduct) {
            /** @var CalculatedProduct $calculated */
            $calculated = $collection->get($listProduct->getNumber());

            if (isset($covers[$listProduct->getNumber()])) {
                $listProduct->setCover($covers[$listProduct->getNumber()]);
            }

            $template = ViewProduct::createFromProducts($listProduct, $calculated);

            $templateCart->getLineItems()->add($template);
        }
    }
}
