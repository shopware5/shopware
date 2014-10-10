<?php
namespace Page\Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Mink\Exception\ResponseTextException;

class Form extends Page
{
    /**
     * @var string $path
     */
    protected $path = '?sViewport=ticket&sFid={formId}';

    public $cssLocator = array(
        'captchaPlaceholder' => 'div.captcha-placeholder',
        'captchaImage' => 'div.captcha-placeholder img',
        'captchaHidden' => 'div.captcha-placeholder input'
    );

    public function checkCaptcha()
    {
        $element = \Helper::findElements($this);

        $captchaPlaceholder = $element['captchaPlaceholder']->getAttribute('data-src');
        $captchaImage = $element['captchaImage']->getAttribute('src');
        $captchaHidden = $element['captchaHidden']->getValue();

        if (($captchaPlaceholder !== '/shopware/widgets/Captcha/refreshCaptcha')
            || (strpos($captchaImage, 'data:image/png;base64') === false)
            || (empty($captchaHidden))
        ) {
            $message = 'There is no capture in this form!';
            \Helper::throwException($message);
        }
    }
}
