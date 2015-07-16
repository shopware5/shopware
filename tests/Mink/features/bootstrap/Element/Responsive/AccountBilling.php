<?php

namespace Shopware\Tests\Mink\Element\Responsive;

/**
 * Element: AccountBilling
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class AccountBilling extends \Shopware\Tests\Mink\Element\Emotion\AccountBilling
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.account--billing.account--box'];
}
