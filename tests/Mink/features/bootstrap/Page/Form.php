<?php

use Behat\Mink\Driver\SahiDriver;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Behat\Context\Step;

class Form extends Page
{
    /**
     * @var string $path
     */
    protected $path = '?sViewport=ticket&sFid={formId}';

    public function checkCaptcha()
    {
        $captchaPlaceholder = $this->find('css', 'div.captcha-placeholder')->getAttribute('data-src');
        $captchaImage = $this->find('css', 'div.captcha-placeholder img')->getAttribute('src');
        $captchaHidden = $this->find('css', 'div.captcha-placeholder input')->getValue();

        if (($captchaPlaceholder !== '/shopware/widgets/Captcha/refreshCaptcha')
            || (strpos($captchaImage, 'data:image/png;base64') === false)
            || (empty($captchaHidden))
        ) {
            $message = 'There is no capture in this form!';
            throw new ResponseTextException($message, $this->getSession());
        }
    }

}