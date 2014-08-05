<?php

namespace Responsive;

use  Behat\Mink\Exception\ResponseTextException;

class AccountShipping extends \Emotion\AccountShipping
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.account--shipping.account--box');
}
