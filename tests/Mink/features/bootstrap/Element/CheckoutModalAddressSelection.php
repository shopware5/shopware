<?php

namespace Shopware\Tests\Mink\Element;

use Shopware\Tests\Mink\Helper;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Element: CheckoutModalAddressSelection
 * Location: Checkout modal address selection
 *
 * Available retrievable properties:
 * -
 */
class CheckoutModalAddressSelection extends MultipleElement
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.address-manager--selection'];

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'createNewAddress' => ['de' => 'hier eine neue erstellen'],
        ];
    }
}
