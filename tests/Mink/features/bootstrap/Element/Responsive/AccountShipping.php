<?php

namespace Element\Responsive;

/**
 * Element: AccountShipping
 * Location: Shipping address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class AccountShipping extends \Element\Emotion\AccountShipping
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.account--shipping.account--box'];
}
