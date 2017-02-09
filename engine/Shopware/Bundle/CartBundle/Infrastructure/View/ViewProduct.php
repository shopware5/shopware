<?php

namespace Shopware\Bundle\CartBundle\Infrastructure\View;

use Shopware\Bundle\CartBundle\Domain\JsonSerializableTrait;
use Shopware\Bundle\CartBundle\Domain\Product\CalculatedProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\SimpleProduct;

class ViewProduct extends SimpleProduct implements ViewLineItemInterface
{
    use JsonSerializableTrait;

    /**
     * @var CalculatedProduct
     */
    protected $product;

    /**
     * @param CalculatedProduct $product
     */
    public function setLineItem($product)
    {
        $this->product = $product;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineItem()
    {
        return $this->product;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->name;
    }

    final public function __construct($id, $variantId, $number)
    {
        parent::__construct($id, $variantId, $number);
    }

    /**
     * @param SimpleProduct $simpleProduct
     * @param CalculatedProduct $calculatedProduct
     * @return ViewProduct
     */
    public static function createFromProducts(
        SimpleProduct $simpleProduct,
        CalculatedProduct $calculatedProduct
    ) {
        $product = new self(
            $simpleProduct->getId(),
            $simpleProduct->getVariantId(),
            $simpleProduct->getNumber()
        );
        foreach ($simpleProduct as $key => $value) {
            $product->$key = $value;
        }
        $product->setLineItem($calculatedProduct);
        return $product;
    }
}
