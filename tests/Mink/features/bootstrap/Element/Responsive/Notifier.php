<?php

namespace Responsive;

class Notifier extends \Emotion\Notifier
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.product--notification');

    public $cssLocator = array(
        'textInput' => 'input.notification--field',
        'submitButton' => 'button.notification--button'
    );
}