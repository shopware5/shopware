<?php

namespace Shopware\Tests\Unit\Bundle\CartBundle\Common;

use Shopware\Bundle\CartBundle\Domain\Cart\CartContextInterface;
use Shopware\Bundle\CartBundle\Infrastructure\Cart\CartContextServiceInterface;

class PresetContextService implements CartContextServiceInterface
{
    /**
     * @var CartContextInterface
     */
    public $context;

    /**
     * @param CartContextInterface $context
     */
    public function __construct(CartContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * @return CartContextInterface
     */
    public function getCartContext()
    {
        return $this->context;
    }
}
