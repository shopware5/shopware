<?php
namespace Shopware\Tests\Mink\Page\Responsive;

class Form extends \Shopware\Tests\Mink\Page\Emotion\Form
{
    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'captchaPlaceholder' => 'div.captcha--placeholder',
            'captchaImage' => 'div.captcha--placeholder img',
            'captchaHidden' => 'div.captcha--placeholder input'
        );
    }
}
