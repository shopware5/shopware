<?php
namespace Responsive;

class Form extends \Emotion\Form
{
    public $cssLocator = array(
        'captchaPlaceholder' => 'div.captcha--placeholder',
        'captchaImage' => 'div.captcha--placeholder img',
        'captchaHidden' => 'div.captcha--placeholder input'
    );
}
