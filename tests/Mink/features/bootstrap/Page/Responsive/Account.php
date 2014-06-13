<?php
namespace Responsive;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Behat\Context\Step;

class Account extends \Emotion\Account
{
    public $cssLocator = array(
        'payment' => 'div.account--payment.account--box strong',
        'logout' => 'div.account--menu-container a.link--logout'
    );

    protected function getRegistrationForm()
    {

    }
}
