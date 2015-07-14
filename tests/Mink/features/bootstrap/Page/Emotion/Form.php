<?php
namespace Page\Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Form extends Page implements \HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = 'shopware.php?sViewport=ticket&sFid={formId}';

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'captchaPlaceholder' => 'div.captcha-placeholder',
            'captchaImage' => 'div.captcha-placeholder img',
            'captchaHidden' => 'div.captcha-placeholder input'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array();
    }

    public function checkCaptcha()
    {
        $locators = array('captchaPlaceholder', 'captchaImage', 'captchaHidden');
        $element = \Helper::findElements($this, $locators);

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
