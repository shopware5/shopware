<?php
namespace Page\Responsive;

class Form extends \Page\Emotion\Form
{
    public $cssLocator = array(
        'captchaPlaceholder' => 'div.captcha--placeholder',
        'captchaImage' => 'div.captcha--placeholder img',
        'captchaHidden' => 'div.captcha--placeholder input'
    );
}
