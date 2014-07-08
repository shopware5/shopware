<?php
namespace Responsive;

use Behat\Mink\Driver\SahiDriver;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Behat\Context\Step;

class Form extends \Emotion\Form
{
    public $cssLocator = array(
        'captchaPlaceholder' => 'div.captcha--placeholder',
        'captchaImage' => 'div.captcha--placeholder img',
        'captchaHidden' => 'div.captcha--placeholder input'
    );
}