<?php
namespace  Shopware\Tests\Mink\Page\Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

class Form extends Page implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = 'shopware.php?sViewport=ticket&sFid={formId}';

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'captchaPlaceholder' => 'div.captcha-placeholder',
            'captchaImage' => 'div.captcha-placeholder img',
            'captchaHidden' => 'div.captcha-placeholder input'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [];
    }

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     * @throws \Exception
     */
    public function verifyPage()
    {
        $info = Helper::getPageInfo($this->getSession(), ['controller']);

        if($info['controller'] === 'forms') {
            return;
        }

        $message = ['You are not on a form page!', 'Current URL: ' . $this->getSession()->getCurrentUrl()];
        Helper::throwException($message);
    }

    /**
     * @throws \Exception
     */
    public function checkCaptcha()
    {
        $element = Helper::findElements($this, ['captchaPlaceholder', 'captchaImage', 'captchaHidden']);

        $captchaPlaceholder = $element['captchaPlaceholder']->getAttribute('data-src');
        $captchaImage = $element['captchaImage']->getAttribute('src');
        $captchaHidden = $element['captchaHidden']->getValue();

        if (($captchaPlaceholder !== '/shopware/widgets/Captcha/refreshCaptcha')
            || (strpos($captchaImage, 'data:image/png;base64') === false)
            || (empty($captchaHidden))
        ) {
            $message = 'There is no capture in this form!';
            Helper::throwException($message);
        }
    }
}
