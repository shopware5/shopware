<?php

namespace Emotion;

require_once('tests/Mink/features/bootstrap/Element/TextInputForm.php');

class NewsletterForm extends \TextInputForm
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div#footer');

    public $cssLocator = array(
        'textInput' => 'input#newsletter_input',
        'submitButton' => 'input#newsletter'
    );
}