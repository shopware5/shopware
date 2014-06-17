<?php
namespace Responsive;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Behat\Context\Step;

class Account extends \Emotion\Account
{
    public $cssLocator = array(
        'pageIdentifier1'  => 'section.content-main > div > div.account--content',
        'pageIdentifier2'  => 'section.content-main > div > div.register--content',
        'payment' => 'div.account--payment.account--box strong',
        'logout' => 'div.account--menu-container a.link--logout'
    );

    protected function getRegistrationForm()
    {

    }
}
