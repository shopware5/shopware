<?php

namespace Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

class AccountPayment extends Element
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div#selected_payment > div.inner_container');

    /** @var array $cssLocator */
    public $cssLocator = array(
        'currentMethod' => 'p'
    );

    /** @var array $namedSelectors */
    public $namedSelectors = array(
        'changeButton'  => array('de' => 'Zahlungsart ändern',       'en' => 'Change payment method')
    );

    public function getCurrentMethodsToCheck()
    {
        $locators = array('currentMethod');
        $elements = \Helper::findElements($this, $locators);

        $currentMethod = $elements['currentMethod']->getText();
        $currentMethod = str_word_count($currentMethod, 1);

        return array(
            'currentMethod' => $currentMethod[0]
        );
    }
}
