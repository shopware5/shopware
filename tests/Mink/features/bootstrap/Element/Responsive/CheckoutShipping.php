<?php

namespace Shopware\Tests\Mink\Element\Responsive;

/**
 * Element: CheckoutShipping
 * Location: Billing address box on checkout confirm page
 *
 * Available retrievable properties:
 * - ???
 */
class CheckoutShipping extends \Shopware\Tests\Mink\Element\Emotion\CheckoutShipping
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.shipping--panel'];
}
