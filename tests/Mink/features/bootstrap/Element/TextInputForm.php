<?php

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

abstract class TextInputForm extends Element
{
    public $cssLocator = array(
        'textInput' => '',
        'submitButton' => ''
    );

    /**
     * @param $text
     */
    public function submit($text)
    {
        $element = \Helper::findElements($this);

        $element['textInput']->setValue($text);
        $element['submitButton']->press();
    }
}