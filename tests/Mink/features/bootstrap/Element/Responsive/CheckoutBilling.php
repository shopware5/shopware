<?php

namespace Shopware\Tests\Mink\Element\Responsive;

/**
 * Element: CheckoutBilling
 * Location: Billing address box on checkout confirm page
 *
 * Available retrievable properties:
 * - ???
 */
class CheckoutBilling extends \Shopware\Tests\Mink\Element\Emotion\CheckoutBilling
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.billing--panel'];
}
