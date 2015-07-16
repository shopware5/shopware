<?php

namespace Shopware\Tests\Mink\Element\Responsive;

/**
 * Element: AccountPayment
 * Location: Payment box on account dashboard
 *
 * Available retrievable properties:
 * -
 */
class AccountPayment extends \Shopware\Tests\Mink\Element\Emotion\AccountPayment
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.account--payment.account--box'];
}
