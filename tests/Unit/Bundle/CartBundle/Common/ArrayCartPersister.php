<?php

namespace Shopware\Tests\Unit\Bundle\CartBundle\Common;

use Shopware\Bundle\CartBundle\Domain\Cart\Cart;
use Shopware\Bundle\CartBundle\Domain\Cart\CartPersisterInterface;

class ArrayCartPersister implements CartPersisterInterface
{
    private $carts = [];

    private $signatures = [];

    /**
     * @param string $signature
     * @return string
     */
    public function loadBySignature($signature)
    {
        return $this->signatures[$signature];
    }

    public function load($token)
    {
        if (array_key_exists($token, $this->carts)) {
            return $this->carts[$token];
        }
        return null;
    }

    public function save(Cart $cart)
    {
        $this->carts[$cart->getToken()] = $cart;
    }
}
