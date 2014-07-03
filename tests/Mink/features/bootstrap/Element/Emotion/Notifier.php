<?php

namespace Emotion;

require_once('tests/Mink/features/bootstrap/Element/TextInputForm.php');

class Notifier extends \TextInputForm
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div#article_notification');

    public $cssLocator = array(
        'textInput' => 'input#txtmail',
        'submitButton' => 'input.button-right'
    );
}