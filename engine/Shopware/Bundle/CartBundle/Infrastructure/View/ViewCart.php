<?php

namespace Shopware\Bundle\CartBundle\Infrastructure\View;

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\JsonSerializableTrait;
use Shopware\Bundle\CartBundle\Domain\Price\CartPrice;

class ViewCart implements \JsonSerializable
{
    use JsonSerializableTrait;

    /**
     * @var ViewLineItemCollection
     */
    protected $lineItems;

    /**
     * @var CalculatedCart
     */
    protected $calculatedCart;

    final private function __construct(CalculatedCart $calculatedCart)
    {
        $this->calculatedCart = $calculatedCart;
        $this->lineItems = new ViewLineItemCollection();
    }

    public static function createFromCalculatedCart(CalculatedCart $calculatedCart)
    {
        return new self($calculatedCart);
    }

    /**
     * @return CartPrice
     */
    public function getPrice()
    {
        return $this->calculatedCart->getPrice();
    }

    /**
     * @return ViewLineItemCollection
     */
    public function getLineItems()
    {
        return $this->lineItems;
    }

    /**
     * @return CalculatedCart
     */
    public function getCalculatedCart()
    {
        return $this->calculatedCart;
    }
}
