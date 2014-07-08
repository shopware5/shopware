<?php

namespace Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use  Behat\Mink\Exception\ResponseTextException;

class AccountShipping extends AccountBilling
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.shipping > div.inner_container');
}