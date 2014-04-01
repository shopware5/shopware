<?php

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Behat\Context\Step;


class CheckoutCart extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/checkout/cart';

    public function assertTotalSum($sum)
    {
        // todo: specify selector
        // todo: normalize sum?
        $assert = new \Behat\Mink\WebAssert($this->getSession());
        $assert->pageTextContains($sum);
    }

    public function proceedToCheckout()
    {
        $this->checkField('sAGB');
        $this->pressButton('basketButton');
    }
}
