<?php

namespace Responsive;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use  Behat\Mink\Exception\ResponseTextException;

class AccountBilling extends \Emotion\AccountBilling
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.account--billing.account--box');

    public $cssLocator = array(
        'addressData' => 'p',
        'chooseOtherButton' => 'div.panel--actions > a:nth-of-type(1)',
        'changeAddressButton' => 'div.panel--actions > a:nth-of-type(2)'
    );
}