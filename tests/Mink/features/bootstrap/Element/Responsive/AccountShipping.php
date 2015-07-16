<?php

namespace Shopware\Tests\Mink\Element\Responsive;

/**
 * Element: AccountShipping
 * Location: Shipping address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class AccountShipping extends \Shopware\Tests\Mink\Element\Emotion\AccountShipping
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.account--shipping.account--box'];
}
