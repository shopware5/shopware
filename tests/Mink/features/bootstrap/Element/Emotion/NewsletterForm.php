<?php

namespace Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class NewsletterForm extends Element
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div#footer');

    public $cssLocator = array(
        'newsletterInput' => 'input#newsletter_input',
        'newsletterButton' => 'input#newsletter'
    );

    /**
     * @param string $keywords
     *
     * @return Page
     */
    public function subscribe($email)
    {
        $element = \Helper::findElements($this);

        $element['newsletterInput']->setValue($email);
        $element['newsletterButton']->press();
    }
}