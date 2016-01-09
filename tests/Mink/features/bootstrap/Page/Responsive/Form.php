<?php
namespace Shopware\Tests\Mink\Page\Responsive;

class Form extends \Shopware\Tests\Mink\Page\Emotion\Form
{
    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'captchaPlaceholder' => 'div.captcha--placeholder',
            'captchaImage' => 'div.captcha--placeholder img',
            'captchaHidden' => 'div.captcha--placeholder input',
            'inquiryForm' => 'form#support'
        ];
    }
}
